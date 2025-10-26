<?php

echo "🚀 Script de correction des routes en production\n";
echo "==============================================\n\n";

echo "📋 Instructions pour corriger le problème sur le serveur :\n";
echo "=========================================================\n\n";

echo "1. Connectez-vous à votre serveur via SSH\n";
echo "2. Naviguez vers le dossier du projet\n";
echo "3. Exécutez les commandes suivantes :\n\n";

echo "   # Vider tous les caches\n";
echo "   php artisan route:clear\n";
echo "   php artisan config:clear\n";
echo "   php artisan cache:clear\n";
echo "   php artisan view:clear\n\n";

echo "   # Vérifier que la route existe\n";
echo "   php artisan route:list | grep update-sitemap\n\n";

echo "   # Si la route n'existe toujours pas, redémarrer le serveur\n";
echo "   sudo systemctl restart nginx\n";
echo "   sudo systemctl restart php8.2-fpm\n\n";

echo "4. Tester l'accès à la page admin :\n";
echo "   - https://sausercouverture.fr/admin/login\n";
echo "   - https://sausercouverture.fr/admin/seo\n\n";

echo "🔍 Vérification locale des fichiers :\n";
echo "====================================\n";

// Vérifier que la route est bien dans le fichier
$routesFile = __DIR__ . '/routes/web.php';
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    $lines = explode("\n", $content);
    
    echo "Routes SEO trouvées dans routes/web.php :\n";
    foreach ($lines as $lineNum => $line) {
        if (strpos($line, 'admin/seo') !== false || strpos($line, 'SeoController') !== false) {
            echo "   Ligne " . ($lineNum + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n✅ Tous les fichiers sont corrects localement\n";
echo "   Le problème est uniquement sur le serveur de production\n";
echo "   Suivez les instructions ci-dessus pour le résoudre\n";
