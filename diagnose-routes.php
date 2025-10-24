<?php
/**
 * Script de diagnostic des routes
 */

echo "🔍 Diagnostic des routes...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Vérifier les routes
echo "\n📋 Routes liées aux annonces :\n";
$output = shell_exec('php artisan route:list | grep -E "(ads|admin)"');
echo $output ?: "Aucune route trouvée\n";

echo "\n🔍 Recherche spécifique de admin.ads.index :\n";
$output = shell_exec('php artisan route:list | grep "admin.ads.index"');
echo $output ?: "Route admin.ads.index non trouvée\n";

echo "\n📁 Vérification du fichier routes/web.php :\n";
if (file_exists('routes/web.php')) {
    $content = file_get_contents('routes/web.php');
    if (strpos($content, 'admin.ads.index') !== false) {
        echo "✅ Route admin.ads.index trouvée dans routes/web.php\n";
    } else {
        echo "❌ Route admin.ads.index non trouvée dans routes/web.php\n";
    }
} else {
    echo "❌ Fichier routes/web.php non trouvé\n";
}

echo "\n🧹 Nettoyage du cache...\n";
shell_exec('php artisan route:clear');
shell_exec('php artisan cache:clear');
shell_exec('php artisan view:clear');
shell_exec('php artisan config:clear');
echo "✅ Cache nettoyé\n";

echo "\n🔍 Vérification après nettoyage :\n";
$output = shell_exec('php artisan route:list | grep "admin.ads.index"');
echo $output ?: "Route admin.ads.index toujours non trouvée\n";

echo "\n✅ Diagnostic terminé !\n";
