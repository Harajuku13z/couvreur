<?php
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Test de l'API OpenAI avec vraie clé\n";
echo "=====================================\n\n";

// Demander la clé API à l'utilisateur
echo "Entrez votre clé API OpenAI (ou appuyez sur Entrée pour utiliser la clé de test): ";
$handle = fopen("php://stdin", "r");
$apiKey = trim(fgets($handle));
fclose($handle);

if (empty($apiKey)) {
    $apiKey = 'sk-test-key-replace-with-real-key';
    echo "Utilisation de la clé de test...\n\n";
} else {
    echo "Utilisation de la clé fournie...\n\n";
}

// Test de l'API
echo "1. Test de l'API OpenAI...\n";
try {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Écris un court paragraphe sur l\'hydrofugation de toiture.'
            ]
        ],
        'max_tokens' => 100,
        'temperature' => 0.7
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        echo "✅ API OpenAI : Fonctionne\n";
        echo "📝 Réponse: " . substr($content, 0, 100) . "...\n\n";
        
        // Mettre à jour la clé dans la base de données
        DB::table('settings')->updateOrInsert(
            ['key' => 'chatgpt_api_key'],
            ['value' => $apiKey, 'updated_at' => now()]
        );
        echo "✅ Clé API mise à jour dans la base de données\n";
        
    } else {
        echo "❌ Erreur API: " . $response->status() . "\n";
        echo "📝 Réponse: " . $response->body() . "\n\n";
        
        if ($response->status() === 401) {
            echo "⚠️  Clé API invalide ou expirée\n";
        } elseif ($response->status() === 429) {
            echo "⚠️  Limite de taux dépassée\n";
        } elseif ($response->status() === 500) {
            echo "⚠️  Erreur serveur OpenAI\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎯 Résumé:\n";
echo "===========\n";
if ($apiKey === 'sk-test-key-replace-with-real-key') {
    echo "⚠️  Clé de test utilisée - contenu générique\n";
    echo "💡 Pour du contenu personnalisé, utilisez une vraie clé API OpenAI\n";
} else {
    echo "✅ Clé API configurée - contenu personnalisé disponible\n";
}
