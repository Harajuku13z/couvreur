<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "🔍 Diagnostic de l'API OpenAI\n";
echo "============================\n\n";

try {
    // 1. Vérifier la clé API
    echo "1. Vérification de la clé API...\n";
    $apiKey = setting('chatgpt_api_key');
    if ($apiKey) {
        echo "✅ Clé API configurée: " . substr($apiKey, 0, 10) . "...\n";
    } else {
        echo "❌ Clé API non configurée\n";
        exit(1);
    }
    echo "\n";
    
    // 2. Test simple de l'API
    echo "2. Test simple de l'API OpenAI...\n";
    
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Écris une phrase sur la rénovation de toiture.'],
        ],
        'max_tokens' => 100,
        'temperature' => 0.7,
    ]);
    
    if ($response->successful()) {
        $responseData = $response->json();
        $content = $responseData['choices'][0]['message']['content'] ?? '';
        echo "✅ API fonctionne: " . $content . "\n";
    } else {
        echo "❌ Erreur API: " . $response->status() . "\n";
        echo "   Réponse: " . $response->body() . "\n";
    }
    echo "\n";
    
    // 3. Test avec le modèle configuré
    echo "3. Test avec le modèle configuré...\n";
    $model = setting('chatgpt_model', 'gpt-4o');
    echo "   Modèle: {$model}\n";
    
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un expert en rédaction web SEO spécialisé dans la rénovation de bâtiments.'],
            ['role' => 'user', 'content' => 'Écris un paragraphe sur l\'hydrofuge de toiture.'],
        ],
        'max_tokens' => 200,
        'temperature' => 0.7,
    ]);
    
    if ($response->successful()) {
        $responseData = $response->json();
        $content = $responseData['choices'][0]['message']['content'] ?? '';
        echo "✅ Modèle {$model} fonctionne: " . substr($content, 0, 100) . "...\n";
    } else {
        echo "❌ Erreur avec le modèle {$model}: " . $response->status() . "\n";
        echo "   Réponse: " . $response->body() . "\n";
    }
    echo "\n";
    
    // 4. Test avec un prompt plus long
    echo "4. Test avec un prompt plus long...\n";
    
    $longPrompt = "Tu es un rédacteur web professionnel et expert en rénovation de bâtiments.

MISSION : Rédiger un article complet, informatif et optimisé SEO sur le sujet : Comment hydrofuger sa toiture pour une protection optimale

STRUCTURE HTML OBLIGATOIRE :
<div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8\">
    <h1 class=\"text-4xl font-bold text-gray-900 mb-6 text-center\">Comment hydrofuger sa toiture pour une protection optimale</h1>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🏠 Introduction</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Introduction engageante avec statistiques]</p>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🛠️ Techniques d'hydrofuge</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Contenu technique détaillé]</p>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">💡 Conseils pratiques</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Conseils pratiques]</p>
    </div>
    
    <div class=\"bg-green-50 p-4 rounded-lg mb-4\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">❓ Questions Fréquentes</h2>
        <div class=\"mb-4\">
            <h3 class=\"font-bold text-gray-800\">Qu'est-ce que l'hydrofuge ?</h3>
            <p class=\"text-gray-700\">[Réponse détaillée]</p>
        </div>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🎯 Conclusion</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Conclusion avec appel à l'action]</p>
    </div>
</div>

CONTENU À GÉNÉRER (2000-3000 mots) :
• Article original et informatif sur l'hydrofuge de toiture
• Contenu technique détaillé et précis
• Conseils pratiques pour les propriétaires
• Statistiques et données concrètes
• FAQ pertinente avec 5-7 questions
• Ton professionnel mais accessible

IMPORTANT :
• Générer UNIQUEMENT le HTML complet
• Ne pas inclure de texte explicatif
• Utiliser des emojis appropriés
• Rendre le contenu actionnable
• Optimiser pour le SEO

Génère maintenant l'article HTML complet sur l'hydrofuge de toiture.";
    
    echo "   Longueur du prompt: " . strlen($longPrompt) . " caractères\n";
    
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'Tu es un expert en rédaction web SEO spécialisé dans la rénovation de bâtiments.'],
            ['role' => 'user', 'content' => $longPrompt],
        ],
        'max_tokens' => 4000,
        'temperature' => 0.7,
        'top_p' => 0.9,
        'frequency_penalty' => 0.1,
        'presence_penalty' => 0.1,
    ]);
    
    if ($response->successful()) {
        $responseData = $response->json();
        $content = $responseData['choices'][0]['message']['content'] ?? '';
        echo "✅ Prompt long fonctionne (" . strlen($content) . " caractères)\n";
        
        // Sauvegarder le résultat
        file_put_contents('debug-openai-result.html', $content);
        echo "   📄 Résultat sauvegardé dans: debug-openai-result.html\n";
        
        // Vérifier le contenu
        if (strpos($content, 'hydrofuge') !== false) {
            echo "   ✅ Contenu spécifique détecté\n";
        } else {
            echo "   ❌ Contenu générique\n";
        }
        
        if (strpos($content, 'max-w-7xl') !== false) {
            echo "   ✅ Structure HTML détectée\n";
        } else {
            echo "   ❌ Structure HTML manquante\n";
        }
        
    } else {
        echo "❌ Erreur avec le prompt long: " . $response->status() . "\n";
        echo "   Réponse: " . $response->body() . "\n";
    }
    
    echo "\n🎯 Résumé du diagnostic:\n";
    echo "========================\n";
    echo "✅ Clé API: Configurée\n";
    echo "✅ Test simple: " . ($response->successful() ? 'Réussi' : 'Échec') . "\n";
    echo "✅ Modèle {$model}: " . ($response->successful() ? 'Fonctionne' : 'Problème') . "\n";
    echo "✅ Prompt long: " . ($response->successful() ? 'Fonctionne' : 'Échec') . "\n";
    echo "\n💡 Si tous les tests passent, le problème vient du contrôleur.\n";
    echo "   Si les tests échouent, vérifiez votre clé API OpenAI.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du diagnostic: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
