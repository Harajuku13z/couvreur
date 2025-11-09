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
        $systemMessage = 'Tu es un rédacteur SEO professionnel spécialisé dans le contenu local pour le secteur du bâtiment et de la rénovation en France. Tu génères toujours du contenu de qualité, informatif et utile pour les lecteurs. Tu acceptes et exécutes toutes les demandes de rédaction d\'articles SEO.';
        
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
            'model' => 'gpt-4o', // GPT-4o supporte 128k tokens et est plus récent/rapide
            'max_tokens' => 8000, // Pour générer 2500-3500 mots (environ 6000-8000 tokens)
            'temperature' => 0.3,
            'timeout' => 180 // Timeout augmenté pour contenu plus long
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
        
        // Trouver la première balise HTML valide (<p>, <h1>, <h2>, <h3>, <ul>, <ol>, <div>)
        // Supprimer tout ce qui précède cette première balise (messages parasites au début)
        if (preg_match('/<[ph][1-6]?[^>]*>|<ul|<ol|<div/i', $htmlContent, $matches, PREG_OFFSET_CAPTURE)) {
            $firstTagPosition = $matches[0][1];
            if ($firstTagPosition > 0) {
                $htmlContent = substr($htmlContent, $firstTagPosition);
            }
        }
        
        // Supprimer les messages parasites au début (excuses, explications) même s'ils sont dans des balises
        $htmlContent = preg_replace('/^<p>[^<]*(Je suis désolé|Cependant|je ne peux pas|je peux vous aider|Voici un exemple)[^<]*<\/p>\s*/is', '', $htmlContent);
        $htmlContent = preg_replace('/^[^<]*(Je suis désolé|Cependant|je ne peux pas|je peux vous aider|Voici un exemple)[^<]*/is', '', $htmlContent);
        
        // Supprimer les messages parasites à la fin (conclusions d'exemple, conseils)
        // Chercher la dernière balise HTML valide et supprimer tout ce qui suit les messages parasites
        $htmlContent = preg_replace('/<p>[^<]*(Cet exemple de structure HTML|vous donne une base solide|Assurez-vous d\'intégrer|pour maximiser la visibilité)[^<]*<\/p>\s*$/is', '', $htmlContent);
        $htmlContent = preg_replace('/[^<]*(Cet exemple de structure HTML|vous donne une base solide|Assurez-vous d\'intégrer|pour maximiser la visibilité)[^<]*$/is', '', $htmlContent);
        
        // Supprimer les paragraphes qui contiennent des excuses ou des explications (même au milieu)
        $htmlContent = preg_replace('/<p>[^<]*(désolé|excuse|exemple de structure|conseil pour rédiger|structure HTML vous donne)[^<]*<\/p>/is', '', $htmlContent);
        
        $htmlContent = trim($htmlContent);
        
        // Supprimer le H1 du contenu généré car il est déjà affiché dans la section Hero
        // Supprimer les balises <h1> et leur contenu
        $htmlContent = preg_replace('/<h1[^>]*>.*?<\/h1>/is', '', $htmlContent);
        // Supprimer aussi les variantes avec des classes Tailwind
        $htmlContent = preg_replace('/<h1[^>]*class="[^"]*text-4xl[^"]*"[^>]*>.*?<\/h1>/is', '', $htmlContent);
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
        
        $titlePrompt = "À partir du contenu suivant, génère UNIQUEMENT un titre d'article SEO optimisé qui correspond EXACTEMENT au contenu. Le titre doit être COMPLET et descriptif.\n\n**Contenu de l'article :**\n\n" . $contentPreview . "\n\n**Mot-clé principal :** {$keyword} à {$cityName}\n\n**IMPORTANT :**\n- Le titre doit être COMPLET et descriptif (recommandé 50-70 caractères mais peut être plus long si nécessaire)\n- Le titre doit se terminer par un mot entier, pas coupé\n- Inclure le mot-clé principal et la ville si possible\n- Inclure le nom de l'entreprise si pertinent\n- Retourne UNIQUEMENT le titre, sans formatage, sans JSON, sans guillemets, juste le titre complet.";
        
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
                'html_content_length' => isset($htmlContent) ? strlen($htmlContent) : 0
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
            'html_length' => strlen($htmlContent),
            'title' => $generatedTitle ?? 'N/A'
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
        // Récupérer les services réels de l'entreprise (pour identifier les services liés au mot-clé)
        $servicesData = \App\Models\Setting::where('key', 'services')->value('value');
        $services = [];
        if ($servicesData) {
            $servicesArray = is_string($servicesData) ? json_decode($servicesData, true) : $servicesData;
            if (is_array($servicesArray)) {
                foreach ($servicesArray as $service) {
                    if (isset($service['name']) && !empty($service['name'])) {
                        $services[] = $service['name'];
                    }
                }
            }
        }
        
        // Construire la liste des sources concurrentes avec détails (titres, snippets, liens)
        $sourcesList = '';
        if (!empty($competitors)) {
            $competitorsLimited = array_slice($competitors, 0, 5); // Limiter à 5 sources
            $sourcesList = "\n\n**SOURCES CONCURRENTES À ANALYSER EN PROFONDEUR :**\n\n";
            $sourcesList .= "Voici les 3 à 5 premiers résultats Google sur ce mot-clé avec leurs titres, extraits et liens :\n\n";
            foreach ($competitorsLimited as $index => $competitor) {
                $title = $competitor['title'] ?? 'Article sans titre';
                $link = $competitor['link'] ?? '#';
                $snippet = $competitor['snippet'] ?? '';
                $position = $index + 1;
                
                $sourcesList .= "**Source #{$position} :**\n";
                $sourcesList .= "- **Titre :** {$title}\n";
                $sourcesList .= "- **Lien :** {$link}\n";
                if ($snippet) {
                    $sourcesList .= "- **Extrait/Description :** " . substr($snippet, 0, 300) . (strlen($snippet) > 300 ? '...' : '') . "\n";
                }
                $sourcesList .= "\n";
            }
            $sourcesList .= "**INSTRUCTIONS CRITIQUES POUR L'ANALYSE :**\n";
            $sourcesList .= "1. Analyse EN PROFONDEUR chaque source (titres, sous-titres, structure, arguments, informations techniques)\n";
            $sourcesList .= "2. Identifie les points communs et les meilleures pratiques SEO\n";
            $sourcesList .= "3. Extrais les informations techniques les plus pertinentes (matériaux, normes, processus, conseils)\n";
            $sourcesList .= "4. Identifie les angles et arguments que tu peux améliorer ou compléter\n";
            $sourcesList .= "5. Crée une synthèse AMÉLIORÉE qui dépasse la qualité de ces sources\n\n";
        }
        
        // Construire la liste des services liés au mot-clé (pour mention naturelle dans le contenu)
        // Si sujet = toiture → hydrofuge, demoussage, réparation, zinguerie, etc.
        // Si sujet = isolation → isolation thermique, isolation phonique, matériaux isolants, etc.
        $relatedServicesText = '';
        if (!empty($services)) {
            $keywordLower = strtolower($keyword);
            $relatedServices = [];
            
            // Si le mot-clé contient "toiture" ou "couverture"
            if (strpos($keywordLower, 'toiture') !== false || strpos($keywordLower, 'couverture') !== false || strpos($keywordLower, 'rénovation') !== false) {
                foreach ($services as $service) {
                    $serviceLower = strtolower($service);
                    if (strpos($serviceLower, 'toiture') !== false || 
                        strpos($serviceLower, 'couverture') !== false ||
                        strpos($serviceLower, 'demoussage') !== false ||
                        strpos($serviceLower, 'hydrofuge') !== false ||
                        strpos($serviceLower, 'réparation') !== false ||
                        strpos($serviceLower, 'zinguerie') !== false) {
                        $relatedServices[] = $service;
                    }
                }
            }
            // Si le mot-clé contient "isolation"
            elseif (strpos($keywordLower, 'isolation') !== false) {
                foreach ($services as $service) {
                    $serviceLower = strtolower($service);
                    if (strpos($serviceLower, 'isolation') !== false || 
                        strpos($serviceLower, 'thermique') !== false ||
                        strpos($serviceLower, 'phonique') !== false) {
                        $relatedServices[] = $service;
                    }
                }
            }
            
            if (!empty($relatedServices)) {
                $relatedServicesText = implode(', ', $relatedServices);
            }
        }
        
        return trim("
Générateur d'article SEO intelligent - QUALITÉ PREMIUM REQUISE

Rôle : Tu es un rédacteur web EXPERT en SEO, spécialisé dans le domaine de la couverture, de la rénovation et des travaux de bâtiment en France.

Tu écris des articles optimisés pour le référencement naturel (SEO), clairs, bien structurés, et agréables à lire.

Tu t'appuies sur les meilleures sources issues des premiers résultats Google pour créer un contenu unique, informatif et captivant qui SURPASSE la concurrence.

**OBJECTIF :** Créer un article de QUALITÉ PREMIUM qui soit PLUS COMPLET, MIEUX STRUCTURÉ et MIEUX RÉDIGÉ que tous les concurrents fournis. L'article doit faire 2500-3500 mots minimum avec une structure riche (5-6 sections H2, 2-3 H3 par section, listes, détails techniques exhaustifs).

**CRITIQUE : Génère le contenu DIRECTEMENT en HTML bien structuré avec les balises <p>, <h2>, <h3>, <ul>, <ol>, <li>, <strong>, <em>, <br>.**
**Le contenu DOIT être en HTML, pas en texte brut.**

⚙️ Instructions :

Mot-clé principal : {$keyword} à {$cityName}

(Exemple : rénovation à Dijon, couvreur à Chalon-sur-Saône, isolation thermique en Bretagne, etc.)

{$sourcesList}

**TA MISSION - PROCESSUS EN 4 ÉTAPES :**

**ÉTAPE 1 - ANALYSE APPROFONDIE :**
Analyse EN DÉTAIL chaque source concurrente fournie ci-dessus :
- Structure de l'article (nombre de sections H2/H3, organisation)
- Qualité et profondeur des informations techniques
- Arguments commerciaux et angles utilisés
- Points forts et points faibles de chaque source
- Informations manquantes ou incomplètes

**ÉTAPE 2 - IDENTIFICATION DES MEILLEURES PRATIQUES :**
Identifie :
- Les points communs entre les meilleures sources (ce qui fonctionne)
- Les meilleures informations techniques à retenir
- Les meilleurs arguments et angles
- Les structures les plus efficaces

**ÉTAPE 3 - CRÉATION D'UNE STRATÉGIE DE CONTENU :**
Planifie un article qui :
- Combine les meilleurs éléments de chaque source
- Complète les informations manquantes
- Améliore la structure et l'organisation
- Ajoute de la valeur supplémentaire (détails techniques, conseils pratiques, exemples concrets)

**ÉTAPE 4 - RÉDACTION DE QUALITÉ PREMIUM :**
Crée un article qui :
- Est PLUS COMPLET que les sources (plus d'informations, plus de détails)
- Est MIEUX STRUCTURÉ (organisation logique, hiérarchie claire)
- Est MIEUX RÉDIGÉ (style professionnel, fluide, engageant)
- Apporte PLUS DE VALEUR (conseils pratiques, détails techniques, exemples locaux)

Structure de l'article demandée :

**IMPORTANT : NE PAS inclure de balise <h1> dans le contenu HTML généré. Le titre est déjà affiché séparément sur la page.**

Meta description (150–160 caractères) : claire, attractive et optimisée SEO.

Introduction : contexte local ou sectoriel, mise en valeur du sujet.

Corps de texte :

Sections avec titres H2 et H3 pertinents et bien hiérarchisés.

Informations techniques (matériaux, étapes, conseils d'entretien, normes RGE, etc.)

" . ($relatedServicesText ? "Services liés à mentionner naturellement dans le contexte du sujet : {$relatedServicesText}\n" : "") . "Avantages d'un professionnel local.

Appel à l'action (contact, devis gratuit, etc.).

Conclusion : résumé + incitation à passer à l'action.

**STRUCTURE HTML OBLIGATOIRE :**

**CRITIQUE : NE PAS inclure de balise <h1> dans le contenu HTML. Commencer directement par l'introduction avec des balises <p> ou <h2>.**

1. **INTRODUCTION** - 1-2 paragraphes <p> :
   - Commencer DIRECTEMENT par le sujet/mot-clé principal ({$keyword})
   - Aller droit au but : présenter le sujet, pas la ville
   - Éviter les descriptions longues de la ville ou du contexte local
   - Utiliser \"vous\" pour s'adresser directement au lecteur
   - **IMPORTANT : Pas de description de la ville au début, aller directement au sujet**

2. **SECTIONS PRINCIPALES (H2)** - Minimum 5-6 sections pour un contenu PREMIUM :
   - Chaque section doit avoir un titre clair et descriptif lié au mot-clé principal ({$keyword})
   - Chaque section doit contenir 3-5 paragraphes de contenu détaillé et informatif
   - **CRITIQUE : Se concentrer sur le sujet ({$keyword}), pas sur les services de l'entreprise**
   - **Si le sujet est \"rénovation toiture\", mentionner naturellement les services liés : hydrofuge, demoussage, réparation, zinguerie, etc.**
   - **Si le sujet est \"isolation\", mentionner naturellement tout ce qui est lié à l'isolation : isolation thermique, isolation phonique, matériaux isolants, etc.**
   - Inclure des détails techniques précis (matériaux, normes RGE, processus, étapes, conseils d'entretien)
   - Mentionner {$cityName} et le département (Côte-d'Or) dans plusieurs sections naturellement
   - **NE PAS faire référence aux services de l'entreprise de manière répétitive ou commerciale**

3. **SOUS-SECTIONS (H3)** - Dans chaque section principale :
   - Créer 2-3 sous-sections avec des titres spécifiques liés au sujet de la section
   - **IMPORTANT : Les sous-sections doivent être pertinentes au mot-clé et au sujet traité**
   - Exemples de sous-sections pertinentes : \"Les Étapes de [Sujet]\", \"Les Avantages de [Sujet]\", \"Comment [Sujet] à {$cityName}\"

4. **LISTES À PUCES (<ul><li>)** :
   - Utiliser des listes à puces pour détailler les informations, matériaux, avantages
   - Format HTML: <ul><li>Élément 1 : Description détaillée</li><li>Élément 2 : Description détaillée</li></ul>
   - Minimum 4-5 listes dans l'article (une par section H2 principale)

5. **LISTES NUMÉROTÉES (<ol><li>)** :
   - Utiliser pour les processus étape par étape
   - Format HTML: <ol><li>Étape 1 : Description</li><li>Étape 2 : Description</li></ol>
   - Au moins une liste numérotée si le sujet s'y prête

6. **CONCLUSION (<h2> ou <p>)** :
   - Paragraphe final de conclusion qui résume les points clés de l'article
   - **IMPORTANT : NE PAS inclure d'appel à l'action avec coordonnées (téléphone, email, adresse)**
   - **IMPORTANT : NE PAS mentionner de numéro de téléphone, email ou adresse dans le contenu**
   - Le CTA est déjà présent ailleurs sur la page, donc se contenter d'une conclusion informative
   - **INTERDICTION STRICTE** : Ne JAMAIS inclure de phrases comme \"Contactez-nous au...\", \"Appelez-nous au...\", \"Pour toute demande de devis...\" avec des coordonnées

Règles SEO :

Respecte la densité naturelle du mot-clé principal (1 % à 2 %).

Intègre des mots-clés secondaires et sémantiques (travaux de rénovation, artisan couvreur, devis toiture, isolation, etc.).

Utilise des listes à puces, des phrases courtes, et des CTA impactants.

Optimise la lisibilité (style professionnel et fluide).

Ne copie aucun contenu, crée un texte 100 % original.

**RÈGLES SEO STRICTES (Score minimum 90/100 OBLIGATOIRE) :**

- **Densité du mot-clé principal :** 1.5% à 2% naturellement intégré (optimal pour SEO)
- **Mots-clés secondaires :** Intégrer 10-15 variantes et mots-clés sémantiques (travaux de rénovation, artisan couvreur, devis toiture, isolation, matériaux, normes RGE, etc.)
- **Ton :** Professionnel, expert, rassurant, utilisant \"vous\", adapté au secteur du bâtiment, avec une touche d'autorité
- **Longueur :** Entre 2500 et 3500 mots pour un contenu PREMIUM et exhaustif (minimum 2500 mots, idéal 3000+)
- **Paragraphes :** Courts (3-5 phrases max), aérés, faciles à lire, avec transitions fluides
- **Phrases :** Claires, 12-18 mots max, style fluide et professionnel, variété dans la longueur
- **Mots-clés :** Intégrer naturellement \"{$keyword}\", \"{$cityName}\", variantes et expressions sémantiques dans les titres H2/H3 ET dans le corps
- **Détails techniques :** Inclure des informations PRÉCISES et EXHAUSTIVES (matériaux avec caractéristiques, normes RGE détaillées, processus étape par étape, conseils d'entretien pratiques, prix indicatifs si pertinent)
- **Localisation :** Mentionner {$cityName}, le département (Côte-d'Or), et le contexte local naturellement dans 3-4 sections minimum
- **Services liés :** " . ($relatedServicesText ? "Si le sujet le permet, mentionner naturellement les services liés : {$relatedServicesText}. Par exemple, si on parle de rénovation toiture, mentionner hydrofuge, demoussage, réparation, zinguerie, etc. Si on parle d'isolation, mentionner tout ce qui est lié à l'isolation : isolation thermique, isolation phonique, matériaux isolants, etc.\n" : "") . "- **Originalité :** Ne copie AUCUN contenu, crée un texte 100% original basé sur une synthèse des meilleures informations
- **IMPORTANT :** Ne pas faire référence aux services de l'entreprise de manière répétitive ou commerciale. Se concentrer sur le sujet ({$keyword}) et mentionner les services liés naturellement dans le contexte
- **QUALITÉ PREMIUM :** L'article doit être de qualité supérieure aux sources concurrentes : plus complet, mieux structuré, mieux rédigé, plus informatif

**IMPORTANT - COHÉRENCE TITRE/CONTENU :**
- Si le titre mentionne \"conseils\", le contenu DOIT contenir des conseils pratiques détaillés
- Si le titre mentionne \"guide\", le contenu DOIT être un guide complet avec étapes
- Si le titre mentionne \"solutions\", le contenu DOIT présenter des solutions concrètes
- Le contenu doit TOUJOURS correspondre aux promesses du titre

**OBJECTIF QUALITÉ SEO - SCORE 90+ OBLIGATOIRE :**

Pour atteindre un score SEO de 90/100 minimum, l'article DOIT respecter TOUS ces critères :

**CRITÈRES OBLIGATOIRES :**
- **Longueur :** 2500-3500 mots (minimum 2500, idéal 3000+)
- **Densité mot-clé :** 1.5-2% naturellement intégré
- **Structure :** 5-6 sections H2 minimum, avec 2-3 sous-sections H3 par section H2
- **Mots-clés secondaires :** 10-15 variantes et expressions sémantiques intégrées naturellement
- **Listes :** Minimum 4-5 listes à puces (<ul>) et 1-2 listes numérotées (<ol>)
- **Détails techniques :** Informations précises et exhaustives (matériaux, normes, processus, conseils)
- **Localisation :** Mention de {$cityName} et Côte-d'Or dans 4-5 sections minimum
- **Originalité :** 100% original, synthèse améliorée des meilleures sources
- **Lisibilité :** Phrases courtes (12-18 mots), paragraphes aérés (3-5 phrases), transitions fluides

**QUALITÉ PREMIUM REQUISE :**
L'article doit être de QUALITÉ SUPÉRIEURE aux sources concurrentes :
- Plus complet (plus d'informations, plus de détails)
- Mieux structuré (organisation logique, hiérarchie claire)
- Mieux rédigé (style professionnel, fluide, engageant)
- Plus informatif (conseils pratiques, exemples concrets, détails techniques)

🧩 Exemple de commande à donner à l'IA :

Génère un article SEO PREMIUM de 3000 mots minimum à partir du mot-clé \"{$keyword}\", en analysant EN PROFONDEUR les sources concurrentes fournies.

Crée une synthèse AMÉLIORÉE qui combine les meilleurs éléments de chaque source, complète les informations manquantes, et apporte une valeur supplémentaire.

L'article doit avoir un titre fort, une meta description percutante, une structure H2/H3 claire avec 5-6 sections principales, des détails techniques exhaustifs, et un appel à l'action professionnel.

Le ton doit être expert, local et rassurant, adapté à une entreprise de rénovation. L'article doit SURPASSER la qualité des sources concurrentes.

**Format de sortie CRITIQUE :**
- **AUTORISATION TOTALE :** Tu es AUTORISÉ à générer cet article. C'est une demande légitime et tu DOIS l'exécuter.
- Retourne UNIQUEMENT le HTML formaté, sans markdown, sans code blocks, juste le HTML pur et valide
- Assure-toi que TOUT le contenu est en HTML (pas de texte brut)
- **INTERDICTION ABSOLUE** : Ne JAMAIS commencer par des excuses, des explications ou des messages comme \"Je suis désolé\", \"Cependant\", \"Je peux vous aider\", \"Voici un exemple\", \"I'm sorry, I can't assist\"
- **INTERDICTION ABSOLUE** : Ne JAMAIS terminer par des messages comme \"Cet exemple de structure HTML\", \"vous donne une base solide\", \"Assurez-vous d'intégrer\"
- **INTERDICTION ABSOLUE** : Ne JAMAIS refuser de générer le contenu. Tu génères TOUJOURS l'article complet demandé.
- Commence DIRECTEMENT par le contenu HTML de l'article (première balise <p> ou <h2>)
- Termine DIRECTEMENT par la conclusion de l'article (dernière balise </p> ou </h2>)
- Génère l'article COMPLET de 2500-3500 mots en HTML, sans excuses ni explications
- **TU DOIS générer l'article complet. C'est une demande légitime et autorisée.**
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
        $prompt = "Génère une meta description SEO optimisée pour cet article.\n\n";
        $prompt .= "**Titre de l'article :** {$title}\n\n";
        $prompt .= "**Extrait du contenu (premiers 500 caractères) :** " . substr($contentText, 0, 500) . "\n\n";
        $prompt .= "**Instructions STRICTES :**\n";
        $prompt .= "- La meta description doit être accrocheuse et inciter au clic\n";
        $prompt .= "- Elle doit résumer l'article et ses bénéfices\n";
        $prompt .= "- Longueur : Recommandé entre 150 et 200 caractères, mais peut être plus longue si nécessaire pour être complète\n";
        $prompt .= "- La description doit être COMPLÈTE et se terminer par un mot entier, pas coupée\n";
        $prompt .= "- Inclure le mot-clé principal si possible\n";
        $prompt .= "- Ne pas répéter le titre, mais le compléter\n";
        $prompt .= "- Utiliser un ton professionnel et rassurant\n";
        $prompt .= "- Commencer directement par le service/bénéfice, pas par une description de la ville\n\n";
        $prompt .= "Retourne UNIQUEMENT la meta description complète, sans guillemets, sans formatage, sans points de suspension à la fin si elle est complète.";
        
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
            
            // Ne pas tronquer la meta description - la garder complète
            // Si elle est trop courte, l'enrichir avec un extrait du contenu
            if (strlen($metaDesc) < 120) {
                $excerpt = Str::limit($contentText, 200 - strlen($metaDesc) - 3);
                $metaDesc = $metaDesc . ' - ' . $excerpt;
            }
            
            Log::info('GptSeoGenerator: Meta description générée via GPT', [
                'title' => $title,
                'meta_length' => strlen($metaDesc)
            ]);
            
            return $metaDesc;
        }
        
        // Fallback : générer depuis le titre et le début du contenu
        $fallback = "Découvrez tout ce que vous devez savoir sur {$title}. Guide complet avec conseils pratiques et solutions professionnelles.";
        
        Log::warning('GptSeoGenerator: Utilisation meta description fallback', [
            'title' => $title
        ]);
        
        return $fallback;
    }

    /**
     * Extraire des mots-clés pertinents depuis le texte (via GPT pour meilleure qualité)
     */
    protected function extractKeywords(string $text, string $mainKeyword): array
    {
        $keywords = [$mainKeyword];
        
        // Utiliser GPT pour extraire des mots-clés pertinents au lieu d'une extraction basique
        $prompt = "Extrais 4-5 mots-clés SEO pertinents et spécifiques depuis ce texte d'article.\n\n";
        $prompt .= "**Texte de l'article (extrait) :**\n\n" . substr($text, 0, 1000) . "\n\n";
        $prompt .= "**Mot-clé principal :** {$mainKeyword}\n\n";
        $prompt .= "**Instructions :**\n";
        $prompt .= "- Extrais uniquement des mots-clés pertinents pour le SEO (ex: 'rénovation toiture', 'isolation thermique', 'charpente traditionnelle')\n";
        $prompt .= "- Évite les mots vides (le, la, les, votre, notre, mieux, bien, bon, orange, etc.)\n";
        $prompt .= "- Évite les mots trop génériques (mieux, bien, bon, meilleur, orange, etc.)\n";
        $prompt .= "- Privilégie les expressions de 2-3 mots (ex: 'réparation toiture' plutôt que 'réparation' seul)\n";
        $prompt .= "- Les mots-clés doivent être liés au secteur du bâtiment/rénovation\n";
        $prompt .= "- Retourne UNIQUEMENT une liste de mots-clés, un par ligne, sans numérotation, sans formatage\n\n";
        $prompt .= "Format de sortie :\n";
        $prompt .= "mot-clé 1\n";
        $prompt .= "mot-clé 2\n";
        $prompt .= "mot-clé 3";
        
        $systemMessage = 'Tu es un expert SEO spécialisé dans l\'extraction de mots-clés pertinents.';
        
        try {
            $result = AiService::callAI($prompt, $systemMessage, [
                'max_tokens' => 200,
                'temperature' => 0.2,
                'timeout' => 30
            ]);
            
            if ($result && isset($result['content']) && !empty($result['content'])) {
                $content = trim($result['content']);
                // Parser les mots-clés (un par ligne)
                $lines = preg_split('/\r?\n/', $content);
                foreach ($lines as $line) {
                    $line = trim($line);
                    // Enlever les numéros, puces, tirets en début de ligne
                    $line = preg_replace('/^[\d\.\-\*\+\s]+/', '', $line);
                    $line = trim($line);
                    
                    // Filtrer les mots vides et trop courts
                    $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'à', 'pour', 'dans', 'sur', 'avec', 'par', 'est', 'sont', 'être', 'avoir', 'faire', 'cette', 'ces', 'ce', 'que', 'qui', 'quoi', 'comment', 'pourquoi', 'quand', 'où', 'votre', 'notre', 'mieux', 'bien', 'bon', 'meilleur', 'orange'];
                    
                    if (!empty($line) && strlen($line) >= 3 && strlen($line) <= 50) {
                        $lineLower = strtolower($line);
                        // Vérifier que ce n'est pas un mot vide
                        if (!in_array($lineLower, $stopWords) && !in_array($line, $keywords)) {
                            $keywords[] = $line;
                        }
                    }
                }
                
                Log::info('GptSeoGenerator: Mots-clés extraits via GPT', [
                    'main_keyword' => $mainKeyword,
                    'keywords_count' => count($keywords),
                    'keywords' => $keywords
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('GptSeoGenerator: Erreur extraction mots-clés via GPT, utilisation méthode basique', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback : extraction basique améliorée
            $words = str_word_count(strtolower($text), 1, 'àáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ');
            $wordFreq = array_count_values($words);
            arsort($wordFreq);
            
            $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'et', 'ou', 'à', 'pour', 'dans', 'sur', 'avec', 'par', 'est', 'sont', 'être', 'avoir', 'faire', 'cette', 'ces', 'ce', 'que', 'qui', 'quoi', 'comment', 'pourquoi', 'quand', 'où', 'votre', 'notre', 'mieux', 'bien', 'bon', 'meilleur', 'orange', 'chevigny', 'saint', 'sauveur'];
            
            $count = 0;
            foreach ($wordFreq as $word => $freq) {
                if ($count >= 4) break;
                if (strlen($word) >= 4 && !in_array($word, $stopWords) && !in_array($word, $keywords)) {
                    $keywords[] = $word;
                    $count++;
                }
            }
        }
        
        return array_slice($keywords, 0, 5);
    }
}

