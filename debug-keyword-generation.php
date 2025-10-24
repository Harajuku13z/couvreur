<?php

require_once 'vendor/autoload.php';

use App\Models\City;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Simuler l'environnement Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Diagnostic de la génération d'annonces par mot-clé\n";
echo "================================================\n\n";

// 1. Vérifier la clé API
echo "1. Vérification de la clé API ChatGPT...\n";
$apiKey = Setting::get('chatgpt_api_key');
if (!$apiKey) {
    $setting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
    $apiKey = $setting ? $setting->value : null;
}

if (!$apiKey) {
    echo "❌ ERREUR: Clé API ChatGPT non configurée\n";
    echo "   Configurez-la dans /config\n";
    exit(1);
} else {
    echo "✅ Clé API trouvée (longueur: " . strlen($apiKey) . " caractères)\n";
}

// 2. Vérifier les villes favorites
echo "\n2. Vérification des villes favorites...\n";
$favoriteCities = City::where('is_favorite', true)->get();
echo "   Villes favorites trouvées: " . $favoriteCities->count() . "\n";

if ($favoriteCities->count() === 0) {
    echo "⚠️  ATTENTION: Aucune ville favorite trouvée\n";
    echo "   Ajoutez des villes favorites dans /admin/cities\n";
} else {
    echo "   Première ville: " . $favoriteCities->first()->name . "\n";
}

// 3. Test de l'API ChatGPT
echo "\n3. Test de l'API ChatGPT...\n";
try {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json'
    ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'user',
                'content' => 'Test de connexion - répondez simplement "OK"'
            ]
        ],
        'max_tokens' => 10,
        'temperature' => 0.1
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        echo "✅ API ChatGPT fonctionne: " . trim($content) . "\n";
    } else {
        echo "❌ ERREUR API: " . $response->status() . " - " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
}

// 4. Test de génération complète
echo "\n4. Test de génération complète...\n";
if ($favoriteCities->count() > 0) {
    $testCity = $favoriteCities->first();
    $testKeywords = "rénovation toiture";
    
    echo "   Ville de test: " . $testCity->name . "\n";
    echo "   Mots-clés de test: " . $testKeywords . "\n";
    
    try {
        // Simuler la génération
        $prompt = "Génère une annonce SEO optimisée pour les mots-clés : {$testKeywords} à {$testCity->name} ({$testCity->postal_code}). 
        
        Format de réponse JSON :
        {
            \"title\": \"Titre SEO optimisé (max 60 caractères)\",
            \"content\": \"Contenu HTML simple\",
            \"meta_title\": \"Titre meta SEO (max 60 caractères)\",
            \"meta_description\": \"Description meta SEO (max 160 caractères)\"
        }";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            echo "✅ Génération IA réussie\n";
            echo "   Contenu reçu: " . strlen($content) . " caractères\n";
            echo "   Aperçu: " . substr($content, 0, 100) . "...\n";
            
            // Tester le parsing JSON
            $jsonStart = strpos($content, '{');
            $jsonEnd = strrpos($content, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $parsed = json_decode($jsonContent, true);
                
                if ($parsed && isset($parsed['title'])) {
                    echo "✅ Parsing JSON réussi\n";
                    echo "   Titre: " . $parsed['title'] . "\n";
                } else {
                    echo "❌ ERREUR: Parsing JSON échoué\n";
                    echo "   JSON extrait: " . substr($jsonContent, 0, 200) . "...\n";
                }
            } else {
                echo "❌ ERREUR: Aucun JSON trouvé dans la réponse\n";
            }
        } else {
            echo "❌ ERREUR API: " . $response->status() . " - " . $response->body() . "\n";
        }
    } catch (Exception $e) {
        echo "❌ ERREUR: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Impossible de tester - aucune ville favorite\n";
}

// 5. Vérifier les logs récents
echo "\n5. Vérification des logs récents...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $recentLogs = array_slice(explode("\n", $logs), -50);
    $keywordLogs = array_filter($recentLogs, function($line) {
        return strpos($line, 'Keyword cities') !== false || strpos($line, 'ChatGPT') !== false;
    });
    
    if (count($keywordLogs) > 0) {
        echo "   Logs récents trouvés:\n";
        foreach (array_slice($keywordLogs, -5) as $log) {
            echo "   " . $log . "\n";
        }
    } else {
        echo "   Aucun log récent trouvé\n";
    }
} else {
    echo "   Fichier de log non trouvé\n";
}

echo "\n🏁 Diagnostic terminé\n";
echo "===================\n";
