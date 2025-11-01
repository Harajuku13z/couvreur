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
        // VIDER LE CACHE pour s'assurer de lire les dernières valeurs
        \Cache::forget('setting_chatgpt_enabled');
        \Cache::forget('setting_chatgpt_api_key');
        \Cache::forget('setting_groq_api_key');
        \Cache::forget('setting_groq_model');
        \Cache::forget('setting_chatgpt_model');
        
        // Lire directement depuis DB pour éviter le cache
        $chatgptEnabledSetting = \App\Models\Setting::where('key', 'chatgpt_enabled')->first();
        $chatgptEnabled = $chatgptEnabledSetting ? ($chatgptEnabledSetting->type === 'boolean' ? filter_var($chatgptEnabledSetting->value, FILTER_VALIDATE_BOOLEAN) : $chatgptEnabledSetting->value) : true;
        
        $chatgptApiKeySetting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
        $chatgptApiKey = $chatgptApiKeySetting ? $chatgptApiKeySetting->value : null;
        
        $groqApiKeySetting = \App\Models\Setting::where('key', 'groq_api_key')->first();
        $groqApiKey = $groqApiKeySetting ? $groqApiKeySetting->value : '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';
        
        $chatgptModelSetting = \App\Models\Setting::where('key', 'chatgpt_model')->first();
        $model = $options['model'] ?? ($chatgptModelSetting ? $chatgptModelSetting->value : 'gpt-4o');
        
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 4000;
        $timeout = $options['timeout'] ?? 60;
        
        Log::info('AiService::callAI - Clés API récupérées', [
            'chatgpt_enabled' => $chatgptEnabled,
            'chatgpt_api_key_exists' => !empty($chatgptApiKey),
            'chatgpt_api_key_length' => $chatgptApiKey ? strlen($chatgptApiKey) : 0,
            'groq_api_key_exists' => !empty($groqApiKey),
            'groq_api_key_length' => $groqApiKey ? strlen($groqApiKey) : 0,
            'model' => $model
        ]);
        
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
                    $errorBody = $response->json();
                    Log::warning('Erreur API OpenAI, tentative avec Groq', [
                        'status' => $response->status(),
                        'error_message' => $errorBody['error']['message'] ?? 'Unknown error',
                        'error_type' => $errorBody['error']['type'] ?? 'unknown',
                        'response_preview' => substr($response->body(), 0, 500)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur appel ChatGPT', [
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]);
            }
        } else {
            Log::info('ChatGPT désactivé ou clé manquante, utilisation de Groq');
        }
        
        // Fallback sur Groq si ChatGPT a échoué ou est désactivé
        if ($groqApiKey) {
            try {
                $groqModelSetting = \App\Models\Setting::where('key', 'groq_model')->first();
                $groqModel = $options['groq_model'] ?? ($groqModelSetting ? $groqModelSetting->value : 'llama-3.1-8b-instant');
                
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
                    $errorBody = $groqResponse->json();
                    Log::error('Erreur API Groq', [
                        'status' => $groqResponse->status(),
                        'error_message' => $errorBody['error']['message'] ?? 'Unknown error',
                        'error_type' => $errorBody['error']['type'] ?? 'unknown',
                        'response_preview' => substr($groqResponse->body(), 0, 500)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erreur appel Groq', [
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]);
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

