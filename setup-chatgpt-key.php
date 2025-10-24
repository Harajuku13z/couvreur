<?php
/**
 * Script pour configurer une clé API ChatGPT
 */

echo "🔧 Configuration de la clé API ChatGPT...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Demander la clé API
echo "📋 Pour configurer la clé API ChatGPT :\n";
echo "1. Allez sur https://platform.openai.com/api-keys\n";
echo "2. Créez une nouvelle clé API\n";
echo "3. Copiez la clé (format: sk-...)\n\n";

echo "Entrez votre clé API ChatGPT : ";
$handle = fopen("php://stdin", "r");
$apiKey = trim(fgets($handle));
fclose($handle);

if (empty($apiKey)) {
    echo "❌ Aucune clé API fournie\n";
    exit(1);
}

if (strpos($apiKey, 'sk-') !== 0) {
    echo "❌ Format de clé API invalide. Doit commencer par 'sk-'\n";
    exit(1);
}

echo "\n🔄 Configuration de la clé API...\n";

// Script de configuration
$configScript = '<?php
use App\Models\Setting;

try {
    Setting::set("chatgpt_api_key", "' . $apiKey . '", "string", "ai");
    Setting::clearCache();
    echo "✅ Clé API configurée avec succès !\n";
    echo "Clé: " . substr("' . $apiKey . '", 0, 10) . "...\n";
} catch (Exception $e) {
    echo "❌ Erreur lors de la configuration: " . $e->getMessage() . "\n";
}
';

file_put_contents('temp-setup.php', $configScript);
$result = shell_exec('php temp-setup.php');
echo $result . "\n";
unlink('temp-setup.php');

// Test de la clé
echo "\n🌐 Test de la clé API...\n";
$testScript = '<?php
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

$apiKey = Setting::get("chatgpt_api_key");
if (!$apiKey) {
    echo "❌ Clé API non trouvée";
    exit(1);
}

try {
    $response = Http::timeout(30)->post("https://api.openai.com/v1/chat/completions", [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => "Test - réponds OK"]
        ],
        "max_tokens" => 5
    ], [
        "Authorization" => "Bearer " . $apiKey,
        "Content-Type" => "application/json"
    ]);
    
    if ($response->successful()) {
        echo "✅ Test réussi ! La clé API fonctionne.\n";
    } else {
        echo "❌ Test échoué (Status: " . $response->status() . ")\n";
        echo "Réponse: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
';

file_put_contents('temp-test.php', $testScript);
$testResult = shell_exec('php temp-test.php');
echo $testResult . "\n";
unlink('temp-test.php');

echo "\n✅ Configuration terminée !\n";
echo "Vous pouvez maintenant utiliser la génération d'annonces.\n";
