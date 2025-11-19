<?php

namespace App\Services;

use App\Models\UrlIndexationStatus;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Service d'indexation SIMPLIFIÉ et FONCTIONNEL
 * Vérifie et indexe les URLs de manière fiable
 */
class SimpleIndexationService
{
    protected $googleService;
    protected $sitemapService;
    
    public function __construct()
    {
        $this->googleService = new GoogleSearchConsoleService();
        $this->sitemapService = new SitemapService();
    }
    
    /**
     * Obtenir toutes les URLs du site (depuis sitemaps)
     */
    public function getAllSiteUrls(): array
    {
        try {
            $allUrls = $this->sitemapService->getAllUrls();
            
            $urls = [];
            foreach ($allUrls as $item) {
                $url = is_array($item) ? ($item['url'] ?? null) : $item;
                if (!empty($url) && is_string($url)) {
                    $urls[] = $url;
                }
            }
            
            return array_unique($urls);
        } catch (\Exception $e) {
            Log::error('Erreur récupération URLs sitemap', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Obtenir les statistiques d'indexation
     */
    public function getStats(): array
    {
        $allUrls = $this->getAllSiteUrls();
        
        return [
            'total_sitemap' => count($allUrls),
            'total_tracked' => UrlIndexationStatus::count(),
            'indexed' => UrlIndexationStatus::where('indexed', true)->count(),
            'not_indexed' => UrlIndexationStatus::where('indexed', false)
                ->whereNotNull('last_verification_time')->count(),
            'never_verified' => UrlIndexationStatus::whereNull('last_verification_time')->count(),
            'verified_24h' => UrlIndexationStatus::where('last_verification_time', '>', now()->subHours(24))->count(),
        ];
    }
    
    /**
     * Vérifier le statut d'une URL
     */
    public function verifyUrl(string $url): array
    {
        try {
            if (!$this->googleService->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Google Search Console non configuré'
                ];
            }
            
            $result = $this->googleService->verifyIndexationStatus($url);
            
            return [
                'success' => true,
                'url' => $url,
                'indexed' => $result['indexed'] ?? false,
                'details' => $result
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur vérification URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'url' => $url,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Vérifier plusieurs URLs
     */
    public function verifyUrls(array $urls, int $limit = 50): array
    {
        $urls = array_slice($urls, 0, $limit);
        $results = [
            'total' => count($urls),
            'indexed' => 0,
            'not_indexed' => 0,
            'errors' => 0,
            'details' => []
        ];
        
        foreach ($urls as $url) {
            $result = $this->verifyUrl($url);
            
            if ($result['success']) {
                if ($result['indexed']) {
                    $results['indexed']++;
                } else {
                    $results['not_indexed']++;
                }
            } else {
                $results['errors']++;
            }
            
            $results['details'][] = $result;
            
            // Pause pour éviter rate limit (2 secondes entre chaque)
            sleep(2);
        }
        
        return $results;
    }
    
    /**
     * Indexer une URL via Google Indexing API
     */
    public function indexUrl(string $url): array
    {
        try {
            if (!$this->googleService->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Google Search Console non configuré'
                ];
            }
            
            $result = $this->googleService->indexUrl($url);
            
            if ($result['success'] ?? false) {
                // Enregistrer la soumission
                UrlIndexationStatus::recordSubmission($url);
                
                return [
                    'success' => true,
                    'url' => $url,
                    'message' => 'Demande d\'indexation envoyée'
                ];
            }
            
            return [
                'success' => false,
                'url' => $url,
                'error' => $result['message'] ?? 'Erreur inconnue'
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur indexation URL', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'url' => $url,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Indexer plusieurs URLs
     */
    public function indexUrls(array $urls, int $limit = 150): array
    {
        $urls = array_slice($urls, 0, $limit);
        $results = [
            'total' => count($urls),
            'success' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        foreach ($urls as $url) {
            $result = $this->indexUrl($url);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = $result;
            
            // Petite pause
            usleep(50000); // 0.05 seconde
        }
        
        return $results;
    }
    
    /**
     * Obtenir les URLs non indexées
     */
    public function getUnindexedUrls(int $limit = 150): array
    {
        // URLs vérifiées et confirmées non indexées
        $unindexed = UrlIndexationStatus::where('indexed', false)
            ->whereNotNull('last_verification_time')
            ->orderBy('last_verification_time', 'asc')
            ->limit($limit)
            ->pluck('url')
            ->toArray();
        
        // Si pas assez, ajouter URLs jamais vérifiées
        if (count($unindexed) < $limit) {
            $allSitemapUrls = $this->getAllSiteUrls();
            $trackedUrls = UrlIndexationStatus::pluck('url')->toArray();
            
            $neverTracked = array_diff($allSitemapUrls, $trackedUrls);
            $needed = $limit - count($unindexed);
            $neverTracked = array_slice($neverTracked, 0, $needed);
            
            $unindexed = array_merge($unindexed, $neverTracked);
        }
        
        return $unindexed;
    }
    
    /**
     * Indexation quotidienne intelligente
     */
    public function runDailyIndexing(int $limit = 150): array
    {
        Log::info('Indexation quotidienne - Début', ['limit' => $limit]);
        
        // Récupérer les URLs à indexer
        $urlsToIndex = $this->getUnindexedUrls($limit);
        
        if (empty($urlsToIndex)) {
            Log::info('Indexation quotidienne - Aucune URL à indexer');
            return [
                'success' => true,
                'message' => 'Aucune URL à indexer',
                'total' => 0,
                'indexed' => 0
            ];
        }
        
        Log::info('Indexation quotidienne - URLs à traiter', [
            'count' => count($urlsToIndex)
        ]);
        
        // Indexer
        $results = $this->indexUrls($urlsToIndex, $limit);
        
        Log::info('Indexation quotidienne - Terminée', $results);
        
        return $results;
    }
}

