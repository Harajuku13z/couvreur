<?php
/**
 * Script de test pour la configuration ChatGPT
 */

echo "🔍 Test de la configuration ChatGPT...\n\n";

// Vérifier si Laravel est accessible
if (!file_exists('artisan')) {
    echo "❌ Fichier artisan non trouvé. Êtes-vous dans le bon répertoire ?\n";
    exit(1);
}

echo "✅ Laravel détecté\n";

// Tester la clé API
echo "\n🔑 Test de la clé API ChatGPT :\n";
$output = shell_exec('php artisan tinker --execute="echo \App\Models\Setting::get(\'chatgpt_api_key\', \'NON_CONFIGURÉE\');"');
echo "Clé API : " . trim($output) . "\n";

if (trim($output) === 'NON_CONFIGURÉE' || empty(trim($output))) {
    echo "❌ Clé API ChatGPT non configurée\n";
    echo "📋 Pour configurer la clé API :\n";
    echo "   1. Allez sur https://platform.openai.com/api-keys\n";
    echo "   2. Créez une nouvelle clé API\n";
    echo "   3. Ajoutez-la dans les paramètres de l'application\n";
} else {
    echo "✅ Clé API ChatGPT configurée\n";
}

// Tester la connexion à l'API
echo "\n🌐 Test de connexion à l'API ChatGPT :\n";
$testScript = '<?php
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

$apiKey = Setting::get("chatgpt_api_key");
if (!$apiKey) {
    echo "Clé API non configurée";
    exit(1);
}

try {
    $response = Http::timeout(10)->post("https://api.openai.com/v1/chat/completions", [
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
        echo "✅ Connexion réussie (Status: " . $response->status() . ")";
    } else {
        echo "❌ Erreur de connexion (Status: " . $response->status() . ")";
        echo "\nRéponse: " . $response->body();
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
}
';

file_put_contents('test-api.php', $testScript);
$output = shell_exec('php test-api.php');
echo $output . "\n";
unlink('test-api.php');

echo "\n📋 Vérification des logs :\n";
if (file_exists('storage/logs/laravel.log')) {
    $logContent = file_get_contents('storage/logs/laravel.log');
    $recentLogs = array_slice(explode("\n", $logContent), -20);
    echo "Dernières entrées de log :\n";
    foreach ($recentLogs as $log) {
        if (strpos($log, 'ChatGPT') !== false || strpos($log, 'AI') !== false) {
            echo "  " . $log . "\n";
        }
    }
} else {
    echo "Aucun fichier de log trouvé\n";
}

echo "\n✅ Test terminé !\n";
