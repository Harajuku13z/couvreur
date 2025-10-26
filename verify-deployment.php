<?php

/**
 * Script de vérification du déploiement
 * Vérifie que toutes les modifications sont bien présentes
 */

echo "🔍 Vérification du déploiement - Sauser Couverture\n";
echo "================================================\n\n";

// Vérifier que le contrôleur ServiceAiController existe
$controllerPath = __DIR__ . '/app/Http/Controllers/ServiceAiController.php';
if (file_exists($controllerPath)) {
    echo "✅ ServiceAiController.php - Présent\n";
} else {
    echo "❌ ServiceAiController.php - MANQUANT\n";
}

// Vérifier que la vue AI existe
$viewPath = __DIR__ . '/resources/views/admin/services/ai.blade.php';
if (file_exists($viewPath)) {
    echo "✅ Vue AI (ai.blade.php) - Présente\n";
} else {
    echo "❌ Vue AI (ai.blade.php) - MANQUANTE\n";
}

// Vérifier que le SeoHelper est corrigé
$seoHelperPath = __DIR__ . '/app/Helpers/SeoHelper.php';
if (file_exists($seoHelperPath)) {
    $content = file_get_contents($seoHelperPath);
    if (strpos($content, '($seo[\'og_title\'] ?: ($seo[\'meta_title\'] ?: ($customData[\'title\'] ?? \'\')))') !== false) {
        echo "✅ SeoHelper.php - Syntaxe corrigée\n";
    } else {
        echo "❌ SeoHelper.php - Syntaxe non corrigée\n";
    }
} else {
    echo "❌ SeoHelper.php - MANQUANT\n";
}

// Vérifier que les routes sont présentes
$routesPath = __DIR__ . '/routes/web.php';
if (file_exists($routesPath)) {
    $content = file_get_contents($routesPath);
    if (strpos($content, 'ai.form') !== false) {
        echo "✅ Routes IA - Présentes\n";
    } else {
        echo "❌ Routes IA - MANQUANTES\n";
    }
} else {
    echo "❌ routes/web.php - MANQUANT\n";
}

// Vérifier que le bouton est dans la vue admin
$adminViewPath = __DIR__ . '/resources/views/admin/services/index.blade.php';
if (file_exists($adminViewPath)) {
    $content = file_get_contents($adminViewPath);
    if (strpos($content, 'Génération IA') !== false) {
        echo "✅ Bouton IA dans admin - Présent\n";
    } else {
        echo "❌ Bouton IA dans admin - MANQUANT\n";
    }
} else {
    echo "❌ Vue admin services - MANQUANTE\n";
}

// Vérifier la documentation
$docPath = __DIR__ . '/GROQ_SETUP.md';
if (file_exists($docPath)) {
    echo "✅ Documentation GROQ - Présente\n";
} else {
    echo "❌ Documentation GROQ - MANQUANTE\n";
}

echo "\n🎯 Résumé:\n";
echo "Si tous les éléments sont ✅, le déploiement est correct.\n";
echo "Si des éléments sont ❌, il faut les déployer manuellement.\n\n";

echo "🔗 URLs à tester:\n";
echo "- Admin Services: https://sausercouverture.fr/admin/services\n";
echo "- Génération IA: https://sausercouverture.fr/admin/services/ai\n";
echo "- Test connexion: https://sausercouverture.fr/admin/services/ai (bouton test)\n\n";

echo "📋 Pour déployer manuellement:\n";
echo "1. Uploader tous les fichiers modifiés via FTP\n";
echo "2. Exécuter: php artisan cache:clear\n";
echo "3. Exécuter: php artisan route:clear\n";
echo "4. Exécuter: php artisan view:clear\n";
echo "5. Tester les URLs ci-dessus\n";
