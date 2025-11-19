<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Stratégie d'indexation intelligente pour 10000+ pages
 * Priorise la qualité sur la quantité pour éviter les pénalités Google
 */
class SmartIndexingStrategy
{
    protected $qualityValidator;
    protected $googleIndexing;
    
    public function __construct()
    {
        $this->qualityValidator = app(ContentQualityValidator::class);
        $this->googleIndexing = app(GoogleIndexingService::class);
    }
    
    /**
     * Stratégie d'indexation par phases
     * Phase 1: Pages stratégiques (accueil, services principaux)
     * Phase 2: Articles de qualité (score > 80)
     * Phase 3: Annonces de qualité par ville (top villes)
     * Phase 4: Reste progressivement (200 URLs/jour max)
     */
    public function executeSmartIndexing($phase = 'all')
    {
        $results = [
            'phase_1' => [],
            'phase_2' => [],
            'phase_3' => [],
            'phase_4' => [],
            'summary' => []
        ];
        
        if ($phase === 'all' || $phase === '1') {
            $results['phase_1'] = $this->indexStrategicPages();
        }
        
        if ($phase === 'all' || $phase === '2') {
            $results['phase_2'] = $this->indexQualityArticles();
        }
        
        if ($phase === 'all' || $phase === '3') {
            $results['phase_3'] = $this->indexTopCityAds();
        }
        
        if ($phase === 'all' || $phase === '4') {
            $results['phase_4'] = $this->indexRemainingContent();
        }
        
        // Résumé
        $results['summary'] = [
            'total_indexed' => array_sum([
                count($results['phase_1']['indexed'] ?? []),
                count($results['phase_2']['indexed'] ?? []),
                count($results['phase_3']['indexed'] ?? []),
                count($results['phase_4']['indexed'] ?? []),
            ]),
            'total_skipped' => array_sum([
                count($results['phase_1']['skipped'] ?? []),
                count($results['phase_2']['skipped'] ?? []),
                count($results['phase_3']['skipped'] ?? []),
                count($results['phase_4']['skipped'] ?? []),
            ]),
        ];
        
        return $results;
    }
    
    /**
     * Phase 1: Indexer les pages stratégiques (priorité maximale)
     */
    protected function indexStrategicPages()
    {
        $indexed = [];
        $skipped = [];
        
        $strategicUrls = [
            url('/'),                    // Accueil
            url('/services'),            // Liste services
            url('/contact'),             // Contact
            url('/blog'),                // Blog
            url('/simulateur'),          // Simulateur de coûts
        ];
        
        // Ajouter les 5 services principaux
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : $servicesData;
        
        if (is_array($services)) {
            $topServices = array_slice($services, 0, 5);
            foreach ($topServices as $service) {
                if (isset($service['slug'])) {
                    $strategicUrls[] = url('/services/' . $service['slug']);
                }
            }
        }
        
        foreach ($strategicUrls as $url) {
            try {
                $result = $this->googleIndexing->indexUrl($url);
                if ($result) {
                    $indexed[] = $url;
                    Log::info('Page stratégique indexée', ['url' => $url]);
                    sleep(2); // Respecter les limites API
                } else {
                    $skipped[] = ['url' => $url, 'reason' => 'Échec indexation'];
                }
            } catch (\Exception $e) {
                $skipped[] = ['url' => $url, 'reason' => $e->getMessage()];
                Log::error('Erreur indexation page stratégique', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }
        
        return compact('indexed', 'skipped');
    }
    
    /**
     * Phase 2: Indexer uniquement les articles de haute qualité (score > 80)
     */
    protected function indexQualityArticles($limit = 50)
    {
        $indexed = [];
        $skipped = [];
        
        // Récupérer les articles publiés
        $articles = Article::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        foreach ($articles as $article) {
            // Valider la qualité
            $validation = $this->qualityValidator->validateArticle($article);
            
            if ($validation['score'] < 80) {
                $skipped[] = [
                    'url' => route('blog.show', $article),
                    'reason' => "Score qualité insuffisant ({$validation['score']}/100)",
                    'issues' => $validation['issues']
                ];
                continue;
            }
            
            // Indexer si qualité suffisante
            try {
                $url = url('/blog/' . $article->slug);
                $result = $this->googleIndexing->indexUrl($url);
                
                if ($result) {
                    $indexed[] = [
                        'url' => $url,
                        'score' => $validation['score'],
                        'word_count' => $validation['word_count']
                    ];
                    Log::info('Article de qualité indexé', [
                        'article_id' => $article->id,
                        'score' => $validation['score']
                    ]);
                    sleep(2);
                }
            } catch (\Exception $e) {
                $skipped[] = ['url' => $url, 'reason' => $e->getMessage()];
            }
        }
        
        return compact('indexed', 'skipped');
    }
    
    /**
     * Phase 3: Indexer les annonces des villes prioritaires uniquement
     * Ne pas indexer toutes les 10000 annonces, mais seulement les villes stratégiques
     */
    protected function indexTopCityAds($maxCities = 20, $adsPerCity = 3)
    {
        $indexed = [];
        $skipped = [];
        
        // Récupérer les villes favorites ou les plus peuplées
        $topCities = \App\Models\City::where('is_favorite', true)
            ->orWhere(function($q) {
                $q->where('population', '>', 10000)->orderBy('population', 'desc');
            })
            ->limit($maxCities)
            ->get();
        
        foreach ($topCities as $city) {
            // Récupérer les meilleures annonces pour cette ville
            $cityAds = Ad::where('city_id', $city->id)
                ->where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->limit($adsPerCity)
                ->get();
            
            foreach ($cityAds as $ad) {
                // Valider la qualité
                $validation = $this->qualityValidator->validateAd($ad);
                
                if ($validation['score'] < 70) {
                    $skipped[] = [
                        'url' => route('ads.show', $ad),
                        'reason' => "Score qualité insuffisant ({$validation['score']}/100)"
                    ];
                    continue;
                }
                
                // Indexer
                try {
                    $url = url('/annonces/' . $ad->slug);
                    $result = $this->googleIndexing->indexUrl($url);
                    
                    if ($result) {
                        $indexed[] = [
                            'url' => $url,
                            'city' => $city->name,
                            'score' => $validation['score']
                        ];
                        sleep(2);
                    }
                } catch (\Exception $e) {
                    $skipped[] = ['url' => $url ?? 'N/A', 'reason' => $e->getMessage()];
                }
            }
        }
        
        return compact('indexed', 'skipped');
    }
    
    /**
     * Phase 4: Indexation progressive du reste (200 URLs/jour max)
     */
    protected function indexRemainingContent($dailyLimit = 200)
    {
        $indexed = [];
        $skipped = [];
        
        // Vérifier combien d'URLs ont déjà été indexées aujourd'hui
        $todayIndexCount = DB::table('url_indexation_statuses')
            ->whereDate('last_submission_at', today())
            ->count();
        
        $remainingQuota = max(0, $dailyLimit - $todayIndexCount);
        
        if ($remainingQuota === 0) {
            return [
                'indexed' => [],
                'skipped' => [],
                'message' => 'Quota journalier atteint (' . $dailyLimit . ' URLs/jour)'
            ];
        }
        
        // Récupérer les contenus non indexés par ordre de qualité décroissante
        $unindexedContent = $this->getUnindexedQualityContent($remainingQuota);
        
        foreach ($unindexedContent as $item) {
            if (count($indexed) >= $remainingQuota) {
                break;
            }
            
            try {
                $result = $this->googleIndexing->indexUrl($item['url']);
                
                if ($result) {
                    $indexed[] = [
                        'url' => $item['url'],
                        'type' => $item['type'],
                        'score' => $item['score']
                    ];
                    sleep(2);
                } else {
                    $skipped[] = ['url' => $item['url'], 'reason' => 'Échec indexation'];
                }
            } catch (\Exception $e) {
                $skipped[] = ['url' => $item['url'], 'reason' => $e->getMessage()];
            }
        }
        
        return compact('indexed', 'skipped');
    }
    
    /**
     * Récupérer les contenus non indexés triés par qualité
     */
    protected function getUnindexedQualityContent($limit = 200)
    {
        $content = [];
        
        // Articles non indexés
        $articles = Article::where('status', 'published')
            ->whereDoesntHave('indexationStatus', function($q) {
                $q->where('indexed', true);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        foreach ($articles as $article) {
            $validation = $this->qualityValidator->validateArticle($article);
            if ($validation['score'] >= 70) {
                $content[] = [
                    'type' => 'article',
                    'url' => url('/blog/' . $article->slug),
                    'score' => $validation['score'],
                    'created_at' => $article->created_at
                ];
            }
        }
        
        // Annonces non indexées (limitées aux villes importantes)
        $topCityIds = \App\Models\City::where('is_favorite', true)
            ->orWhere('population', '>', 5000)
            ->pluck('id');
        
        $ads = Ad::where('status', 'published')
            ->whereIn('city_id', $topCityIds)
            ->whereDoesntHave('indexationStatus', function($q) {
                $q->where('indexed', true);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        foreach ($ads as $ad) {
            $validation = $this->qualityValidator->validateAd($ad);
            if ($validation['score'] >= 65) {
                $content[] = [
                    'type' => 'ad',
                    'url' => url('/annonces/' . $ad->slug),
                    'score' => $validation['score'],
                    'created_at' => $ad->created_at
                ];
            }
        }
        
        // Trier par score décroissant
        usort($content, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($content, 0, $limit);
    }
    
    /**
     * Créer un rapport d'indexation recommandé
     */
    public function getIndexingRecommendations()
    {
        $recommendations = [];
        
        // 1. Analyser la qualité globale du contenu
        $totalArticles = Article::where('status', 'published')->count();
        $totalAds = Ad::where('status', 'published')->count();
        
        $sampleArticles = Article::where('status', 'published')->inRandomOrder()->limit(20)->get();
        $articleScores = [];
        foreach ($sampleArticles as $article) {
            $validation = $this->qualityValidator->validateArticle($article);
            $articleScores[] = $validation['score'];
        }
        
        $avgArticleScore = !empty($articleScores) ? array_sum($articleScores) / count($articleScores) : 0;
        
        $sampleAds = Ad::where('status', 'published')->inRandomOrder()->limit(20)->get();
        $adScores = [];
        foreach ($sampleAds as $ad) {
            $validation = $this->qualityValidator->validateAd($ad);
            $adScores[] = $validation['score'];
        }
        
        $avgAdScore = !empty($adScores) ? array_sum($adScores) / count($adScores) : 0;
        
        // 2. Recommandations stratégiques
        if ($totalAds > 1000) {
            $recommendations[] = [
                'priority' => 'CRITIQUE',
                'issue' => "Trop d'annonces ({$totalAds}) risque de diluer votre autorité SEO",
                'solution' => "NE PAS indexer toutes les annonces. Seulement les 100-200 villes les plus importantes.",
                'action' => "Activer le filtrage par qualité et par ville dans la stratégie d'indexation"
            ];
        }
        
        if ($avgAdScore < 70) {
            $recommendations[] = [
                'priority' => 'HAUTE',
                'issue' => "Score qualité moyen des annonces trop faible ({$avgAdScore}/100)",
                'solution' => "Améliorer la personnalisation par ville avec l'IA pour créer du contenu unique",
                'action' => "Activer 'ad_template_ai_personalization' dans les settings"
            ];
        }
        
        if ($avgArticleScore > 80) {
            $recommendations[] = [
                'priority' => 'OPPORTUNITÉ',
                'issue' => "Articles de bonne qualité ({$avgArticleScore}/100)",
                'solution' => "Prioriser l'indexation des articles sur les annonces",
                'action' => "Créer plus d'articles de blog (5-10 par semaine) plutôt que des annonces"
            ];
        }
        
        // 3. Recommandations sur le volume
        $recommendedIndexablePages = min(500, $totalArticles + ($totalAds > 0 ? 200 : 0));
        
        $recommendations[] = [
            'priority' => 'STRATÉGIE',
            'issue' => "Stratégie d'indexation pour {$totalArticles} articles et {$totalAds} annonces",
            'solution' => "Indexer maximum {$recommendedIndexablePages} pages de haute qualité",
            'action' => "Utiliser l'indexation sélective basée sur le score qualité (>= 70)"
        ];
        
        return [
            'recommendations' => $recommendations,
            'stats' => [
                'total_articles' => $totalArticles,
                'total_ads' => $totalAds,
                'avg_article_score' => round($avgArticleScore, 1),
                'avg_ad_score' => round($avgAdScore, 1),
                'recommended_indexable_pages' => $recommendedIndexablePages
            ]
        ];
    }
    
    /**
     * Désindexer les pages de faible qualité
     */
    public function removeOLowQualityPages()
    {
        $removed = [];
        
        // Articles de très faible qualité (score < 50)
        $lowQualityArticles = Article::where('status', 'published')->get();
        
        foreach ($lowQualityArticles as $article) {
            $validation = $this->qualityValidator->validateArticle($article);
            
            if ($validation['score'] < 50) {
                try {
                    $url = url('/blog/' . $article->slug);
                    // Demander la suppression de l'index
                    $result = $this->googleIndexing->removeUrl($url);
                    
                    if ($result) {
                        $removed[] = [
                            'url' => $url,
                            'score' => $validation['score'],
                            'issues' => $validation['issues']
                        ];
                        
                        // Optionnel : passer l'article en draft
                        // $article->update(['status' => 'draft']);
                    }
                    
                    sleep(2);
                } catch (\Exception $e) {
                    Log::error('Erreur suppression index', ['url' => $url ?? 'N/A', 'error' => $e->getMessage()]);
                }
            }
        }
        
        return $removed;
    }
}

