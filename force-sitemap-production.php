<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Service;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;

echo "=== Génération forcée du sitemap de production ===\n\n";

// Forcer l'URL de production
$baseUrl = 'https://sausercouverture.fr';

$sitemap = Sitemap::create();

// Page d'accueil
$sitemap->add(Url::create($baseUrl)
    ->setLastModificationDate(Carbon::now())
    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
    ->setPriority(1.0));

// Pages statiques
$staticPages = [
    '/services' => ['priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
    '/nos-realisations' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY],
    '/avis' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
    '/blog' => ['priority' => 0.7, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
    '/contact' => ['priority' => 0.6, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY],
    '/mentions-legales' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
    '/politique-confidentialite' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
    '/cgv' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
];

foreach ($staticPages as $url => $config) {
    $sitemap->add(Url::create($baseUrl . $url)
        ->setLastModificationDate(Carbon::now())
        ->setChangeFrequency($config['changefreq'])
        ->setPriority($config['priority']));
}

// Services
$servicesData = Setting::get('services', '[]');
$services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);

if (!is_array($services)) {
    $services = [];
}

// Filtrer les services visibles
$visibleServices = collect($services)->filter(function($service) {
    return ($service['is_visible'] ?? true) && ($service['is_active'] ?? true);
});

echo "📋 Ajout de {$visibleServices->count()} services...\n";

foreach ($visibleServices as $service) {
    if (isset($service['slug'])) {
        $sitemap->add(Url::create($baseUrl . '/services/' . $service['slug'])
            ->setLastModificationDate(Carbon::parse($service['updated_at'] ?? $service['created_at'] ?? now()))
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.8));
    }
}

// Articles
$articles = Article::where('status', 'published')->get();
echo "📰 Ajout de {$articles->count()} articles...\n";

foreach ($articles as $article) {
    $sitemap->add(Url::create($baseUrl . '/blog/' . $article->slug)
        ->setLastModificationDate($article->updated_at)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
        ->setPriority(0.7));
}

// Annonces (toutes, sans filtre de statut)
$ads = Ad::orderBy('updated_at', 'desc')->limit(5000)->get();
echo "📢 Ajout de {$ads->count()} annonces...\n";

foreach ($ads as $ad) {
    $sitemap->add(Url::create($baseUrl . '/annonces/' . $ad->slug)
        ->setLastModificationDate($ad->updated_at)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
        ->setPriority(0.6));
}

// Portfolio
$portfolioItems = Setting::get('portfolio_items', '[]');
if (is_string($portfolioItems)) {
    $portfolioItems = json_decode($portfolioItems, true) ?? [];
}

$visiblePortfolioItems = array_filter($portfolioItems, function($item) {
    return ($item['is_visible'] ?? true);
});

echo "🖼️ Ajout de " . count($visiblePortfolioItems) . " éléments de portfolio...\n";

foreach ($visiblePortfolioItems as $item) {
    if (isset($item['slug'])) {
        $sitemap->add(Url::create($baseUrl . '/nos-realisations/' . $item['slug'])
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            ->setPriority(0.5));
    }
}

// Sauvegarder le sitemap
$sitemapPath = public_path('sitemap.xml');
$sitemap->writeToFile($sitemapPath);

echo "\n✅ Sitemap généré avec succès : {$sitemapPath}\n";
echo "🌐 URL du sitemap : {$baseUrl}/sitemap.xml\n";

// Vérifier quelques URLs générées
echo "\n=== Vérification des URLs générées ===\n";
$content = file_get_contents($sitemapPath);
$lines = explode("\n", $content);
$urlLines = array_filter($lines, function($line) {
    return strpos($line, '<loc>') !== false;
});

$sampleUrls = array_slice($urlLines, 0, 5);
foreach ($sampleUrls as $urlLine) {
    echo "- " . strip_tags($urlLine) . "\n";
}

echo "\n=== Instructions ===\n";
echo "1. Téléchargez ce fichier sitemap.xml sur votre serveur de production\n";
echo "2. Remplacez le fichier public/sitemap.xml sur le serveur\n";
echo "3. Vérifiez que https://sausercouverture.fr/sitemap.xml fonctionne\n";
echo "4. Soumettez le sitemap dans Google Search Console\n";
