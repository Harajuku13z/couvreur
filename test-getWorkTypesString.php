<?php
/**
 * Script de test pour la méthode getWorkTypesString
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\EmailService;

echo "🧪 Test de la méthode getWorkTypesString...\n";

// Créer un objet de soumission simulé
class MockSubmission {
    public $work_types;
    
    public function __construct($workTypes) {
        $this->work_types = $workTypes;
    }
}

// Tester avec différents types de travaux
$testCases = [
    'JSON string' => '["roof","facade","isolation"]',
    'Array' => ['roof', 'facade', 'isolation'],
    'Types inconnus' => '["unknown1","unknown2"]',
    'Vide' => '[]',
    'Null' => null
];

$emailService = new EmailService();

foreach ($testCases as $description => $workTypes) {
    echo "\n📊 Test: $description\n";
    
    $submission = new MockSubmission($workTypes);
    
    // Utiliser la réflexion pour accéder à la méthode privée
    $reflection = new ReflectionClass($emailService);
    $method = $reflection->getMethod('getWorkTypesString');
    $method->setAccessible(true);
    
    $result = $method->invoke($emailService, $submission);
    
    echo "📊 Types de travaux: " . ($workTypes ? (is_array($workTypes) ? json_encode($workTypes) : $workTypes) : 'null') . "\n";
    echo "📊 Résultat: '$result'\n";
}

echo "\n✅ Test terminé\n";
?>
