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
     * @return SeoAutomation Instance du log créé
     */
    public function runForCity(City $city): SeoAutomation
    {
        $log = SeoAutomation::create([
            'city_id' => $city->id,
            'status' => 'pending',
        ]);

        try {
            // 1. Récupérer tendances (utiliser region si dispo, sinon 'FR')
            $geo = $city->region ?? 'FR';
            // Nettoyer le code région si nécessaire (ex: "FR-27" -> "FR")
            if (strpos($geo, '-') !== false) {
                $geo = explode('-', $geo)[0];
            }
            
            $keywords = $this->serp->getTrendingKeywords($geo, 12);
            
            if (empty($keywords)) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Aucun mot-clé récupéré depuis SerpAPI'
                ]);
                return $log;
            }

            // 2. Choisir mot-clé : priorité = mot non déjà utilisé récemment pour cette ville
            $keyword = $this->selectKeywordForCity($keywords, $city);
            
            if (!$keyword) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Aucun mot-clé disponible (tous déjà utilisés récemment)'
                ]);
                return $log;
            }

            // 3. Related + competitors
            $related = $this->serp->getRelatedQueries($keyword, 6);
            $competitors = $this->serp->getTopSERP($keyword, 5);

            // 4. Génération GPT
            $gptData = $this->gpt->generateSeoArticle($keyword, $city->name, $related, $competitors);

            if (!$gptData || empty($gptData['titre']) || empty($gptData['contenu_html'])) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Génération GPT échouée ou réponse invalide',
                    'metadata' => $gptData
                ]);
                return $log;
            }

            // 5. Créer l'article
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

            // 6. Indexation Google
            $url = url("/articles/{$article->slug}");
            $indexed = $this->indexer->indexUrl($url);

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

