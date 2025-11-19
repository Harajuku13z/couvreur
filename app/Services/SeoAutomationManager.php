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
     * Nettoie les données pour éviter les erreurs UTF-8 malformées
     */
    protected function cleanForJson($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanForJson'], $data);
        } elseif (is_string($data)) {
            // Supprimer les caractères UTF-8 invalides
            $cleaned = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Supprimer les caractères de contrôle non valides
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
            // Vérifier que c'est bien de l'UTF-8 valide
            if (!mb_check_encoding($cleaned, 'UTF-8')) {
                // Si toujours invalide, utiliser iconv avec ignore
                $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
                if ($cleaned === false) {
                    // Dernier recours : supprimer tous les caractères non-ASCII
                    $cleaned = preg_replace('/[^\x20-\x7E]/', '', $data);
                }
            }
            return $cleaned;
        } elseif (is_object($data)) {
            return $this->cleanForJson((array)$data);
        }
        return $data;
    }

    /**
     * Exécute la génération pour une ville
     * 
     * @param City $city Ville à traiter
     * @param string|null $customKeyword Mot-clé personnalisé (optionnel)
     * @param callable|null $progressCallback Callback pour le suivi de progression
     * @param \Carbon\Carbon|null $scheduledTime Heure planifiée pour la publication (optionnel)
     * @return SeoAutomation Instance du log créé
     */
    public function runForCity(City $city, ?string $customKeyword = null, ?callable $progressCallback = null, ?\Carbon\Carbon $scheduledTime = null): SeoAutomation
    {
        // Créer le log AVANT toute opération pour qu'il soit toujours visible
        $log = SeoAutomation::create([
            'city_id' => $city->id,
            'keyword' => $customKeyword, // Enregistrer le mot-clé dès le début
            'status' => 'pending',
        ]);
        
        Log::info('SeoAutomationManager: Log créé', [
            'log_id' => $log->id,
            'city_id' => $city->id,
            'city_name' => $city->name,
            'keyword' => $customKeyword
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
                    
                    // Sélectionner un mot-clé aléatoire si disponible
                    $keyword = !empty($keywords) ? $keywords[array_rand($keywords)] : null;
                    
                    // Mettre à jour le log avec le mot-clé sélectionné
                    if ($keyword) {
                        $log->update(['keyword' => $keyword]);
                    }
                    
                    $steps[] = [
                        'step' => 'keyword_selection',
                        'title' => 'Sélection du mot-clé',
                        'status' => $keyword ? 'success' : 'failed',
                        'message' => $keyword ? "Mot-clé sélectionné: {$keyword}" : 'Aucun mot-clé disponible',
                        'data' => $keyword ? ['keyword' => $keyword] : []
                    ];
                    if ($progressCallback) $progressCallback($steps);
                    
                    if (!$keyword) {
                        $log->update([
                            'status' => 'failed',
                            'error_message' => 'Aucun mot-clé personnalisé disponible'
                        ]);
                        return $log;
                    }
                } else {
                    // Vérifier si SerpAPI est activé pour l'automatisation
                    $serpapiEnabled = \App\Models\Setting::where('key', 'seo_automation_serpapi_enabled')->value('value');
                    $serpapiEnabled = filter_var($serpapiEnabled, FILTER_VALIDATE_BOOLEAN);
                    if ($serpapiEnabled === false && $serpapiEnabled !== true) {
                        $serpapiEnabled = true; // Par défaut activé
                    }
                    
                    // 1. Récupérer tendances (SerpAPI ou ChatGPT selon configuration)
                    $steps[] = [
                        'step' => 'trending_keywords',
                        'title' => $serpapiEnabled ? 'Récupération des mots-clés tendances (SerpAPI)' : 'Récupération des mots-clés tendances (ChatGPT)',
                        'status' => 'processing',
                        'message' => $serpapiEnabled ? 'Analyse des tendances locales via SerpAPI...' : 'Génération des mots-clés via ChatGPT...',
                        'data' => []
                    ];
                    if ($progressCallback) $progressCallback($steps);
                    
                    if ($serpapiEnabled) {
                        // Utiliser SerpAPI (avec fallback ChatGPT intégré)
                        $geo = $city->region ?? 'FR';
                        // Nettoyer le code région si nécessaire (ex: "FR-27" -> "FR")
                        if (strpos($geo, '-') !== false) {
                            $geo = explode('-', $geo)[0];
                        }
                        $keywords = $this->serp->getTrendingKeywords($geo, 12);
                    } else {
                        // Utiliser uniquement ChatGPT pour générer les mots-clés
                        Log::info('SeoAutomationManager: SerpAPI désactivé, utilisation ChatGPT pour mots-clés', [
                            'city' => $city->name
                        ]);
                        $keywords = $this->getTrendingKeywordsWithChatGPT($city);
                    }
                }
                
                if (empty($keywords)) {
                    $steps[count($steps) - 1]['status'] = 'failed';
                    $steps[count($steps) - 1]['message'] = 'Aucun mot-clé récupéré (SerpAPI et ChatGPT ont échoué)';
                    if ($progressCallback) $progressCallback($steps);
                    $log->update([
                        'status' => 'failed',
                        'error_message' => 'Aucun mot-clé récupéré (SerpAPI et ChatGPT ont échoué)',
                        'metadata' => $this->cleanForJson(['steps' => $steps])
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
                    'metadata' => $this->cleanForJson(['steps' => $steps])
                ]);
                    return $log;
                }
                
                // Mettre à jour le log avec le mot-clé sélectionné
                $log->update(['keyword' => $keyword]);
                
                $steps[count($steps) - 1]['status'] = 'success';
                $steps[count($steps) - 1]['message'] = "Mot-clé sélectionné: {$keyword}";
                $steps[count($steps) - 1]['data'] = ['keyword' => $keyword];
                if ($progressCallback) $progressCallback($steps);
            }

            // 3. Related + competitors (10 résultats pour meilleure analyse)
            // Vérifier si SerpAPI est activé pour l'automatisation
            $serpapiEnabled = \App\Models\Setting::where('key', 'seo_automation_serpapi_enabled')->value('value');
            $serpapiEnabled = filter_var($serpapiEnabled, FILTER_VALIDATE_BOOLEAN);
            if ($serpapiEnabled === false && $serpapiEnabled !== true) {
                $serpapiEnabled = true; // Par défaut activé
            }
            
            $steps[] = [
                'step' => 'serp_analysis',
                'title' => $serpapiEnabled ? 'Analyse des concurrents (SerpAPI)' : 'Analyse des concurrents (ChatGPT)',
                'status' => 'processing',
                'message' => $serpapiEnabled ? 'Récupération des requêtes associées via SerpAPI...' : 'Génération des données concurrentes via ChatGPT...',
                'data' => []
            ];
            if ($progressCallback) $progressCallback($steps);
            
            if ($serpapiEnabled) {
                // Utiliser SerpAPI (avec fallback ChatGPT intégré)
                $related = $this->serp->getRelatedQueries($keyword, 6);
                
                // Recherche avec la ville pour des résultats plus pertinents
                $searchQuery = $keyword . ' ' . $city->name;
                $steps[count($steps) - 1]['message'] = 'Récupération des 10 premiers résultats Google pour "' . $searchQuery . '"...';
                if ($progressCallback) $progressCallback($steps);
                
                $competitors = $this->serp->getTopSERP($searchQuery, 10);
            } else {
                // Utiliser uniquement ChatGPT pour générer les données concurrentes
                Log::info('SeoAutomationManager: SerpAPI désactivé, utilisation ChatGPT pour analyse concurrents', [
                    'keyword' => $keyword,
                    'city' => $city->name
                ]);
                $related = $this->getRelatedQueriesWithChatGPT($keyword, 6);
                $competitors = $this->getTopSERPWithChatGPT($keyword . ' ' . $city->name, 10);
            }
            
            // Vérifier que nous avons au moins quelques données
            $hasData = !empty($related) || !empty($competitors);
            
            if (!$hasData) {
                Log::warning('SeoAutomationManager: Aucune donnée de recherche (related queries ou competitors)', [
                    'keyword' => $keyword,
                    'city' => $city->name,
                    'serpapi_enabled' => $serpapiEnabled
                ]);
                // Continuer quand même, GPT peut générer sans ces données
            }
            
            $steps[count($steps) - 1]['status'] = $hasData ? 'success' : 'warning';
            $steps[count($steps) - 1]['message'] = count($related) . ' requêtes associées et ' . count($competitors) . ' concurrents analysés' . ($serpapiEnabled ? '' : ' (via ChatGPT)');
            
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
            
            try {
                $gptData = $this->gpt->generateSeoArticle($keyword, $city->name, $related, $competitors, $gptProgressCallback);
            } catch (\Exception $gptException) {
                Log::error('SeoAutomationManager: Exception lors de la génération GPT', [
                    'city' => $city->name,
                    'keyword' => $keyword,
                    'error' => $gptException->getMessage(),
                    'trace' => $gptException->getTraceAsString()
                ]);
                
                // Utiliser le message d'erreur de l'exception (qui contient déjà les détails)
                $errorMessage = $gptException->getMessage();
                
                // Vérifier si c'est une erreur Groq spécifique
                $chatgptEnabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
                $chatgptEnabled = filter_var($chatgptEnabled, FILTER_VALIDATE_BOOLEAN);
                
                if (!$chatgptEnabled && strpos($errorMessage, 'Groq') !== false) {
                    // Si c'est Groq qui est utilisé et qu'il y a une erreur, améliorer le message
                    if (strpos($errorMessage, 'Request too large') !== false || strpos($errorMessage, 'TPM') !== false) {
                        $errorMessage = 'Erreur Groq: Le prompt est trop long pour les limites de Groq (TPM: 6000 tokens). Le système a tenté de réduire le prompt automatiquement mais cela n\'a pas suffi. Essayez de réduire la longueur du prompt ou utilisez ChatGPT.';
                    } elseif (strpos($errorMessage, 'Clé API') !== false) {
                        $errorMessage = 'Erreur Groq: ' . $errorMessage . ' Vérifiez votre clé API Groq dans la configuration.';
                    } else {
                        $errorMessage = 'Erreur Groq: ' . $errorMessage;
                    }
                }
                
                $steps[count($steps) - 1]['status'] = 'failed';
                $steps[count($steps) - 1]['message'] = $errorMessage;
                if ($progressCallback) $progressCallback($steps);
                $log->update([
                    'status' => 'failed',
                    'error_message' => $this->cleanForJson($errorMessage),
                    'metadata' => $this->cleanForJson(['gpt_data' => null, 'steps' => $steps, 'exception' => $gptException->getMessage()])
                ]);
                return $log;
            }

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
                } elseif (!$chatgptEnabled && !empty($groqApiKey)) {
                    $errorMessage = 'Erreur lors de l\'appel à Groq. Vérifiez votre clé API Groq et vos quotas (TPM: 6000 tokens). Le prompt peut être trop long.';
                } elseif (!empty($chatgptApiKey) && !empty($groqApiKey)) {
                    $errorMessage = 'Erreur lors de l\'appel aux APIs. Vérifiez vos clés API et vos quotas (ChatGPT et Groq).';
                }
                
                $steps[count($steps) - 1]['status'] = 'failed';
                $steps[count($steps) - 1]['message'] = $errorMessage;
                if ($progressCallback) $progressCallback($steps);
                $log->update([
                    'status' => 'failed',
                    'error_message' => $this->cleanForJson($errorMessage),
                    'metadata' => $this->cleanForJson(['gpt_data' => $gptData, 'steps' => $steps])
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
            
            // Préparer les mots-clés meta
            $metaKeywords = null;
            if (!empty($gptData['mots_cles'])) {
                if (is_array($gptData['mots_cles'])) {
                    $metaKeywords = implode(', ', $gptData['mots_cles']);
                } else {
                    $metaKeywords = $gptData['mots_cles'];
                }
            }
            
            // Utiliser l'heure planifiée si fournie, sinon utiliser maintenant
            $publishTime = $scheduledTime ?? now();
            
            try {
                $article = Article::create([
                    'title' => $gptData['titre'],
                    'slug' => $slug,
                    'content_html' => $contentHtml,
                    'meta_description' => $gptData['meta_description'] ?? null,
                    'meta_keywords' => $metaKeywords,
                    'focus_keyword' => $keyword,
                    'featured_image' => $featuredImage,
                    'status' => 'published',
                    'published_at' => $publishTime,
                    'city_id' => $city->id,
                ]);
                
                Log::info('SeoAutomationManager: Article créé avec heure planifiée', [
                    'article_id' => $article->id,
                    'published_at' => $publishTime->format('Y-m-d H:i:s'),
                    'scheduled_time_provided' => $scheduledTime !== null
                ]);
            } catch (\Exception $createException) {
                Log::error('SeoAutomationManager: Erreur lors de la création de l\'article', [
                    'city' => $city->name,
                    'keyword' => $keyword,
                    'slug' => $slug,
                    'error' => $createException->getMessage(),
                    'trace' => $createException->getTraceAsString()
                ]);
                
                // Essayer avec un slug différent si erreur de duplication
                if (str_contains($createException->getMessage(), 'Duplicate') || str_contains($createException->getMessage(), 'unique')) {
                    $slug = $slug . '-' . time() . '-' . rand(1000, 9999);
                    try {
                        $article = Article::create([
                            'title' => $gptData['titre'],
                            'slug' => $slug,
                            'content_html' => $contentHtml,
                            'meta_description' => $gptData['meta_description'] ?? null,
                            'meta_keywords' => $metaKeywords,
                            'focus_keyword' => $keyword,
                            'featured_image' => $featuredImage,
                            'status' => 'published',
                            'published_at' => $publishTime,
                            'city_id' => $city->id,
                        ]);
                        Log::info('SeoAutomationManager: Article créé avec slug modifié', [
                            'article_id' => $article->id,
                            'slug' => $slug
                        ]);
                    } catch (\Exception $retryException) {
                        $steps[count($steps) - 1]['status'] = 'failed';
                        $steps[count($steps) - 1]['message'] = 'Erreur lors de la création de l\'article: ' . $retryException->getMessage();
                        if ($progressCallback) $progressCallback($steps);
                        $log->update([
                            'status' => 'failed',
                            'error_message' => $this->cleanForJson('Erreur lors de la création de l\'article: ' . $retryException->getMessage()),
                            'metadata' => $this->cleanForJson(['gpt_data' => $gptData, 'steps' => $steps])
                        ]);
                        return $log;
                    }
                } else {
                    $steps[count($steps) - 1]['status'] = 'failed';
                    $steps[count($steps) - 1]['message'] = 'Erreur lors de la création de l\'article: ' . $createException->getMessage();
                    if ($progressCallback) $progressCallback($steps);
                    $log->update([
                        'status' => 'failed',
                        'error_message' => $this->cleanForJson('Erreur lors de la création de l\'article: ' . $createException->getMessage()),
                        'metadata' => $this->cleanForJson(['gpt_data' => $gptData, 'steps' => $steps])
                    ]);
                    return $log;
                }
            }
            
            Log::info('Article créé avec mots-clés', [
                'article_id' => $article->id,
                'meta_keywords' => $metaKeywords,
                'meta_keywords_length' => strlen($metaKeywords ?? '')
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
            
            // Utiliser url() au lieu de route() pour éviter localhost
            $url = url('/blog/' . $article->slug);
            
            // Vérifier si le service Google est configuré AVANT d'essayer d'indexer
            $googleSearchConsole = app(\App\Services\GoogleSearchConsoleService::class);
            $isConfigured = $googleSearchConsole->isConfigured();
            
            Log::info('SeoAutomationManager: Vérification configuration Google avant indexation', [
                'url' => $url,
                'is_configured' => $isConfigured
            ]);
            
            if (!$isConfigured) {
                $steps[count($steps) - 1]['status'] = 'warning';
                $steps[count($steps) - 1]['message'] = 'Google Search Console non configuré - Indexation non disponible';
                $steps[count($steps) - 1]['data'] = ['url' => $url, 'index_requested' => false, 'reason' => 'service_not_configured'];
                if ($progressCallback) $progressCallback($steps);
                
                Log::warning('SeoAutomationManager: Indexation ignorée - Google Search Console non configuré', [
                    'url' => $url,
                    'article_id' => $article->id
                ]);
                
                $indexRequested = false;
            } else {
                // Étape 6.1 : Demander l'indexation
                Log::info('SeoAutomationManager: Tentative d\'indexation', [
                    'url' => $url,
                    'article_id' => $article->id
                ]);
                
                $indexRequested = $this->indexer->indexUrl($url);
                
                Log::info('SeoAutomationManager: Résultat indexation', [
                    'url' => $url,
                    'index_requested' => $indexRequested,
                    'article_id' => $article->id
                ]);
                
                $steps[count($steps) - 1]['status'] = $indexRequested ? 'success' : 'warning';
                $steps[count($steps) - 1]['message'] = $indexRequested ? '✅ Demande d\'indexation envoyée à Google' : '❌ Erreur lors de l\'envoi de la demande d\'indexation';
                $steps[count($steps) - 1]['data'] = ['url' => $url, 'index_requested' => $indexRequested];
                if ($progressCallback) $progressCallback($steps);
                
                // Logger explicitement pour le suivi
                if ($indexRequested) {
                    Log::info('SeoAutomationManager: ✅ Demande d\'indexation envoyée avec succès', [
                        'url' => $url,
                        'article_id' => $article->id,
                        'log_id' => $log->id
                    ]);
                } else {
                    Log::warning('SeoAutomationManager: ❌ Échec de la demande d\'indexation', [
                        'url' => $url,
                        'article_id' => $article->id,
                        'log_id' => $log->id
                    ]);
                }
            }
            
            // Étape 6.2 : Vérifier le statut d'indexation (après un court délai)
            $isIndexed = false;
            $verificationResult = null;
            
            if ($indexRequested && $isConfigured) {
                sleep(2); // Attendre 2 secondes pour que Google traite la demande
                
                try {
                    $verificationResult = $googleSearchConsole->verifyIndexationStatus($url);
                    $isIndexed = $verificationResult['success'] && ($verificationResult['indexed'] ?? false);
                    
                    Log::info('SeoAutomationManager: Vérification indexation', [
                        'url' => $url,
                        'is_indexed' => $isIndexed,
                        'verification_result' => $verificationResult
                    ]);
                    
                    $steps[count($steps) - 1]['status'] = $isIndexed ? 'success' : 'warning';
                    $steps[count($steps) - 1]['message'] = $isIndexed 
                        ? '✅ URL indexée dans Google' 
                        : '⏳ Demande d\'indexation envoyée, vérification en cours...';
                    $steps[count($steps) - 1]['data'] = [
                        'url' => $url, 
                        'index_requested' => $indexRequested,
                        'indexed' => $isIndexed,
                        'verification' => $verificationResult
                    ];
                } catch (\Exception $e) {
                    Log::error('SeoAutomationManager: Erreur lors de la vérification d\'indexation', [
                        'url' => $url,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $isIndexed = false;
                    $steps[count($steps) - 1]['status'] = 'warning';
                    $steps[count($steps) - 1]['message'] = 'Demande d\'indexation envoyée (erreur lors de la vérification: ' . substr($e->getMessage(), 0, 100) . ')';
                    $steps[count($steps) - 1]['data'] = ['url' => $url, 'index_requested' => $indexRequested, 'indexed' => false, 'error' => $e->getMessage()];
                }
            } elseif (!$indexRequested) {
                // Si l'indexation a échoué, récupérer plus de détails depuis les logs
                Log::warning('SeoAutomationManager: Indexation échouée', [
                    'url' => $url,
                    'article_id' => $article->id,
                    'is_configured' => $isConfigured
                ]);
                
                $steps[count($steps) - 1]['status'] = 'warning';
                $steps[count($steps) - 1]['message'] = $isConfigured 
                    ? 'Erreur lors de l\'envoi de la demande d\'indexation. Vérifiez les logs pour plus de détails.'
                    : 'Indexation non disponible - Google Search Console non configuré';
                $steps[count($steps) - 1]['data'] = [
                    'url' => $url, 
                    'index_requested' => false,
                    'is_configured' => $isConfigured
                ];
            }
            
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

            // 8. Update log - FORCER le statut à "indexed" ou "published" (jamais "pending")
            $finalStatus = $isIndexed ? 'indexed' : 'published';
            
            // Préparer les métadonnées avec les informations d'indexation bien visibles
            $metadata = [
                'gpt_data' => $gptData,
                'related_queries' => $related,
                'competitors' => $competitors,
                'index_requested' => $indexRequested,
                'indexed' => $isIndexed,
                'index_requested_at' => $indexRequested ? now()->toIso8601String() : null,
                'verification_result' => $verificationResult ?? null,
                'steps' => $steps,
                'seo_analysis' => $seoAnalysis,
                'published_at' => $publishTime->toIso8601String(),
                'scheduled_time' => $scheduledTime ? $scheduledTime->toIso8601String() : null,
            ];
            
            $log->update([
                'keyword' => $keyword,
                'status' => $finalStatus, // Toujours "indexed" ou "published", jamais "pending"
                'article_id' => (string)$article->id,
                'article_url' => $url,
                'metadata' => $this->cleanForJson($metadata),
                'error_message' => null,
            ]);
            
            // Logger explicitement le statut d'indexation pour le suivi
            Log::info('SeoAutomationManager: Log mis à jour avec statut d\'indexation', [
                'log_id' => $log->id,
                'status' => $finalStatus,
                'index_requested' => $indexRequested,
                'indexed' => $isIndexed,
                'article_id' => $article->id,
                'url' => $url
            ]);

            // Vérification finale : s'assurer que le statut n'est JAMAIS resté "pending"
            $log->refresh(); // Recharger depuis la DB
            if ($log->status === 'pending') {
                Log::error('SeoAutomationManager: Le statut est resté "pending" après update - FORCAGE en "published"', [
                    'city_id' => $city->id,
                    'log_id' => $log->id,
                    'article_id' => $article->id
                ]);
                // Forcer le statut à "published" si l'article existe
                $log->update([
                    'status' => 'published',
                    'error_message' => null
                ]);
            }

            Log::info('SeoAutomationManager: Article créé avec succès', [
                'city' => $city->name,
                'keyword' => $keyword,
                'article_id' => $article->id,
                'url' => $url,
                'status' => $log->status,
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
            
            // S'assurer que le log existe et mettre à jour son statut
            $cleanedErrorMessage = $this->cleanForJson($e->getMessage());
            
            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $cleanedErrorMessage
                ]);
            } else {
                // Créer un log d'échec si le log n'existe pas
                try {
                    $log = SeoAutomation::create([
                        'city_id' => $city->id,
                        'status' => 'failed',
                        'error_message' => $cleanedErrorMessage
                    ]);
                } catch (\Exception $logException) {
                    Log::error('SeoAutomationManager: Impossible de créer le log d\'échec', [
                        'error' => $this->cleanForJson($logException->getMessage())
                    ]);
                }
            }
            
            // Retourner le log ou créer un nouveau si nécessaire
            if (!$log) {
                try {
                    $log = SeoAutomation::create([
                        'city_id' => $city->id,
                        'status' => 'failed',
                        'error_message' => $cleanedErrorMessage
                    ]);
                } catch (\Exception $logException) {
                    // Si même la création échoue, retourner null
                    Log::error('SeoAutomationManager: Impossible de créer le log d\'échec', [
                        'error' => $this->cleanForJson($logException->getMessage())
                    ]);
                    return null;
                }
            }
            
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

