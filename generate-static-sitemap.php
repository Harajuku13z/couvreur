<?php

echo "=== Génération d'un sitemap statique de production ===\n\n";

// Forcer l'URL de production
$baseUrl = 'https://sausercouverture.fr';

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Page d'accueil
$xml .= '  <url>' . "\n";
$xml .= '    <loc>' . $baseUrl . '</loc>' . "\n";
$xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
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
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>' . $config['changefreq'] . '</changefreq>' . "\n";
    $xml .= '    <priority>' . $config['priority'] . '</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Services (basés sur ce que vous avez mentionné)
$services = [
    'test-service',
    'couvreur', 
    'couverture',
    'hydrofuge'
];

foreach ($services as $service) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . '/services/' . $service . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.8</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Articles (basés sur ce que vous avez mentionné)
$articles = [
    'hydrofuge-comment-proteger-efficacement-vos-surfaces-de-leau-guide-complet-2024',
    'guide-complet-hydrofuge-de-toiture-protection-et-impermeabilisation-2024'
];

foreach ($articles as $article) {
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . $baseUrl . '/blog/' . $article . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.7</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

// Annonces (basées sur ce que vous avez mentionné)
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
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
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
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
    $xml .= '    <priority>0.5</priority>' . "\n";
    $xml .= '  </url>' . "\n";
}

$xml .= '</urlset>';

// Sauvegarder le sitemap
file_put_contents('sitemap-production.xml', $xml);

echo "✅ Sitemap statique généré : sitemap-production.xml\n";
echo "🌐 URL du sitemap : {$baseUrl}/sitemap.xml\n\n";

echo "=== Contenu du sitemap ===\n";
echo substr($xml, 0, 1000) . "...\n\n";

echo "=== Instructions ===\n";
echo "1. Téléchargez le fichier sitemap-production.xml\n";
echo "2. Renommez-le en sitemap.xml\n";
echo "3. Uploadez-le sur votre serveur de production dans le dossier public/\n";
echo "4. Vérifiez que https://sausercouverture.fr/sitemap.xml fonctionne\n";
echo "5. Soumettez le sitemap dans Google Search Console\n";
