<?php

/**
 * Script de nettoyage du dossier vendor pour réduire le nombre d'inodes
 * Supprime les fichiers inutiles (tests, docs, exemples) sans casser le site
 */

class VendorCleaner
{
    private $vendorPath;
    private $deletedFiles = 0;
    private $deletedDirs = 0;
    private $freedSpace = 0;
    private $errors = [];

    // Dossiers/fichiers à supprimer en toute sécurité
    private $safeToDelete = [
        'tests',
        'test',
        'Tests',
        'Test',
        '.tests',
        'docs',
        'doc',
        'Docs',
        'documentation',
        'examples',
        'example',
        'Examples',
        'samples',
        'sample',
        '.git',
        '.github',
        '.gitignore',
        '.gitattributes',
        'CHANGELOG.md',
        'CHANGELOG',
        'CHANGES.md',
        'CHANGES',
        'LICENSE.txt',
        'LICENSE',
        'LICENSE.md',
        'README.md',
        'README',
        'README.txt',
        'CONTRIBUTING.md',
        'CONTRIBUTING',
        'UPGRADING.md',
        'UPGRADING',
        'phpunit.xml',
        'phpunit.xml.dist',
        '.phpunit.result.cache',
        '.phpunit.cache',
        'phpstan.neon',
        'phpstan.neon.dist',
        'psalm.xml',
        'psalm.xml.dist',
        '.php_cs',
        '.php_cs.dist',
        'phpcs.xml',
        'phpcs.xml.dist',
        '.travis.yml',
        '.scrutinizer.yml',
        '.coveralls.yml',
        'Makefile',
        'build.xml',
        'composer.lock',
    ];

    // Extensions de fichiers à supprimer
    private $safeExtensions = [
        '.md',
        '.txt',
        '.yml',
        '.yaml',
        '.json', // sauf composer.json et package.json
        '.xml', // sauf ceux nécessaires
        '.dist',
        '.example',
        '.sample',
    ];

    public function __construct($vendorPath = null)
    {
        $this->vendorPath = $vendorPath ?: __DIR__ . '/vendor';
        
        if (!is_dir($this->vendorPath)) {
            throw new Exception("Le dossier vendor n'existe pas : {$this->vendorPath}");
        }
    }

    /**
     * Nettoyer le dossier vendor
     */
    public function clean($dryRun = false)
    {
        echo "🧹 Nettoyage du dossier vendor...\n";
        echo "Mode: " . ($dryRun ? "DRY RUN (simulation)" : "SUPPRESSION RÉELLE") . "\n\n";

        $packages = glob($this->vendorPath . '/*', GLOB_ONLYDIR);
        
        foreach ($packages as $packagePath) {
            $packageName = basename($packagePath);
            echo "📦 Traitement de: {$packageName}\n";
            
            $this->cleanPackage($packagePath, $dryRun);
        }

        $this->displaySummary();
    }

    /**
     * Nettoyer un package spécifique
     */
    private function cleanPackage($packagePath, $dryRun)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packagePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            $relativePath = str_replace($packagePath . '/', '', $path->getPathname());
            $name = $path->getFilename();
            $extension = $path->getExtension();

            // Ignorer composer.json et autoload files
            if ($name === 'composer.json' || $name === 'autoload.php' || $name === 'autoload_real.php') {
                continue;
            }

            // Supprimer les dossiers/fichiers dans la liste safe
            if (in_array($name, $this->safeToDelete)) {
                $this->delete($path, $dryRun, "Dossier/fichier standard: {$name}");
                continue;
            }

            // Supprimer les fichiers avec extensions sûres (sauf composer.json)
            if ($path->isFile() && in_array('.' . $extension, $this->safeExtensions)) {
                // Ne pas supprimer composer.json, package.json, etc.
                if (in_array($name, ['composer.json', 'package.json', 'autoload.php'])) {
                    continue;
                }
                $this->delete($path, $dryRun, "Extension sûre: .{$extension}");
                continue;
            }

            // Supprimer les dossiers de tests (variations)
            if (preg_match('/^(tests?|test-|\.test|specs?|spec-|\.spec)/i', $name)) {
                $this->delete($path, $dryRun, "Dossier de test: {$name}");
                continue;
            }

            // Supprimer les fichiers de documentation
            if (preg_match('/^(readme|changelog|license|contributing|upgrading|history)/i', $name)) {
                $this->delete($path, $dryRun, "Documentation: {$name}");
                continue;
            }
        }
    }

    /**
     * Supprimer un fichier ou dossier
     */
    private function delete($path, $dryRun, $reason = '')
    {
        try {
            $size = 0;
            if ($path->isFile()) {
                $size = $path->getSize();
            } elseif ($path->isDir()) {
                $size = $this->getDirSize($path->getPathname());
            }

            if (!$dryRun) {
                if ($path->isDir()) {
                    $this->deleteDirectory($path->getPathname());
                    $this->deletedDirs++;
                } else {
                    unlink($path->getPathname());
                    $this->deletedFiles++;
                }
                $this->freedSpace += $size;
            } else {
                // Simulation
                if ($path->isDir()) {
                    $this->deletedDirs++;
                } else {
                    $this->deletedFiles++;
                }
                $this->freedSpace += $size;
            }

            $type = $path->isDir() ? '📁' : '📄';
            echo "  {$type} " . ($dryRun ? "[SIMULATION] " : "") . "Supprimé: " . basename($path->getPathname());
            if ($reason) {
                echo " ({$reason})";
            }
            echo "\n";
        } catch (Exception $e) {
            $this->errors[] = "Erreur lors de la suppression de {$path->getPathname()}: " . $e->getMessage();
        }
    }

    /**
     * Supprimer un dossier récursivement
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($dir);
    }

    /**
     * Calculer la taille d'un dossier
     */
    private function getDirSize($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $filePath = $dir . '/' . $file;
                if (is_dir($filePath)) {
                    $size += $this->getDirSize($filePath);
                } else {
                    $size += filesize($filePath);
                }
            }
        }
        return $size;
    }

    /**
     * Afficher le résumé
     */
    private function displaySummary()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RÉSUMÉ DU NETTOYAGE\n";
        echo str_repeat("=", 60) . "\n";
        echo "📁 Dossiers supprimés: {$this->deletedDirs}\n";
        echo "📄 Fichiers supprimés: {$this->deletedFiles}\n";
        echo "💾 Espace libéré: " . $this->formatBytes($this->freedSpace) . "\n";
        echo "📊 Total inodes libérés: " . ($this->deletedFiles + $this->deletedDirs) . "\n";
        
        if (!empty($this->errors)) {
            echo "\n⚠️  Erreurs rencontrées:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
        }
        
        echo "\n";
    }

    /**
     * Formater les bytes en format lisible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Exécution du script
try {
    $dryRun = in_array('--dry-run', $argv) || in_array('-d', $argv);
    $vendorPath = null;
    
    // Chercher l'argument --vendor-path
    foreach ($argv as $key => $arg) {
        if ($arg === '--vendor-path' && isset($argv[$key + 1])) {
            $vendorPath = $argv[$key + 1];
            break;
        }
    }
    
    $cleaner = new VendorCleaner($vendorPath);
    $cleaner->clean($dryRun);
    
    if ($dryRun) {
        echo "\n💡 Pour effectuer la suppression réelle, exécutez:\n";
        echo "   php cleanup-vendor.php\n";
    } else {
        echo "\n✅ Nettoyage terminé avec succès!\n";
        echo "💡 Vous pouvez maintenant exécuter:\n";
        echo "   composer dump-autoload --optimize\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

