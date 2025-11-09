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
        $systemMessage = 'Tu es un rédacteur SEO professionnel spécialisé dans le contenu local pour le secteur du bâtiment et de la rénovation.';
        
        // Étape 1: Générer le contenu en HTML directement (ou markdown bien formaté)
        if ($progressCallback) {
            $progressCallback([
                'step' => 'article_generation',
                'message' => 'Génération du contenu HTML...'
            ]);
        }
        
        $htmlPrompt = $this->buildHtmlContentPrompt($keyword, $cityName, $relatedQueries, $competitors);
        
        Log::info('GptSeoGenerator: Début génération contenu HTML', [
            'keyword' => $keyword,
            'city' => $cityName,
            'prompt_length' => strlen($htmlPrompt),
            'related_queries_count' => count($relatedQueries),
            'competitors_count' => count($competitors)
        ]);
        
        $htmlResult = AiService::callAI($htmlPrompt, $systemMessage, [
            'max_tokens' => 4000,
            'temperature' => 0.3,
            'timeout' => 120
        ]);
        
        if (!$htmlResult || !isset($htmlResult['content']) || empty($htmlResult['content'])) {
            Log::error('GptSeoGenerator: Échec génération contenu HTML');
            return null;
        }
        
        $htmlContent = trim($htmlResult['content']);
        // Nettoyer le HTML (enlever markdown code blocks si présents)
        $htmlContent = preg_replace('/```html\s*/', '', $htmlContent);
        $htmlContent = preg_replace('/```\s*/', '', $htmlContent);
        $htmlContent = trim($htmlContent);
        
        Log::info('GptSeoGenerator: Contenu HTML généré', [
            'length' => strlen($htmlContent)
        ]);
        
        // Étape 2: Générer le titre BASÉ sur le contenu généré
        if ($progressCallback) {
            $progressCallback([
                'step' => 'title_generation',
                'message' => 'Génération du titre basé sur le contenu...'
            ]);
        }
        
        // Extraire un extrait du contenu pour générer le titre
        $contentText = strip_tags($htmlContent);
        $contentPreview = substr($contentText, 0, 2000);
        
        $titlePrompt = "À partir du contenu suivant, génère UNIQUEMENT un titre d'article SEO optimisé (60-70 caractères max) qui correspond EXACTEMENT au contenu. Le titre doit refléter fidèlement ce qui est écrit dans l'article.\n\n**Contenu de l'article :**\n\n" . $contentPreview . "\n\n**Mot-clé principal :** {$keyword} à {$cityName}\n\nRetourne UNIQUEMENT le titre, sans formatage, sans JSON, juste le titre.";
        
        $titleResult = AiService::callAI($titlePrompt, $systemMessage, [
            'max_tokens' => 100,
            'temperature' => 0.2,
            'timeout' => 30
        ]);
        
        $generatedTitle = null;
        if ($titleResult && isset($titleResult['content']) && !empty($titleResult['content'])) {
            $generatedTitle = trim($titleResult['content']);
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
            }
        }
        
        // Étape 3: Générer la meta description BASÉE sur le titre et le contenu
        if ($progressCallback) {
            $progressCallback([
                'step' => 'meta_description',
                'message' => 'Génération de la meta description...'
            ]);
        }
        
        $metaDescription = $this->generateMetaDescriptionFromTitle($generatedTitle ?? $keyword . ' à ' . $cityName, $htmlContent);
        
        // Construire le tableau décodé
        $decoded = [
            'titre' => $generatedTitle ?? $keyword . ' à ' . $cityName,
            'meta_description' => $metaDescription,
            'contenu_html' => $htmlContent, // HTML directement depuis ChatGPT
            'mots_cles' => $this->extractKeywords($contentText, $keyword),
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
            'html_length' => strlen($rawText),
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
     * Construire le prompt pour générer le contenu directement en HTML
     */
    protected function buildHtmlContentPrompt(
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
            $companyInfo .= "\n**IMPORTANT:** Intègre naturellement ces informations dans le contenu, notamment dans un paragraphe dédié à {$cityName} où tu mentionneras {$companyName} comme acteur local de confiance. **NE PAS inclure de numéro de téléphone, email ou adresse dans le contenu. Le CTA est déjà présent ailleurs sur la page.**";
        }
        
        return trim("
Tu es un expert en rédaction SEO et marketing de contenu pour le secteur du bâtiment et de la rénovation. Ta tâche est de rédiger un article web de qualité supérieure, engageant et optimisé pour le référencement Google.

**IMPORTANT : Écris UNIQUEMENT le texte brut, SANS HTML, SANS formatage, juste le contenu de qualité.**

**1. Mot-clé principal :** {$keyword} à {$cityName}

{$sourcesList}
**Requêtes associées à intégrer naturellement :** {$related}
{$companyInfo}

**STRUCTURE OBLIGATOIRE DE L'ARTICLE :**

1. **INTRODUCTION (2-3 paragraphes)** :
   - Commencer par une phrase d'accroche qui mentionne la ville ({$cityName}) et le contexte local
   - Présenter l'entreprise ({$companyName}) comme expert local
   - Mentionner les services principaux liés au mot-clé
   - Utiliser \"vous\" pour s'adresser directement au lecteur

2. **SECTIONS PRINCIPALES (H2)** - Minimum 3-4 sections :
   - Chaque section doit avoir un titre clair et descriptif (ex: \"Rénovation et Réparation de Couverture à {$cityName}\")
   - Chaque section doit contenir 2-4 paragraphes de contenu détaillé
   - Inclure des détails techniques précis (matériaux, processus, normes)
   - Mentionner {$cityName} dans au moins une section

3. **SOUS-SECTIONS (H3)** - Dans chaque section principale :
   - Créer 1-2 sous-sections avec des titres spécifiques
   - Exemple: \"Les Matériaux de Couverture Maîtrisés\", \"Isolation Thermique et Traitement d'Humidité\"

4. **LISTES À PUCES** :
   - Utiliser des listes à puces pour détailler les services, matériaux, avantages
   - Format: \"• Service 1 : Description détaillée\"
   - Minimum 2-3 listes dans l'article

5. **LISTES NUMÉROTÉES** :
   - Utiliser pour les processus étape par étape
   - Format: \"1. Étape 1 : Description\"
   - Au moins une liste numérotée si le sujet s'y prête

6. **SECTION \"POURQUOI CHOISIR [ENTREPRISE]\"** :
   - Section dédiée mettant en avant l'entreprise
   - Liste des avantages/garanties
   - Mention de la localisation et de la réactivité

7. **CONCLUSION** :
   - Paragraphe final de conclusion qui résume les points clés de l'article
   - **IMPORTANT : NE PAS inclure d'appel à l'action avec coordonnées (téléphone, email, adresse)**
   - **IMPORTANT : NE PAS mentionner de numéro de téléphone, email ou adresse dans le contenu**
   - Le CTA est déjà présent ailleurs sur la page, donc se contenter d'une conclusion informative
   - **INTERDICTION STRICTE** : Ne JAMAIS inclure de phrases comme \"Contactez-nous au...\", \"Appelez-nous au...\", \"Pour toute demande de devis...\" avec des coordonnées

**RÈGLES DE RÉDACTION :**

- Ton : Professionnel, expert, rassurant, utilisant \"vous\" et \"notre\"
- Longueur : Entre 1800 et 2500 mots pour un contenu complet
- Paragraphes : Courts (3-5 phrases max), aérés
- Phrases : Claires, 15-20 mots max
- Mots-clés : Intégrer naturellement \"{$keyword}\", \"{$cityName}\", variantes
- Détails techniques : Inclure des informations précises (matériaux, normes, processus)
- Localisation : Mentionner {$cityName} et le contexte local (département, région) naturellement
- Entreprise : Mettre en avant {$companyName} comme acteur local de confiance, expert

**IMPORTANT - COHÉRENCE TITRE/CONTENU :**
- Si le titre mentionne \"conseils\", le contenu DOIT contenir des conseils pratiques détaillés
- Si le titre mentionne \"guide\", le contenu DOIT être un guide complet avec étapes
- Si le titre mentionne \"solutions\", le contenu DOIT présenter des solutions concrètes
- Le contenu doit TOUJOURS correspondre aux promesses du titre

**EXEMPLE DE STRUCTURE :**

Introduction (2-3 paragraphes)
[Section H2: Titre principal]
  Paragraphe d'introduction de la section
  [Sous-section H3: Titre sous-section]
    Contenu détaillé avec listes à puces si nécessaire
  [Sous-section H3: Autre sous-section]
    Contenu détaillé
[Section H2: Autre section principale]
  Contenu avec processus numéroté si applicable
[Section H2: Pourquoi Choisir [Entreprise]]
  Liste des avantages
[Appel à l'action et contact]

**Format de sortie :**
Retourne UNIQUEMENT le texte brut, sans HTML, sans JSON, sans formatage. Juste le contenu de qualité, paragraphe par paragraphe, séparés par des retours à la ligne. Indique les sections principales par des lignes vides avant et après.
");
    }

    /**
     * Construire le prompt pour ajouter le HTML au texte
     */
    protected function buildHtmlPrompt(string $rawText, string $title): string
    {
        return trim("
Tu es un expert en formatage HTML pour articles web. Ta tâche est de transformer le texte brut suivant en HTML bien structuré, en suivant EXACTEMENT la structure de l'exemple de référence.

**Titre de l'article :** {$title}

**Texte brut à formater :**

{$rawText}

**Instructions de formatage STRICTES :**

1. **Sections principales (H2)** :
   - Identifie les sections principales (généralement après un paragraphe d'introduction)
   - Ajoute des balises <h2> pour chaque section principale
   - Exemples de titres H2 : \"Rénovation et Réparation de Couverture à [Ville]\", \"Nettoyage et Entretien de Toiture\", \"Services Complémentaires\", \"Pourquoi Choisir [Entreprise]\"

2. **Sous-sections (H3)** :
   - Identifie les sous-sections dans chaque section principale
   - Ajoute des balises <h3> pour les sous-titres
   - Exemples : \"Les Matériaux de Couverture Maîtrisés\", \"Isolation Thermique et Traitement d'Humidité\", \"Démoussage, Lavage et Traitement Hydrofuge\"

3. **Paragraphes** :
   - Entoure chaque paragraphe avec <p>...</p>
   - Ajoute un seul <br> entre chaque paragraphe <p> (pas de double <br><br>)
   - NE PAS ajouter <br> après les titres H2/H3

4. **Listes à puces** :
   - Identifie les listes avec puces (éléments commençant par \"•\", \"-\", ou similaires)
   - Utilise <ul><li>...</li></ul> pour les listes à puces
   - Chaque élément de liste doit être dans un <li>
   - Format : <ul><li>Élément 1</li><li>Élément 2</li></ul>

5. **Listes numérotées** :
   - Identifie les listes numérotées (éléments commençant par \"1.\", \"2.\", etc.)
   - Utilise <ol><li>...</li></ol> pour les listes numérotées
   - Format : <ol><li>Étape 1</li><li>Étape 2</li></ol>

6. **Mise en forme** :
   - Utilise <strong> pour les mots importants, noms d'entreprise, services clés
   - Utilise <em> pour l'emphase légère
   - Mettre en <strong> le nom de l'entreprise et les services principaux

7. **Structure finale** :
   - Introduction : 2-3 paragraphes <p> au début
   - Sections : Minimum 3-4 sections <h2> avec contenu
   - Sous-sections : 1-2 <h3> par section principale
   - Listes : Au moins 2-3 listes <ul> ou <ol> dans l'article
   - Conclusion : Paragraphe final de conclusion (SANS coordonnées, SANS appel à l'action avec téléphone/email)
   - **INTERDICTION STRICTE** : Ne JAMAIS inclure de numéro de téléphone, email, adresse dans le HTML généré

**EXEMPLE DE STRUCTURE HTML ATTENDUE :**

<p>Introduction paragraphe 1...</p>
<br>
<p>Introduction paragraphe 2...</p>
<br>
<h2>Section Principale 1</h2>
<p>Contenu de la section...</p>
<br>
<h3>Sous-section 1</h3>
<p>Contenu sous-section...</p>
<br>
<ul>
<li>Élément de liste 1</li>
<li>Élément de liste 2</li>
</ul>
<br>
<h2>Section Principale 2</h2>
<p>Contenu...</p>
<br>
<ol>
<li>Étape 1</li>
<li>Étape 2</li>
</ol>

**Format de sortie :**
Retourne UNIQUEMENT le HTML formaté, sans markdown, sans code blocks, juste le HTML pur. Assure-toi que la structure est claire avec des H2 pour les sections principales et H3 pour les sous-sections.
");
    }

    /**
     * Générer une meta description BASÉE sur le titre et le contenu
     */
    protected function generateMetaDescriptionFromTitle(string $title, string $htmlContent): string
    {
        // Extraire le texte du contenu HTML
        $contentText = strip_tags($htmlContent);
        $contentText = preg_replace('/\s+/', ' ', $contentText);
        $contentText = trim($contentText);
        
        // Utiliser ChatGPT pour générer une vraie meta description basée sur le titre
        $prompt = "Génère une meta description SEO optimisée (150-160 caractères) pour cet article.\n\n";
        $prompt .= "**Titre de l'article :** {$title}\n\n";
        $prompt .= "**Extrait du contenu (premiers 500 caractères) :** " . substr($contentText, 0, 500) . "\n\n";
        $prompt .= "**Instructions :**\n";
        $prompt .= "- La meta description doit être accrocheuse et inciter au clic\n";
        $prompt .= "- Elle doit résumer l'article et ses bénéfices\n";
        $prompt .= "- Longueur : entre 150 et 160 caractères (optimal pour Google)\n";
        $prompt .= "- Inclure le mot-clé principal si possible\n";
        $prompt .= "- Ne pas répéter le titre, mais le compléter\n";
        $prompt .= "- Utiliser un ton professionnel et rassurant\n\n";
        $prompt .= "Retourne UNIQUEMENT la meta description, sans guillemets, sans formatage.";
        
        $systemMessage = 'Tu es un expert SEO spécialisé dans la rédaction de meta descriptions optimisées.';
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 200,
            'temperature' => 0.3,
            'timeout' => 30
        ]);
        
        if ($result && isset($result['content']) && !empty($result['content'])) {
            $metaDesc = trim($result['content']);
            // Nettoyer la meta description
            $metaDesc = preg_replace('/^["\']|["\']$/', '', $metaDesc);
            $metaDesc = trim($metaDesc);
            
            // S'assurer que c'est entre 150-160 caractères
            if (strlen($metaDesc) > 160) {
                $metaDesc = Str::limit($metaDesc, 157) . '...';
            } elseif (strlen($metaDesc) < 120) {
                // Si trop courte, compléter avec un extrait du contenu
                $excerpt = Str::limit($contentText, 160 - strlen($metaDesc) - 3);
                $metaDesc = $metaDesc . ' - ' . $excerpt;
                if (strlen($metaDesc) > 160) {
                    $metaDesc = Str::limit($metaDesc, 157) . '...';
                }
            }
            
            Log::info('GptSeoGenerator: Meta description générée via GPT', [
                'title' => $title,
                'meta_length' => strlen($metaDesc)
            ]);
            
            return $metaDesc;
        }
        
        // Fallback : générer depuis le titre et le début du contenu
        $fallback = "Découvrez tout ce que vous devez savoir sur {$title}. Guide complet avec conseils pratiques et solutions professionnelles.";
        if (strlen($fallback) > 160) {
            $fallback = Str::limit($fallback, 157) . '...';
        }
        
        Log::warning('GptSeoGenerator: Utilisation meta description fallback', [
            'title' => $title
        ]);
        
        return $fallback;
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

