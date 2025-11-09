<?php

namespace App\Services;

use App\Models\SeoAutomation;
use App\Models\Article;
use App\Models\City;
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
            $steps[count($steps) - 1]['message'] = 'Contenu généré avec succès (' . strlen($gptData['contenu_html']) . ' caractères)';
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

            // Ajouter les images à la fin de l'article
            $contentHtml = $gptData['contenu_html'];
            
            // Ajouter la section des réalisations avec images si disponibles
            if (isset($gptData['images']) && (!empty($gptData['images']['portfolio']) || !empty($gptData['images']['generated']))) {
                $realizationsSection = "\n\n<h2>Nos Réalisations</h2>\n<p>Découvrez quelques-unes de nos réalisations récentes dans le domaine de {$keyword} :</p>\n<div class=\"realizations-gallery\" style=\"display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;\">\n";
                
                // Ajouter l'image générée si disponible
                if (!empty($gptData['images']['generated'])) {
                    $realizationsSection .= "<div class=\"realization-item\">\n";
                    $realizationsSection .= "<img src=\"{$gptData['images']['generated']}\" alt=\"{$keyword} à {$city->name}\" style=\"width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\" />\n";
                    $realizationsSection .= "</div>\n";
                }
                
                // Ajouter les images de réalisations
                if (!empty($gptData['images']['portfolio'])) {
                    foreach ($gptData['images']['portfolio'] as $img) {
                        $imgUrl = $img['url'] ?? $img;
                        $imgTitle = $img['title'] ?? 'Réalisation';
                        $realizationsSection .= "<div class=\"realization-item\">\n";
                        $realizationsSection .= "<img src=\"{$imgUrl}\" alt=\"{$imgTitle}\" style=\"width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);\" />\n";
                        $realizationsSection .= "<p style=\"text-align: center; margin-top: 10px; font-size: 0.9em; color: #666;\">{$imgTitle}</p>\n";
                        $realizationsSection .= "</div>\n";
                    }
                }
                
                $realizationsSection .= "</div>\n";
                $contentHtml .= $realizationsSection;
            }
            
            $article = Article::create([
                'title' => $gptData['titre'],
                'slug' => $slug,
                'content_html' => $contentHtml,
                'meta_description' => $gptData['meta_description'] ?? null,
                'meta_keywords' => !empty($gptData['mots_cles']) ? implode(', ', $gptData['mots_cles']) : null,
                'focus_keyword' => $keyword,
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

            // 7. Update log
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
                ],
                'error_message' => null,
            ]);

            Log::info('SeoAutomationManager: Article créé avec succès', [
                'city' => $city->name,
                'keyword' => $keyword,
                'article_id' => $article->id,
                'url' => $url
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

