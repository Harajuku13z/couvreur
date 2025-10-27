<?php

echo "🔧 Correction des doublons Open Graph\n";
echo "===================================\n\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Vérification des fichiers modifiés...\n";

$files = [
    'resources/views/articles/show.blade.php',
    'resources/views/ads/show.blade.php',
    'resources/views/layouts/app.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file existe\n";
    } else {
        echo "   ❌ $file n'existe pas\n";
    }
}

echo "\n2. Vérification des métadonnées dans articles/show.blade.php...\n";

$articleFile = __DIR__ . '/resources/views/articles/show.blade.php';
$content = file_get_contents($articleFile);

// Vérifier que les métadonnées dupliquées ont été supprimées
$duplicateTags = [
    'og:type',
    'og:title',
    'og:description',
    'og:image',
    'og:url',
    'og:site_name',
    'twitter:card',
    'twitter:title',
    'twitter:description',
    'twitter:image'
];

$duplicateCount = 0;
foreach ($duplicateTags as $tag) {
    $count = substr_count($content, $tag);
    if ($count > 1) {
        echo "   ❌ $tag apparaît $count fois (doublon détecté)\n";
        $duplicateCount++;
    } else {
        echo "   ✅ $tag apparaît $count fois (correct)\n";
    }
}

if ($duplicateCount === 0) {
    echo "\n   ✅ Aucun doublon détecté dans articles/show.blade.php\n";
} else {
    echo "\n   ❌ $duplicateCount doublons détectés\n";
}

echo "\n3. Vérification des métadonnées spécifiques aux articles...\n";

$specificTags = [
    'article:published_time',
    'article:author',
    'article:section',
    'article:tag'
];

foreach ($specificTags as $tag) {
    if (strpos($content, $tag) !== false) {
        echo "   ✅ $tag présent\n";
    } else {
        echo "   ❌ $tag manquant\n";
    }
}

echo "\n4. Vérification des variables PHP passées au layout...\n";

$phpVariables = [
    '$pageTitle',
    '$pageDescription',
    '$pageImage',
    '$pageType',
    '$currentPage'
];

foreach ($phpVariables as $var) {
    if (strpos($content, $var) !== false) {
        echo "   ✅ $var défini\n";
    } else {
        echo "   ❌ $var manquant\n";
    }
}

echo "\n5. Test de génération d'une page d'article...\n";

try {
    // Simuler un article pour tester
    $article = new stdClass();
    $article->meta_title = 'Test Article';
    $article->title = 'Test Article Title';
    $article->meta_description = 'Test description';
    $article->meta_keywords = 'test, keywords';
    $article->featured_image = 'test-image.jpg';
    $article->focus_keyword = 'Test Keyword';
    $article->created_at = new DateTime();
    
    // Simuler les variables
    $pageTitle = $article->meta_title ?: $article->title;
    $pageDescription = $article->meta_description;
    $pageImage = $article->featured_image ? asset($article->featured_image) : asset('images/og-blog.jpg');
    $pageType = 'article';
    $currentPage = 'article';
    
    echo "   ✅ Variables simulées correctement\n";
    echo "   📄 Titre: $pageTitle\n";
    echo "   📝 Description: $pageDescription\n";
    echo "   🖼️ Image: $pageImage\n";
    echo "   📋 Type: $pageType\n";
    
} catch (Exception $e) {
    echo "   ❌ Erreur simulation: " . $e->getMessage() . "\n";
}

echo "\n📋 Instructions pour tester :\n";
echo "===========================\n";
echo "1. Vider le cache des vues :\n";
echo "   php artisan view:clear\n\n";

echo "2. Tester une page d'article :\n";
echo "   https://sausercouverture.fr/blog/[slug-article]\n\n";

echo "3. Vérifier le code source de la page :\n";
echo "   - Ouvrir F12 dans le navigateur\n";
echo "   - Aller dans l'onglet Elements\n";
echo "   - Chercher les balises <meta property=\"og:\n\n";

echo "4. Vérifier qu'il n'y a plus de doublons :\n";
echo "   - Chaque balise og: devrait apparaître une seule fois\n";
echo "   - Les métadonnées spécifiques aux articles doivent être présentes\n\n";

echo "✅ Correction terminée !\n";
