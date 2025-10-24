<?php
/**
 * Script pour configurer directement la clé API ChatGPT
 */

echo "🔧 Configuration directe de la clé API ChatGPT...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Demander la clé API
echo "📋 Entrez votre clé API ChatGPT (format: sk-...) : ";
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

file_put_contents('temp-configure.php', $configScript);
$result = shell_exec('php temp-configure.php');
echo $result . "\n";
unlink('temp-configure.php');

// Test de la clé
echo "\n🌐 Test de la clé API...\n";
$testScript = '<?php
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

$apiKey = Setting::get("chatgpt_api_key");
if (!$apiKey) {
    echo "❌ Clé API non trouvée\n";
    exit(1);
}

try {
    $response = Http::timeout(30)->post("https://api.openai.com/v1/chat/completions", [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => "Test de connexion"]
        ],
        "max_tokens" => 10
    ], [
        "Authorization" => "Bearer " . $apiKey,
        "Content-Type" => "application/json"
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        $content = $data["choices"][0]["message"]["content"] ?? "Pas de contenu";
        echo "✅ Test réussi ! La clé API fonctionne.\n";
        echo "Réponse: " . $content . "\n";
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
