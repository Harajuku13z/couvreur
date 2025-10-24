<?php
/**
 * Script de test pour la génération d'annonces
 */

echo "🔍 Test de génération d'annonces...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Test 1: Vérifier la clé API ChatGPT
echo "\n🔑 Test de la clé API ChatGPT :\n";
$output = shell_exec('php artisan tinker --execute="echo \App\Models\Setting::get(\'chatgpt_api_key\', \'NON_CONFIGURÉE\');"');
$apiKey = trim($output);
echo "Clé API : " . ($apiKey === 'NON_CONFIGURÉE' ? '❌ NON CONFIGURÉE' : '✅ Configurée (' . strlen($apiKey) . ' caractères)') . "\n";

if ($apiKey === 'NON_CONFIGURÉE' || empty($apiKey)) {
    echo "❌ Clé API ChatGPT non configurée\n";
    echo "📋 Pour configurer la clé API :\n";
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
foreach($services as $service) {
    echo \"- \" . $service[\"name\"] . \" (\" . $service[\"slug\"] . \")\\n\";
}
"');
echo $servicesOutput;

// Test 3: Vérifier les villes
echo "\n🏙️ Test des villes :\n";
$citiesOutput = shell_exec('php artisan tinker --execute="
$cities = \App\Models\City::take(5)->get();
echo \"Villes disponibles: \" . \App\Models\City::count() . \"\\n\";
foreach($cities as $city) {
    echo \"- \" . $city->name . \" (\" . $city->postal_code . \")\\n\";
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
    echo "❌ Clé API non configurée";
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
        $data = $response->json();
        $content = $data["choices"][0]["message"]["content"] ?? "Pas de contenu";
        echo "✅ Connexion réussie (Status: " . $response->status() . ")\n";
        echo "Réponse: " . $content . "\n";
    } else {
        echo "❌ Erreur de connexion (Status: " . $response->status() . ")\n";
        echo "Réponse: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
';

file_put_contents('test-api-simple.php', $testScript);
$output = shell_exec('php test-api-simple.php');
echo $output . "\n";
unlink('test-api-simple.php');

// Test 5: Test de génération d'annonce complète
echo "\n🎯 Test de génération d'annonce complète :\n";
$fullTestScript = '<?php
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\City;
use App\Models\Ad;

try {
    echo "🔄 Test de génération complète...\n";
    
    // Récupérer un service
    $services = Setting::get("services", "[]");
    $services = is_string($services) ? json_decode($services, true) : $services;
    if (empty($services)) {
        echo "❌ Aucun service configuré";
        exit(1);
    }
    $service = $services[0];
    echo "Service: " . $service["name"] . "\n";
    
    // Récupérer une ville
    $city = City::first();
    if (!$city) {
        echo "❌ Aucune ville trouvée";
        exit(1);
    }
    echo "Ville: " . $city->name . "\n";
    
    // Générer le contenu IA
    $apiKey = Setting::get("chatgpt_api_key");
    $prompt = "Génère une annonce SEO optimisée pour {$service["name"]} à {$city->name} ({$city->postal_code}). 
    
    Format de réponse JSON :
    {
        \"title\": \"Titre SEO optimisé (max 60 caractères)\",
        \"content\": \"Contenu HTML complet de l\'annonce\",
        \"meta_title\": \"Titre meta SEO (max 60 caractères)\",
        \"meta_description\": \"Description meta SEO (max 160 caractères)\",
        \"meta_keywords\": \"Mots-clés SEO séparés par virgules\"
    }
    
    Le contenu doit être en HTML avec des balises appropriées.
    ";
    
    $response = Http::timeout(60)->post("https://api.openai.com/v1/chat/completions", [
        "model" => "gpt-4",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 2000
    ], [
        "Authorization" => "Bearer " . $apiKey,
        "Content-Type" => "application/json"
    ]);
    
    if (!$response->successful()) {
        echo "❌ Erreur API: " . $response->status() . " - " . $response->body() . "\n";
        exit(1);
    }
    
    $data = $response->json();
    $content = $data["choices"][0]["message"]["content"] ?? "";
    
    echo "✅ Contenu généré (" . strlen($content) . " caractères)\n";
    
    // Parser le JSON
    $aiContent = json_decode($content, true);
    if (!$aiContent) {
        echo "❌ Erreur de parsing JSON\n";
        echo "Contenu reçu: " . substr($content, 0, 200) . "...\n";
        exit(1);
    }
    
    echo "✅ JSON parsé avec succès\n";
    echo "Titre: " . ($aiContent["title"] ?? "N/A") . "\n";
    echo "Meta title: " . ($aiContent["meta_title"] ?? "N/A") . "\n";
    
    // Test de création d'annonce
    $ad = Ad::create([
        "title" => $aiContent["title"] ?? "Test",
        "content" => $aiContent["content"] ?? "Test content",
        "meta_title" => $aiContent["meta_title"] ?? "Test meta",
        "meta_description" => $aiContent["meta_description"] ?? "Test description",
        "meta_keywords" => $aiContent["meta_keywords"] ?? "test",
        "city_id" => $city->id,
        "service_slug" => $service["slug"],
        "generation_type" => "test",
        "status" => "draft"
    ]);
    
    echo "✅ Annonce créée avec succès (ID: " . $ad->id . ")\n";
    
    // Nettoyer l'annonce de test
    $ad->delete();
    echo "🧹 Annonce de test supprimée\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
';

file_put_contents('test-full-generation.php', $fullTestScript);
$output = shell_exec('php test-full-generation.php');
echo $output . "\n";
unlink('test-full-generation.php');

echo "\n✅ Test terminé !\n";
