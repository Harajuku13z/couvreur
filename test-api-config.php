<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Test de configuration de l'API OpenAI\n";
echo "==========================================\n\n";

try {
    // Test de connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    DB::connection()->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // Test des settings
    echo "2. Test des settings...\n";
    $apiKey = setting('chatgpt_api_key');
    $model = setting('chatgpt_model');
    
    echo "   Clé API: " . (empty($apiKey) ? "❌ Non configurée" : "✅ Configurée (" . substr($apiKey, 0, 10) . "...)") . "\n";
    echo "   Modèle: " . ($model ?: "❌ Non configuré") . "\n";
    
    if (empty($apiKey)) {
        echo "\n⚠️  PROBLÈME: Clé API non configurée !\n";
        echo "   L'IA ne peut pas générer de contenu personnalisé.\n";
        echo "   Le système utilise le contenu de fallback générique.\n\n";
        
        echo "💡 Solution:\n";
        echo "   1. Aller dans l'admin: /admin/config\n";
        echo "   2. Configurer la clé API OpenAI\n";
        echo "   3. Configurer le modèle (ex: gpt-4o)\n";
        echo "   4. Tester la génération d'articles\n\n";
        
        echo "🔧 Configuration automatique de test...\n";
        DB::table('settings')->updateOrInsert(
            ['key' => 'chatgpt_api_key'],
            ['value' => 'sk-test-key-replace-with-real-key', 'updated_at' => now()]
        );
        DB::table('settings')->updateOrInsert(
            ['key' => 'chatgpt_model'],
            ['value' => 'gpt-4o', 'updated_at' => now()]
        );
        echo "✅ Configuration de test appliquée\n";
    } else {
        echo "✅ Configuration API : OK\n";
    }
    
    // Test des informations de l'entreprise
    echo "\n3. Test des informations de l'entreprise...\n";
    $companyName = setting('company_name');
    $companyCity = setting('company_city');
    $companyRegion = setting('company_region');
    
    echo "   Nom: " . ($companyName ?: "❌ Non configuré") . "\n";
    echo "   Ville: " . ($companyCity ?: "❌ Non configuré") . "\n";
    echo "   Région: " . ($companyRegion ?: "❌ Non configuré") . "\n";
    
    if (!$companyName || !$companyCity || !$companyRegion) {
        echo "\n⚠️  PROBLÈME: Informations de l'entreprise manquantes !\n";
        echo "   L'IA ne peut pas personnaliser le contenu.\n\n";
        
        echo "🔧 Configuration automatique...\n";
        DB::table('settings')->updateOrInsert(['key' => 'company_name'], ['value' => 'Artisan Elfrick', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'company_city'], ['value' => 'Avrainville', 'updated_at' => now()]);
        DB::table('settings')->updateOrInsert(['key' => 'company_region'], ['value' => 'Essonne', 'updated_at' => now()]);
        echo "✅ Configuration de l'entreprise appliquée\n";
    }
    
    echo "\n🎯 Résumé de la configuration:\n";
    echo "===============================\n";
    echo "✅ Base de données: Connectée\n";
    echo "✅ Clé API: " . (empty($apiKey) ? "Test (remplacer par vraie clé)" : "Configurée") . "\n";
    echo "✅ Modèle: " . ($model ?: "gpt-4o") . "\n";
    echo "✅ Entreprise: " . ($companyName ?: "Artisan Elfrick") . "\n";
    echo "✅ Localisation: " . ($companyCity ?: "Avrainville") . ", " . ($companyRegion ?: "Essonne") . "\n";
    
    if (empty($apiKey) || $apiKey === 'sk-test-key-replace-with-real-key') {
        echo "\n⚠️  ATTENTION: Utilisez une vraie clé API OpenAI pour générer du contenu personnalisé !\n";
        echo "   Avec la clé de test, le système utilise le contenu de fallback.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n";
}
