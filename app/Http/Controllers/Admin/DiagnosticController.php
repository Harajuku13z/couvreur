<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiagnosticController extends Controller
{
    /**
     * Diagnostic complet des APIs IA
     */
    public function diagnosticIA()
    {
        // Vider le cache
        Setting::clearCache();
        \Cache::forget('setting_chatgpt_enabled');
        \Cache::forget('setting_chatgpt_api_key');
        \Cache::forget('setting_groq_api_key');
        \Cache::forget('setting_groq_model');
        \Cache::forget('setting_chatgpt_model');
        
        $results = [
            'timestamp' => now()->toDateTimeString(),
            'settings' => [],
            'tests' => []
        ];
        
        // 1. Lire les settings directement depuis la DB
        $chatgptEnabledSetting = Setting::where('key', 'chatgpt_enabled')->first();
        $chatgptEnabled = $chatgptEnabledSetting ? ($chatgptEnabledSetting->type === 'boolean' ? filter_var($chatgptEnabledSetting->value, FILTER_VALIDATE_BOOLEAN) : $chatgptEnabledSetting->value) : true;
        
        $chatgptApiKeySetting = Setting::where('key', 'chatgpt_api_key')->first();
        $chatgptApiKey = $chatgptApiKeySetting ? $chatgptApiKeySetting->value : null;
        
        $groqApiKeySetting = Setting::where('key', 'groq_api_key')->first();
        $groqApiKey = $groqApiKeySetting ? $groqApiKeySetting->value : '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';
        
        $groqModelSetting = Setting::where('key', 'groq_model')->first();
        $groqModel = $groqModelSetting ? $groqModelSetting->value : 'llama-3.1-8b-instant';
        
        $chatgptModelSetting = Setting::where('key', 'chatgpt_model')->first();
        $chatgptModel = $chatgptModelSetting ? $chatgptModelSetting->value : 'gpt-4o';
        
        $results['settings'] = [
            'chatgpt_enabled' => $chatgptEnabled,
            'chatgpt_api_key_exists' => !empty($chatgptApiKey),
            'chatgpt_api_key_length' => $chatgptApiKey ? strlen($chatgptApiKey) : 0,
            'chatgpt_api_key_preview' => $chatgptApiKey ? substr($chatgptApiKey, 0, 10) . '...' : 'NULL',
            'chatgpt_model' => $chatgptModel,
            'groq_api_key_exists' => !empty($groqApiKey),
            'groq_api_key_length' => $groqApiKey ? strlen($groqApiKey) : 0,
            'groq_api_key_preview' => $groqApiKey ? substr($groqApiKey, 0, 10) . '...' : 'NULL',
            'groq_model' => $groqModel
        ];
        
        // 2. Test ChatGPT
        if ($chatgptEnabled && $chatgptApiKey) {
            $results['tests']['chatgpt'] = $this->testChatGPTAPI($chatgptApiKey, $chatgptModel);
        } else {
            $results['tests']['chatgpt'] = [
                'success' => false,
                'error' => 'ChatGPT désactivé ou clé manquante',
                'enabled' => $chatgptEnabled,
                'has_key' => !empty($chatgptApiKey)
            ];
        }
        
        // 3. Test Groq
        if ($groqApiKey) {
            $results['tests']['groq'] = $this->testGroqAPI($groqApiKey, $groqModel);
        } else {
            $results['tests']['groq'] = [
                'success' => false,
                'error' => 'Clé Groq manquante'
            ];
        }
        
        // 4. Test avec AiService
        $results['tests']['aiservice'] = $this->testAiService();
        
        return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    }
    
    private function testChatGPTAPI($apiKey, $model)
    {
        try {
            // Test simple
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Réponds simplement "OK"']
                ],
                'max_tokens' => 10,
                'temperature' => 0.1
            ]);
            
            $status = $response->status();
            $successful = $response->successful();
            $body = $response->body();
            $json = $response->json();
            
            if ($successful) {
                $content = $json['choices'][0]['message']['content'] ?? '';
                return [
                    'success' => true,
                    'status' => $status,
                    'response' => $content,
                    'usage' => $json['usage'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $status,
                    'error' => $json['error']['message'] ?? 'Unknown error',
                    'error_type' => $json['error']['type'] ?? 'unknown',
                    'error_code' => $json['error']['code'] ?? null,
                    'body_preview' => substr($body, 0, 500)
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception_type' => get_class($e)
            ];
        }
    }
    
    private function testGroqAPI($apiKey, $model)
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Réponds simplement "OK"']
                    ],
                    'max_tokens' => 10,
                    'temperature' => 0.1
                ]);
            
            $status = $response->status();
            $successful = $response->successful();
            $body = $response->body();
            $json = $response->json();
            
            if ($successful) {
                $content = $json['choices'][0]['message']['content'] ?? '';
                return [
                    'success' => true,
                    'status' => $status,
                    'response' => $content,
                    'usage' => $json['usage'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'status' => $status,
                    'error' => $json['error']['message'] ?? 'Unknown error',
                    'error_type' => $json['error']['type'] ?? 'unknown',
                    'body_preview' => substr($body, 0, 500)
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception_type' => get_class($e)
            ];
        }
    }
    
    private function testAiService()
    {
        try {
            $result = \App\Services\AiService::callAI(
                'Test simple - réponds "OK"',
                'Tu es un assistant.',
                [
                    'max_tokens' => 10,
                    'temperature' => 0.1,
                    'timeout' => 30
                ]
            );
            
            return [
                'success' => !is_null($result) && isset($result['content']),
                'result' => $result,
                'has_content' => isset($result['content']),
                'provider' => $result['provider'] ?? 'none'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception_type' => get_class($e)
            ];
        }
    }
}




