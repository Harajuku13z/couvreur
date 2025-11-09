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
        $groqApiKey = $groqApiKeySetting ? $groqApiKeySetting->value : null;
        
        $chatgptModelSetting = \App\Models\Setting::where('key', 'chatgpt_model')->first();
        // Par défaut, utiliser gpt-4-turbo qui supporte 128k tokens (idéal pour articles longs SEO)
        // Si un modèle est spécifié dans les options, l'utiliser en priorité
        if (isset($options['model'])) {
            $model = $options['model'];
        } elseif ($chatgptModelSetting && !empty($chatgptModelSetting->value)) {
            $model = $chatgptModelSetting->value;
        } else {
            $model = 'gpt-4-turbo';
        }
        
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 4000;
        $timeout = $options['timeout'] ?? 60;
        
        // S'assurer qu'on utilise un modèle qui supporte les tokens longs
        // Si max_tokens > 4096, forcer gpt-4-turbo ou gpt-4o
        if ($maxTokens > 4096) {
            // Forcer un modèle qui supporte les tokens longs
            if (!in_array($model, ['gpt-4-turbo', 'gpt-4-turbo-preview', 'gpt-4-0125-preview', 'gpt-4-1106-preview', 'gpt-4o', 'gpt-4o-2024-08-06'])) {
                Log::warning('AiService: Modèle incompatible avec max_tokens élevé, passage à gpt-4-turbo', [
                    'original_model' => $model,
                    'max_tokens' => $maxTokens
                ]);
                $model = 'gpt-4-turbo';
            }
        }
        
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
                Log::info('Tentative appel ChatGPT', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                    'messages_count' => count($messages),
                    'total_prompt_length' => strlen($prompt),
                    'api_key_length' => strlen($chatgptApiKey)
                ]);
                
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $chatgptApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout($timeout)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);
                
                Log::info('Réponse ChatGPT reçue', [
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    
                    if (empty($content)) {
                        Log::warning('ChatGPT: Réponse vide', [
                            'data_keys' => array_keys($data ?? []),
                            'choices_count' => isset($data['choices']) ? count($data['choices']) : 0
                        ]);
                        return null;
                    }
                    
                    Log::info('Réponse ChatGPT réussie', [
                        'content_length' => strlen($content),
                        'model' => $model,
                        'provider' => 'chatgpt'
                    ]);
                    
                    return [
                        'content' => $content,
                        'provider' => 'chatgpt'
                    ];
                } else {
                    $errorBody = $response->json();
                    $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
                    $errorType = $errorBody['error']['type'] ?? 'unknown';
                    $errorCode = $errorBody['error']['code'] ?? null;
                    
                    // Logger l'erreur complète pour diagnostic
                    $errorBodyFull = $response->body();
                    Log::error('Erreur API OpenAI', [
                        'status' => $response->status(),
                        'error_message' => $errorMessage,
                        'error_type' => $errorType,
                        'error_code' => $errorCode,
                        'response_body' => substr($errorBodyFull, 0, 1000),
                        'chatgpt_enabled' => $chatgptEnabled,
                        'api_key_length' => $chatgptApiKey ? strlen($chatgptApiKey) : 0
                    ]);
                    
                    // Si c'est une erreur de clé API invalide, ne pas essayer Groq
                    if ($response->status() === 401 || 
                        strpos(strtolower($errorMessage), 'invalid api key') !== false ||
                        strpos(strtolower($errorMessage), 'invalid_api_key') !== false ||
                        ($errorCode && strpos(strtolower($errorCode), 'invalid_api_key') !== false)) {
                        Log::error('ChatGPT: Clé API invalide, arrêt des tentatives', [
                            'error_message' => $errorMessage,
                            'status' => $response->status()
                        ]);
                        return null;
                    }
                    
                    // Si c'est une erreur de quota ou rate limit, logger mais continuer
                    if ($response->status() === 429 || 
                        strpos(strtolower($errorMessage), 'rate limit') !== false ||
                        strpos(strtolower($errorMessage), 'quota') !== false ||
                        strpos(strtolower($errorMessage), 'billing') !== false) {
                        Log::error('ChatGPT: Quota ou rate limit dépassé', [
                            'error_message' => $errorMessage,
                            'status' => $response->status()
                        ]);
                        return null;
                    }
                    
                    // Si ChatGPT est activé, ne pas utiliser Groq en fallback
                    // Forcer l'utilisation de ChatGPT uniquement
                    Log::error('ChatGPT: Erreur API, mais ChatGPT est activé donc pas de fallback Groq', [
                        'error_message' => $errorMessage,
                        'status' => $response->status(),
                        'error_type' => $errorType,
                        'error_code' => $errorCode
                    ]);
                    return null;
                }
            } catch (\Exception $e) {
                Log::error('Erreur appel ChatGPT', [
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]);
                // Si ChatGPT est activé, ne pas utiliser Groq en fallback
                if ($chatgptEnabled) {
                    return null;
                }
            }
        } else {
            Log::info('ChatGPT désactivé ou clé manquante, utilisation de Groq');
        }
        
        // Fallback sur Groq UNIQUEMENT si ChatGPT est désactivé ou clé manquante
        // Si ChatGPT est activé mais échoue, on ne doit PAS utiliser Groq
        if (!$chatgptEnabled && $groqApiKey) {
            try {
                $groqModelSetting = \App\Models\Setting::where('key', 'groq_model')->first();
                $groqModel = $options['groq_model'] ?? ($groqModelSetting ? $groqModelSetting->value : 'llama-3.1-8b-instant');
                
                Log::info('Tentative avec Groq', ['model' => $groqModel]);
                
                // Pour Groq on-demand: ajuster max_tokens pour respecter la limite TPM (6000)
                // Estimation: ~1 token = 4 caractères pour le texte
                $totalMessageLength = 0;
                foreach ($messages as $msg) {
                    $totalMessageLength += strlen($msg['content'] ?? '');
                }
                $estimatedInputTokens = (int)($totalMessageLength / 4);
                // Laisser une marge de sécurité: limiter à 5500 tokens totaux
                // Réduire max_tokens si nécessaire pour respecter la limite
                $groqMaxTokens = min($maxTokens, max(500, 5500 - $estimatedInputTokens));
                
                Log::info('Calcul tokens Groq', [
                    'estimated_input_tokens' => $estimatedInputTokens,
                    'original_max_tokens' => $maxTokens,
                    'adjusted_max_tokens' => $groqMaxTokens
                ]);
                
                $groqResponse = Http::withToken($groqApiKey)
                    ->timeout($timeout)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $groqModel,
                        'messages' => $messages,
                        'temperature' => $temperature,
                        'max_tokens' => $groqMaxTokens,
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
                    $status = $groqResponse->status();
                    $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
                    $errorType = $errorBody['error']['type'] ?? 'unknown';
                    $errorCode = $errorBody['error']['code'] ?? null;
                    
                    Log::error('Erreur API Groq', [
                        'status' => $status,
                        'error_message' => $errorMessage,
                        'error_type' => $errorType,
                        'error_code' => $errorCode,
                        'estimated_input_tokens' => $estimatedInputTokens ?? 0,
                        'groq_max_tokens' => $groqMaxTokens ?? 0,
                        'response_preview' => substr($groqResponse->body(), 0, 500),
                        'full_response' => config('app.debug') ? $groqResponse->body() : null
                    ]);
                    
                    // Si c'est une erreur de clé API invalide, arrêter les tentatives
                    if ($status === 401 || 
                        strpos(strtolower($errorMessage), 'invalid api key') !== false ||
                        strpos(strtolower($errorMessage), 'invalid_api_key') !== false ||
                        ($errorCode && strpos(strtolower($errorCode), 'invalid_api_key') !== false)) {
                        Log::error('Groq: Clé API invalide, arrêt des tentatives', [
                            'error_message' => $errorMessage
                        ]);
                        // Ne pas continuer avec les retries si la clé est invalide
                        return null;
                    }
                    
                    // Gérer spécifiquement l'erreur 413 (Request too large)
                    if ($status === 413 || (strpos($errorMessage, 'Request too large') !== false || strpos($errorMessage, 'TPM') !== false)) {
                        Log::warning('Limite TPM Groq dépassée, tentative avec prompt réduit', [
                            'original_input_length' => $totalMessageLength,
                            'estimated_tokens' => $estimatedInputTokens
                        ]);
                        
                        // Essayer avec un prompt réduit (tronquer le prompt utilisateur de 30%)
                        $reducedMessages = $messages;
                        if (isset($reducedMessages[count($reducedMessages) - 1]) && $reducedMessages[count($reducedMessages) - 1]['role'] === 'user') {
                            $originalUserPrompt = $reducedMessages[count($reducedMessages) - 1]['content'];
                            $reducedUserPrompt = substr($originalUserPrompt, 0, (int)(strlen($originalUserPrompt) * 0.7));
                            $reducedMessages[count($reducedMessages) - 1]['content'] = $reducedUserPrompt;
                            
                            // Recalculer avec le prompt réduit
                            $reducedLength = 0;
                            foreach ($reducedMessages as $msg) {
                                $reducedLength += strlen($msg['content'] ?? '');
                            }
                            $reducedInputTokens = (int)($reducedLength / 4);
                            $reducedMaxTokens = min($maxTokens, max(500, 5500 - $reducedInputTokens));
                            
                            try {
                                $retryResponse = Http::withToken($groqApiKey)
                                    ->timeout($timeout)
                                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                                        'model' => $groqModel,
                                        'messages' => $reducedMessages,
                                        'temperature' => $temperature,
                                        'max_tokens' => $reducedMaxTokens,
                                    ]);
                                
                                if ($retryResponse->successful()) {
                                    $groqData = $retryResponse->json();
                                    $groqContent = $groqData['choices'][0]['message']['content'] ?? '';
                                    
                                    Log::info('Réponse Groq reçue après réduction du prompt', [
                                        'content_length' => strlen($groqContent),
                                        'model' => $groqModel
                                    ]);
                                    
                                    return [
                                        'content' => $groqContent,
                                        'provider' => 'groq'
                                    ];
                                } else {
                                    $retryErrorBody = $retryResponse->json();
                                    Log::error('Échec retry Groq avec prompt réduit', [
                                        'status' => $retryResponse->status(),
                                        'error_message' => $retryErrorBody['error']['message'] ?? 'Unknown error',
                                        'reduced_input_length' => $reducedLength ?? 0,
                                        'reduced_input_tokens' => $reducedInputTokens ?? 0,
                                        'reduced_max_tokens' => $reducedMaxTokens ?? 0
                                    ]);
                                }
                            } catch (\Exception $retryException) {
                                Log::error('Exception lors du retry Groq avec prompt réduit', [
                                    'message' => $retryException->getMessage(),
                                    'trace' => config('app.debug') ? $retryException->getTraceAsString() : null
                                ]);
                            }
                        }
                    }
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
        
        // Log final si tout a échoué
        Log::error('AiService::callAI - Échec total : toutes les tentatives ont échoué', [
            'chatgpt_enabled' => $chatgptEnabled ?? false,
            'chatgpt_api_key_exists' => !empty($chatgptApiKey),
            'groq_api_key_exists' => !empty($groqApiKey),
            'prompt_length' => strlen($prompt),
            'system_message_length' => $systemMessage ? strlen($systemMessage) : 0,
            'total_input_length' => $totalMessageLength ?? 0
        ]);
        
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

