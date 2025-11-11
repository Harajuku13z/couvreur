<?php

/**
 * Script pour analyser les packages inutilisés dans vendor
 * et proposer leur suppression
 */

class PackageAnalyzer
{
    private $vendorPath;
    private $appPath;
    private $packages = [];
    private $usedPackages = [];
    private $unusedPackages = [];
    private $devPackages = [];

    public function __construct($vendorPath = null, $appPath = null)
    {
        $this->vendorPath = $vendorPath ?: __DIR__ . '/vendor';
        $this->appPath = $appPath ?: __DIR__ . '/app';
        
        if (!is_dir($this->vendorPath)) {
            throw new Exception("Le dossier vendor n'existe pas : {$this->vendorPath}");
        }
    }

    /**
     * Analyser tous les packages
     */
    public function analyze()
    {
        echo "🔍 Analyse des packages...\n\n";
        
        $this->loadPackages();
        $this->identifyDevPackages();
        $this->scanCodeUsage();
        $this->identifyUnusedPackages();
        $this->displayResults();
    }

    /**
     * Charger tous les packages depuis composer
     */
    private function loadPackages()
    {
        $composerLock = __DIR__ . '/composer.lock';
        if (!file_exists($composerLock)) {
            throw new Exception("composer.lock n'existe pas");
        }

        $lockData = json_decode(file_get_contents($composerLock), true);
        
        foreach ($lockData['packages'] as $package) {
            $this->packages[$package['name']] = [
                'name' => $package['name'],
                'version' => $package['version'],
                'type' => 'production'
            ];
        }

        foreach ($lockData['packages-dev'] ?? [] as $package) {
            $this->packages[$package['name']] = [
                'name' => $package['name'],
                'version' => $package['version'],
                'type' => 'dev'
            ];
        }

        echo "📦 Packages trouvés: " . count($this->packages) . "\n";
    }

    /**
     * Identifier les packages de développement
     */
    private function identifyDevPackages()
    {
        $composerJson = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);
        $devRequire = $composerJson['require-dev'] ?? [];

        foreach ($devRequire as $package => $version) {
            $this->devPackages[] = $package;
        }

        echo "🧪 Packages de développement: " . count($this->devPackages) . "\n";
    }

    /**
     * Scanner l'utilisation dans le code
     */
    private function scanCodeUsage()
    {
        $files = $this->getPhpFiles($this->appPath);
        $files = array_merge($files, $this->getPhpFiles(__DIR__ . '/routes'));
        $files = array_merge($files, $this->getPhpFiles(__DIR__ . '/config'));
        $files = array_merge($files, $this->getPhpFiles(__DIR__ . '/database'));

        echo "📄 Fichiers à analyser: " . count($files) . "\n\n";

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $this->checkPackageUsage($content);
        }
    }

    /**
     * Obtenir tous les fichiers PHP
     */
    private function getPhpFiles($dir)
    {
        $files = [];
        if (!is_dir($dir)) {
            return $files;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Vérifier l'utilisation d'un package dans le code
     */
    private function checkPackageUsage($content)
    {
        foreach ($this->packages as $packageName => $packageInfo) {
            // Extraire le namespace principal du package
            $namespace = $this->extractNamespace($packageName);
            
            // Vérifier les use statements
            if (preg_match('/\buse\s+' . preg_quote($namespace, '/') . '/i', $content)) {
                $this->usedPackages[$packageName] = true;
                continue;
            }

            // Vérifier les appels directs
            if (preg_match('/\b' . preg_quote($namespace, '/') . '\b/i', $content)) {
                $this->usedPackages[$packageName] = true;
                continue;
            }

            // Vérifier les facades Laravel
            if (preg_match('/Facades\\\\' . preg_quote($namespace, '/') . '/i', $content)) {
                $this->usedPackages[$packageName] = true;
                continue;
            }

            // Packages spéciaux à vérifier
            $specialChecks = [
                'laravel/framework' => ['Illuminate', 'Laravel', 'Route', 'DB', 'Auth', 'Cache', 'Log', 'Mail', 'Storage', 'View', 'Config', 'Session', 'Request', 'Response', 'Validator', 'Hash', 'Str', 'Arr', 'Collection', 'Carbon'],
                'spatie/laravel-sitemap' => ['Spatie\\Sitemap', 'Sitemap'],
                'spatie/laravel-analytics' => ['Spatie\\Analytics', 'Analytics'],
                'barryvdh/laravel-dompdf' => ['Barryvdh\\DomPDF', 'PDF', 'Dompdf'],
                'dompdf/dompdf' => ['Dompdf\\Dompdf'],
                'phpmailer/phpmailer' => ['PHPMailer\\PHPMailer', 'PHPMailer'],
                'openai-php/laravel' => ['OpenAI\\Laravel', 'OpenAI'],
                'google/apiclient' => ['Google_Client', 'Google\\Client'],
                'adnanhussainturki/google-my-business-php' => ['GoogleMyBusiness'],
            ];

            if (isset($specialChecks[$packageName])) {
                foreach ($specialChecks[$packageName] as $check) {
                    if (stripos($content, $check) !== false) {
                        $this->usedPackages[$packageName] = true;
                        break;
                    }
                }
            }
        }
    }

    /**
     * Extraire le namespace principal d'un package
     */
    private function extractNamespace($packageName)
    {
        $parts = explode('/', $packageName);
        $vendor = $parts[0];
        $package = $parts[1] ?? '';

        // Cas spéciaux
        $mapping = [
            'laravel/framework' => 'Illuminate',
            'spatie/laravel-sitemap' => 'Spatie\\Sitemap',
            'spatie/laravel-analytics' => 'Spatie\\Analytics',
            'barryvdh/laravel-dompdf' => 'Barryvdh\\DomPDF',
            'dompdf/dompdf' => 'Dompdf',
            'phpmailer/phpmailer' => 'PHPMailer',
            'openai-php/laravel' => 'OpenAI',
            'google/apiclient' => 'Google',
        ];

        if (isset($mapping[$packageName])) {
            return $mapping[$packageName];
        }

        // Génération automatique
        $vendorParts = explode('-', $vendor);
        $packageParts = explode('-', $package);
        
        $vendorNamespace = implode('', array_map('ucfirst', $vendorParts));
        $packageNamespace = implode('', array_map('ucfirst', $packageParts));
        
        return $vendorNamespace . '\\' . $packageNamespace;
    }

    /**
     * Identifier les packages inutilisés
     */
    private function identifyUnusedPackages()
    {
        // Packages toujours nécessaires (core Laravel)
        $alwaysNeeded = [
            'laravel/framework',
            'laravel/tinker',
            'symfony/',
            'psr/',
            'monolog/monolog',
            'nesbot/carbon',
            'guzzlehttp/',
            'league/',
            'doctrine/',
            'brick/',
            'dflydev/',
            'dragonmantank/',
            'egulias/',
            'fruitcake/',
            'graham-campbell/',
            'laravel/prompts',
            'laravel/serializable-closure',
            'nette/',
            'nikic/',
            'nunomaduro/',
            'php-http/',
            'phpoption/',
            'phpseclib/',
            'ramsey/',
            'sabberworm/',
            'tijsverkoyen/',
            'vlucas/',
            'voku/',
            'webmozart/',
            'masterminds/',
            'paragonie/',
            'ralouphie/',
            'theseer/',
            'nicmart/',
            'filp/',
            'firebase/',
            'grpc/',
            'hamcrest/',
            'myclabs/',
            'phar-io/',
        ];

        foreach ($this->packages as $packageName => $packageInfo) {
            // Ignorer les packages toujours nécessaires
            $isAlwaysNeeded = false;
            foreach ($alwaysNeeded as $needed) {
                if (strpos($packageName, $needed) === 0) {
                    $isAlwaysNeeded = true;
                    break;
                }
            }

            if ($isAlwaysNeeded) {
                continue;
            }

            // Packages de dev sont toujours considérés comme inutilisés en production
            if (in_array($packageName, $this->devPackages)) {
                $this->unusedPackages[$packageName] = [
                    'reason' => 'Package de développement',
                    'safe' => true
                ];
                continue;
            }

            // Si pas utilisé et pas toujours nécessaire
            if (!isset($this->usedPackages[$packageName])) {
                $this->unusedPackages[$packageName] = [
                    'reason' => 'Non utilisé dans le code',
                    'safe' => $this->isSafeToRemove($packageName)
                ];
            }
        }
    }

    /**
     * Vérifier si un package peut être supprimé en toute sécurité
     */
    private function isSafeToRemove($packageName)
    {
        // Packages qui peuvent être supprimés
        $safeToRemove = [
            'spatie/browsershot',
            'spatie/crawler',
            'spatie/temporary-directory',
            'spatie/robots-txt',
            'staabm/side-effects-detector',
        ];

        foreach ($safeToRemove as $safe) {
            if (strpos($packageName, $safe) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Afficher les résultats
     */
    private function displayResults()
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "📊 RÉSULTATS DE L'ANALYSE\n";
        echo str_repeat("=", 80) . "\n\n";

        echo "✅ Packages utilisés: " . count($this->usedPackages) . "\n";
        echo "❌ Packages potentiellement inutilisés: " . count($this->unusedPackages) . "\n\n";

        // Packages de développement (sûrs à supprimer en production)
        $devUnused = array_filter($this->unusedPackages, function($info, $name) {
            return $info['reason'] === 'Package de développement';
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($devUnused)) {
            echo "🧪 PACKAGES DE DÉVELOPPEMENT (sûrs à supprimer en production):\n";
            echo str_repeat("-", 80) . "\n";
            foreach ($devUnused as $package => $info) {
                echo "  ❌ {$package}\n";
            }
            echo "\n";
        }

        // Autres packages inutilisés
        $otherUnused = array_filter($this->unusedPackages, function($info, $name) {
            return $info['reason'] !== 'Package de développement';
        }, ARRAY_FILTER_USE_BOTH);

        if (!empty($otherUnused)) {
            echo "⚠️  AUTRES PACKAGES POTENTIELLEMENT INUTILISÉS:\n";
            echo str_repeat("-", 80) . "\n";
            foreach ($otherUnused as $package => $info) {
                $safe = $info['safe'] ? '✅ Sûr' : '⚠️  À vérifier';
                echo "  {$safe} {$package} ({$info['reason']})\n";
            }
            echo "\n";
        }

        // Recommandations
        echo "💡 RECOMMANDATIONS:\n";
        echo str_repeat("-", 80) . "\n";
        echo "1. En production, supprimez les packages de développement:\n";
        echo "   composer install --no-dev --optimize-autoloader\n\n";
        
        if (!empty($otherUnused)) {
            echo "2. Packages à vérifier manuellement avant suppression:\n";
            foreach ($otherUnused as $package => $info) {
                if (!$info['safe']) {
                    echo "   - {$package}\n";
                }
            }
            echo "\n";
        }

        // Générer un script de suppression
        $this->generateRemovalScript($devUnused, $otherUnused);
    }

    /**
     * Générer un script pour supprimer les packages inutilisés
     */
    private function generateRemovalScript($devPackages, $otherPackages)
    {
        $script = "#!/bin/bash\n\n";
        $script .= "# Script généré automatiquement pour supprimer les packages inutilisés\n";
        $script .= "# ATTENTION: Vérifiez avant d'exécuter!\n\n";

        if (!empty($devPackages)) {
            $script .= "# Supprimer les packages de développement\n";
            foreach (array_keys($devPackages) as $package) {
                $script .= "# composer remove {$package}\n";
            }
            $script .= "\n";
        }

        if (!empty($otherPackages)) {
            $safePackages = array_filter($otherPackages, function($info) {
                return $info['safe'];
            });

            if (!empty($safePackages)) {
                $script .= "# Packages sûrs à supprimer\n";
                foreach (array_keys($safePackages) as $package) {
                    $script .= "# composer remove {$package}\n";
                }
            }
        }

        file_put_contents(__DIR__ . '/remove-unused-packages.sh', $script);
        chmod(__DIR__ . '/remove-unused-packages.sh', 0755);
        
        echo "📝 Script de suppression généré: remove-unused-packages.sh\n";
        echo "   (Décommentez les lignes et exécutez pour supprimer)\n\n";
    }
}

// Exécution
try {
    $analyzer = new PackageAnalyzer();
    $analyzer->analyze();
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

