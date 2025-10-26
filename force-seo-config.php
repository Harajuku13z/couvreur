<?php

echo "🔧 Configuration forcée des métadonnées SEO portfolio\n";
echo "==================================================\n\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

echo "1. Configuration des métadonnées SEO par défaut...\n";

try {
    // Configurer les métadonnées SEO pour portfolio
    $seoConfig = [
        'seo_page_portfolio_meta_title' => 'Nos Réalisations',
        'seo_page_portfolio_meta_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
        'seo_page_portfolio_og_title' => 'Nos Réalisations',
        'seo_page_portfolio_og_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
    ];
    
    foreach ($seoConfig as $key => $value) {
        Setting::set($key, $value);
        echo "   ✅ $key = $value\n";
    }
    
    // Vider le cache
    Setting::clearCache();
    echo "\n✅ Cache vidé\n";
    
    echo "\n2. Vérification de la configuration...\n";
    
    foreach ($seoConfig as $key => $expected) {
        $actual = Setting::get($key, '');
        $status = $actual === $expected ? '✅' : '❌';
        echo "   $status $key: $actual\n";
    }
    
    echo "\n3. Test de la page portfolio...\n";
    
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
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}

echo "\n📋 Si le problème persiste :\n";
echo "===========================\n";
echo "1. Vérifiez que les fichiers sont déployés sur le serveur\n";
echo "2. Videz le cache : php artisan cache:clear\n";
echo "3. Redémarrez le serveur web si nécessaire\n";
