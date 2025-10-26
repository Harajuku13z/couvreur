<?php

require_once 'vendor/autoload.php';

// Charger Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\City;
use App\Models\Ad;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG GÉNÉRATION ANNONCES PAR MOT-CLÉ ===\n\n";

// Test 1: Vérifier les villes
echo "1. Vérification des villes :\n";
$cities = City::limit(2)->get();
foreach ($cities as $city) {
    echo "   - Ville ID {$city->id}: {$city->name} ({$city->postal_code})\n";
}

if ($cities->isEmpty()) {
    echo "   ❌ Aucune ville trouvée !\n";
    exit;
}

// Test 2: Vérifier les annonces existantes
echo "\n2. Vérification des annonces existantes :\n";
$keyword = 'test-couvreur';
$existingAds = Ad::where('keyword', $keyword)->get();
echo "   - Annonces existantes pour '{$keyword}': " . $existingAds->count() . "\n";

// Test 3: Test de création d'annonce
echo "\n3. Test de création d'annonce :\n";
$city = $cities->first();

try {
    // Vérifier si une annonce existe déjà
    $existingAd = Ad::where('keyword', $keyword)
        ->where('city_id', $city->id)
        ->first();

    if ($existingAd) {
        echo "   - Annonce déjà existante pour {$keyword} à {$city->name}\n";
    } else {
        echo "   - Tentative de création d'annonce pour {$keyword} à {$city->name}...\n";
        
        // Créer l'annonce
        $ad = Ad::create([
            'title' => $keyword . ' à ' . $city->name,
            'keyword' => $keyword,
            'city_id' => $city->id,
            'slug' => Str::slug($keyword . ' ' . $city->name),
            'status' => 'draft',
            'meta_title' => $keyword . ' à ' . $city->name . ' | Devis Gratuit',
            'meta_description' => 'Service professionnel de ' . $keyword . ' à ' . $city->name . '. Devis gratuit et intervention rapide.',
            'content_html' => '<p>Contenu de test pour ' . $keyword . ' à ' . $city->name . '</p>',
            'content_json' => json_encode([
                'keyword' => $keyword,
                'city' => $city->toArray(),
                'ai_prompt' => '',
                'service_featured_image' => '',
                'service_icon' => 'fas fa-tools'
            ])
        ]);
        
        echo "   ✅ Annonce créée avec succès ! ID: {$ad->id}\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Erreur lors de la création : " . $e->getMessage() . "\n";
    echo "   Stack trace : " . $e->getTraceAsString() . "\n";
}

// Test 4: Vérifier la structure de la table ads
echo "\n4. Vérification de la structure de la table ads :\n";
try {
    $columns = \DB::select('DESCRIBE ads');
    echo "   Colonnes de la table ads :\n";
    foreach ($columns as $column) {
        echo "   - {$column->Field} ({$column->Type}) - Null: {$column->Null} - Default: {$column->Default}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Erreur lors de la vérification de la structure : " . $e->getMessage() . "\n";
}

// Test 5: Vérifier les logs récents
echo "\n5. Vérification des logs récents :\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = array_slice(explode("\n", $logs), -20);
    echo "   Dernières 20 lignes de log :\n";
    foreach ($recentLogs as $log) {
        if (trim($log)) {
            echo "   " . $log . "\n";
        }
    }
} else {
    echo "   ❌ Fichier de log non trouvé\n";
}

echo "\n=== FIN DU DEBUG ===\n";
