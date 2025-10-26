<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Setting;

echo "🔍 Test des métadonnées SEO pour la page portfolio\n";
echo "================================================\n\n";

// Simuler l'environnement Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "1. Vérification des métadonnées SEO configurées...\n";

try {
    $seoMeta = [
        'meta_title' => Setting::get('seo_page_portfolio_meta_title', ''),
        'meta_description' => Setting::get('seo_page_portfolio_meta_description', ''),
        'og_title' => Setting::get('seo_page_portfolio_og_title', ''),
        'og_description' => Setting::get('seo_page_portfolio_og_description', ''),
        'og_image' => Setting::get('seo_page_portfolio_og_image', ''),
    ];
    
    echo "✅ Métadonnées récupérées :\n";
    foreach ($seoMeta as $key => $value) {
        $status = !empty($value) ? '✅' : '❌';
        echo "   $status $key: " . ($value ?: '(vide)') . "\n";
    }
    
    if (empty($seoMeta['meta_title']) && empty($seoMeta['meta_description'])) {
        echo "\n⚠️  Aucune métadonnée SEO configurée pour la page portfolio\n";
        echo "   → Allez sur https://sausercouverture.fr/admin/seo/pages\n";
        echo "   → Configurez les métadonnées pour la page 'Réalisations'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la récupération des métadonnées : " . $e->getMessage() . "\n";
}

echo "\n2. Test de la page portfolio...\n";

$url = 'https://sausercouverture.fr/nos-realisations';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (compatible; SEOTest/1.0)',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ],
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Impossible d'accéder à la page\n";
} else {
    // Vérifier le titre
    if (preg_match('/<title>(.*?)<\/title>/i', $response, $matches)) {
        $title = trim($matches[1]);
        echo "✅ Titre trouvé : $title\n";
        
        if (strpos($title, 'Nos Réalisations') !== false) {
            echo "   → Le titre contient 'Nos Réalisations' ✓\n";
        } else {
            echo "   → Le titre ne contient pas 'Nos Réalisations' ✗\n";
        }
    } else {
        echo "❌ Aucun titre trouvé\n";
    }
    
    // Vérifier la description
    if (preg_match('/<meta name="description" content="(.*?)"/i', $response, $matches)) {
        $description = trim($matches[1]);
        echo "✅ Description trouvée : $description\n";
        
        if (strpos($description, 'réalisations récentes') !== false) {
            echo "   → La description contient 'réalisations récentes' ✓\n";
        } else {
            echo "   → La description ne contient pas 'réalisations récentes' ✗\n";
        }
    } else {
        echo "❌ Aucune description trouvée\n";
    }
    
    // Vérifier Open Graph
    if (preg_match('/<meta property="og:title" content="(.*?)"/i', $response, $matches)) {
        $ogTitle = trim($matches[1]);
        echo "✅ Open Graph Title trouvé : $ogTitle\n";
    } else {
        echo "❌ Aucun Open Graph Title trouvé\n";
    }
}

echo "\n📋 Instructions pour corriger :\n";
echo "=============================\n";
echo "1. Allez sur https://sausercouverture.fr/admin/seo/pages\n";
echo "2. Cliquez sur le bouton 'Réalisations'\n";
echo "3. Remplissez les champs :\n";
echo "   - Titre Meta : Nos Réalisations\n";
echo "   - Description Meta : Découvrez quelques-unes de nos réalisations récentes...\n";
echo "   - Titre Open Graph : Nos Réalisations\n";
echo "   - Description Open Graph : Découvrez quelques-unes de nos réalisations récentes...\n";
echo "4. Cliquez sur 'Sauvegarder la Configuration SEO'\n";
echo "5. Videz le cache : php artisan cache:clear\n";
