<?php

namespace App\Services;

use App\Services\AiService;
use App\Services\PortfolioImageService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GptSeoGenerator
{
    protected $portfolioImageService;

    public function __construct(PortfolioImageService $portfolioImageService)
    {
        $this->portfolioImageService = $portfolioImageService;
    }

    /**
     * Générer un article SEO optimisé via GPT
     * 
     * @param string $keyword Mot-clé principal
     * @param string $cityName Nom de la ville
     * @param array $relatedQueries Requêtes associées
     * @param array $competitors Résultats SERP concurrents
     * @return array|null Données de l'article généré (titre, contenu, meta, etc.)
     */
    public function generateSeoArticle(
        string $keyword,
        string $cityName,
        array $relatedQueries = [],
        array $competitors = [],
        ?callable $progressCallback = null
    ): ?array {
        $prompt = $this->buildPrompt($keyword, $cityName, $relatedQueries, $competitors);
        
        $systemMessage = 'Tu es un rédacteur SEO professionnel spécialisé dans le contenu local pour le secteur du bâtiment et de la rénovation.';
        
        // Étape 1: Générer uniquement le titre d'abord
        if ($progressCallback) {
            $progressCallback([
                'step' => 'title_generation',
                'message' => 'Génération du titre optimisé...'
            ]);
        }
        
        $titlePrompt = "Pour le mot-clé \"{$keyword}\" ciblant la ville {$cityName}, génère UNIQUEMENT un titre d'article SEO optimisé (60-70 caractères max).\n\nRetourne UNIQUEMENT le titre, sans formatage, sans JSON, juste le titre.";
        
        $titleResult = AiService::callAI($titlePrompt, $systemMessage, [
            'max_tokens' => 100,
            'temperature' => 0.3,
            'timeout' => 30
        ]);
        
        $generatedTitle = null;
        if ($titleResult && isset($titleResult['content']) && !empty($titleResult['content'])) {
            $generatedTitle = trim($titleResult['content']);
            // Nettoyer le titre (enlever guillemets, markdown, etc.)
            $generatedTitle = preg_replace('/^["\']|["\']$/', '', $generatedTitle);
            $generatedTitle = preg_replace('/^#+\s*/', '', $generatedTitle);
            $generatedTitle = trim($generatedTitle);
            
            if (!empty($generatedTitle)) {
                if ($progressCallback) {
                    $progressCallback([
                        'step' => 'title_generated',
                        'message' => 'Titre généré: ' . $generatedTitle,
                        'title' => $generatedTitle
                    ]);
                }
                
                Log::info('GptSeoGenerator: Titre généré avec succès', [
                    'keyword' => $keyword,
                    'city' => $cityName,
                    'title' => $generatedTitle
                ]);
            } else {
                Log::warning('GptSeoGenerator: Titre généré mais vide après nettoyage', [
                    'original' => $titleResult['content'] ?? 'N/A'
                ]);
            }
        } else {
            Log::warning('GptSeoGenerator: Échec génération titre', [
                'has_result' => !empty($titleResult),
                'has_content' => isset($titleResult['content']),
                'content_preview' => isset($titleResult['content']) ? substr($titleResult['content'], 0, 100) : 'N/A'
            ]);
        }
        
        // Étape 2: Générer l'article complet avec le titre
        if ($progressCallback) {
            $progressCallback([
                'step' => 'article_generation',
                'message' => 'Génération de l\'article complet...'
            ]);
        }
        
        Log::info('GptSeoGenerator: Début génération article complet', [
            'keyword' => $keyword,
            'city' => $cityName,
            'prompt_length' => strlen($prompt),
            'related_queries_count' => count($relatedQueries),
            'competitors_count' => count($competitors)
        ]);
        
        // Vérifier la longueur du prompt pour éviter les erreurs
        $promptLength = strlen($prompt);
        Log::info('GptSeoGenerator: Longueur prompt avant appel', [
            'prompt_length' => $promptLength,
            'prompt_length_kb' => round($promptLength / 1024, 2)
        ]);
        
        // Si le prompt est trop long, réduire les sources
        if ($promptLength > 6000) {
            Log::warning('GptSeoGenerator: Prompt trop long, réduction des sources', [
                'original_length' => $promptLength
            ]);
            // Réduire encore plus les sources si nécessaire
            $sourcesList = '';
            if (!empty($competitors)) {
                $competitorsLimited = array_slice($competitors, 0, 3); // Limiter à 3 sources
                $sourcesList = "\n\n**Sources principales** (3 articles) :\n\n";
                foreach ($competitorsLimited as $index => $competitor) {
                    $title = $competitor['title'] ?? 'Article sans titre';
                    $sourcesList .= ($index + 1) . ". {$title}\n";
                }
            }
            // Reconstruire le prompt avec sources réduites
            $prompt = $this->buildPrompt($keyword, $cityName, array_slice($relatedQueries, 0, 3), $competitorsLimited);
        }
        
        // Calculer max_tokens en fonction du modèle et de la longueur du prompt
        // Estimation: ~1 token = 4 caractères
        $estimatedInputTokens = (int)(strlen($prompt) / 4);
        // Pour GPT-4o et GPT-4: limite de 4096 tokens de completion
        // Pour GPT-3.5: limite de 4096 tokens de completion
        // Laisser une marge de sécurité: max 3500 tokens de completion
        $maxTokens = min(3500, max(2000, 4096 - $estimatedInputTokens));
        
        // Si le calcul donne un max_tokens trop faible, utiliser une valeur minimale raisonnable
        if ($maxTokens < 1500) {
            $maxTokens = 2000; // Minimum pour un article de qualité
            Log::warning('GptSeoGenerator: Prompt très long, utilisation max_tokens minimum', [
                'max_tokens' => $maxTokens,
                'estimated_input_tokens' => $estimatedInputTokens
            ]);
        }
        
        Log::info('GptSeoGenerator: Paramètres génération', [
            'max_tokens' => $maxTokens,
            'estimated_input_tokens' => $estimatedInputTokens,
            'total_estimated_tokens' => $estimatedInputTokens + $maxTokens
        ]);
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => $maxTokens, // Limité à 3500 max pour respecter la limite de 4096
            'temperature' => 0.2,
            'timeout' => 180 // Timeout augmenté pour génération plus longue
        ]);

        if (!$result || !isset($result['content']) || empty($result['content'])) {
            // Vérifier les clés API pour donner un message d'erreur plus précis
            $chatgptApiKey = \App\Models\Setting::where('key', 'chatgpt_api_key')->value('value');
            $chatgptEnabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
            $chatgptEnabled = filter_var($chatgptEnabled, FILTER_VALIDATE_BOOLEAN);
            
            $errorDetails = [
                'keyword' => $keyword,
                'city' => $cityName,
                'chatgpt_enabled' => $chatgptEnabled,
                'chatgpt_api_key_exists' => !empty($chatgptApiKey),
                'has_result' => !empty($result),
                'has_content' => isset($result['content']),
                'content_length' => isset($result['content']) ? strlen($result['content']) : 0,
                'provider' => $result['provider'] ?? 'unknown'
            ];
            
            // Message d'erreur plus détaillé
            if ($chatgptEnabled && empty($chatgptApiKey)) {
                $errorDetails['suggestion'] = 'Clé API ChatGPT manquante. Configurez-la dans la section "Configuration des APIs".';
            } elseif ($chatgptEnabled && !empty($chatgptApiKey)) {
                $errorDetails['suggestion'] = 'ChatGPT a échoué. Vérifiez vos logs pour plus de détails. Vérifiez votre clé API, vos quotas et votre solde.';
            } else {
                $errorDetails['suggestion'] = 'ChatGPT est désactivé. Activez-le dans la section "Configuration des APIs".';
            }
            
            Log::error('GptSeoGenerator: Échec génération article', $errorDetails);
            return null;
        }
        
        Log::info('GptSeoGenerator: Article généré avec succès', [
            'keyword' => $keyword,
            'city' => $cityName,
            'content_length' => strlen($result['content']),
            'provider' => $result['provider'] ?? 'unknown'
        ]);

        $content = $result['content'];
        
        // Nettoyer le contenu (enlever markdown code blocks si présents)
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);
        
        // Essayer de parser le JSON
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Si pas de JSON, essayer d'extraire un bloc JSON
            if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
                $decoded = json_decode($matches[0], true);
            } else {
                Log::warning('GptSeoGenerator: Réponse non-JSON', [
                    'content_preview' => substr($content, 0, 500),
                    'json_error' => json_last_error_msg()
                ]);
                return null;
            }
        }

        if (!$decoded) {
            Log::error('GptSeoGenerator: Impossible de décoder JSON', [
                'content_preview' => substr($content, 0, 500),
                'json_error' => json_last_error_msg()
            ]);
            return null;
        }

        // Utiliser le titre généré précédemment si disponible et si le titre du JSON est vide
        if (empty($decoded['titre']) && $generatedTitle) {
            $decoded['titre'] = $generatedTitle;
            Log::info('GptSeoGenerator: Utilisation du titre généré précédemment', [
                'title' => $generatedTitle
            ]);
        }
        
        if (empty($decoded['titre']) || empty($decoded['contenu_html'])) {
            Log::error('GptSeoGenerator: Données invalides (titre ou contenu_html manquant)', [
                'has_titre' => !empty($decoded['titre']),
                'has_contenu_html' => !empty($decoded['contenu_html']),
                'generated_title' => $generatedTitle,
                'decoded_keys' => array_keys($decoded ?? [])
            ]);
            return null;
        }

        // Récupérer les images de réalisations
        $portfolioImages = $this->portfolioImageService->getImagesByKeyword($keyword, 5);
        
        // Récupérer l'image depuis la banque d'images par mot-clé (au lieu de DALL-E)
        $keywordImage = null;
        try {
            $keywordImageModel = \App\Models\KeywordImage::where('keyword', $keyword)
                ->where('is_active', true)
                ->orderBy('display_order')
                ->first();
            
            if ($keywordImageModel && !empty($keywordImageModel->image_path)) {
                $keywordImage = $keywordImageModel->image_path;
                Log::info('Image récupérée depuis la banque d\'images', [
                    'keyword' => $keyword,
                    'image_path' => $keywordImage
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Erreur récupération image banque', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);
        }
        
        // Ajouter les images à l'article (sans DALL-E)
        if (!empty($portfolioImages) || $keywordImage) {
            $decoded['images'] = [
                'keyword_image' => $keywordImage, // Image de la banque d'images
                'portfolio' => $portfolioImages
            ];
        }

        return $decoded;
    }

    /**
     * Construire le prompt pour GPT
     */
    protected function buildPrompt(
        string $keyword,
        string $cityName,
        array $relatedQueries,
        array $competitors
    ): string {
        // Récupérer les informations de l'entreprise
        $companyName = \App\Models\Setting::where('key', 'company_name')->value('value') ?? 'notre entreprise';
        $companyDescription = \App\Models\Setting::where('key', 'company_description')->value('value') ?? '';
        $companyCity = \App\Models\Setting::where('key', 'company_city')->value('value') ?? '';
        $companyPhone = \App\Models\Setting::where('key', 'company_phone')->value('value') ?? '';
        
        // Construire la liste des sources (titres + liens) - Limiter à 5 sources pour éviter prompt trop long
        $sourcesList = '';
        if (!empty($competitors)) {
            $competitorsLimited = array_slice($competitors, 0, 5); // Limiter à 5 sources
            $sourcesList = "\n\n**Sources à utiliser** - Voici " . count($competitorsLimited) . " titres d'articles existants + liens pour comprendre le sujet :\n\n";
            foreach ($competitorsLimited as $index => $competitor) {
                $title = $competitor['title'] ?? 'Article sans titre';
                $link = $competitor['link'] ?? '#';
                $snippet = $competitor['snippet'] ?? '';
                $sourcesList .= ($index + 1) . ". **{$title}**\n";
                $sourcesList .= "   Lien: {$link}\n";
                if ($snippet) {
                    $sourcesList .= "   Extrait: " . substr($snippet, 0, 100) . "...\n"; // Réduire à 100 caractères
                }
                $sourcesList .= "\n";
            }
        }
        
        $related = empty($relatedQueries) ? '' : implode(', ', array_slice($relatedQueries, 0, 4)); // Réduire à 4 requêtes
        
        $companyInfo = '';
        if ($companyName && $companyName !== 'notre entreprise') {
            $companyInfo = "\n\n**INFORMATIONS DE L'ENTREPRISE À METTRE EN AVANT:**\n";
            $companyInfo .= "- Nom: {$companyName}\n";
            if ($companyDescription) {
                $companyInfo .= "- Description: {$companyDescription}\n";
            }
            if ($companyCity) {
                $companyInfo .= "- Localisation: {$companyCity}\n";
            }
            if ($companyPhone) {
                $companyInfo .= "- Téléphone: {$companyPhone}\n";
            }
            $companyInfo .= "\n**IMPORTANT:** Intègre naturellement ces informations dans le contenu, notamment dans un paragraphe dédié à {$cityName} où tu mentionneras {$companyName} comme acteur local de confiance. Ajoute un appel à l'action à la fin pour inviter les lecteurs à contacter {$companyName}.";
        }
        
        return trim("
Tu es un expert en rédaction SEO et marketing de contenu. Ta tâche est de rédiger un article web de qualité supérieure, structuré, engageant et optimisé pour le référencement Google.

**1. Mot-clé principal :** {$keyword} à {$cityName}

{$sourcesList}
**Requêtes associées à intégrer naturellement :** {$related}
{$companyInfo}

**Objectifs de l'article :**

- Créer un contenu **unique**, qui n'est pas dupliqué par rapport aux sources.
- Fournir une **introduction captivante** (2-3 paragraphes) qui accroche le lecteur.
- Structurer l'article avec des **sous-titres H2 et H3 pertinents** et bien espacés.
- Inclure le **mot-clé principal** et des variantes naturelles tout au long de l'article.
- Utiliser des phrases claires, engageantes et faciles à lire (15-20 mots max par phrase).
- Proposer des **listes à puces** pour rendre le contenu plus digeste.
- Utiliser des **paragraphes courts** (3-5 phrases max) avec des espaces entre eux.
- Longueur: **entre 1000 et 1800 mots** pour un contenu complet et détaillé.
- HTML propre avec des balises sémantiques: <h1>, <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>
- **ESPACEMENT** : Ajouter un seul <br> entre chaque paragraphe pour une meilleure lisibilité (pas de double <br><br>).
- Ajouter un **appel à l'action** à la fin (ex : \"Découvrez nos services\", \"Contactez-nous pour un devis gratuit\", etc.)
- Inclure une **FAQ de 5 à 8 questions** pertinentes avec réponses détaillées.

**STRUCTURE HTML STRICTE À RESPECTER :**

<h1>Titre principal</h1>

<p>Premier paragraphe d'introduction (2-3 phrases).</p>
<br>
<p>Deuxième paragraphe d'introduction (2-3 phrases).</p>
<br>

<h2>Premier sous-titre H2</h2>
<p>Paragraphe d'introduction de la section (2-3 phrases).</p>
<br>
<p>Paragraphe de développement (3-4 phrases).</p>
<br>

<h3>Sous-sous-titre H3</h3>
<p>Paragraphe explicatif (2-3 phrases).</p>
<br>

<ul>
<li>Point important 1</li>
<li>Point important 2</li>
<li>Point important 3</li>
</ul>
<br>

<p>Paragraphe de conclusion de la section (2-3 phrases).</p>
<br>

<h2>Deuxième sous-titre H2</h2>
<p>Contenu de la section...</p>
<br>

<h2>Conclusion</h2>
<p>Paragraphe de conclusion (3-4 phrases) avec appel à l'action.</p>
<br>

<h2>FAQ</h2>
<br>
<div class=\"faq\">
<h3>Question 1 ?</h3>
<p>Réponse détaillée 1 (2-3 phrases).</p>
<br>
<h3>Question 2 ?</h3>
<p>Réponse détaillée 2 (2-3 phrases).</p>
</div>

**Format de sortie STRICTEMENT EN JSON (pas de markdown, pas de code block):**

{
  \"titre\": \"Titre optimisé SEO (60-70 caractères max)\",
  \"meta_description\": \"Description SEO optimisée (155 caractères max)\",
  \"contenu_html\": \"Article complet en HTML avec structure propre, espaces entre paragraphes (<br><br>), et formatage clair\",
  \"mots_cles\": [\"mot-clé 1\", \"mot-clé 2\", \"mot-clé 3\", \"mot-clé 4\", \"mot-clé 5\"],
  \"faq\": [
    {\"question\": \"Question 1\", \"reponse\": \"Réponse détaillée 1\"},
    {\"question\": \"Question 2\", \"reponse\": \"Réponse détaillée 2\"},
    ...
  ]
}

**RÈGLES CRITIQUES DE FORMATAGE :**
1. **TOUJOURS** ajouter un seul <br> entre chaque paragraphe <p> (pas de double <br><br>)
2. **NE PAS** ajouter <br> après les titres H2/H3 (les styles CSS gèrent l'espacement)
3. Utiliser des **paragraphes courts** (3-5 phrases maximum)
4. Utiliser des **listes à puces** pour les informations importantes
5. Le contenu doit être **bien structuré** avec des espaces modérés
6. Inclure au moins un paragraphe mentionnant explicitement {$cityName} et l'expertise locale
7. Ne te contente pas de reformuler les sources, synthétise et enrichis avec des exemples concrets
");
    }
}

