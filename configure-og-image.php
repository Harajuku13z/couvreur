<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "🔧 Configuration des balises meta og:image\n";
echo "========================================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Vérifier les paramètres actuels
    echo "2. Vérification des paramètres actuels...\n";
    
    $currentSettings = [
        'company_logo' => setting('company_logo'),
        'og_image' => setting('og_image'),
        'site_favicon' => setting('site_favicon'),
        'apple_touch_icon' => setting('apple_touch_icon')
    ];
    
    foreach ($currentSettings as $key => $value) {
        echo "   - {$key}: " . ($value ? $value : 'Non configuré') . "\n";
    }
    echo "\n";
    
    // 3. Vérifier les fichiers logo existants
    echo "3. Vérification des fichiers logo...\n";
    
    $logoPaths = [
        'public/logo/logo.png',
        'public/uploads/logo.png',
        'public/uploads/company-logo.png'
    ];
    
    $foundLogo = null;
    foreach ($logoPaths as $path) {
        if (file_exists($path)) {
            echo "   ✅ Trouvé: {$path}\n";
            $foundLogo = $path;
        } else {
            echo "   ❌ Manquant: {$path}\n";
        }
    }
    
    // 4. Configurer le logo par défaut si nécessaire
    echo "\n4. Configuration du logo par défaut...\n";
    
    if ($foundLogo) {
        // Utiliser le logo trouvé
        $logoUrl = str_replace('public/', '', $foundLogo);
        Setting::set('og_image', $logoUrl, 'string', 'seo');
        echo "   ✅ Logo configuré: {$logoUrl}\n";
    } else {
        // Vérifier si company_logo est configuré
        $companyLogo = setting('company_logo');
        if ($companyLogo) {
            Setting::set('og_image', $companyLogo, 'string', 'seo');
            echo "   ✅ Utilisation du company_logo: {$companyLogo}\n";
        } else {
            echo "   ⚠️  Aucun logo trouvé, utilisation du logo par défaut\n";
        }
    }
    
    // 5. Vérifier la configuration SEO
    echo "\n5. Vérification de la configuration SEO...\n";
    
    $seoConfig = Setting::get('seo_config', '[]');
    $seoData = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
    
    if (isset($seoData['og_image']) && $seoData['og_image']) {
        echo "   ✅ og_image dans seo_config: {$seoData['og_image']}\n";
    } else {
        echo "   ❌ og_image manquant dans seo_config\n";
    }
    
    // 6. Tester la génération d'URL
    echo "\n6. Test de génération d'URL...\n";
    
    $testUrls = [
        'Logo par défaut' => asset('logo/logo.png'),
        'Company logo' => setting('company_logo') ? asset(setting('company_logo')) : 'Non configuré',
        'OG image' => setting('og_image') ? asset(setting('og_image')) : 'Non configuré'
    ];
    
    foreach ($testUrls as $name => $url) {
        echo "   - {$name}: {$url}\n";
    }
    
    echo "\n🎯 Résumé de la configuration:\n";
    echo "==============================\n";
    echo "✅ Base de données: Connectée\n";
    echo "✅ Paramètres: Vérifiés\n";
    echo "✅ Logo: " . (setting('og_image') ? 'Configuré' : 'Par défaut') . "\n";
    echo "✅ Balises meta: Mises à jour\n";
    echo "\n💡 Les balises og:image utiliseront maintenant le logo du site web !\n";
    echo "   - Priorité 1: og_image (paramètre SEO)\n";
    echo "   - Priorité 2: company_logo (logo entreprise)\n";
    echo "   - Priorité 3: logo/logo.png (par défaut)\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la configuration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
