<?php

require_once 'vendor/autoload.php';

// Charger Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

echo "=== Test de génération IA ===\n";

// Vérifier la clé API
$apiKey = setting('chatgpt_api_key');
echo "Clé API configurée: " . ($apiKey ? "OUI" : "NON") . "\n";

if (!$apiKey) {
    echo "❌ Clé API manquante !\n";
    exit;
}

// Test de génération
$serviceName = "Ravalement & façades";
$shortDescription = "Service professionnel de ravalement et façades";
$companyInfo = [
    'company_name' => 'JD RENOVATION SERVICE',
    'company_city' => 'Pontoise',
    'company_region' => 'Val-d\'Oise',
    'company_phone' => '0609372706',
    'company_email' => 'contact@jd-renovation-service.fr',
    'company_address' => '123 Rue de la Paix'
];

echo "Service: $serviceName\n";
echo "Description courte: $shortDescription\n";

// Créer une instance du contrôleur
$controller = new \App\Http\Controllers\ServicesController();

// Utiliser la réflexion pour accéder à la méthode privée
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('generateServiceContentWithAI');
$method->setAccessible(true);

try {
    $result = $method->invoke($controller, $serviceName, $shortDescription, $companyInfo);
    
    echo "\n=== Résultat ===\n";
    echo "Description courte: " . $result['short_description'] . "\n";
    echo "Longueur description: " . strlen($result['description']) . " caractères\n";
    echo "Icon: " . $result['icon'] . "\n";
    echo "Meta title: " . $result['meta_title'] . "\n";
    
    // Afficher un aperçu de la description
    echo "\n=== Aperçu description ===\n";
    echo substr(strip_tags($result['description']), 0, 500) . "...\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n";
