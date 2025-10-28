<?php

// =====================================================
// SCRIPT DE TEST DE CONNECTIVITÉ API
// =====================================================
// Teste la connectivité à l'API OpenAI

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 Test de connectivité API OpenAI...\n\n";

// Récupérer la clé API
$apiKey = setting('openai_api_key') ?: setting('chatgpt_api_key');

if (!$apiKey) {
    echo "❌ Aucune clé API trouvée\n";
    echo "   Vérifiez que 'openai_api_key' ou 'chatgpt_api_key' est configuré dans les settings\n";
    exit(1);
}

echo "✅ Clé API trouvée: " . substr($apiKey, 0, 10) . "...\n\n";

// Test simple de connectivité
echo "🌐 Test de connectivité...\n";

try {
    $response = Http::timeout(30)
        ->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Test de connectivité - répondez simplement "OK"'
                ]
            ],
            'max_tokens' => 10,
            'temperature' => 0
        ]);

    if ($response->successful()) {
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? 'Pas de contenu';
        
        echo "✅ Connexion réussie!\n";
        echo "   Réponse: " . trim($content) . "\n";
        echo "   Status: " . $response->status() . "\n";
        echo "   Temps de réponse: " . $response->transferStats->getHandlerStat('total_time') . "s\n";
        
    } else {
        echo "❌ Erreur de connexion\n";
        echo "   Status: " . $response->status() . "\n";
        echo "   Réponse: " . $response->body() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'timeout') !== false) {
        echo "\n💡 Suggestions pour résoudre le timeout:\n";
        echo "   1. Vérifiez votre connexion internet\n";
        echo "   2. Essayez avec un modèle plus rapide (gpt-3.5-turbo)\n";
        echo "   3. Réduisez le nombre de tokens demandés\n";
        echo "   4. Vérifiez les restrictions de votre serveur\n";
    }
    
    if (strpos($e->getMessage(), '401') !== false) {
        echo "\n💡 Erreur d'authentification:\n";
        echo "   1. Vérifiez que votre clé API est correcte\n";
        echo "   2. Vérifiez que votre compte OpenAI a des crédits\n";
        echo "   3. Vérifiez que l'API est activée sur votre compte\n";
    }
}

echo "\n🔧 Configuration recommandée pour éviter les timeouts:\n";
echo "   - Timeout: 120 secondes\n";
echo "   - Retry: 3 tentatives\n";
echo "   - Modèle: gpt-3.5-turbo (plus rapide que gpt-4)\n";
echo "   - Max tokens: 3000 (au lieu de 4000)\n";

echo "\n✅ Test terminé!\n";
