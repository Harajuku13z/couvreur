<?php

echo "=== Script de déploiement du sitemap corrigé ===\n\n";

// Forcer l'URL de production
$baseUrl = 'https://sausercouverture.fr';

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Page d'accueil
$xml .= '  <url>' . "\n";
$xml .= '    <loc>' . $baseUrl . '</loc>' . "\n";
$xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
$xml .= '    <changefreq>daily</changefreq>' . "\n";
$xml .= '    <priority>1.0</priority>' . "\n";
$xml .= '  </url>' . "\n";

// Pages statiques
$staticPages = [
    '/services' => ['priority' => 0.9, 'changefreq' => 'weekly'],
    '/nos-realisations' => ['priority' => 0.8, 'changefreq' => 'monthly'],
    '/avis' => ['priority' => 0.8, 'changefreq' => 'weekly'],
    '/blog' => ['priority' => 0.7, 'changefreq' => 'weekly'],
    '/contact' => ['priority' => 0.6, 'changefreq' => 'monthly'],
    '/mentions-legales' => ['priority' => 0.3, 'changefreq' => 'yearly'],
    '/politique-confidentialite' => ['priority' => 0.3, 'changefreq' => 'yearly'],
    '/cgv' => ['priority' => 0.3, 'changefreq' => 'yearly'],
];

foreach ($staticPages as $url => $config) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . $url . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>' . $config['changefreq'] . '</changefreq>' . "\n";
    $xml .= '    <priority>' . $config['priority'] . '</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Services (basés sur les services existants)
$services = [
    'test-service',
    'couvreur', 
    'couverture',
    'hydrofuge'
];

foreach ($services as $service) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . '/services/' . $service . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.8</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Articles (basés sur les articles existants)
$articles = [
    'hydrofuge-comment-proteger-efficacement-vos-surfaces-de-leau-guide-complet-2024',
    'guide-complet-hydrofuge-de-toiture-protection-et-impermeabilisation-2024'
];

foreach ($articles as $article) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . '/blog/' . $article . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.7</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Annonces (basées sur les annonces existantes)
$ads = [
    'test-couvreur-2-chantilly',
    'test-couvreur-2-senlis',
    'test-couvreur-chantilly',
    'hydrofuge-vitry-en-charollais',
    'test-service-chantilly'
];

foreach ($ads as $ad) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . '/annonces/' . $ad . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.6</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Portfolio
$portfolio = [
    'renovation-de-toiture-a-avrainville'
];

foreach ($portfolio as $item) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . '/nos-realisations/' . $item . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.5</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

$xml .= '</urlset>';

// Sauvegarder le sitemap
file_put_contents('public/sitemap.xml', $xml);

echo "✅ Sitemap corrigé généré : public/sitemap.xml\n";
echo "🌐 URL du sitemap : {$baseUrl}/sitemap.xml\n\n";

echo "=== Vérification des URLs ===\n";
$lines = explode("\n", $xml);
$urlLines = array_filter($lines, function($line) {
    return strpos($line, '<loc>') !== false;
});

$sampleUrls = array_slice($urlLines, 0, 5);
foreach ($sampleUrls as $urlLine) {
    echo "- " . strip_tags($urlLine) . "\n";
}

echo "\n✅ Sitemap prêt pour le déploiement !\n";
