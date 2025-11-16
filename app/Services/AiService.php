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
        // Si le setting n'existe pas, par défaut ChatGPT est activé (true)
        // Si le setting existe, lire sa valeur (peut être '0', '1', 'true', 'false', etc.)
        if ($chatgptEnabledSetting) {
            if ($chatgptEnabledSetting->type === 'boolean') {
                $chatgptEnabled = filter_var($chatgptEnabledSetting->value, FILTER_VALIDATE_BOOLEAN);
            } else {
                // Si c'est stocké comme string, convertir
                $value = strtolower(trim($chatgptEnabledSetting->value));
                $chatgptEnabled = in_array($value, ['1', 'true', 'yes', 'on'], true);
            }
        } else {
            // Par défaut, ChatGPT est activé si le setting n'existe pas
            $chatgptEnabled = true;
        }
        
        $chatgptApiKeySetting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
        $chatgptApiKey = $chatgptApiKeySetting ? $chatgptApiKeySetting->value : null;
        
        // La clé API sera utilisée directement pour créer le client
        
        $groqApiKeySetting = \App\Models\Setting::where('key', 'groq_api_key')->first();
        $groqApiKey = $groqApiKeySetting ? $groqApiKeySetting->value : null;
        
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
                
                // Si c'est une erreur de clé API invalide, arrêter les tentatives
                if (strpos(strtolower($errorMessage), 'invalid api key') !== false ||
                    strpos(strtolower($errorMessage), 'invalid_api_key') !== false ||
                    $errorCode === 401) {
                    Log::error('ChatGPT: Clé API invalide, arrêt des tentatives', [
                        'error_message' => $errorMessage
                    ]);
                    return null;
                }
                
                // Si c'est une erreur de quota ou rate limit, logger mais continuer
                if (strpos(strtolower($errorMessage), 'rate limit') !== false ||
                    strpos(strtolower($errorMessage), 'quota') !== false ||
                    strpos(strtolower($errorMessage), 'billing') !== false ||
                    $errorCode === 429) {
                    Log::error('ChatGPT: Quota ou rate limit dépassé', [
                        'error_message' => $errorMessage
                    ]);
                    return null;
                }
                
                // Si ChatGPT est activé, ne pas utiliser Groq en fallback
                // Forcer l'utilisation de ChatGPT uniquement
                if ($chatgptEnabled) {
                    Log::error('ChatGPT: Erreur API, mais ChatGPT est activé donc pas de fallback Groq', [
                        'error_message' => $errorMessage,
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
                    
                    // Construire un message d'erreur détaillé
                    $detailedErrorMessage = 'Erreur API Groq';
                    if ($status === 401) {
                        $detailedErrorMessage = 'Clé API Groq invalide ou expirée. Vérifiez votre clé API dans la configuration.';
                    } elseif ($status === 413 || strpos($errorMessage, 'Request too large') !== false || strpos($errorMessage, 'TPM') !== false) {
                        $detailedErrorMessage = 'Limite de tokens Groq dépassée (TPM: 6000). Le prompt est trop long. Réduction automatique en cours...';
                    } elseif ($status === 429) {
                        $detailedErrorMessage = 'Quota Groq dépassé. Attendez quelques minutes ou passez à un plan supérieur.';
                    } elseif ($status === 500 || $status === 502 || $status === 503) {
                        $detailedErrorMessage = 'Erreur serveur Groq (code ' . $status . '). Réessayez dans quelques instants.';
                    } else {
                        $detailedErrorMessage = 'Erreur API Groq: ' . $errorMessage . ' (code: ' . $status . ')';
                    }
                    
                    Log::error('Erreur API Groq', [
                        'status' => $status,
                        'error_message' => $errorMessage,
                        'detailed_error' => $detailedErrorMessage,
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
                            'error_message' => $errorMessage,
                            'detailed_error' => $detailedErrorMessage
                        ]);
                        // Ne pas continuer avec les retries si la clé est invalide
                        // Lancer une exception avec le message détaillé pour qu'il soit remonté
                        throw new \Exception($detailedErrorMessage);
                    }
                    
                    // Gérer spécifiquement l'erreur 413 (Request too large)
                    if ($status === 413 || (strpos($errorMessage, 'Request too large') !== false || strpos($errorMessage, 'TPM') !== false)) {
                        Log::warning('Limite TPM Groq dépassée, tentative avec prompt réduit', [
                            'original_input_length' => $totalMessageLength,
                            'estimated_tokens' => $estimatedInputTokens
                        ]);
                        
                        // Essayer avec un prompt réduit (tronquer le prompt utilisateur de 50% pour être sûr)
                        $reducedMessages = $messages;
                        if (isset($reducedMessages[count($reducedMessages) - 1]) && $reducedMessages[count($reducedMessages) - 1]['role'] === 'user') {
                            $originalUserPrompt = $reducedMessages[count($reducedMessages) - 1]['content'];
                            // Réduire de 50% au lieu de 30% pour Groq
                            $reducedUserPrompt = substr($originalUserPrompt, 0, (int)(strlen($originalUserPrompt) * 0.5));
                            $reducedMessages[count($reducedMessages) - 1]['content'] = $reducedUserPrompt;
                            
                            // Réduire aussi le system message si présent
                            if (isset($reducedMessages[0]) && $reducedMessages[0]['role'] === 'system') {
                                $originalSystemMessage = $reducedMessages[0]['content'];
                                $reducedSystemMessage = substr($originalSystemMessage, 0, (int)(strlen($originalSystemMessage) * 0.7));
                                $reducedMessages[0]['content'] = $reducedSystemMessage;
                            }
                            
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
                                        'model' => $groqModel,
                                        'original_tokens' => $estimatedInputTokens,
                                        'reduced_tokens' => $reducedInputTokens
                                    ]);
                                    
                                    return [
                                        'content' => $groqContent,
                                        'provider' => 'groq'
                                    ];
                                } else {
                                    $retryErrorBody = $retryResponse->json();
                                    $retryErrorMessage = $retryErrorBody['error']['message'] ?? 'Unknown error';
                                    $retryDetailedError = 'Erreur API Groq après réduction du prompt: ' . $retryErrorMessage . ' (code: ' . $retryResponse->status() . '). Le prompt a été réduit de 50% mais reste trop long pour Groq.';
                                    
                                    Log::error('Échec retry Groq avec prompt réduit', [
                                        'status' => $retryResponse->status(),
                                        'error_message' => $retryErrorMessage,
                                        'detailed_error' => $retryDetailedError,
                                        'reduced_input_length' => $reducedLength ?? 0,
                                        'reduced_input_tokens' => $reducedInputTokens ?? 0,
                                        'reduced_max_tokens' => $reducedMaxTokens ?? 0
                                    ]);
                                    
                                    // Lancer une exception avec le message détaillé
                                    throw new \Exception($retryDetailedError);
                                }
                            } catch (\Exception $retryException) {
                                Log::error('Exception lors du retry Groq avec prompt réduit', [
                                    'message' => $retryException->getMessage(),
                                    'trace' => config('app.debug') ? $retryException->getTraceAsString() : null
                                ]);
                                // Relancer l'exception pour qu'elle soit capturée plus haut
                                throw $retryException;
                            }
                        } else {
                            // Si on ne peut pas réduire le prompt, lancer une exception
                            throw new \Exception($detailedErrorMessage);
                        }
                    } else {
                        // Pour les autres erreurs, lancer une exception avec le message détaillé
                        throw new \Exception($detailedErrorMessage);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erreur appel Groq', [
                    'message' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]);
                // Relancer l'exception pour qu'elle soit capturée et affichée
                throw $e;
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
