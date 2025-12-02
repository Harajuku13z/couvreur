<?php
/**
 * Script pour vider les caches et optimiser les routes
 * À exécuter sur le serveur de production après un déploiement
 */

echo "🔄 Vidage des caches...\n";

// Vider le cache des routes
try {
    if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
        unlink(base_path('bootstrap/cache/routes-v7.php'));
        echo "✅ Cache des routes vidé\n";
    }
} catch (\Exception $e) {
    echo "⚠️ Erreur vidage cache routes: " . $e->getMessage() . "\n";
}

// Vider le cache de configuration
try {
    if (file_exists(base_path('bootstrap/cache/config.php'))) {
        unlink(base_path('bootstrap/cache/config.php'));
        echo "✅ Cache de configuration vidé\n";
    }
} catch (\Exception $e) {
    echo "⚠️ Erreur vidage cache config: " . $e->getMessage() . "\n";
}

// Vider le cache de l'application
try {
    $cachePath = storage_path('framework/cache');
    if (is_dir($cachePath)) {
        $files = glob($cachePath . '/data/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✅ Cache de l'application vidé\n";
    }
} catch (\Exception $e) {
    echo "⚠️ Erreur vidage cache app: " . $e->getMessage() . "\n";
}

// Vérifier que la route existe
echo "\n📋 Vérification de la route devis.public.pdf...\n";
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $routes = \Illuminate\Support\Facades\Route::getRoutes();
    $route = $routes->getByName('devis.public.pdf');
    
    if ($route) {
        echo "✅ Route 'devis.public.pdf' trouvée !\n";
        echo "   URI: " . $route->uri() . "\n";
        echo "   Méthode: " . implode('|', $route->methods()) . "\n";
        echo "   Action: " . $route->getActionName() . "\n";
    } else {
        echo "❌ Route 'devis.public.pdf' NON trouvée !\n";
        echo "   Vérifiez le fichier routes/web.php\n";
    }
} catch (\Exception $e) {
    echo "⚠️ Erreur lors de la vérification: " . $e->getMessage() . "\n";
}

echo "\n✅ Script terminé !\n";
echo "💡 Sur le serveur, exécutez aussi:\n";
echo "   php artisan route:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan route:cache\n";

