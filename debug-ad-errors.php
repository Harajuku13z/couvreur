<?php
/**
 * Script de diagnostic des erreurs de génération d'annonces
 */

echo "🔍 Diagnostic des erreurs de génération d'annonces...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Test 1: Vérifier la clé API ChatGPT
echo "\n🔑 Test de la clé API ChatGPT :\n";
$apiKeyOutput = shell_exec('php artisan tinker --execute="echo \App\Models\Setting::get(\'chatgpt_api_key\', \'NON_CONFIGURÉE\');"');
$apiKey = trim($apiKeyOutput);
echo "Clé API : " . ($apiKey === 'NON_CONFIGURÉE' ? '❌ NON CONFIGURÉE' : '✅ Configurée') . "\n";

if ($apiKey === 'NON_CONFIGURÉE' || empty($apiKey)) {
    echo "❌ PROBLÈME IDENTIFIÉ : Clé API ChatGPT non configurée\n";
    echo "📋 Solution :\n";
    echo "   1. Allez sur https://platform.openai.com/api-keys\n";
    echo "   2. Créez une nouvelle clé API\n";
    echo "   3. Ajoutez-la dans les paramètres de l'application\n";
    exit(1);
}

// Test 2: Vérifier les services
echo "\n📋 Test des services :\n";
$servicesOutput = shell_exec('php artisan tinker --execute="
$services = \App\Models\Setting::get(\'services\', \'[]\');
$services = is_string($services) ? json_decode($services, true) : $services;
echo \"Services disponibles: \" . count($services) . \"\\n\";
if (count($services) == 0) {
    echo \"❌ PROBLÈME IDENTIFIÉ : Aucun service configuré\\n\";
} else {
    echo \"✅ Services OK\\n\";
    foreach($services as $service) {
        echo \"- \" . $service[\"name\"] . \" (\" . $service[\"slug\"] . \")\\n\";
    }
}
"');
echo $servicesOutput;

// Test 3: Vérifier les villes
echo "\n🏙️ Test des villes :\n";
$citiesOutput = shell_exec('php artisan tinker --execute="
$citiesCount = \App\Models\City::count();
echo \"Villes disponibles: \" . $citiesCount . \"\\n\";
if ($citiesCount == 0) {
    echo \"❌ PROBLÈME IDENTIFIÉ : Aucune ville trouvée\\n\";
} else {
    echo \"✅ Villes OK\\n\";
    $cities = \App\Models\City::take(3)->get();
    foreach($cities as $city) {
        echo \"- \" . $city->name . \" (\" . $city->postal_code . \")\\n\";
    }
}
"');
echo $citiesOutput;

// Test 4: Test de connexion API simple
echo "\n🌐 Test de connexion API ChatGPT :\n";
$testScript = '<?php
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

$apiKey = Setting::get("chatgpt_api_key");
if (!$apiKey) {
    echo "❌ PROBLÈME IDENTIFIÉ : Clé API non configurée";
    exit(1);
}

try {
    echo "🔄 Test de connexion à l\'API...\n";
    $response = Http::timeout(30)->post("https://api.openai.com/v1/chat/completions", [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => "Test de connexion - réponds juste OK"]
        ],
        "max_tokens" => 10
    ], [
        "Authorization" => "Bearer " . $apiKey,
        "Content-Type" => "application/json"
    ]);
    
    if ($response->successful()) {
        echo "✅ Connexion API réussie\n";
    } else {
        echo "❌ PROBLÈME IDENTIFIÉ : Erreur API (Status: " . $response->status() . ")\n";
        echo "Réponse: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ PROBLÈME IDENTIFIÉ : Exception API - " . $e->getMessage() . "\n";
}
';

file_put_contents('test-api-debug.php', $testScript);
$output = shell_exec('php test-api-debug.php');
echo $output . "\n";
unlink('test-api-debug.php');

// Test 5: Vérifier la table ads
echo "\n📊 Test de la table ads :\n";
$adsOutput = shell_exec('php artisan tinker --execute="
try {
    \$adsCount = \App\Models\Ad::count();
    echo \"Annonces existantes: \" . \$adsCount . \"\\n\";
    echo \"✅ Table ads accessible\\n\";
} catch (Exception \$e) {
    echo \"❌ PROBLÈME IDENTIFIÉ : Erreur table ads - \" . \$e->getMessage() . \"\\n\";
}
"');
echo $adsOutput;

// Test 6: Vérifier les logs récents
echo "\n📋 Vérification des logs récents :\n";
if (file_exists('storage/logs/laravel.log')) {
    $logContent = file_get_contents('storage/logs/laravel.log');
    $recentLogs = array_slice(explode("\n", $logContent), -50);
    $errorLogs = [];
    foreach ($recentLogs as $log) {
        if (strpos($log, 'ERROR') !== false || strpos($log, 'Exception') !== false || strpos($log, 'Failed') !== false) {
            $errorLogs[] = $log;
        }
    }
    
    if (!empty($errorLogs)) {
        echo "❌ PROBLÈMES IDENTIFIÉS dans les logs :\n";
        foreach (array_slice($errorLogs, -10) as $error) {
            echo "  " . $error . "\n";
        }
    } else {
        echo "✅ Aucune erreur récente dans les logs\n";
    }
} else {
    echo "⚠️ Fichier de log non trouvé\n";
}

// Test 7: Test de génération d'annonce simple
echo "\n🎯 Test de génération d'annonce simple :\n";
$simpleTestScript = '<?php
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\City;
use App\Models\Ad;

try {
    echo "🔄 Test de génération simple...\n";
    
    // Récupérer un service
    $services = Setting::get("services", "[]");
    $services = is_string($services) ? json_decode($services, true) : $services;
    if (empty($services)) {
        echo "❌ PROBLÈME IDENTIFIÉ : Aucun service configuré\n";
        exit(1);
    }
    $service = $services[0];
    echo "Service: " . $service["name"] . "\n";
    
    // Récupérer une ville
    $city = City::first();
    if (!$city) {
        echo "❌ PROBLÈME IDENTIFIÉ : Aucune ville trouvée\n";
        exit(1);
    }
    echo "Ville: " . $city->name . "\n";
    
    // Test de création d'annonce simple (sans IA)
    $ad = Ad::create([
        "title" => "Test annonce " . $city->name,
        "content" => "Contenu de test pour " . $city->name,
        "meta_title" => "Test meta",
        "meta_description" => "Test description",
        "meta_keywords" => "test",
        "city_id" => $city->id,
        "service_slug" => $service["slug"],
        "generation_type" => "test",
        "status" => "draft"
    ]);
    
    echo "✅ Annonce de test créée avec succès (ID: " . $ad->id . ")\n";
    
    // Nettoyer l'annonce de test
    $ad->delete();
    echo "🧹 Annonce de test supprimée\n";
    
} catch (Exception $e) {
    echo "❌ PROBLÈME IDENTIFIÉ : Erreur création annonce - " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
';

file_put_contents('test-simple-ad.php', $simpleTestScript);
$output = shell_exec('php test-simple-ad.php');
echo $output . "\n";
unlink('test-simple-ad.php');

echo "\n✅ Diagnostic terminé !\n";
echo "\n📋 Résumé des problèmes identifiés :\n";
echo "Si vous voyez des messages '❌ PROBLÈME IDENTIFIÉ', ce sont les erreurs à corriger.\n";
echo "Si tout est '✅', le problème est ailleurs.\n";
