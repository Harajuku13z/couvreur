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
            $competitorTitles = [];
            foreach ($competitors as $competitor) {
                $competitorTitles[] = $competitor['title'] ?? 'N/A';
            }
            
            $steps[count($steps) - 1]['data'] = [
                'related_queries' => array_slice($related, 0, 3),
                'competitors_count' => count($competitors),
                'competitors_titles' => array_slice($competitorTitles, 0, 5)
            ];
            if ($progressCallback) $progressCallback($steps);

            // 4. Génération GPT
            $steps[] = [
                'step' => 'gpt_generation',
                'title' => 'Génération du contenu (GPT)',
                'status' => 'processing',
                'message' => 'Création du contenu optimisé avec GPT...',
                'data' => []
            ];
            if ($progressCallback) $progressCallback($steps);
            
            $gptData = $this->gpt->generateSeoArticle($keyword, $city->name, $related, $competitors);

            if (!$gptData || empty($gptData['titre']) || empty($gptData['contenu_html'])) {
                $steps[count($steps) - 1]['status'] = 'failed';
                $steps[count($steps) - 1]['message'] = 'Génération GPT échouée ou réponse invalide';
                if ($progressCallback) $progressCallback($steps);
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Génération GPT échouée ou réponse invalide',
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

            $article = Article::create([
                'title' => $gptData['titre'],
                'slug' => $slug,
                'content_html' => $gptData['contenu_html'],
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
     * Sélectionner un mot-clé pour une ville (éviter les doublons récents)
     */
    protected function selectKeywordForCity(array $keywords, City $city): ?string
    {
        // Récupérer les mots-clés utilisés dans les 14 derniers jours pour cette ville
        $recent = SeoAutomation::where('city_id', $city->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->whereNotNull('keyword')
            ->pluck('keyword')
            ->toArray();

        // Essayer de trouver un mot-clé non utilisé
        foreach ($keywords as $k) {
            if (!in_array($k, $recent)) {
                return $k;
            }
        }

        // Si tous sont déjà utilisés, prendre un au hasard
        if (!empty($keywords)) {
            return $keywords[array_rand($keywords)];
        }

        return null;
    }
}

