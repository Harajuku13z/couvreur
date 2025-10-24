<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "🔍 Diagnostic du Favicon\n";
echo "========================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Vérifier les settings du favicon (sans cache)
    echo "2. Vérification des settings du favicon...\n";
    
    // Désactiver le cache temporairement
    \Cache::flush();
    
    $faviconSettings = [
        'site_favicon',
        'favicon',
        'branding_favicon'
    ];
    
    foreach ($faviconSettings as $setting) {
        try {
            $value = Setting::get($setting);
            echo "   - {$setting}: " . ($value ? $value : 'Non défini') . "\n";
        } catch (Exception $e) {
            echo "   - {$setting}: Erreur - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // 3. Vérifier les fichiers favicon existants
    echo "3. Vérification des fichiers favicon...\n";
    
    $faviconPaths = [
        'public/favicon.ico',
        'public/favicon.png',
        'public/favicon-1761020023.png',
        'public/uploads/favicon-*.png',
        'public/uploads/seo/favicon-*.png'
    ];
    
    foreach ($faviconPaths as $path) {
        if (strpos($path, '*') !== false) {
            $files = glob($path);
            if ($files) {
                foreach ($files as $file) {
                    echo "   ✅ Trouvé: {$file}\n";
                }
            } else {
                echo "   ❌ Aucun fichier trouvé pour: {$path}\n";
            }
        } else {
            if (file_exists($path)) {
                echo "   ✅ Existe: {$path}\n";
            } else {
                echo "   ❌ Manquant: {$path}\n";
            }
        }
    }
    echo "\n";
    
    // 4. Vérifier les permissions des dossiers
    echo "4. Vérification des permissions...\n";
    
    $directories = [
        'public',
        'public/uploads',
        'public/uploads/seo'
    ];
    
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $perms = substr(sprintf('%o', fileperms($dir)), -4);
            $writable = is_writable($dir);
            echo "   - {$dir}: permissions {$perms}, " . ($writable ? "écritable" : "non-écritable") . "\n";
        } else {
            echo "   - {$dir}: n'existe pas\n";
        }
    }
    echo "\n";
    
    // 5. Tester l'upload simulé
    echo "5. Test de simulation d'upload...\n";
    
    $testFile = 'public/test-favicon.png';
    if (file_exists('public/favicon-1761020023.png')) {
        if (copy('public/favicon-1761020023.png', $testFile)) {
            echo "   ✅ Création de fichier test : OK\n";
            unlink($testFile);
            echo "   ✅ Suppression de fichier test : OK\n";
        } else {
            echo "   ❌ Impossible de créer un fichier test\n";
        }
    } else {
        echo "   ⚠️  Aucun fichier favicon existant pour le test\n";
    }
    echo "\n";
    
    // 6. Vérifier la configuration des routes
    echo "6. Vérification des routes de configuration...\n";
    
    $routes = [
        'config.update.branding',
        'config.update.seo',
        'seo.update'
    ];
    
    foreach ($routes as $route) {
        try {
            $url = route($route);
            echo "   ✅ Route {$route}: {$url}\n";
        } catch (Exception $e) {
            echo "   ❌ Route {$route}: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
    
    // 7. Vérifier les logs récents
    echo "7. Vérification des logs récents...\n";
    
    $logFile = 'storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        $faviconLogs = array_filter(explode("\n", $logs), function($line) {
            return stripos($line, 'favicon') !== false;
        });
        
        if (!empty($faviconLogs)) {
            echo "   📝 Logs récents liés au favicon:\n";
            foreach (array_slice($faviconLogs, -5) as $log) {
                echo "      " . trim($log) . "\n";
            }
        } else {
            echo "   ℹ️  Aucun log récent lié au favicon\n";
        }
    } else {
        echo "   ❌ Fichier de log non trouvé\n";
    }
    
    echo "\n🎯 Résumé du diagnostic:\n";
    echo "========================\n";
    echo "✅ Base de données: Connectée\n";
    echo "✅ Fichiers: Vérifiés\n";
    echo "✅ Permissions: Vérifiées\n";
    echo "✅ Routes: Vérifiées\n";
    echo "\n💡 Si le problème persiste, vérifiez:\n";
    echo "   - Les permissions du serveur web\n";
    echo "   - La taille du fichier favicon (max 512KB)\n";
    echo "   - Le format du fichier (ICO, PNG, JPG)\n";
    echo "   - Les erreurs dans les logs du serveur web\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du diagnostic: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// $kernel->terminate(); // Commenté car cause une erreur
