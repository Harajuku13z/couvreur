<?php

require_once 'vendor/autoload.php';

// Charger Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\AdGenerationController;
use App\Models\City;
use Illuminate\Http\Request;

echo "=== TEST GÉNÉRATION ANNONCES PAR MOT-CLÉ ===\n\n";

// Créer une requête simulée
$request = new Request();
$request->merge([
    'keyword' => 'test-couvreur-2',
    'city_ids' => [1, 2], // Chantilly et Senlis
    'ai_prompt' => '',
    'batch_size' => 20
]);

echo "1. Test de la génération avec mot-clé 'test-couvreur-2' pour 2 villes...\n";

try {
    $controller = new AdGenerationController();
    $response = $controller->generateByKeywordCities($request);
    
    $data = json_decode($response->getContent(), true);
    
    echo "2. Résultat de la génération :\n";
    echo "   - Success: " . ($data['success'] ? 'OUI' : 'NON') . "\n";
    echo "   - Message: " . $data['message'] . "\n";
    echo "   - Créées: " . ($data['created'] ?? 'N/A') . "\n";
    echo "   - Ignorées: " . ($data['skipped'] ?? 'N/A') . "\n";
    echo "   - Villes: " . ($data['cities_count'] ?? 'N/A') . "\n";
    
    if ($data['success']) {
        echo "\n✅ Test réussi ! Les annonces devraient maintenant être créées.\n";
    } else {
        echo "\n❌ Test échoué : " . $data['message'] . "\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ Erreur lors du test : " . $e->getMessage() . "\n";
    echo "Stack trace : " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
