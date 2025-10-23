<?php
/**
 * Script de mise à jour forcée pour corriger l'erreur regenerate()
 * À exécuter sur le serveur de production
 */

echo "🚀 Mise à jour forcée du contrôleur ServicesController...\n";

// Vérifier si nous sommes sur le serveur de production
if (!file_exists('/public_html/app/Http/Controllers/ServicesController.php')) {
    echo "❌ Erreur: Ce script doit être exécuté sur le serveur de production\n";
    exit(1);
}

// Changer vers le répertoire du projet
chdir('/public_html');

echo "📁 Répertoire: " . getcwd() . "\n";

// Sauvegarder le contrôleur actuel
echo "💾 Sauvegarde du contrôleur actuel...\n";
copy('app/Http/Controllers/ServicesController.php', 'app/Http/Controllers/ServicesController.php.backup.' . date('Y-m-d_H-i-s'));

// Vérifier si la méthode regenerate existe
echo "🔍 Vérification de la méthode regenerate()...\n";
$controllerContent = file_get_contents('app/Http/Controllers/ServicesController.php');

if (strpos($controllerContent, 'public function regenerate') !== false) {
    echo "✅ Méthode regenerate() déjà présente\n";
} else {
    echo "❌ Méthode regenerate() manquante - mise à jour nécessaire\n";
    
    // Forcer la mise à jour depuis GitHub
    echo "📥 Mise à jour depuis GitHub...\n";
    $output = [];
    $returnCode = 0;
    exec('git pull origin main 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ Mise à jour réussie\n";
        echo "📋 Output: " . implode("\n", $output) . "\n";
    } else {
        echo "❌ Erreur lors de la mise à jour: " . implode("\n", $output) . "\n";
        exit(1);
    }
}

// Vérifier à nouveau la méthode
echo "🔍 Vérification finale de la méthode regenerate()...\n";
$controllerContent = file_get_contents('app/Http/Controllers/ServicesController.php');

if (strpos($controllerContent, 'public function regenerate') !== false) {
    echo "✅ Méthode regenerate() trouvée - correction réussie !\n";
} else {
    echo "❌ Méthode regenerate() toujours manquante - problème de déploiement\n";
    exit(1);
}

// Nettoyer le cache
echo "🧹 Nettoyage du cache...\n";
exec('php artisan cache:clear');
exec('php artisan config:clear');
exec('php artisan route:clear');
exec('php artisan view:clear');

echo "🎉 Mise à jour terminée avec succès !\n";
echo "🔗 Testez maintenant: https://www.jd-renovation-service.fr/admin/services\n";
?>
