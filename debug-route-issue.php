<?php

echo "🔍 Diagnostic du problème de route admin.seo.update-sitemap\n";
echo "========================================================\n\n";

// Test 1: Vérifier les routes localement
echo "1. Vérification des routes localement...\n";
$output = shell_exec('php artisan route:list | grep update-sitemap');
if ($output && strpos($output, 'admin.seo.update-sitemap') !== false) {
    echo "✅ Route trouvée localement: " . trim($output) . "\n";
} else {
    echo "❌ Route non trouvée localement\n";
}

// Test 2: Vérifier le fichier de routes
echo "\n2. Vérification du fichier de routes...\n";
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    if (strpos($content, 'update-sitemap') !== false) {
        echo "✅ Route update-sitemap trouvée dans routes/web.php\n";
        
        // Extraire la ligne exacte
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, 'update-sitemap') !== false) {
                echo "   Ligne " . ($lineNum + 1) . ": " . trim($line) . "\n";
            }
        }
    } else {
        echo "❌ Route update-sitemap non trouvée dans routes/web.php\n";
    }
} else {
    echo "❌ Fichier routes/web.php non trouvé\n";
}

// Test 3: Vérifier le contrôleur
echo "\n3. Vérification du contrôleur...\n";
$controllerFile = __DIR__ . '/app/Http/Controllers/SeoController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'public function updateSitemap') !== false) {
        echo "✅ Méthode updateSitemap trouvée dans SeoController\n";
    } else {
        echo "❌ Méthode updateSitemap non trouvée dans SeoController\n";
    }
} else {
    echo "❌ Fichier SeoController non trouvé\n";
}

// Test 4: Vérifier le cache des routes
echo "\n4. Vérification du cache des routes...\n";
$cacheFile = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($cacheFile)) {
    echo "⚠️  Cache des routes trouvé - il faut le vider\n";
    echo "   Commande: php artisan route:clear\n";
} else {
    echo "✅ Pas de cache des routes\n";
}

echo "\n🔧 Solutions recommandées :\n";
echo "==========================\n";
echo "1. Vider le cache des routes sur le serveur :\n";
echo "   php artisan route:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n\n";

echo "2. Vérifier que les fichiers sont bien déployés :\n";
echo "   - routes/web.php contient la route\n";
echo "   - app/Http/Controllers/SeoController.php contient la méthode\n\n";

echo "3. Redémarrer le serveur web si nécessaire\n\n";

echo "4. Tester la route après le déploiement :\n";
echo "   php artisan route:list | grep update-sitemap\n";
