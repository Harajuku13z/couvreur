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
        
        // Étape 2: Générer le texte brut de qualité (SANS HTML)
        if ($progressCallback) {
            $progressCallback([
                'step' => 'article_generation',
                'message' => 'Génération du texte de qualité...'
            ]);
        }
        
        $textPrompt = $this->buildTextPrompt($keyword, $cityName, $relatedQueries, $competitors);
        
        Log::info('GptSeoGenerator: Début génération texte brut', [
            'keyword' => $keyword,
            'city' => $cityName,
            'prompt_length' => strlen($textPrompt),
            'related_queries_count' => count($relatedQueries),
            'competitors_count' => count($competitors)
        ]);
        
        $textResult = AiService::callAI($textPrompt, $systemMessage, [
            'max_tokens' => 3000,
            'temperature' => 0.3, // Plus créatif pour un texte de qualité
            'timeout' => 120
        ]);
        
        if (!$textResult || !isset($textResult['content']) || empty($textResult['content'])) {
            Log::error('GptSeoGenerator: Échec génération texte brut');
            return null;
        }
        
        $rawText = trim($textResult['content']);
        Log::info('GptSeoGenerator: Texte brut généré', [
            'length' => strlen($rawText)
        ]);
        
        // Étape 3: Ajouter le HTML au texte généré
        if ($progressCallback) {
            $progressCallback([
                'step' => 'html_formatting',
                'message' => 'Ajout du formatage HTML...'
            ]);
        }
        
        $htmlPrompt = $this->buildHtmlPrompt($rawText, $generatedTitle ?? $keyword);
        
        $htmlResult = AiService::callAI($htmlPrompt, 'Tu es un expert en formatage HTML pour articles web.', [
            'max_tokens' => 4000,
            'temperature' => 0.1, // Plus précis pour le formatage
            'timeout' => 90
        ]);
        
        if (!$htmlResult || !isset($htmlResult['content']) || empty($htmlResult['content'])) {
            Log::warning('GptSeoGenerator: Échec formatage HTML, utilisation texte brut');
            // Fallback : utiliser le texte brut avec un formatage minimal
            $formattedHtml = '<p>' . nl2br(htmlspecialchars($rawText)) . '</p>';
        } else {
            $formattedHtml = trim($htmlResult['content']);
            // Nettoyer le HTML (enlever markdown code blocks si présents)
            $formattedHtml = preg_replace('/```html\s*/', '', $formattedHtml);
            $formattedHtml = preg_replace('/```\s*/', '', $formattedHtml);
            $formattedHtml = trim($formattedHtml);
        }
        
        // Construire directement le tableau décodé (pas besoin de JSON)
        $decoded = [
            'titre' => $generatedTitle ?? $keyword . ' à ' . $cityName,
            'meta_description' => $this->generateMetaDescription($rawText),
            'contenu_html' => $formattedHtml,
            'mots_cles' => $this->extractKeywords($rawText, $keyword),
            'faq' => []
        ];

        if (empty($decoded['titre']) || empty($decoded['contenu_html'])) {
            // Vérifier les clés API pour donner un message d'erreur plus précis
            $chatgptApiKey = \App\Models\Setting::where('key', 'chatgpt_api_key')->value('value');
            $chatgptEnabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
            $chatgptEnabled = filter_var($chatgptEnabled, FILTER_VALIDATE_BOOLEAN);
            
            $errorDetails = [
                'keyword' => $keyword,
                'city' => $cityName,
                'chatgpt_enabled' => $chatgptEnabled,
                'chatgpt_api_key_exists' => !empty($chatgptApiKey),
                'has_titre' => !empty($decoded['titre']),
                'has_contenu_html' => !empty($decoded['contenu_html']),
                'html_length' => strlen($decoded['contenu_html'] ?? ''),
                'text_length' => strlen($rawText ?? '')
            ];
            
            // Message d'erreur plus détaillé
            if ($chatgptEnabled && empty($chatgptApiKey)) {
                $errorDetails['suggestion'] = 'Clé API ChatGPT manquante. Configurez-la dans la section "Configuration des APIs".';
            } elseif ($chatgptEnabled && !empty($chatgptApiKey)) {
                $errorDetails['suggestion'] = 'ChatGPT a échoué. Vérifiez vos logs pour plus de détails. Vérifiez votre clé API, vos quotas et votre solde.';
            } else {
                $errorDetails['suggestion'] = 'ChatGPT est désactivé. Activez-le dans la section "Configuration des APIs".';
            }
            
            Log::error('GptSeoGenerator: Données invalides après génération', $errorDetails);
            return null;
        }
        
        Log::info('GptSeoGenerator: Article généré avec succès', [
            'keyword' => $keyword,
            'city' => $cityName,
            'html_length' => strlen($formattedHtml),
            'text_length' => strlen($rawText)
        ]);

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
     * Construire le prompt pour générer un texte brut de qualité (SANS HTML)
     */
    protected function buildTextPrompt(
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
Tu es un expert en rédaction SEO et marketing de contenu. Ta tâche est de rédiger un article web de qualité supérieure, engageant et optimisé pour le référencement Google.

**IMPORTANT : Écris UNIQUEMENT le texte brut, SANS HTML, SANS formatage, juste le contenu de qualité.**

**1. Mot-clé principal :** {$keyword} à {$cityName}

{$sourcesList}
**Requêtes associées à intégrer naturellement :** {$related}
{$companyInfo}

**Objectifs de l'article :**

- Créer un contenu **unique et original**, qui n'est pas dupliqué par rapport aux sources.
- Fournir une **introduction captivante** (2-3 paragraphes) qui accroche le lecteur.
- Structurer mentalement l'article avec des sections claires (mais ne pas écrire de titres HTML).
- Inclure le **mot-clé principal** et des variantes naturelles tout au long de l'article.
- Utiliser des phrases claires, engageantes et faciles à lire (15-20 mots max par phrase).
- Utiliser des **paragraphes courts** (3-5 phrases max).
- Longueur: **entre 1500 et 2500 mots** pour un contenu complet et détaillé.
- Ajouter un **appel à l'action** à la fin.
- Inclure au moins un paragraphe mentionnant explicitement {$cityName} et l'expertise locale.
- Ne te contente pas de reformuler les sources, synthétise et enrichis avec des exemples concrets, des conseils pratiques, des statistiques si pertinentes.

**Format de sortie :**
Retourne UNIQUEMENT le texte brut, sans HTML, sans JSON, sans formatage. Juste le contenu de qualité, paragraphe par paragraphe, séparés par des retours à la ligne.
");
    }

    /**
     * Construire le prompt pour ajouter le HTML au texte
     */
    protected function buildHtmlPrompt(string $rawText, string $title): string
    {
        return trim("
Tu es un expert en formatage HTML pour articles web. Ta tâche est de transformer le texte brut suivant en HTML bien structuré.

**Titre de l'article :** {$title}

**Texte brut à formater :**

{$rawText}

**Instructions de formatage :**

1. Identifie les sections principales et ajoute des balises <h2> pour les titres de section
2. Identifie les sous-sections et ajoute des balises <h3> pour les sous-titres
3. Entoure chaque paragraphe avec <p>...</p>
4. Identifie les listes (éléments avec puces ou numérotés) et utilise <ul><li>...</li></ul> ou <ol><li>...</li></ol>
5. Ajoute un seul <br> entre chaque paragraphe <p> (pas de double <br><br>)
6. NE PAS ajouter <br> après les titres H2/H3
7. Utilise <strong> pour les mots importants et <em> pour l'emphase
8. Assure-toi que le HTML est valide et bien structuré

**Format de sortie :**
Retourne UNIQUEMENT le HTML formaté, sans markdown, sans code blocks, juste le HTML pur.
");
    }

    /**
     * Générer une meta description depuis le texte
     */
    protected function generateMetaDescription(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (strlen($text) <= 155) {
            return $text;
        }
        
        return Str::limit($text, 152) . '...';
    }

    /**
     * Extraire des mots-clés depuis le texte
     */
    protected function extractKeywords(string $text, string $mainKeyword): array
    {
        $keywords = [$mainKeyword];
        
        // Extraire quelques mots-clés supplémentaires (simplifié)
        $words = str_word_count(strtolower($text), 1, 'àáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ');
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'à', 'pour', 'dans', 'sur', 'avec', 'par', 'est', 'sont', 'être', 'avoir', 'faire', 'cette', 'ces', 'ce', 'que', 'qui', 'quoi', 'comment', 'pourquoi', 'quand', 'où'];
        
        $count = 0;
        foreach ($wordFreq as $word => $freq) {
            if ($count >= 4) break;
            if (strlen($word) >= 4 && !in_array($word, $stopWords) && !in_array($word, $keywords)) {
                $keywords[] = $word;
                $count++;
            }
        }
        
        return array_slice($keywords, 0, 5);
    }
}

