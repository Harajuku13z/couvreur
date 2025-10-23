<?php
/**
 * Script de test pour les templates d'emails
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;
use App\Models\Submission;

echo "🧪 Test des templates d'emails...\n";

// Vérifier les templates configurés
$clientTemplate = Setting::get('email_client_template', '');
$adminTemplate = Setting::get('email_admin_template', '');

echo "📧 Template client configuré: " . (!empty($clientTemplate) ? "OUI" : "NON") . "\n";
echo "📧 Template admin configuré: " . (!empty($adminTemplate) ? "OUI" : "NON") . "\n";

if (!empty($clientTemplate)) {
    echo "📝 Template client contient {work_types}: " . (strpos($clientTemplate, '{work_types}') !== false ? "OUI" : "NON") . "\n";
}

if (!empty($adminTemplate)) {
    echo "📝 Template admin contient {work_types}: " . (strpos($adminTemplate, '{work_types}') !== false ? "OUI" : "NON") . "\n";
}

// Vérifier les soumissions récentes
echo "\n📊 Soumissions récentes:\n";
$submissions = Submission::orderBy('created_at', 'desc')->take(3)->get();

foreach ($submissions as $submission) {
    echo "  - ID: {$submission->id}, Types: {$submission->work_types}\n";
}

echo "\n✅ Test terminé\n";
?>
