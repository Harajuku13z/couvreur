<?php

namespace App\Services;

use App\Models\SeoAutomation;
use App\Models\Article;
use App\Models\City;
use App\Services\SeoQualityAnalyzer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Exception;

class SeoAutomationManager
{
    protected $serp;
    protected $gpt;
    protected $indexer;

    public function __construct(
        SerpApiService $serp,
        GptSeoGenerator $gpt,
        GoogleIndexingService $indexer
    ) {
        $this->serp = $serp;
        $this->gpt = $gpt;
        $this->indexer = $indexer;
    }

    /**
     * Exécute la génération pour une ville
     * 
     * @param City $city Ville à traiter
     * @param string|null $customKeyword Mot-clé personnalisé (optionnel)
     * @return SeoAutomation Instance du log créé
     */
    public function runForCity(City $city, ?string $customKeyword = null, ?callable $progressCallback = null): SeoAutomation
    {
        $log = SeoAutomation::create([
            'city_id' => $city->id,
            'status' => 'pending',
        ]);

        try {
            $steps = [];
            
            // Si un mot-clé personnalisé est fourni, l'utiliser directement
            if ($customKeyword) {
                $keyword = trim($customKeyword);
                $steps[] = [
                    'step' => 'keyword_selection',
                    'title' => 'Sélection du mot-clé',
                    'status' => 'success',
                    'message' => "Mot-clé personnalisé utilisé: {$keyword}",
                    'data' => ['keyword' => $keyword]
                ];
                if ($progressCallback) $progressCallback($steps);
                Log::info('SeoAutomationManager: Utilisation du mot-clé personnalisé', [
                    'city' => $city->name,
                    'keyword' => $keyword
                ]);
            } else {
                // Vérifier d'abord les mots-clés personnalisés
                $customKeywordsData = \App\Models\Setting::where('key', 'seo_custom_keywords')->value('value') ?? '[]';
                $customKeywords = json_decode($customKeywordsData, true) ?? [];
                
                if (!empty($customKeywords) && is_array($customKeywords)) {
                    // Utiliser les mots-clés personnalisés
                    $keywords = $customKeywords;
                    $steps[] = [
                        'step' => 'keyword_selection',
                        'title' => 'Sélection du mot-clé',
                        'status' => 'processing',
                        'message' => 'Utilisation des mots-clés personnalisés...',
                        'data' => []
                    ];
                    if ($progressCallback) $progressCallback($steps);
                } else {
                    // 1. Récupérer tendances (utiliser region si dispo, sinon 'FR')
                    $steps[] = [
                        'step' => 'trending_keywords',
                        'title' => 'Récupération des mots-clés tendances (SerpAPI)',
                        'status' => 'processing',
                        'message' => 'Analyse des tendances locales...',
                        'data' => []
                    ];
                    if ($progressCallback) $progressCallback($steps);
                    
                    $geo = $city->region ?? 'FR';
                    // Nettoyer le code région si nécessaire (ex: "FR-27" -> "FR")
                    if (strpos($geo, '-') !== false) {
                        $geo = explode('-', $geo)[0];
                    }
                    
                    $keywords = $this->serp->getTrendingKeywords($geo, 12);
                }
                
                if (empty($keywords)) {
                    $steps[count($steps) - 1]['status'] = 'failed';
                    $steps[count($steps) - 1]['message'] = 'Aucun mot-clé récupéré depuis SerpAPI';
                    if ($progressCallback) $progressCallback($steps);
                    $log->update([
                        'status' => 'failed',
                        'error_message' => 'Aucun mot-clé récupéré depuis SerpAPI',
                        'metadata' => ['steps' => $steps]
                    ]);
                    return $log;
                }
                
                $steps[count($steps) - 1]['status'] = 'success';
                $steps[count($steps) - 1]['message'] = count($keywords) . ' mots-clés tendances récupérés';
                $steps[count($steps) - 1]['data'] = ['keywords' => array_slice($keywords, 0, 5), 'total' => count($keywords)];
                if ($progressCallback) $progressCallback($steps);

                // 2. Choisir mot-clé : priorité = mot non déjà utilisé récemment pour cette ville
                $steps[] = [
                    'step' => 'keyword_selection',
                    'title' => 'Sélection du mot-clé optimal',
                    'status' => 'processing',
                    'message' => 'Recherche d\'un mot-clé non utilisé récemment...',
                    'data' => []
                ];
                if ($progressCallback) $progressCallback($steps);
                
                $keyword = $this->selectKeywordForCity($keywords, $city);
                
                if (!$keyword) {
                    $steps[count($steps) - 1]['status'] = 'failed';
                    $steps[count($steps) - 1]['message'] = 'Aucun mot-clé disponible (tous déjà utilisés récemment)';
                    if ($progressCallback) $progressCallback($steps);
                    $log->update([
                        'status' => 'failed',
                        'error_message' => 'Aucun mot-clé disponible (tous déjà utilisés récemment)',
                        'metadata' => ['steps' => $steps]
                    ]);
                    return $log;
                }
                
                $steps[count($steps) - 1]['status'] = 'success';
                $steps[count($steps) - 1]['message'] = "Mot-clé sélectionné: {$keyword}";
                $steps[count($steps) - 1]['data'] = ['keyword' => $keyword];
                if ($progressCallback) $progressCallback($steps);
            }

            // 3. Related + competitors (10 résultats pour meilleure analyse)
            $steps[] = [
                'step' => 'serp_analysis',
                'title' => 'Analyse des concurrents (SerpAPI)',
                'status' => 'processing',
                'message' => 'Récupération des requêtes associées...',
                'data' => []
            ];
            if ($progressCallback) $progressCallback($steps);
            
            $related = $this->serp->getRelatedQueries($keyword, 6);
            
            // Recherche avec la ville pour des résultats plus pertinents
            $searchQuery = $keyword . ' ' . $city->name;
            $steps[count($steps) - 1]['message'] = 'Récupération des 10 premiers résultats Google pour "' . $searchQuery . '"...';
            if ($progressCallback) $progressCallback($steps);
            
            $competitors = $this->serp->getTopSERP($searchQuery, 10);
            
            $steps[count($steps) - 1]['status'] = 'success';
            $steps[count($steps) - 1]['message'] = count($related) . ' requêtes associées et ' . count($competitors) . ' concurrents analysés';
            
            // Préparer les données des concurrents avec titres et liens
            $competitorsData = [];
            foreach ($competitors as $competitor) {
                $competitorsData[] = [
                    'title' => $competitor['title'] ?? 'N/A',
                    'link' => $competitor['link'] ?? null,
                    'snippet' => $competitor['snippet'] ?? null
                ];
            }
            
            $steps[count($steps) - 1]['data'] = [
                'related_queries' => array_slice($related, 0, 6),
                'competitors_count' => count($competitors),
                'competitors' => array_slice($competitorsData, 0, 10) // Tous les concurrents avec leurs liens
            ];
            if ($progressCallback) $progressCallback($steps);

            // 4. Génération GPT
            $steps[] = [
                'step' => 'gpt_generation',
                'title' => 'Génération du contenu (GPT)',
                'status' => 'processing',
                'message' => 'Génération du titre optimisé...',
                'data' => []
            ];
            if ($progressCallback) $progressCallback($steps);
            
            $gptProgressCallback = function($progressData) use (&$steps, $progressCallback) {
                if (isset($progressData['step'])) {
                    $stepIndex = count($steps) - 1;
                    if ($progressData['step'] === 'title_generated' && isset($progressData['title'])) {
                        $steps[$stepIndex]['message'] = 'Titre généré: ' . $progressData['title'];
                        $steps[$stepIndex]['data'] = ['title' => $progressData['title']];
                    } elseif ($progressData['step'] === 'article_generation') {
                        $steps[$stepIndex]['message'] = 'Génération de l\'article complet...';
                    }
                    if ($progressCallback) $progressCallback($steps);
                }
            };
            
            $gptData = $this->gpt->generateSeoArticle($keyword, $city->name, $related, $competitors, $gptProgressCallback);

            if (!$gptData || empty($gptData['titre']) || empty($gptData['contenu_html'])) {
                $errorMessage = 'Génération GPT échouée ou réponse invalide';
                
                // Vérifier les clés API pour un message plus précis
                $chatgptApiKey = \App\Models\Setting::where('key', 'chatgpt_api_key')->value('value');
                $chatgptEnabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
                $chatgptEnabled = filter_var($chatgptEnabled, FILTER_VALIDATE_BOOLEAN);
                $groqApiKey = \App\Models\Setting::where('key', 'groq_api_key')->value('value');
                
                if ($chatgptEnabled && empty($chatgptApiKey)) {
                    $errorMessage = 'Clé API ChatGPT manquante. Configurez-la dans "Configuration des APIs".';
                } elseif ($chatgptEnabled && !empty($chatgptApiKey) && empty($groqApiKey)) {
                    $errorMessage = 'Clé API ChatGPT invalide ou quota dépassé. Vérifiez votre clé ou configurez Groq.';
                } elseif (empty($chatgptApiKey) && empty($groqApiKey)) {
                    $errorMessage = 'Aucune clé API configurée. Configurez ChatGPT ou Groq dans "Configuration des APIs".';
                } elseif (!empty($chatgptApiKey) && !empty($groqApiKey)) {
                    $errorMessage = 'Erreur lors de l\'appel aux APIs. Vérifiez vos clés API et vos quotas (ChatGPT et Groq).';
                }
                
                $steps[count($steps) - 1]['status'] = 'failed';
                $steps[count($steps) - 1]['message'] = $errorMessage;
                if ($progressCallback) $progressCallback($steps);
                $log->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'metadata' => ['gpt_data' => $gptData, 'steps' => $steps]
                ]);
                return $log;
            }
            
            $steps[count($steps) - 1]['status'] = 'success';
            $steps[count($steps) - 1]['message'] = 'Texte brut généré avec succès (' . strlen($gptData['contenu_html']) . ' caractères)';
            $steps[count($steps) - 1]['data'] = [
                'title' => $gptData['titre'],
                'meta_description' => $gptData['meta_description'] ?? null,
                'keywords_count' => count($gptData['mots_cles'] ?? []),
                'faq_count' => count($gptData['faq'] ?? [])
            ];
            if ($progressCallback) $progressCallback($steps);

            // 5. Créer l'article
            $steps[] = [
                'step' => 'article_creation',
                'title' => 'Publication de l\'article',
                'status' => 'processing',
                'message' => 'Création de l\'article dans la base de données...',
                'data' => []
            ];
            if ($progressCallback) $progressCallback($steps);
            
            $slug = Str::slug($gptData['titre'] . '-' . $city->name);
            
            // Vérifier si le slug existe déjà
            $existingArticle = Article::where('slug', $slug)->first();
            if ($existingArticle) {
                $slug = $slug . '-' . time();
            }

            // Utiliser directement le texte brut (sans ajouter de HTML)
            $contentHtml = $gptData['contenu_html'];
            
            // Note: Les images de réalisations ne sont plus ajoutées automatiquement
            // car nous utilisons maintenant le texte brut pur de ChatGPT
            // Si besoin, elles peuvent être ajoutées manuellement dans le contenu
            
            // Utiliser l'image de la banque d'images comme featured_image si disponible
            // Sinon, utiliser l'image par défaut du blog
            $featuredImage = null;
            if (isset($gptData['images']['keyword_image']) && !empty($gptData['images']['keyword_image'])) {
                $featuredImage = $gptData['images']['keyword_image'];
            } else {
                // Utiliser l'image par défaut du blog
                $defaultBlogImage = \App\Models\Setting::where('key', 'default_blog_og_image')->value('value');
                if ($defaultBlogImage && file_exists(public_path($defaultBlogImage))) {
                    $featuredImage = $defaultBlogImage;
                    Log::info('SeoAutomationManager: Utilisation image par défaut du blog', [
                        'image' => $defaultBlogImage
                    ]);
                }
            }
            
            $article = Article::create([
                'title' => $gptData['titre'],
                'slug' => $slug,
                'content_html' => $contentHtml,
                'meta_description' => $gptData['meta_description'] ?? null,
                'meta_keywords' => !empty($gptData['mots_cles']) ? implode(', ', $gptData['mots_cles']) : null,
                'focus_keyword' => $keyword,
                'featured_image' => $featuredImage,
                'status' => 'published',
                'published_at' => now(),
                'city_id' => $city->id,
            ]);
            
            $steps[count($steps) - 1]['status'] = 'success';
            $steps[count($steps) - 1]['message'] = 'Article publié avec succès';
            $steps[count($steps) - 1]['data'] = ['article_id' => $article->id, 'slug' => $slug];
            if ($progressCallback) $progressCallback($steps);

            // 6. Indexation Google
            $steps[] = [
                'step' => 'google_indexing',
                'title' => 'Indexation Google',
                'status' => 'processing',
                'message' => 'Envoi de la notification à Google Indexing API...',
                'data' => []
            ];
            if ($progressCallback) $progressCallback($steps);
            
            // Utiliser la route blog.show pour les articles publics
            $url = route('blog.show', $article);
            $indexed = $this->indexer->indexUrl($url);
            
            $steps[count($steps) - 1]['status'] = $indexed ? 'success' : 'warning';
            $steps[count($steps) - 1]['message'] = $indexed ? 'URL notifiée à Google avec succès' : 'Notification envoyée, en attente d\'indexation';
            $steps[count($steps) - 1]['data'] = ['url' => $url, 'indexed' => $indexed];
            if ($progressCallback) $progressCallback($steps);

            // 7. Analyser la qualité SEO de l'article
            try {
                $analyzer = app(SeoQualityAnalyzer::class);
                $seoAnalysis = $analyzer->analyze($article);
                
                Log::info('SeoAutomationManager: Analyse SEO de l\'article', [
                    'article_id' => $article->id,
                    'score' => $seoAnalysis['score'],
                    'percentage' => $seoAnalysis['percentage'],
                    'grade' => $seoAnalysis['grade']
                ]);
                
                // Si le score est < 90, logger un avertissement
                if ($seoAnalysis['percentage'] < 90) {
                    Log::warning('SeoAutomationManager: Score SEO inférieur à 90', [
                        'article_id' => $article->id,
                        'score' => $seoAnalysis['percentage'],
                        'issues' => $seoAnalysis['issues'],
                        'suggestion' => 'Améliorer le contenu pour atteindre un score de 90+'
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('SeoAutomationManager: Erreur lors de l\'analyse SEO', [
                    'article_id' => $article->id,
                    'error' => $e->getMessage()
                ]);
                $seoAnalysis = null;
            }

            // 8. Update log
            $log->update([
                'keyword' => $keyword,
                'status' => $indexed ? 'indexed' : 'published',
                'article_id' => (string)$article->id,
                'article_url' => $url,
                'metadata' => [
                    'gpt_data' => $gptData,
                    'related_queries' => $related,
                    'competitors' => $competitors,
                    'indexed' => $indexed,
                    'steps' => $steps,
                    'seo_analysis' => $seoAnalysis,
                ],
                'error_message' => null,
            ]);

            Log::info('SeoAutomationManager: Article créé avec succès', [
                'city' => $city->name,
                'keyword' => $keyword,
                'article_id' => $article->id,
                'url' => $url,
                'seo_score' => $seoAnalysis['percentage'] ?? null,
                'seo_grade' => $seoAnalysis['grade'] ?? null
            ]);

            return $log;
        } catch (Exception $e) {
            Log::error('SeoAutomationManager: Erreur', [
                'city_id' => $city->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return $log;
        }
    }

    /**
     * Sélectionner un mot-clé pour une ville (éviter les doublons récents, sélection aléatoire)
     */
    protected function selectKeywordForCity(array $keywords, City $city): ?string
    {
        if (empty($keywords)) {
            return null;
        }
        
        // Récupérer les mots-clés utilisés dans les 14 derniers jours pour cette ville
        $recent = SeoAutomation::where('city_id', $city->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->whereNotNull('keyword')
            ->pluck('keyword')
            ->toArray();

        // Filtrer les mots-clés non utilisés récemment
        $availableKeywords = array_filter($keywords, function($k) use ($recent) {
            return !in_array($k, $recent);
        });
        
        // Si des mots-clés sont disponibles, en choisir un au hasard
        if (!empty($availableKeywords)) {
            $availableKeywords = array_values($availableKeywords); // Réindexer
            $selected = $availableKeywords[array_rand($availableKeywords)];
            Log::info('SeoAutomationManager: Mot-clé sélectionné aléatoirement parmi les disponibles', [
                'city' => $city->name,
                'selected' => $selected,
                'available_count' => count($availableKeywords)
            ]);
            return $selected;
        }

        // Si tous sont déjà utilisés, prendre un au hasard parmi tous
        $selected = $keywords[array_rand($keywords)];
        Log::info('SeoAutomationManager: Tous les mots-clés déjà utilisés, sélection aléatoire parmi tous', [
            'city' => $city->name,
            'selected' => $selected,
            'total_count' => count($keywords)
        ]);
        return $selected;
    }
}

