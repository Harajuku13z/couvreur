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
        // Récupérer directement depuis la base pour éviter les problèmes de cache
        $setting = \App\Models\Setting::where('key', 'serp_api_key')->first();
        $this->apiKey = $setting ? $setting->value : null;
        $this->rateLimitSleep = 2;
        
        if (empty($this->apiKey)) {
            Log::warning('SerpAPI: Clé API non configurée');
        }
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
            // Vérifier que la clé API est configurée
            if (empty($this->apiKey)) {
                Log::error('SerpAPI: Clé API manquante');
                throw new \Exception('Clé API SerpAPI non configurée');
            }
            
            // Pour Google Trends, on doit fournir soit 'q' (query) soit 'cat' (category)
            // Le paramètre est 'cat' (pas 'category') et utilise des IDs numériques
            // Pour obtenir des mots-clés tendances, on utilise RELATED_QUERIES avec un mot-clé générique
            $params = [
                'engine' => 'google_trends',
                'geo' => $geo,
                'q' => 'couvreur', // Mot-clé générique pour le secteur
                'data_type' => 'RELATED_QUERIES', // Pour obtenir des requêtes liées (mots-clés tendances)
                'api_key' => $this->apiKey,
            ];
            
            Log::info('SerpAPI Trends request', ['params' => array_merge($params, ['api_key' => '***']), 'geo' => $geo]);
            
            $response = Http::timeout(30)->get('https://serpapi.com/search.json', $params);

            if (!$response->successful()) {
                Log::error('SerpAPI Trends error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'geo' => $geo
                ]);
                
                // Si l'erreur persiste, essayer avec 'cat' (pas 'category') - 0 = All categories
                if ($response->status() === 400) {
                    Log::info('Tentative alternative SerpAPI Trends avec cat');
                    $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                        'engine' => 'google_trends',
                        'geo' => $geo,
                        'cat' => 0, // 0 = All categories (paramètre 'cat' pas 'category')
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
            
            // Google Trends API retourne différentes structures selon le type de recherche
            // Pour 'q' (query), on cherche dans 'related_queries' ou 'interest_over_time'
            // Pour 'cat' (category), on cherche dans 'interest_over_time'
            $titles = [];
            
            // Essayer d'abord les related_queries (si on a utilisé 'q')
            if (isset($json['related_queries'])) {
                $relatedQueries = $json['related_queries'];
                // Prendre les "top" et "rising" queries
                if (isset($relatedQueries['top'])) {
                    foreach ($relatedQueries['top'] as $query) {
                        $title = $query['query'] ?? null;
                        if ($title && !empty(trim($title))) {
                            $titles[] = trim($title);
                        }
                        if (count($titles) >= $limit) {
                            break;
                        }
                    }
                }
                if (count($titles) < $limit && isset($relatedQueries['rising'])) {
                    foreach ($relatedQueries['rising'] as $query) {
                        $title = $query['query'] ?? null;
                        if ($title && !empty(trim($title)) && !in_array(trim($title), $titles)) {
                            $titles[] = trim($title);
                        }
                        if (count($titles) >= $limit) {
                            break;
                        }
                    }
                }
            }
            
            // Si pas assez de résultats, essayer interest_over_time (pour cat)
            if (count($titles) < $limit && isset($json['interest_over_time'])) {
                // Pour cat, on n'a pas de queries directes, on retourne ce qu'on a
                // ou on peut extraire des données de timeline
            }
            
            // Fallback: essayer trending_searches (format alternatif)
            if (empty($titles)) {
                $items = $json['trending_searches'] ?? ($json['trendingSearches'] ?? []);
                foreach ($items as $item) {
                    $title = $item['title'] ?? ($item['title']['query'] ?? null);
                    if ($title && !empty(trim($title))) {
                        $titles[] = trim($title);
                    }
                    if (count($titles) >= $limit) {
                        break;
                    }
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

