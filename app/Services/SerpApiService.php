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
            
            $titles = [];
            
            // APPROCHE 1: Utiliser Google Search standard pour obtenir des requêtes liées
            // C'est plus fiable que Google Trends et fonctionne toujours
            Log::info('SerpAPI: Tentative avec Google Search standard');
            try {
                $searchParams = [
                    'engine' => 'google',
                    'q' => 'couvreur ' . $geo, // Recherche générique avec localisation
                    'gl' => strtolower($geo), // Code pays (fr pour France)
                    'hl' => 'fr', // Langue
                    'num' => 10, // Nombre de résultats
                    'api_key' => $this->apiKey,
                ];
                
                $url = 'https://serpapi.com/search.json?' . http_build_query($searchParams);
                $response = Http::timeout(30)->get($url);
                
                if ($response->successful()) {
                    $json = $response->json();
                    
                    // Extraire les requêtes liées depuis les résultats de recherche
                    if (isset($json['related_questions'])) {
                        foreach ($json['related_questions'] as $question) {
                            $title = $question['question'] ?? null;
                            if ($title && !empty(trim($title))) {
                                $titles[] = trim($title);
                            }
                            if (count($titles) >= $limit) {
                                break;
                            }
                        }
                    }
                    
                    // Extraire aussi les suggestions de recherche
                    if (count($titles) < $limit && isset($json['related_searches'])) {
                        foreach ($json['related_searches'] as $search) {
                            $title = $search['query'] ?? null;
                            if ($title && !empty(trim($title)) && !in_array(trim($title), $titles)) {
                                $titles[] = trim($title);
                            }
                            if (count($titles) >= $limit) {
                                break;
                            }
                        }
                    }
                    
                    // Extraire les "People also ask"
                    if (count($titles) < $limit && isset($json['people_also_ask'])) {
                        foreach ($json['people_also_ask'] as $item) {
                            $title = $item['question'] ?? $item['title'] ?? null;
                            if ($title && !empty(trim($title)) && !in_array(trim($title), $titles)) {
                                $titles[] = trim($title);
                            }
                            if (count($titles) >= $limit) {
                                break;
                            }
                        }
                    }
                    
                    if (!empty($titles)) {
                        Log::info('SerpAPI: Mots-clés récupérés via Google Search', ['count' => count($titles)]);
                        return $titles;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('SerpAPI Google Search failed', ['error' => $e->getMessage()]);
            }
            
            // APPROCHE 2: Utiliser Google Autocomplete pour obtenir des suggestions
            Log::info('SerpAPI: Tentative avec Google Autocomplete');
            try {
                $autocompleteParams = [
                    'engine' => 'google_autocomplete',
                    'q' => 'couvreur',
                    'gl' => strtolower($geo),
                    'hl' => 'fr',
                    'api_key' => $this->apiKey,
                ];
                
                $url = 'https://serpapi.com/search.json?' . http_build_query($autocompleteParams);
                $response = Http::timeout(30)->get($url);
                
                if ($response->successful()) {
                    $json = $response->json();
                    
                    if (isset($json['suggestions'])) {
                        foreach ($json['suggestions'] as $suggestion) {
                            $title = $suggestion['value'] ?? $suggestion ?? null;
                            if ($title && !empty(trim($title)) && !in_array(trim($title), $titles)) {
                                $titles[] = trim($title);
                            }
                            if (count($titles) >= $limit) {
                                break;
                            }
                        }
                    }
                    
                    if (!empty($titles)) {
                        Log::info('SerpAPI: Mots-clés récupérés via Autocomplete', ['count' => count($titles)]);
                        return $titles;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('SerpAPI Autocomplete failed', ['error' => $e->getMessage()]);
            }
            
            // APPROCHE 3: Utiliser Google Trends avec TIMESERIES (plus simple, pas de data_type)
            Log::info('SerpAPI: Tentative avec Google Trends TIMESERIES');
            try {
                $trendsParams = [
                    'engine' => 'google_trends',
                    'q' => 'couvreur',
                    'geo' => $geo,
                    'data_type' => 'TIMESERIES', // Format par défaut, plus fiable
                    'api_key' => $this->apiKey,
                ];
                
                $url = 'https://serpapi.com/search.json?' . http_build_query($trendsParams);
                $response = Http::timeout(30)->get($url);
                
                if ($response->successful()) {
                    $json = $response->json();
                    
                    // Extraire depuis related_queries si disponible
                    if (isset($json['related_queries'])) {
                        $relatedQueries = $json['related_queries'];
                        if (isset($relatedQueries['top'])) {
                            foreach ($relatedQueries['top'] as $query) {
                                $title = $query['query'] ?? null;
                                if ($title && !empty(trim($title)) && !in_array(trim($title), $titles)) {
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
                    
                    if (!empty($titles)) {
                        Log::info('SerpAPI: Mots-clés récupérés via Trends TIMESERIES', ['count' => count($titles)]);
                        return $titles;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('SerpAPI Trends TIMESERIES failed', ['error' => $e->getMessage()]);
            }
            
            // Si aucune approche n'a fonctionné, retourner un tableau vide
            Log::warning('SerpAPI: Aucune approche n\'a fonctionné pour récupérer les mots-clés');
            return [];
            
        } catch (\Exception $e) {
            Log::error('Exception SerpAPI getTrendingKeywords', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
            if (empty($this->apiKey)) {
                Log::error('SerpAPI: Clé API manquante pour getRelatedQueries');
                return [];
            }
            
            $questions = [];
            
            // Approche 1: Recherche Google standard (contient related_questions, related_searches, people_also_ask)
            try {
                $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                    'engine' => 'google',
                    'q' => $q,
                    'api_key' => $this->apiKey,
                    'num' => 5, // Juste pour récupérer les sections related
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    
                    // Fonction pour filtrer les requêtes inutiles (pronunciation, meaning, whisky, etc.)
                    $isRelevantQuery = function($query) use ($q) {
                        if (empty($query)) return false;
                        $queryLower = strtolower($query);
                        $keywordLower = strtolower($q);
                        
                        // Mots-clés à exclure (requêtes non pertinentes pour SEO)
                        $excludeWords = ['pronunciation', 'meaning', 'whisky', 'whiskey', 'synonym', 'sentence', 'definition', 'dictionary', 'translate', 'traduction'];
                        foreach ($excludeWords as $exclude) {
                            if (strpos($queryLower, $exclude) !== false) {
                                return false;
                            }
                        }
                        
                        // La requête doit contenir le mot-clé principal ou être liée au domaine (couvreur, toiture, etc.)
                        $domainWords = ['couvreur', 'toiture', 'couverture', 'charpente', 'rénovation', 'réparation', 'isolation', 'zinguerie', 'demoussage', 'hydrofuge'];
                        $hasDomainWord = false;
                        foreach ($domainWords as $domainWord) {
                            if (strpos($queryLower, $domainWord) !== false) {
                                $hasDomainWord = true;
                                break;
                            }
                        }
                        
                        // Si la requête contient le mot-clé principal ou un mot du domaine, elle est pertinente
                        return strpos($queryLower, $keywordLower) !== false || $hasDomainWord;
                    };
                    
                    // Récupérer depuis related_questions (FILTRÉES)
                    if (isset($json['related_questions']) && is_array($json['related_questions'])) {
                        foreach ($json['related_questions'] as $item) {
                            $question = $item['question'] ?? $item['query'] ?? null;
                            if ($question && !empty(trim($question)) && $isRelevantQuery($question) && !in_array(trim($question), $questions)) {
                                $questions[] = trim($question);
                            }
                            if (count($questions) >= $limit) break;
                        }
                    }
                    
                    // Récupérer depuis related_searches (FILTRÉES)
                    if (count($questions) < $limit && isset($json['related_searches']) && is_array($json['related_searches'])) {
                        foreach ($json['related_searches'] as $item) {
                            $query = $item['query'] ?? null;
                            if ($query && !empty(trim($query)) && $isRelevantQuery($query) && !in_array(trim($query), $questions)) {
                                $questions[] = trim($query);
                            }
                            if (count($questions) >= $limit) break;
                        }
                    }
                    
                    // Récupérer depuis people_also_ask (FILTRÉES)
                    if (count($questions) < $limit && isset($json['people_also_ask']) && is_array($json['people_also_ask'])) {
                        foreach ($json['people_also_ask'] as $item) {
                            $question = $item['question'] ?? $item['query'] ?? null;
                            if ($question && !empty(trim($question)) && $isRelevantQuery($question) && !in_array(trim($question), $questions)) {
                                $questions[] = trim($question);
                            }
                            if (count($questions) >= $limit) break;
                        }
                    }
                    
                    if (count($questions) > 0) {
                        Log::info('SerpAPI Related queries récupérées via Google Search', [
                            'q' => $q,
                            'count' => count($questions)
                        ]);
                        return array_slice($questions, 0, $limit);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('SerpAPI Google Search pour related queries échoué', [
                    'q' => $q,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Approche 2: Engine google_related_questions (fallback)
            try {
                $response = Http::timeout(30)->get('https://serpapi.com/search.json', [
                    'engine' => 'google_related_questions',
                    'q' => $q,
                    'api_key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $items = $json['related_questions'] ?? [];
                    
                    // Fonction pour filtrer les requêtes inutiles
                    $isRelevantQuery = function($query) use ($q) {
                        if (empty($query)) return false;
                        $queryLower = strtolower($query);
                        $keywordLower = strtolower($q);
                        
                        // Mots-clés à exclure
                        $excludeWords = ['pronunciation', 'meaning', 'whisky', 'whiskey', 'synonym', 'sentence', 'definition', 'dictionary', 'translate', 'traduction'];
                        foreach ($excludeWords as $exclude) {
                            if (strpos($queryLower, $exclude) !== false) {
                                return false;
                            }
                        }
                        
                        // La requête doit contenir le mot-clé principal ou être liée au domaine
                        $domainWords = ['couvreur', 'toiture', 'couverture', 'charpente', 'rénovation', 'réparation', 'isolation', 'zinguerie', 'demoussage', 'hydrofuge'];
                        $hasDomainWord = false;
                        foreach ($domainWords as $domainWord) {
                            if (strpos($queryLower, $domainWord) !== false) {
                                $hasDomainWord = true;
                                break;
                            }
                        }
                        
                        return strpos($queryLower, $keywordLower) !== false || $hasDomainWord;
                    };
                    
                    foreach ($items as $item) {
                        $question = $item['question'] ?? $item['query'] ?? null;
                        if ($question && !empty(trim($question)) && $isRelevantQuery($question) && !in_array(trim($question), $questions)) {
                            $questions[] = trim($question);
                        }
                        if (count($questions) >= $limit) {
                            break;
                        }
                    }
                    
                    if (count($questions) > 0) {
                        Log::info('SerpAPI Related queries récupérées via google_related_questions', [
                            'q' => $q,
                            'count' => count($questions)
                        ]);
                        return array_slice($questions, 0, $limit);
                    }
                } else {
                    Log::warning('SerpAPI Related error', [
                        'q' => $q,
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 200)
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('SerpAPI google_related_questions échoué', [
                    'q' => $q,
                    'error' => $e->getMessage()
                ]);
            }
            
            return array_slice($questions, 0, $limit);
        } catch (\Exception $e) {
            Log::error('Exception SerpAPI Related', [
                'message' => $e->getMessage(),
                'q' => $q,
                'trace' => $e->getTraceAsString()
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
