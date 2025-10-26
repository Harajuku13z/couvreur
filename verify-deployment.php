<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Vérification du déploiement ===\n\n";

// Test 1: Configuration SEO
echo "1. Test de la configuration SEO...\n";
$seoConfig = \App\Models\Setting::get('seo_config', '[]');
$seoConfig = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
echo "   sitemap_enabled: " . ($seoConfig['sitemap_enabled'] ?? 'NON DÉFINI') . "\n";

// Test 2: Sitemap
echo "\n2. Test du sitemap...\n";
try {
    $controller = new \App\Http\Controllers\SeoController();
    $response = $controller->generateSitemap();
    echo "   Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 200) {
        echo "   ✅ Sitemap fonctionne\n";
    } else {
        echo "   ❌ Sitemap ne fonctionne pas\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur sitemap: " . $e->getMessage() . "\n";
}

// Test 3: Analytics & Tracking
echo "\n3. Test des tags de tracking...\n";
echo "   Google Analytics: " . (!empty($seoConfig['google_analytics']) ? '✅ Configuré' : '❌ Non configuré') . "\n";
echo "   Facebook Pixel: " . (!empty($seoConfig['facebook_pixel']) ? '✅ Configuré' : '❌ Non configuré') . "\n";
echo "   Google Search Console: " . (!empty($seoConfig['google_search_console']) ? '✅ Configuré' : '❌ Non configuré') . "\n";

// Test 4: Services
echo "\n4. Test des services...\n";
$services = \App\Models\Setting::get('services', []);
$services = is_string($services) ? json_decode($services, true) : ($services ?? []);
echo "   Nombre de services: " . count($services) . "\n";

echo "\n=== Résumé ===\n";
echo "Si tous les tests sont ✅, le déploiement est réussi !\n";