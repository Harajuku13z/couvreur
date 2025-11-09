<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SerpApiService
{
    protected $apiKey;
    protected $rateLimitSleep;

    public function __construct()
    {
        $this->apiKey = setting('serp_api_key');
        $this->rateLimitSleep = 2;
    }

    /**
     * Récupérer les tendances locales via Google Trends
     * 
     * @param string $geo Code géographique (ex: 'FR', 'FR-27')
     * @param int $limit Nombre de mots-clés à retourner
     * @return array Liste de mots-clés tendances
     */
    public function getTrendingKeywords(string $geo = 'FR', int $limit = 12): array
    {
        try {
            // Pour Google Trends, on doit fournir soit 'q' (query) soit 'category'
            // Pour les tendances générales, on utilise 'category: "all"'
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine' => 'google_trends',
                'geo' => $geo,
                'category' => 'all', // Catégorie "all" pour les tendances générales
                'api_key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                Log::error('SerpAPI Trends error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'geo' => $geo
                ]);
                
                // Si l'erreur persiste, essayer avec une catégorie spécifique ou un mot-clé générique
                if ($response->status() === 400) {
                    Log::info('Tentative alternative SerpAPI Trends avec query générique');
                    $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                        'engine' => 'google_trends',
                        'geo' => $geo,
                        'q' => 'couvreur', // Mot-clé générique pour le secteur
                        'api_key' => $this->apiKey,
                    ]);
                    
                    if (!$response->successful()) {
                        Log::error('SerpAPI Trends alternative error', [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                        return [];
                    }
                } else {
                    return [];
                }
            }

            $json = $response->json();
            $items = $json['trending_searches'] ?? ($json['trendingSearches'] ?? []);
            $titles = [];
            
            foreach ($items as $item) {
                $title = $item['title'] ?? $item['title']['query'] ?? null;
                if ($title && !empty(trim($title))) {
                    $titles[] = trim($title);
                }
                if (count($titles) >= $limit) {
                    break;
                }
            }
            
            Log::info('SerpAPI Trends récupérés', [
                'geo' => $geo,
                'count' => count($titles)
            ]);
            
            return $titles;
        } catch (\Exception $e) {
            Log::error('Exception SerpAPI Trends', [
                'message' => $e->getMessage(),
                'geo' => $geo
            ]);
            return [];
        }
    }

    /**
     * Récupérer les requêtes associées / questions liées
     * 
     * @param string $q Mot-clé principal
     * @param int $limit Nombre de requêtes à retourner
     * @return array Liste de questions/requêtes
     */
    public function getRelatedQueries(string $q, int $limit = 6): array
    {
        try {
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine' => 'google_related_questions',
                'q' => $q,
                'api_key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                Log::warning('SerpAPI Related error', [
                    'q' => $q,
                    'status' => $response->status()
                ]);
                return [];
            }

            $json = $response->json();
            $items = $json['related_questions'] ?? [];
            $questions = [];
            
            foreach ($items as $item) {
                $question = $item['question'] ?? $item['query'] ?? null;
                if ($question && !empty(trim($question))) {
                    $questions[] = trim($question);
                }
                if (count($questions) >= $limit) {
                    break;
                }
            }
            
            return $questions;
        } catch (\Exception $e) {
            Log::error('Exception SerpAPI Related', [
                'message' => $e->getMessage(),
                'q' => $q
            ]);
            return [];
        }
    }

    /**
     * Récupérer le top SERP (concurrents) pour analyse
     * 
     * @param string $q Mot-clé de recherche
     * @param int $limit Nombre de résultats à retourner
     * @return array Liste de résultats avec title, snippet, link
     */
    public function getTopSERP(string $q, int $limit = 5): array
    {
        try {
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                'engine' => 'google',
                'q' => $q,
                'api_key' => $this->apiKey,
                'num' => $limit,
            ]);

            if (!$response->successful()) {
                Log::warning('SerpAPI Search error', [
                    'q' => $q,
                    'status' => $response->status()
                ]);
                return [];
            }

            $json = $response->json();
            $results = $json['organic_results'] ?? [];
            $top = [];
            
            foreach ($results as $r) {
                $top[] = [
                    'title' => $r['title'] ?? null,
                    'snippet' => $r['snippet'] ?? null,
                    'link' => $r['link'] ?? null,
                ];
                if (count($top) >= $limit) {
                    break;
                }
            }
            
            return $top;
        } catch (\Exception $e) {
            Log::error('Exception SerpAPI Search', [
                'message' => $e->getMessage(),
                'q' => $q
            ]);
            return [];
        }
    }
}

