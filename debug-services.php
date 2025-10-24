<?php

// Script de diagnostic pour les services
require_once 'vendor/autoload.php';

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

echo "🔍 Diagnostic des services\n";
echo "========================\n\n";

// Vérifier la connexion à la base de données
try {
    DB::connection()->getPdo();
    echo "✅ Connexion à la base de données : OK\n";
} catch (Exception $e) {
    echo "❌ Connexion à la base de données : ÉCHEC\n";
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}

// Vérifier la table settings
echo "\n📋 Vérification de la table settings :\n";
if (DB::getSchemaBuilder()->hasTable('settings')) {
    $settingsCount = DB::table('settings')->count();
    echo "✅ Table settings : {$settingsCount} enregistrements\n";
    
    // Vérifier la clé services
    $servicesSetting = DB::table('settings')->where('key', 'services')->first();
    if ($servicesSetting) {
        echo "✅ Clé 'services' trouvée dans settings\n";
        echo "📄 Valeur brute : " . substr($servicesSetting->value, 0, 200) . "...\n";
        
        // Décoder le JSON
        $services = json_decode($servicesSetting->value, true);
        if (is_array($services)) {
            echo "✅ Services décodés : " . count($services) . " services\n";
            foreach ($services as $index => $service) {
                echo "  - Service {$index}: " . ($service['name'] ?? 'Sans nom') . " (ID: " . ($service['id'] ?? 'N/A') . ")\n";
            }
        } else {
            echo "❌ Erreur de décodage JSON des services\n";
            echo "Erreur JSON : " . json_last_error_msg() . "\n";
        }
    } else {
        echo "❌ Clé 'services' non trouvée dans settings\n";
    }
} else {
    echo "❌ Table settings manquante\n";
}

// Vérifier les services via le modèle Setting
echo "\n🔧 Vérification via le modèle Setting :\n";
try {
    $servicesData = Setting::get('services', '[]');
    echo "✅ Services via Setting::get : " . (is_string($servicesData) ? 'String' : 'Array') . "\n";
    
    if (is_string($servicesData)) {
        $services = json_decode($servicesData, true);
        if (is_array($services)) {
            echo "✅ Services décodés : " . count($services) . " services\n";
        } else {
            echo "❌ Erreur de décodage JSON\n";
        }
    } elseif (is_array($servicesData)) {
        echo "✅ Services déjà en array : " . count($servicesData) . " services\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la récupération des services : " . $e->getMessage() . "\n";
}

// Vérifier les répertoires d'images
echo "\n📁 Vérification des répertoires d'images :\n";
$imagePaths = [
    'storage/app/public/uploads/services' => 'Répertoire services',
    'public/storage' => 'Lien symbolique public/storage'
];

foreach ($imagePaths as $path => $description) {
    $fullPath = base_path($path);
    if (is_dir($fullPath)) {
        $files = glob($fullPath . '/*');
        echo "✅ {$description} : " . count($files) . " fichiers\n";
    } else {
        echo "❌ {$description} : Répertoire manquant\n";
    }
}

echo "\n🎯 Recommandations :\n";
echo "- Si les services ne s'affichent pas, vérifiez la clé 'services' dans la table settings\n";
echo "- Si les images ne s'affichent pas, vérifiez le lien symbolique public/storage\n";
echo "- Si la suppression ne fonctionne pas, vérifiez les permissions de la base de données\n";
