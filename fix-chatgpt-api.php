<?php
/**
 * Script de correction rapide pour la clé API ChatGPT
 */

echo "🔧 Correction de la clé API ChatGPT...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Vérifier la clé API actuelle
echo "\n🔍 Vérification de la clé API actuelle :\n";
$checkScript = '<?php
use App\Models\Setting;
$apiKey = Setting::get("chatgpt_api_key", "NON_CONFIGURÉE");
if ($apiKey === "NON_CONFIGURÉE" || empty($apiKey)) {
    echo "❌ Clé API non configurée\n";
} else {
    echo "✅ Clé API configurée (" . strlen($apiKey) . " caractères)\n";
    echo "Valeur: " . substr($apiKey, 0, 10) . "...\n";
}
';

file_put_contents('temp-check.php', $checkScript);
$result = shell_exec('php temp-check.php');
echo $result . "\n";
unlink('temp-check.php');

// Si la clé n'est pas configurée, demander à l'utilisateur
if (strpos($result, '❌') !== false) {
    echo "\n📋 La clé API ChatGPT n'est pas configurée.\n";
    echo "Pour obtenir une clé API :\n";
    echo "1. Allez sur https://platform.openai.com/api-keys\n";
    echo "2. Créez une nouvelle clé API\n";
    echo "3. Copiez la clé (format: sk-...)\n\n";
    
    echo "Entrez votre clé API ChatGPT (ou appuyez sur Entrée pour ignorer) : ";
    $handle = fopen("php://stdin", "r");
    $apiKey = trim(fgets($handle));
    fclose($handle);
    
    if (!empty($apiKey) && strpos($apiKey, 'sk-') === 0) {
        echo "\n🔄 Configuration de la clé API...\n";
        
        $configScript = '<?php
use App\Models\Setting;
try {
    Setting::set("chatgpt_api_key", "' . $apiKey . '", "string", "ai");
    Setting::clearCache();
    echo "✅ Clé API configurée avec succès !\n";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
';
        
        file_put_contents('temp-config.php', $configScript);
        $configResult = shell_exec('php temp-config.php');
        echo $configResult . "\n";
        unlink('temp-config.php');
        
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
            ["role" => "user", "content" => "Test"]
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
    } else {
        echo "❌ Clé API invalide ou non fournie\n";
    }
} else {
    echo "\n✅ Clé API déjà configurée\n";
    
    // Test de la clé existante
    echo "\n🌐 Test de la clé API existante...\n";
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
            ["role" => "user", "content" => "Test"]
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
}

echo "\n✅ Diagnostic terminé !\n";
echo "\n📋 Prochaines étapes :\n";
echo "1. Si la clé API est configurée et testée, essayez de générer des annonces\n";
echo "2. Si la clé API n'est pas configurée, allez sur https://www.jd-renovation-service.fr/config\n";
echo "3. Remplissez le champ 'Clé API OpenAI/ChatGPT' et sauvegardez\n";
