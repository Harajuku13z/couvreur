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
        
        // La clé API sera utilisée directement pour créer le client
        
        $groqApiKeySetting = \App\Models\Setting::where('key', 'groq_api_key')->first();
        $groqApiKey = $groqApiKeySetting ? $groqApiKeySetting->value : null;
        
        // Vérifier le fournisseur par défaut
        $defaultProviderSetting = \App\Models\Setting::where('key', 'default_ai_provider')->first();
        $defaultProvider = $defaultProviderSetting ? $defaultProviderSetting->value : 'chatgpt';
        
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 4000;
        $timeout = $options['timeout'] ?? 60;
        
        $chatgptModelSetting = \App\Models\Setting::where('key', 'chatgpt_model')->first();
        
        // PRIORITÉ 1: Si un modèle est spécifié dans les options, l'utiliser
        if (isset($options['model']) && !empty($options['model'])) {
            $model = $options['model'];
        } 
        // PRIORITÉ 2: Utiliser le modèle de la DB
        elseif ($chatgptModelSetting && !empty($chatgptModelSetting->value)) {
            $model = $chatgptModelSetting->value;
        } 
        // PRIORITÉ 3: Si max_tokens > 4096, utiliser gpt-4o (support 128k tokens)
        elseif ($maxTokens > 4096) {
            $model = 'gpt-4o';
            Log::info('AiService: max_tokens > 4096, utilisation de gpt-4o par défaut', [
                'max_tokens' => $maxTokens
            ]);
        }
        // PRIORITÉ 4: Par défaut gpt-4o
        else {
            $model = 'gpt-4o';
        }
        
        // CRITIQUE: Si max_tokens > 4096, FORCER un modèle compatible
        if ($maxTokens > 4096) {
            // Modèles compatibles avec tokens longs
            $compatibleModels = [
                'gpt-4o',                  // GPT-4o (recommandé, plus récent)
                'gpt-4o-2024-08-06',       // GPT-4o avec date
                'gpt-4-turbo',             // gpt-4-turbo
                'gpt-4-turbo-preview',     // Variante
                'gpt-4-0125-preview',      // Variante
                'gpt-4-1106-preview'       // Variante
            ];
            
            if (!in_array($model, $compatibleModels)) {
                $originalModel = $model;
                $model = 'gpt-4o';
                Log::warning('AiService: Modèle incompatible avec max_tokens élevé, passage à gpt-4o', [
                    'original_model' => $originalModel,
                    'new_model' => $model,
                    'max_tokens' => $maxTokens
                ]);
            }
        }
        
        Log::info('AiService::callAI - Clés API récupérées', [
            'chatgpt_enabled' => $chatgptEnabled,
            'chatgpt_api_key_exists' => !empty($chatgptApiKey),
            'chatgpt_api_key_length' => $chatgptApiKey ? strlen($chatgptApiKey) : 0,
            'groq_api_key_exists' => !empty($groqApiKey),
            'groq_api_key_length' => $groqApiKey ? strlen($groqApiKey) : 0,
            'default_provider' => $defaultProvider,
            'model' => $model
        ]);
        
        $messages = [];
        if ($systemMessage) {
            $messages[] = ['role' => 'system', 'content' => $systemMessage];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];
        
        // Si Groq est le fournisseur par défaut et disponible, l'utiliser directement
        if ($defaultProvider === 'groq' && $groqApiKey) {
            Log::info('AiService: Groq sélectionné comme fournisseur par défaut, utilisation directe');
            // Passer directement à Groq (le code Groq est plus bas)
        }
        // Essayer ChatGPT d'abord si activé et clé disponible (et que ce n'est pas Groq par défaut)
        elseif ($chatgptEnabled && $chatgptApiKey && $defaultProvider !== 'groq') {
            try {
                // DERNIÈRE VÉRIFICATION CRITIQUE juste avant l'appel API
                // Si max_tokens > 4096, FORCER gpt-4o (même si déjà vérifié)
                if ($maxTokens > 4096) {
                    $compatibleModels = ['gpt-4o', 'gpt-4o-2024-08-06', 'gpt-4-turbo-preview', 'gpt-4-0125-preview', 'gpt-4-1106-preview'];
                    if (!in_array($model, $compatibleModels)) {
                        $originalModel = $model;
                        $model = 'gpt-4o';
                        Log::error('AiService: DERNIÈRE VÉRIFICATION - Modèle incompatible, FORCÉ à gpt-4o', [
                            'original_model' => $originalModel,
                            'new_model' => $model,
                            'max_tokens' => $maxTokens,
                            'location' => 'juste avant appel API'
                        ]);
                    } else {
                        Log::info('AiService: Modèle compatible confirmé avant appel API', [
                            'model' => $model,
                            'max_tokens' => $maxTokens
                        ]);
                    }
                }
                
                Log::info('Tentative appel ChatGPT via openai-php/laravel', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                    'messages_count' => count($messages),
                    'total_prompt_length' => strlen($prompt)
                ]);
                
                // Utiliser le package openai-php/laravel qui gère automatiquement les modèles
                // Créer le client directement avec la clé API
                // Utiliser Factory pour éviter les conflits de nom de classe
                $openaiClient = (new \OpenAI\Factory())
                    ->withApiKey($chatgptApiKey)
                    ->make();
                $response = $openaiClient->chat()->create([
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);
                
                $content = $response->choices[0]->message->content ?? '';
                
                if (empty($content)) {
                    Log::warning('ChatGPT: Réponse vide', [
                        'choices_count' => count($response->choices ?? [])
                    ]);
                    return null;
                }
                
                Log::info('Réponse ChatGPT réussie via openai-php/laravel', [
                    'content_length' => strlen($content),
                    'model' => $model,
                    'provider' => 'chatgpt'
                ]);
                
                return [
                    'content' => $content,
                    'provider' => 'chatgpt',
                    'model' => $model
                ];
                
            } catch (\OpenAI\Exceptions\ErrorException $e) {
                $errorMessage = $e->getMessage();
                $errorCode = $e->getCode();
                
                Log::error('Erreur API OpenAI (openai-php/laravel)', [
                    'error_message' => $errorMessage,
                    'error_code' => $errorCode,
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'chatgpt_enabled' => $chatgptEnabled
                ]);
                
                // Si c'est une erreur de max_tokens, essayer avec gpt-4o
                if (strpos(strtolower($errorMessage), 'max_tokens') !== false && strpos(strtolower($errorMessage), 'too large') !== false) {
                    if ($model !== 'gpt-4o') {
                        Log::warning('AiService: Erreur max_tokens, tentative avec gpt-4o', [
                            'original_model' => $model,
                            'max_tokens' => $maxTokens
                        ]);
                        
                        try {
                            $openaiClient = (new \OpenAI\Factory())
                                ->withApiKey($chatgptApiKey)
                                ->make();
                            $response = $openaiClient->chat()->create([
                                'model' => 'gpt-4o',
                                'messages' => $messages,
                                'temperature' => $temperature,
                                'max_tokens' => $maxTokens,
                            ]);
                            
                            $content = $response->choices[0]->message->content ?? '';
                            
                            if (!empty($content)) {
                                Log::info('Réponse ChatGPT réussie avec gpt-4o après erreur', [
                                    'content_length' => strlen($content)
                                ]);
                                
                                return [
                                    'content' => $content,
                                    'provider' => 'chatgpt',
                                    'model' => 'gpt-4o'
                                ];
                            }
                        } catch (\Exception $retryException) {
                            Log::error('Erreur lors de la tentative avec gpt-4o', [
                                'error' => $retryException->getMessage()
                            ]);
                        }
                    }
                }
                
                // Si c'est une erreur de clé API invalide, logger et continuer vers Groq
                if (strpos(strtolower($errorMessage), 'invalid api key') !== false ||
                    strpos(strtolower($errorMessage), 'invalid_api_key') !== false ||
                    $errorCode === 401) {
                    Log::warning('ChatGPT: Clé API invalide, fallback vers Groq', [
                        'error_message' => $errorMessage
                    ]);
                    // Continuer vers Groq au lieu de retourner null
                }
                
                // Si c'est une erreur de quota ou rate limit, logger et continuer vers Groq
                if (strpos(strtolower($errorMessage), 'rate limit') !== false ||
                    strpos(strtolower($errorMessage), 'quota') !== false ||
                    strpos(strtolower($errorMessage), 'billing') !== false ||
                    $errorCode === 429) {
                    Log::warning('ChatGPT: Quota ou rate limit dépassé, fallback vers Groq', [
                        'error_message' => $errorMessage
                    ]);
                    // Continuer vers Groq au lieu de retourner null
                } else {
                    // Pour les autres erreurs, logger et continuer vers Groq
                    Log::warning('ChatGPT: Erreur API, fallback vers Groq', [
                        'error_message' => $errorMessage,
                        'error_code' => $errorCode
                    ]);
                }
                // Ne pas retourner null, continuer vers Groq
            } catch (\Exception $e) {
                Log::warning('Erreur appel ChatGPT, fallback vers Groq', [
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]);
                // Ne pas retourner null, continuer vers Groq
            }
        } else {
            if ($defaultProvider === 'groq') {
                Log::info('Groq sélectionné comme fournisseur par défaut, utilisation directe');
            } else {
                Log::info('ChatGPT désactivé ou clé manquante, fallback vers Groq');
            }
        }
        
        // Utiliser Groq si :
        // 1. C'est le fournisseur par défaut ET disponible
        // 2. OU ChatGPT n'est pas disponible (désactivé, clé manquante, ou erreur)
        if ($groqApiKey && ($defaultProvider === 'groq' || !$chatgptEnabled || !$chatgptApiKey)) {
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

