<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class IndexJumpService
{
    protected $token;
    protected $baseUrl = 'https://api.indexjump.com';

    public function __construct()
    {
        $this->token = Setting::get('indexjump_token', '3d93dd2657466b97a401e540aaf9c72e');
    }

    /**
     * Vérifier si le service est configuré
     */
    public function isConfigured()
    {
        return !empty($this->token);
    }

    /**
     * Récupérer le solde (nombre d'URLs disponibles)
     */
    public function getBalance()
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Token IndexJump non configuré'
                ];
            }

            $response = Http::timeout(10)->get($this->baseUrl . '/balance', [
                'token' => $this->token
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['err']) && $data['err'] !== null) {
                    return [
                        'success' => false,
                        'message' => $data['err']
                    ];
                }

                return [
                    'success' => true,
                    'balance' => $data['res']['balance'] ?? 0,
                    'message' => 'Solde récupéré avec succès'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur HTTP: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur IndexJump getBalance: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Indexer une seule URL
     */
    public function indexUrl($url, $bot = 0)
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Token IndexJump non configuré'
                ];
            }

            $response = Http::timeout(10)->get($this->baseUrl . '/index', [
                'url' => $url,
                'token' => $this->token,
                'bot' => $bot
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['err']) && $data['err'] !== null) {
                    return [
                        'success' => false,
                        'message' => $data['err']
                    ];
                }

                if (isset($data['res']['success']) && $data['res']['success']) {
                    Log::info("IndexJump: URL indexée avec succès: {$url}");
                    return [
                        'success' => true,
                        'message' => "URL indexée avec succès: {$url}"
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Réponse inattendue de l\'API'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur HTTP: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error("Erreur IndexJump indexUrl pour {$url}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Indexer plusieurs URLs en batch
     */
    public function indexUrls(array $urls, $bot = 0)
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Token IndexJump non configuré'
                ];
            }

            if (empty($urls)) {
                return [
                    'success' => false,
                    'message' => 'Aucune URL fournie'
                ];
            }

            $response = Http::timeout(30)->asJson()->post($this->baseUrl . '/index/bulk?token=' . urlencode($this->token), [
                'urls' => $urls,
                'bot' => (int)$bot
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['err']) && $data['err'] !== null) {
                    return [
                        'success' => false,
                        'message' => $data['err']
                    ];
                }

                if (isset($data['res']['success']) && $data['res']['success']) {
                    Log::info("IndexJump: " . count($urls) . " URLs indexées avec succès");
                    return [
                        'success' => true,
                        'message' => count($urls) . " URLs indexées avec succès",
                        'count' => count($urls)
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Réponse inattendue de l\'API'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur HTTP: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Erreur IndexJump indexUrls: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'indexation d'une URL
     */
    public function checkStatus($url, $bot = 0)
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Token IndexJump non configuré'
                ];
            }

            $response = Http::timeout(10)->get($this->baseUrl . '/index/status', [
                'url' => $url,
                'token' => $this->token,
                'bot' => $bot
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['err']) && $data['err'] !== null) {
                    return [
                        'success' => false,
                        'message' => $data['err']
                    ];
                }

                return [
                    'success' => true,
                    'status' => $data['res']['status'] ?? 'Unknown',
                    'log_visit' => $data['res']['log_visit'] ?? null,
                    'message' => 'Statut récupéré avec succès'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur HTTP: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error("Erreur IndexJump checkStatus pour {$url}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }
}

