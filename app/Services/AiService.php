<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Appeler l'API IA (ChatGPT ou Groq en fallback)
     * 
     * @param string $prompt Le prompt à envoyer
     * @param string $systemMessage Le message système (optionnel)
     * @param array $options Options supplémentaires (model, temperature, max_tokens, etc.)
     * @return array|null Retourne ['content' => string, 'provider' => 'chatgpt'|'groq'] ou null en cas d'échec
     */
    public static function callAI($prompt, $systemMessage = null, $options = [])
    {
        $chatgptEnabled = setting('chatgpt_enabled', true);
        $chatgptApiKey = setting('chatgpt_api_key');
        $groqApiKey = setting('groq_api_key', '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');
        
        $model = $options['model'] ?? setting('chatgpt_model', 'gpt-4o');
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 4000;
        $timeout = $options['timeout'] ?? 60;
        
        $messages = [];
        if ($systemMessage) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];
        
        // Essayer ChatGPT d'abord si activé et clé disponible
        if ($chatgptEnabled && $chatgptApiKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $chatgptApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout($timeout)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    
                    Log::info('Réponse ChatGPT reçue', [
                        'content_length' => strlen($content),
                        'model' => $model
                    ]);
                    
                    return [
                        'content' => $content,
                        'provider' => 'chatgpt'
                    ];
                } else {
                    Log::warning('Erreur API OpenAI, tentative avec Groq', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur appel ChatGPT: ' . $e->getMessage());
            }
        } else {
            Log::info('ChatGPT désactivé ou clé manquante, utilisation de Groq');
        }
        
        // Fallback sur Groq si ChatGPT a échoué ou est désactivé
        if ($groqApiKey) {
            try {
                $groqModel = $options['groq_model'] ?? setting('groq_model', 'llama-3.1-8b-instant');
                
                Log::info('Tentative avec Groq', ['model' => $groqModel]);
                
                $groqResponse = Http::withToken($groqApiKey)
                    ->timeout($timeout)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $groqModel,
                        'messages' => $messages,
                        'temperature' => $temperature,
                        'max_tokens' => $maxTokens,
                    ]);
                
                if ($groqResponse->successful()) {
                    $groqData = $groqResponse->json();
                    $groqContent = $groqData['choices'][0]['message']['content'] ?? '';
                    
                    Log::info('Réponse Groq reçue', [
                        'content_length' => strlen($groqContent),
                        'model' => $groqModel
                    ]);
                    
                    return [
                        'content' => $groqContent,
                        'provider' => 'groq'
                    ];
                } else {
                    Log::error('Erreur API Groq', [
                        'status' => $groqResponse->status(),
                        'response' => $groqResponse->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur appel Groq: ' . $e->getMessage());
            }
        } else {
            Log::warning('Clé API Groq manquante, impossible d\'utiliser le fallback');
        }
        
        return null;
    }
    
    /**
     * Générer une image avec DALL-E (seul ChatGPT supporte les images)
     */
    public static function generateImage($prompt, $options = [])
    {
        $chatgptEnabled = setting('chatgpt_enabled', true);
        $chatgptApiKey = setting('chatgpt_api_key');
        
        if (!$chatgptEnabled || !$chatgptApiKey) {
            Log::warning('Génération d\'image impossible : ChatGPT désactivé ou clé manquante');
            return null;
        }
        
        $size = $options['size'] ?? '1024x1024';
        $n = $options['n'] ?? 1;
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $chatgptApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/images/generations', [
                'prompt' => $prompt,
                'n' => $n,
                'size' => $size,
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération image: ' . $e->getMessage());
        }
        
        return null;
    }
}

