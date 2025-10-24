<?php
/**
 * Script pour vérifier et configurer la clé API ChatGPT
 */

echo "🔍 Vérification de la configuration ChatGPT...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Vérifier la clé API ChatGPT
echo "\n🔑 Vérification de la clé API ChatGPT :\n";
$output = shell_exec('php artisan tinker --execute="
\$apiKey = \App\Models\Setting::get(\'chatgpt_api_key\', \'NON_CONFIGURÉE\');
echo \"Clé API : \" . (\$apiKey === \'NON_CONFIGURÉE\' ? \'❌ NON CONFIGURÉE\' : \'✅ Configurée (\' . strlen(\$apiKey) . \' caractères)\') . \"\\n\";
if (\$apiKey !== \'NON_CONFIGURÉE\' && !empty(\$apiKey)) {
    echo \"Valeur : \" . substr(\$apiKey, 0, 10) . \"...\\n\";
}
"');
echo $output;

// Vérifier si la clé est configurée
$apiKeyOutput = shell_exec('php artisan tinker --execute="echo \App\Models\Setting::get(\'chatgpt_api_key\', \'NON_CONFIGURÉE\');"');
$apiKey = trim($apiKeyOutput);

if ($apiKey === 'NON_CONFIGURÉE' || empty($apiKey)) {
    echo "\n❌ PROBLÈME IDENTIFIÉ : Clé API ChatGPT non configurée\n";
    echo "\n📋 Solution :\n";
    echo "1. Allez sur https://platform.openai.com/api-keys\n";
    echo "2. Créez une nouvelle clé API\n";
    echo "3. Allez sur https://www.jd-renovation-service.fr/config\n";
    echo "4. Remplissez le champ 'Clé API OpenAI/ChatGPT'\n";
    echo "5. Cliquez sur 'Tester' pour valider la clé\n";
    echo "6. Sauvegardez la configuration\n";
    
    echo "\n🔧 Configuration automatique (si vous avez une clé API) :\n";
    echo "Entrez votre clé API ChatGPT (sk-...) : ";
    $handle = fopen("php://stdin", "r");
    $newApiKey = trim(fgets($handle));
    fclose($handle);
    
    if (!empty($newApiKey) && strpos($newApiKey, 'sk-') === 0) {
        echo "\n🔄 Configuration de la clé API...\n";
        $configScript = '<?php
use App\Models\Setting;
Setting::set("chatgpt_api_key", "' . $newApiKey . '", "string", "ai");
Setting::clearCache();
echo "✅ Clé API configurée avec succès !";
';
        file_put_contents('temp-config.php', $configScript);
        $result = shell_exec('php temp-config.php');
        echo $result . "\n";
        unlink('temp-config.php');
    } else {
        echo "❌ Clé API invalide. Veuillez utiliser le format sk-...\n";
    }
} else {
    echo "\n✅ Clé API ChatGPT configurée\n";
    
    // Test de connexion
    echo "\n🌐 Test de connexion à l'API ChatGPT :\n";
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

    file_put_contents('test-chatgpt-connection.php', $testScript);
    $output = shell_exec('php test-chatgpt-connection.php');
    echo $output . "\n";
    unlink('test-chatgpt-connection.php');
}

echo "\n✅ Vérification terminée !\n";
