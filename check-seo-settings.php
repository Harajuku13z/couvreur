<?php

echo "🔍 Vérification des métadonnées SEO configurées\n";
echo "=============================================\n\n";

// Test 1: Vérifier les métadonnées SEO pour portfolio
echo "1. Vérification des métadonnées SEO portfolio...\n";

$settings = [
    'seo_page_portfolio_meta_title' => 'Nos Réalisations',
    'seo_page_portfolio_meta_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
    'seo_page_portfolio_og_title' => 'Nos Réalisations',
    'seo_page_portfolio_og_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
];

echo "Métadonnées attendues :\n";
foreach ($settings as $key => $expected) {
    echo "   $key: $expected\n";
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
        echo "✅ Titre actuel : $title\n";
        
        if ($title === 'Nos Réalisations') {
            echo "   → Titre correct ✓\n";
        } else {
            echo "   → Titre incorrect (attendu: 'Nos Réalisations') ✗\n";
        }
    }
    
    // Vérifier la description
    if (preg_match('/<meta name="description" content="(.*?)"/i', $response, $matches)) {
        $description = trim($matches[1]);
        echo "✅ Description actuelle : $description\n";
        
        if (strpos($description, 'réalisations récentes') !== false) {
            echo "   → Description correcte ✓\n";
        } else {
            echo "   → Description incorrecte ✗\n";
        }
    }
    
    // Vérifier Open Graph
    if (preg_match('/<meta property="og:title" content="(.*?)"/i', $response, $matches)) {
        $ogTitle = trim($matches[1]);
        echo "✅ Open Graph Title actuel : $ogTitle\n";
        
        if ($ogTitle === 'Nos Réalisations') {
            echo "   → Open Graph Title correct ✓\n";
        } else {
            echo "   → Open Graph Title incorrect ✗\n";
        }
    }
}

echo "\n📋 Instructions pour corriger :\n";
echo "=============================\n";
echo "1. Connectez-vous sur https://sausercouverture.fr/admin/login\n";
echo "2. Allez sur https://sausercouverture.fr/admin/seo/pages\n";
echo "3. Cliquez sur le bouton 'Réalisations' (🏗️)\n";
echo "4. Remplissez EXACTEMENT :\n";
echo "   - Titre Meta : Nos Réalisations\n";
echo "   - Description Meta : Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet\n";
echo "   - Titre Open Graph : Nos Réalisations\n";
echo "   - Description Open Graph : Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet\n";
echo "5. Cliquez sur 'Sauvegarder la Configuration SEO'\n";
echo "6. Videz le cache : php artisan cache:clear\n";
echo "7. Testez la page : https://sausercouverture.fr/nos-realisations\n";
