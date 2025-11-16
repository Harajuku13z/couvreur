<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class GptSeoGenerator
{
    protected $apiKey;
    protected $model;
    protected $maxTokens;
    protected $temperature;
    
    public function __construct()
    {
        $this->apiKey = Setting::where('key', 'chatgpt_api_key')->value('value');
        $this->model = Setting::where('key', 'chatgpt_model')->value('value') ?? 'gpt-4o';
        $this->maxTokens = 4000;
        $this->temperature = 0.7;
    }
    
    /**
     * Générer un article SEO complet optimisé avec score 95%+
     */
    public function generateSeoArticle($keyword, $city, $serpResults = [], $keywordImages = [])
    {
        try {
            Log::info('Génération article SEO premium', [
                'keyword' => $keyword,
                'city' => $city,
                'serp_count' => count($serpResults),
                'images_count' => count($keywordImages)
            ]);
            
            // Analyse sémantique approfondie
            $semanticAnalysis = $this->performSemanticAnalysis($keyword, $city, $serpResults);
            
            // Étape 1 : Générer le titre optimisé (avec test A/B mental)
            $titre = $this->generateTitle($keyword, $city, $semanticAnalysis);
            
            // Étape 2 : Générer la meta description persuasive
            $metaDescription = $this->generateMetaDescription($keyword, $city, $titre, $semanticAnalysis);
            
            // Étape 2.5 : Générer les mots-clés meta
            $metaKeywords = $this->generateMetaKeywords($keyword, $city, $titre, $semanticAnalysis);
            
            // Étape 3 : Générer le contenu HTML ultra-optimisé
            $contenuHtml = $this->generateHtmlContent($keyword, $city, $serpResults, $keywordImages, $titre, $semanticAnalysis);
            
            // Étape 4 : Post-traitement et optimisation finale
            $contenuHtml = $this->postProcessContent($contenuHtml, $keyword, $city);
            
            // Étape 4.5 : Validation des sections (vérifier qu'aucune section n'est vide)
            $contenuHtml = $this->validateAndFixSections($contenuHtml);
            
            // Étape 5 : Validation qualité SEO
            $seoScore = $this->calculateSeoScore($contenuHtml, $keyword, $city, $titre, $metaDescription);
            
            // Étape 6 : Générer le slug optimisé
            $slug = $this->generateOptimizedSlug($titre, $keyword);
            
            Log::info('Article généré avec succès', [
                'seo_score' => $seoScore,
                'word_count' => str_word_count(strip_tags($contenuHtml)),
                'title_length' => strlen($titre),
                'meta_length' => strlen($metaDescription)
            ]);
            
            return [
                'titre' => $titre,
                'slug' => $slug,
                'meta_description' => $metaDescription,
                'mots_cles' => $metaKeywords,
                'contenu_html' => $contenuHtml,
                'keyword' => $keyword,
                'city' => $city,
                'seo_score' => $seoScore,
                'semantic_keywords' => $semanticAnalysis['related_keywords'] ?? [],
                'word_count' => str_word_count(strip_tags($contenuHtml))
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur génération article SEO', [
                'keyword' => $keyword,
                'city' => $city,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Analyse sémantique approfondie pour extraction d'entités et mots-clés connexes
     */
    protected function performSemanticAnalysis($keyword, $city, $serpResults)
    {
        $analysis = [
            'related_keywords' => [],
            'entities' => [],
            'user_intent' => 'informational',
            'content_depth_required' => 2000,
            'competitor_weaknesses' => [],
            'opportunities' => []
        ];
        
        // Déterminer l'intention utilisateur
        $intentPatterns = [
            'transactional' => ['prix', 'tarif', 'devis', 'coût', 'acheter', 'commander'],
            'commercial' => ['meilleur', 'comparatif', 'avis', 'top', 'recommandation'],
            'informational' => ['comment', 'pourquoi', 'guide', 'conseils', 'définition'],
            'local' => ['près de', 'dans', 'à', $city]
        ];
        
        $lowerKeyword = strtolower($keyword);
        foreach ($intentPatterns as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($lowerKeyword, $pattern) !== false) {
                    $analysis['user_intent'] = $intent;
                    break 2;
                }
            }
        }
        
        // Extraire mots-clés sémantiques du secteur du bâtiment
        $analysis['related_keywords'] = $this->extractSemanticKeywords($keyword);
        
        // Analyser les résultats SERP pour identifier les gaps
        if (!empty($serpResults)) {
            $analysis['competitor_weaknesses'] = $this->identifyCompetitorGaps($serpResults);
            $analysis['content_depth_required'] = $this->calculateOptimalWordCount($serpResults);
        }
        
        return $analysis;
    }
    
    /**
     * Extraire mots-clés sémantiques du secteur bâtiment
     */
    protected function extractSemanticKeywords($keyword)
    {
        $semanticMap = [
            'toiture' => ['couverture', 'charpente', 'zinguerie', 'étanchéité', 'isolation', 'ardoise', 'tuile', 'zinc', 'faîtage', 'gouttière'],
            'rénovation' => ['travaux', 'réhabilitation', 'restauration', 'modernisation', 'amélioration', 'rafraîchissement', 'transformation'],
            'isolation' => ['thermique', 'phonique', 'combles', 'murs', 'laine de verre', 'laine de roche', 'polystyrène', 'performance énergétique', 'économies'],
            'façade' => ['ravalement', 'enduit', 'peinture', 'bardage', 'ITE', 'isolation extérieure', 'crépi', 'nettoyage'],
            'charpente' => ['bois', 'traditionnelle', 'fermette', 'poutre', 'structure', 'traitement', 'rénovation'],
            'plomberie' => ['tuyauterie', 'canalisation', 'robinetterie', 'sanitaire', 'chauffage', 'installation', 'dépannage'],
            'électricité' => ['installation électrique', 'tableau', 'disjoncteur', 'mise aux normes', 'éclairage', 'domotique'],
            'maçonnerie' => ['construction', 'mur', 'fondation', 'parpaing', 'béton', 'brique', 'ciment'],
        ];
        
        $relatedKeywords = [];
        $lowerKeyword = strtolower($keyword);
        
        foreach ($semanticMap as $mainTerm => $related) {
            if (strpos($lowerKeyword, $mainTerm) !== false) {
                $relatedKeywords = array_merge($relatedKeywords, $related);
            }
        }
        
        // Ajouter des termes génériques pertinents
        $relatedKeywords = array_merge($relatedKeywords, [
            'artisan', 'professionnel', 'entreprise', 'expert', 'certifié', 'RGE',
            'devis gratuit', 'garantie décennale', 'assurance', 'qualité', 'norme DTU',
            'délai', 'intervention', 'chantier', 'projet', 'réalisation'
        ]);
        
        return array_unique($relatedKeywords);
    }
    
    /**
     * Identifier les faiblesses des concurrents
     */
    protected function identifyCompetitorGaps($serpResults)
    {
        $gaps = [];
        
        $commonTopics = [
            'prix détaillé' => false,
            'processus étape par étape' => false,
            'comparaison matériaux' => false,
            'aides financières' => false,
            'erreurs à éviter' => false,
            'entretien maintenance' => false,
            'normes réglementations' => false,
            'innovations 2025' => false,
            'cas clients' => false,
            'FAQ complète' => false
        ];
        
        foreach ($serpResults as $result) {
            $content = strtolower($result['snippet'] ?? '') . ' ' . strtolower($result['title'] ?? '');
            
            if (strpos($content, 'prix') !== false || strpos($content, 'tarif') !== false) {
                $commonTopics['prix détaillé'] = true;
            }
            if (strpos($content, 'étape') !== false || strpos($content, 'processus') !== false) {
                $commonTopics['processus étape par étape'] = true;
            }
            if (strpos($content, 'comparatif') !== false || strpos($content, 'comparaison') !== false) {
                $commonTopics['comparaison matériaux'] = true;
            }
            if (strpos($content, 'aide') !== false || strpos($content, 'subvention') !== false) {
                $commonTopics['aides financières'] = true;
            }
            if (strpos($content, 'erreur') !== false || strpos($content, 'éviter') !== false) {
                $commonTopics['erreurs à éviter'] = true;
            }
        }
        
        // Identifier les gaps (sujets non couverts)
        foreach ($commonTopics as $topic => $covered) {
            if (!$covered) {
                $gaps[] = $topic;
            }
        }
        
        return $gaps;
    }
    
    /**
     * Calculer la longueur optimale du contenu
     */
    protected function calculateOptimalWordCount($serpResults)
    {
        $wordCounts = [];
        
        foreach ($serpResults as $result) {
            if (isset($result['word_count']) && $result['word_count'] > 0) {
                $wordCounts[] = $result['word_count'];
            } else if (isset($result['snippet'])) {
                // Estimation basée sur le snippet (ratio 1:20)
                $estimatedCount = str_word_count($result['snippet']) * 20;
                if ($estimatedCount > 800 && $estimatedCount < 5000) {
                    $wordCounts[] = $estimatedCount;
                }
            }
        }
        
        if (empty($wordCounts)) {
            return 2200; // Valeur par défaut optimale
        }
        
        $avgWordCount = array_sum($wordCounts) / count($wordCounts);
        $maxWordCount = max($wordCounts);
        
        // Viser 30% au-dessus de la moyenne, mais au moins 3000 mots pour couvrir toutes les sections
        $targetWordCount = max(3000, ceil($avgWordCount * 1.3), $maxWordCount + 500);
        
        // Limiter à 4500 mots max pour éviter contenu trop dilué
        return min(4500, $targetWordCount);
    }
    
    /**
     * Générer un titre SEO optimisé (CTR-focused)
     */
    protected function generateTitle($keyword, $city, $semanticAnalysis)
    {
        $currentYear = date('Y');
        $intent = $semanticAnalysis['user_intent'] ?? 'informational';
        
        // Titres inspirants fournis par l'utilisateur (à fort potentiel de conversion)
        $highConvertingTitles = [
            "Fuite de Toiture : Que Faire Immédiatement Avant l'Arrivée du Couvreur ?",
            "Rénover sa Toiture en {$currentYear} : Prix, Aides, Erreurs à Éviter",
            "Toiture Abîmée : 7 Signes Qui Doivent Vous Alerter Immédiatement",
            "Tuiles Cassées, Infiltrations : Combien Coûte Une Intervention d'Urgence ?",
            "Pourquoi une Toiture Mal Isolée Peut Faire Exploser Votre Facture de Chauffage",
            "Couverture Zinc, Ardoise ou Tuiles : Quelle Toiture Choisir en {$currentYear} ?",
            "Nettoyage de Toiture : Le Guide Complet (Prix + Fréquence + Risques)",
            "Urgence Toiture Après Tempête : Les 5 Gestes Qui Sauvent Votre Maison",
            "Comment Savoir Si Votre Toiture a Plus de 20 Ans ? (Checklist Téléchargeable)",
            "Étanchéité de Toiture : Causes, Solutions et Prix des Réparations en {$currentYear}",
        ];
        
        // Templates optimisés par intention
        $templates = [
            'transactional' => [
                "{$keyword} {$city} : Devis Gratuit & Prix {$currentYear}",
                "{$keyword} à {$city} | Tarifs Transparents {$currentYear}",
                "Prix {$keyword} {$city} : Guide Complet {$currentYear}",
            ],
            'commercial' => [
                "Meilleur {$keyword} {$city} : Top 5 {$currentYear}",
                "{$keyword} {$city} : Comparatif Expert {$currentYear}",
                "{$keyword} à {$city} | Guide & Avis {$currentYear}",
            ],
            'informational' => [
                "{$keyword} {$city} : Guide Expert {$currentYear}",
                "Tout sur {$keyword} à {$city} [{$currentYear}]",
                "{$keyword} {$city} : Conseils Pro {$currentYear}",
            ],
            'local' => [
                "{$keyword} {$city} | Artisan Certifié RGE",
                "Expert {$keyword} à {$city} : Devis Gratuit",
                "{$keyword} {$city} : Professionnel Local {$currentYear}",
            ]
        ];
        
        $selectedTemplates = $templates[$intent] ?? $templates['informational'];
        $template = $selectedTemplates[0]; // Prendre le premier template (meilleur CTR)
        
        // Sélectionner un titre inspirant aléatoire
        $inspirationTitle = $highConvertingTitles[array_rand($highConvertingTitles)];
        
        $prompt = <<<EOT
Génère un titre SEO ULTRA-OPTIMISÉ pour maximiser le CTR (Click-Through Rate).

**Mot-clé principal :** {$keyword}
**Ville :** {$city}
**Intention utilisateur :** {$intent}
**Année :** {$currentYear}

**Template de référence :** {$template}

**INSPIRATION - Titres à fort potentiel de conversion :**
Ces titres ont un excellent taux de conversion car ils :
- Attirent les urgences et prospects chauds
- Incluent des mots-clés puissants (prix, aide, erreurs, signes, coût, urgence)
- Créent de l'urgence ou de la curiosité
- Promettent des solutions concrètes

Exemple d'inspiration : "{$inspirationTitle}"

**ADAPTE ce style au mot-clé "{$keyword}" et à la ville "{$city}"** en créant un titre qui :
- Utilise le même format accrocheur (question, liste, urgence, prix, etc.)
- Intègre naturellement "{$keyword}" et "{$city}"
- Maximise le CTR avec des power words (Immédiatement, Urgence, Prix, Guide, Erreurs, Signes, etc.)

**Critères STRICTS (Score SEO 95%+) :**
✅ Longueur : 50-60 caractères (affichage optimal SERP mobile & desktop)
✅ Mot-clé exact "{$keyword}" présent dans les 30 premiers caractères
✅ Ville "{$city}" intégrée naturellement
✅ Année {$currentYear} pour fraîcheur (si pertinent)
✅ Power words : "Expert", "Guide", "Certifié", "Gratuit", "Complet"
✅ Symboles autorisés : | : • ✓ (utilisés avec parcimonie)
✅ Formulation active et directe
✅ Promesse de valeur claire (prix, qualité, rapidité, expertise)

**Formules gagnantes selon l'intention :**
- Transactionnelle : "[Service] [Ville] : Prix & Devis Gratuit {$currentYear}"
- Commerciale : "Meilleur [Service] [Ville] | Comparatif {$currentYear}"
- Informationnelle : "[Service] [Ville] : Guide Expert Complet {$currentYear}"
- Locale : "Expert [Service] à [Ville] | Certifié RGE"

**Exemples de titres parfaits (60 caractères max) :**
✅ "Rénovation Toiture Paris : Devis Gratuit & Prix 2025" (57 car.)
✅ "Couvreur Dijon | Expert Certifié RGE • Devis 24h" (53 car.)
✅ "Isolation Combles Lyon : Guide Complet Pro 2025" (50 car.)

**RÈGLES D'OR :**
- Susciter la curiosité ET rassurer (expertise + accessibilité)
- Être spécifique (éviter les titres génériques)
- Transmettre un bénéfice immédiat
- Utiliser des chiffres si pertinent (année, délai, prix)
- **NE JAMAIS répéter la ville** (ex: "à {$city} à {$city}" est INTERDIT)

Génère UN SEUL titre optimal. Retourne UNIQUEMENT le titre, sans guillemets, sans explications, sans préambule.
EOT;

        $systemMessage = "Tu es un expert en optimisation de titres SEO avec 15 ans d'expérience. Tu maîtrises parfaitement la psychologie du clic et les algorithmes Google. Tu crées des titres qui obtiennent un CTR de 8-12% (vs moyenne 3-5%).";
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 80,
            'temperature' => 0.85, // Légèrement plus créatif pour le titre
        ]);
        
        $titre = trim($result['content'] ?? '');
        $titre = trim($titre, '"\'');
        
        // Fallback optimisé
        if (empty($titre)) {
            $titre = ucfirst($keyword) . " " . $city . " : Guide Expert " . $currentYear;
        }
        
        // Optimisation longueur (sweet spot 50-60 caractères)
        if (strlen($titre) > 60) {
            // Tronquer intelligemment (garder mot-clé + ville)
            $titre = $this->smartTruncate($titre, 57, $keyword, $city);
        } else if (strlen($titre) < 45) {
            // Trop court, ajouter année si absente
            if (strpos($titre, $currentYear) === false) {
                $titre .= " " . $currentYear;
            }
        }
        
        Log::info('Titre optimisé généré', [
            'titre' => $titre,
            'length' => strlen($titre),
            'intent' => $intent
        ]);
        
        return $titre;
    }
    
    /**
     * Tronquer intelligemment un titre en préservant mots-clés
     */
    protected function smartTruncate($text, $maxLength, $keyword, $city)
    {
        // Si déjà bon, retourner tel quel
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        
        // S'assurer que mot-clé et ville sont présents
        $lowerText = strtolower($text);
        $hasKeyword = strpos($lowerText, strtolower($keyword)) !== false;
        $hasCity = strpos($lowerText, strtolower($city)) !== false;
        
        // Tronquer à la limite
        $truncated = substr($text, 0, $maxLength);
        
        // Trouver le dernier espace pour ne pas couper un mot
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false && $lastSpace > ($maxLength * 0.8)) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . '...';
    }
    
    /**
     * Générer une meta description ultra-persuasive
     */
    protected function generateMetaDescription($keyword, $city, $titre, $semanticAnalysis)
    {
        $companyName = config('app.name', 'Notre Entreprise');
        $intent = $semanticAnalysis['user_intent'] ?? 'informational';
        $relatedKeywords = array_slice($semanticAnalysis['related_keywords'] ?? [], 0, 3);
        
        $prompt = <<<EOT
Génère une meta description SEO ULTRA-PERSUASIVE pour maximiser le CTR.

**Titre article :** {$titre}
**Mot-clé principal :** {$keyword}
**Ville :** {$city}
**Entreprise :** {$companyName}
**Intention utilisateur :** {$intent}
**Mots-clés connexes à intégrer :** {$this->implodeKeywords($relatedKeywords)}

**Critères STRICTS (Score SEO 95%+) :**
✅ Longueur : 150-160 caractères EXACTEMENT (optimal pour affichage complet)
✅ Mot-clé principal "{$keyword}" dans les 120 premiers caractères
✅ Ville "{$city}" mentionnée naturellement
✅ 1-2 mots-clés connexes intégrés subtilement
✅ Proposition de valeur unique (USP) claire
✅ Appel à l'action subtil mais présent
✅ Bénéfice client explicite
✅ Ton professionnel mais accessible
✅ Chiffres/données si pertinents (prix, délais, garanties)
✅ Émojis INTERDITS dans meta description

**Structure gagnante :**
[Accroche bénéfice] + {$keyword} à {$city} + [USP entreprise] + [CTA subtil]

**Formules optimisées selon l'intention :**
- Transactionnelle : "Besoin de {$keyword} à {$city} ? {$companyName} : devis gratuit 24h, tarifs transparents, artisans certifiés RGE. Contactez-nous !"
- Commerciale : "Comparez les meilleures offres {$keyword} à {$city}. {$companyName} : expertise reconnue, 500+ clients satisfaits. Guide complet."
- Informationnelle : "Guide expert {$keyword} à {$city} par {$companyName}. Conseils pro, astuces, prix détaillés. Tout pour réussir votre projet."
- Locale : "Expert {$keyword} à {$city}. {$companyName} intervient sous 48h. Devis gratuit, garantie décennale, artisans locaux qualifiés."

**Exemples parfaits (155-160 caractères) :**
✅ "Expert rénovation toiture Paris. Artisan certifié RGE, devis gratuit sous 24h, garantie 10 ans. +500 clients satisfaits. Contactez-nous !" (156 car.)
✅ "Isolation combles Lyon : guide complet, prix 2025, aides financières. Notre entreprise : 15 ans d'expérience, matériaux premium. Devis gratuit." (158 car.)

**RÈGLES D'OR :**
- Répondre à l'intention de recherche immédiatement
- Différencier de la concurrence (USP forte)
- Créer l'urgence sans être agressif
- Inclure preuve sociale si possible (clients satisfaits, années d'expérience)
- Être spécifique et concret (éviter le vague)

Génère UNE SEULE meta description optimale. Retourne UNIQUEMENT la meta description, sans guillemets, sans explications.
EOT;

        $systemMessage = "Tu es un expert en copywriting de meta descriptions SEO. Tu maîtrises la persuasion éthique et l'optimisation du CTR. Tes meta descriptions obtiennent systématiquement 6-10% de CTR (vs moyenne 2-4%).";
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 180,
            'temperature' => 0.75,
        ]);
        
        $metaDescription = trim($result['content'] ?? '');
        $metaDescription = trim($metaDescription, '"\'');
        
        // Fallback optimisé
        if (empty($metaDescription)) {
            $metaDescription = "Expert {$keyword} à {$city}. {$companyName} : devis gratuit, artisans certifiés, intervention rapide. Qualité garantie, tarifs transparents.";
        }
        
        // Optimisation longueur - Google peut afficher jusqu'à 320 caractères maintenant
        // On ne tronque plus, on laisse Google gérer l'affichage
        $currentLength = strlen($metaDescription);
        
        // Ne plus tronquer - laisser le contenu complet
        // Google peut afficher jusqu'à 320 caractères dans les résultats de recherche
        if ($currentLength < 140) {
            // Trop court, ajouter CTA si absent
            if (strpos(strtolower($metaDescription), 'devis') === false && 
                strpos(strtolower($metaDescription), 'contact') === false) {
                $remaining = 160 - $currentLength;
                if ($remaining > 20) {
                    $metaDescription .= " Demandez votre devis gratuit !";
                }
            }
        }
        
        Log::info('Meta description générée', [
            'length' => strlen($metaDescription),
            'intent' => $intent
        ]);
        
        return $metaDescription;
    }
    
    /**
     * Générer les mots-clés meta optimisés
     */
    protected function generateMetaKeywords($keyword, $city, $titre, $semanticAnalysis)
    {
        $companyName = config('app.name', 'Notre Entreprise');
        $relatedKeywords = $semanticAnalysis['related_keywords'] ?? [];
        
        // Prendre les 8-10 meilleurs mots-clés connexes
        $topRelatedKeywords = array_slice($relatedKeywords, 0, 8);
        
        $prompt = <<<EOT
Génère 12-15 mots-clés SEO pertinents pour cet article.

**Titre article :** {$titre}
**Mot-clé principal :** {$keyword}
**Ville :** {$city}
**Entreprise :** {$companyName}
**Mots-clés connexes identifiés :** {$this->implodeKeywords($topRelatedKeywords)}

**Critères STRICTS :**
✅ 12-15 mots-clés au total
✅ Inclure le mot-clé principal "{$keyword}"
✅ Inclure la ville "{$city}" dans au moins 2-3 variantes
✅ Inclure 3-5 mots-clés connexes pertinents
✅ Inclure des variantes locales (ex: "{$keyword} {$city}", "{$keyword} {département}")
✅ Inclure des mots-clés techniques du secteur (toiture, couverture, isolation, etc.)
✅ Inclure des mots-clés d'intention (devis, prix, tarif, expert, professionnel)
✅ Format: liste séparée par des virgules, sans numérotation, sans puces

**Exemples de format attendu :**
{$keyword}, {$keyword} {$city}, expert {$keyword}, devis {$keyword}, prix {$keyword}, {$keyword} professionnel, couverture {$city}, toiture {$city}, isolation {$city}

**RÈGLES :**
- Pas d'émojis
- Pas de guillemets
- Pas de numérotation (1., 2., etc.)
- Pas de tirets/puces (-, •, etc.)
- Uniquement des mots-clés séparés par des virgules
- Maximum 15 mots-clés

Génère UNIQUEMENT la liste de mots-clés séparés par des virgules, sans explications, sans guillemets.
EOT;

        $systemMessage = "Tu es un expert SEO spécialisé dans la génération de mots-clés meta optimisés. Tu génères des listes de mots-clés pertinents et variés pour maximiser le référencement.";
        
        try {
            $result = AiService::callAI($prompt, $systemMessage, [
                'max_tokens' => 200,
                'temperature' => 0.7,
            ]);
            
            $keywordsString = trim($result['content'] ?? '');
            
            // Nettoyer la réponse
            $keywordsString = trim($keywordsString, '"\'');
            $keywordsString = preg_replace('/^[\d\.\-\*\•\s]+/', '', $keywordsString); // Enlever numéros, puces
            $keywordsString = preg_replace('/\s+/', ' ', $keywordsString); // Normaliser espaces
            
            // Parser en tableau
            $keywords = array_map('trim', explode(',', $keywordsString));
            $keywords = array_filter($keywords, function($kw) {
                return !empty($kw) && strlen($kw) >= 2 && strlen($kw) <= 50;
            });
            
            // S'assurer que le mot-clé principal et la ville sont présents
            $keywordLower = strtolower($keyword);
            $cityLower = strtolower($city);
            $hasKeyword = false;
            $hasCity = false;
            
            foreach ($keywords as $kw) {
                if (stripos(strtolower($kw), $keywordLower) !== false) {
                    $hasKeyword = true;
                }
                if (stripos(strtolower($kw), $cityLower) !== false) {
                    $hasCity = true;
                }
            }
            
            // Ajouter si manquant
            if (!$hasKeyword) {
                array_unshift($keywords, $keyword);
            }
            if (!$hasCity) {
                $keywords[] = $keyword . ' ' . $city;
            }
            
            // Limiter à 15 mots-clés
            $keywords = array_slice(array_unique($keywords), 0, 15);
            
            Log::info('Mots-clés meta générés', [
                'count' => count($keywords),
                'keywords_preview' => array_slice($keywords, 0, 5)
            ]);
            
            return $keywords;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération mots-clés meta', [
                'error' => $e->getMessage()
            ]);
            
            // Fallback : générer des mots-clés basiques
            $fallbackKeywords = [
                $keyword,
                $keyword . ' ' . $city,
                'expert ' . $keyword,
                'devis ' . $keyword,
                $keyword . ' professionnel',
                'couverture ' . $city,
                'toiture ' . $city,
                'isolation ' . $city,
                'rénovation ' . $city,
                'artisan ' . $city
            ];
            
            return array_slice($fallbackKeywords, 0, 12);
        }
    }
    
    /**
     * Générer le contenu HTML ultra-optimisé
     */
    protected function generateHtmlContent($keyword, $city, $serpResults, $keywordImages, $titre, $semanticAnalysis)
    {
        $prompt = $this->buildAdvancedHtmlPrompt($keyword, $city, $serpResults, $keywordImages, $titre, $semanticAnalysis);
        
        $systemMessage = <<<EOT
Tu es un EXPERT SEO SENIOR de niveau international avec 15 ans d'expérience. Tu maîtrises :
- La rédaction SEO qui se classe systématiquement en top 3 Google
- Le HTML5 sémantique parfaitement structuré
- La psychologie du web et l'engagement utilisateur
- Les algorithmes Google 2025 (BERT, MUM, Helpful Content Update)
- Le copywriting persuasif B2C dans le secteur bâtiment

TES COMPÉTENCES CLÉS :
✅ Créer du contenu 100% unique et original (jamais de duplication)
✅ Intégrer naturellement les mots-clés (densité optimale 0.8-1.2%)
✅ Structurer l'information pour lisibilité maximale (Flesch Reading Ease 60-70)
✅ Optimiser pour les featured snippets (position 0)
✅ Maximiser le temps de lecture (dwell time 4-8 minutes)
✅ Convertir les visiteurs en prospects (CTA stratégiques)

TES ARTICLES OBTIENNENT :
📈 Score SEO : 95-100/100
📈 Taux de rebond : <40%
📈 Temps sur page : 5-9 minutes
📈 Taux de conversion : 3-8%
📈 Featured snippets : 30-50% des requêtes

EXIGENCES ABSOLUES :
- Contenu profondément informatif et actionnable (pas de fluff)
- Ton professionnel mais accessible (éviter jargon excessif)
- Expertise démontrée à chaque paragraphe (E-E-A-T)
- HTML parfaitement valide W3C
- ZÉRO texte de conclusion type "Ce contenu HTML..." (INTERDIT)
EOT;
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => $this->maxTokens,
            'temperature' => 0.68, // Sweet spot créativité/cohérence
            'timeout' => 180, // 3 minutes pour la génération de contenu complexe
        ]);
        
        // Vérifier que le résultat n'est pas null
        if (!$result || !isset($result['content'])) {
            $provider = $result['provider'] ?? 'unknown';
            $errorDetails = 'L\'API IA (' . $provider . ') n\'a pas retourné de contenu.';
            
            Log::error('GptSeoGenerator: Résultat AI null ou vide', [
                'result' => $result,
                'keyword' => $keyword,
                'city' => $city,
                'provider' => $provider
            ]);
            
            // Message d'erreur plus détaillé
            if ($provider === 'chatgpt') {
                $errorDetails .= ' Vérifiez votre clé API ChatGPT et vos quotas. Si le problème persiste, configurez Groq comme alternative.';
            } elseif ($provider === 'groq') {
                $errorDetails .= ' Vérifiez votre clé API Groq et vos quotas. Si le problème persiste, configurez ChatGPT comme alternative.';
            } else {
                $errorDetails .= ' Vérifiez vos clés API (ChatGPT et/ou Groq) et vos quotas.';
            }
            
            throw new \Exception($errorDetails);
        }
        
        $contenuHtml = trim($result['content'] ?? '');
        
        Log::info('GptSeoGenerator: Contenu brut reçu', [
            'length' => strlen($contenuHtml),
            'preview' => substr($contenuHtml, 0, 300),
            'provider' => $result['provider'] ?? 'unknown'
        ]);
        
        // Nettoyer le HTML
        $contenuHtml = $this->cleanHtmlOutput($contenuHtml);
        
        Log::info('GptSeoGenerator: Contenu après nettoyage', [
            'length' => strlen($contenuHtml),
            'preview' => substr($contenuHtml, 0, 300)
        ]);
        
        // Validation robuste - ajuster le seuil minimum
        if (empty($contenuHtml) || strlen($contenuHtml) < 500) {
            Log::error('Contenu HTML généré insuffisant', [
                'length' => strlen($contenuHtml),
                'preview' => substr($contenuHtml, 0, 500),
                'raw_preview' => substr($result['content'] ?? '', 0, 500),
                'keyword' => $keyword,
                'city' => $city
            ]);
            throw new \Exception('Le contenu généré est trop court ou vide. Longueur: ' . strlen($contenuHtml) . ' caractères. Vérifiez que l\'IA a bien généré du contenu.');
        }
        
        $wordCount = str_word_count(strip_tags($contenuHtml));
        if ($wordCount < 2500) {
            Log::warning('Contenu en dessous du minimum recommandé', ['word_count' => $wordCount, 'minimum' => 2500]);
        }
        
        Log::info('Contenu HTML premium généré', [
            'length' => strlen($contenuHtml),
            'word_count' => $wordCount,
            'paragraphs' => substr_count($contenuHtml, '</p>'),
            'headings_h2' => substr_count($contenuHtml, '</h2>'),
            'headings_h3' => substr_count($contenuHtml, '</h3>'),
            'lists' => substr_count($contenuHtml, '</ul>') + substr_count($contenuHtml, '</ol>'),
            'images' => substr_count($contenuHtml, '<img')
        ]);
        
        return $contenuHtml;
    }
    
    /**
     * Nettoyer le HTML généré
     */
    protected function cleanHtmlOutput($html)
    {
        // Retirer les balises markdown
        $html = preg_replace('/```html\n?/', '', $html);
        $html = preg_replace('/```\n?/', '', $html);
        
        // Corriger les balises FAQ Schema.org mal formées
        $html = preg_replace('/itemtype="<h([23])>/i', 'itemtype="https://schema.org/Question"><h$1', $html);
        $html = preg_replace('/itemtype="<section/i', 'itemtype="https://schema.org/FAQPage"><section', $html);
        $html = preg_replace('/itemtype="<div/i', 'itemtype="https://schema.org/Answer"><div', $html);
        $html = preg_replace('/<section[^>]*itemtype="<h2>/i', '<section id="faq" itemscope itemtype="https://schema.org/FAQPage"><h2', $html);
        
        // Supprimer les fragments schema.org orphelins
        $html = preg_replace('/https:\/\/schema\.org\/[^>]*">\s*/', '', $html);
        $html = preg_replace('/<section[^>]*itemtype="https:\/\/schema\.org\/FAQPage"[^>]*>\s*https:\/\/schema\.org\/[^>]*">/i', 
                            '<section id="faq" itemscope itemtype="https://schema.org/FAQPage">', $html);
        $html = preg_replace('/<https:\/\/schema\.org\/[^>]*>/i', '', $html);
        
        // Supprimer les textes de conclusion indésirables (méta-commentaires IA)
        $unwantedPhrases = [
            '/Ce contenu HTML intègre toutes les recommandations.*?\./s',
            '/Ce contenu HTML.*?référence pour.*?\./s',
            '/visant à établir.*?comme.*?référence.*?\./s',
            '/Cet article HTML.*?optimisé pour.*?\./s',
            '/Le contenu ci-dessus.*?SEO.*?\./s',
            '/Ce modèle HTML.*?système de gestion de contenu.*?\./s',
            '/Ce modèle HTML est conçu.*?directives SEO.*?\./s',
            '/Ce modèle HTML.*?intégré dans.*?\./s',
            '/modèle HTML.*?gestion de contenu.*?\./s',
            '/respectant les directives SEO.*?meilleures pratiques.*?\./s',
            '/fournissant un contenu riche.*?besoins des résidents.*?\./s',
            '/Ce contenu.*?système de gestion.*?\./s',
            '/HTML.*?intégré.*?CMS.*?\./s',
            '/conçu pour être intégré.*?\./s',
        ];
        
        foreach ($unwantedPhrases as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }
        
        // Supprimer les paragraphes entiers contenant des mentions de "modèle HTML", "système de gestion", etc.
        $html = preg_replace('/<p[^>]*>.*?(?:modèle HTML|système de gestion|directives SEO|meilleures pratiques|intégré dans).*?<\/p>/is', '', $html);
        
        // Nettoyer les espaces multiples
        $html = preg_replace('/\n{3,}/', "\n\n", $html);
        $html = trim($html);
        
        return $html;
    }
    
    /**
     * Valider et corriger les sections vides ou incomplètes
     */
    protected function validateAndFixSections($html)
    {
        // Extraire toutes les sections H2 avec leur contenu
        preg_match_all('/<h2[^>]*id=["\']section-(\d+)["\'][^>]*>(.*?)<\/h2>(.*?)(?=<h2|$)/is', $html, $sections, PREG_SET_ORDER);
        
        $issues = [];
        foreach ($sections as $section) {
            $sectionNum = $section[1];
            $sectionTitle = strip_tags($section[2]);
            $sectionContent = trim($section[3]);
            
            // Vérifier si la section est vide ou trop courte
            $wordCount = str_word_count(strip_tags($sectionContent));
            
            if ($wordCount < 100) {
                $issues[] = [
                    'section' => $sectionNum,
                    'title' => $sectionTitle,
                    'word_count' => $wordCount,
                    'content' => substr($sectionContent, 0, 200)
                ];
                
                Log::warning('Section vide ou incomplète détectée', [
                    'section' => $sectionNum,
                    'title' => $sectionTitle,
                    'word_count' => $wordCount
                ]);
            }
        }
        
        if (!empty($issues)) {
            Log::error('Sections vides ou incomplètes détectées dans l\'article généré', [
                'issues' => $issues,
                'total_sections' => count($sections)
            ]);
        }
        
        return $html;
    }
    
    /**
     * Post-traitement du contenu pour optimisation finale
     */
    protected function postProcessContent($html, $keyword, $city)
    {
        // Corriger les répétitions de ville (ex: "à Chevigny à Chevigny-Saint-Sauveur")
        $cityPattern = preg_quote($city, '/');
        // Pattern pour détecter "à [ville] à [ville]" ou "[ville] à [ville]"
        $html = preg_replace('/\b(à|dans|pour)\s+' . $cityPattern . '\s+(à|dans|pour)\s+' . $cityPattern . '\b/i', '$1 ' . $city, $html);
        $html = preg_replace('/\b' . $cityPattern . '\s+(à|dans|pour)\s+' . $cityPattern . '\b/i', $city, $html);
        
        // Supprimer les textes méta restants après nettoyage initial
        $metaTextPatterns = [
            '/<p[^>]*>.*?(?:Ce modèle HTML|système de gestion de contenu|directives SEO avancées|meilleures pratiques de développement|intégré dans un système|conçu pour être intégré).*?<\/p>/is',
            '/<p[^>]*>.*?(?:modèle HTML|gestion de contenu|CMS|système de gestion).*?(?:SEO|développement web|contenu riche).*?<\/p>/is',
        ];
        
        foreach ($metaTextPatterns as $pattern) {
            $html = preg_replace($pattern, '', $html);
        }
        
        // Vérifier densité mots-clés
        $text = strip_tags($html);
        $wordCount = str_word_count($text);
        $keywordCount = substr_count(strtolower($text), strtolower($keyword));
        $keywordDensity = ($keywordCount / $wordCount) * 100;
        
        Log::info('Densité mots-clés', [
            'keyword' => $keyword,
            'occurrences' => $keywordCount,
            'total_words' => $wordCount,
            'density' => round($keywordDensity, 2) . '%'
        ]);
        
        // Ajouter attributs alt manquants aux images
        $html = preg_replace_callback('/<img([^>]*)>/i', function($matches) use ($keyword, $city) {
            $imgTag = $matches[1];
            if (strpos($imgTag, 'alt=') === false) {
                // Ajouter un alt générique optimisé
                $imgTag .= ' alt="' . htmlspecialchars($keyword . ' à ' . $city) . '"';
            }
            if (strpos($imgTag, 'loading=') === false) {
                $imgTag .= ' loading="lazy"';
            }
            return '<img' . $imgTag . '>';
        }, $html);
        
        // Ajouter attributs title aux liens si absents
        $html = preg_replace_callback('/<a([^>]*href=["\'][^"\']+["\'][^>]*)>/i', function($matches) {
            $linkTag = $matches[1];
            if (strpos($linkTag, 'title=') === false) {
                // Extraire le texte du lien pour générer un title
                return '<a' . $linkTag . '>';
            }
            return '<a' . $linkTag . '>';
        }, $html);
        
        return $html;
    }
    
    /**
     * Calculer le score SEO du contenu généré
     */
    protected function calculateSeoScore($html, $keyword, $city, $titre, $metaDescription)
    {
        $score = 0;
        $maxScore = 100;
        
        $text = strip_tags($html);
        $wordCount = str_word_count($text);
        $lowerText = strtolower($text);
        $lowerKeyword = strtolower($keyword);
        
        // 1. Longueur du contenu (15 points)
        if ($wordCount >= 3000) {
            $score += 15;
        } else if ($wordCount >= 2500) {
            $score += 12;
        } else if ($wordCount >= 2000) {
            $score += 8;
        } else {
            $score += 5;
        }
        
        // 2. Densité mots-clés (15 points)
        $keywordCount = substr_count($lowerText, $lowerKeyword);
        $keywordDensity = ($keywordCount / $wordCount) * 100;
        if ($keywordDensity >= 0.5 && $keywordDensity <= 1.5) {
            $score += 15;
        } else if ($keywordDensity >= 0.3 && $keywordDensity <= 2.0) {
            $score += 10;
        } else {
            $score += 5;
        }
        
        // 3. Présence mot-clé dans le premier paragraphe (10 points)
        $firstParagraph = substr($text, 0, 200);
        if (stripos($firstParagraph, $keyword) !== false) {
            $score += 10;
        } else if (stripos($firstParagraph, $keyword) !== false || stripos(substr($text, 0, 400), $keyword) !== false) {
            $score += 5;
        }
        
        // 4. Structure HTML (15 points)
        $h2Count = substr_count($html, '</h2>');
        $h3Count = substr_count($html, '</h3>');
        if ($h2Count >= 5 && $h3Count >= 8) {
            $score += 15;
        } else if ($h2Count >= 3 && $h3Count >= 5) {
            $score += 10;
        } else {
            $score += 5;
        }
        
        // 5. Listes et tableaux (10 points)
        $listsCount = substr_count($html, '</ul>') + substr_count($html, '</ol>');
        $tablesCount = substr_count($html, '</table>');
        if ($listsCount >= 4 || $tablesCount >= 1) {
            $score += 10;
        } else if ($listsCount >= 2) {
            $score += 7;
        } else {
            $score += 3;
        }
        
        // 6. Présence FAQ Schema.org (10 points)
        if (strpos($html, 'schema.org/FAQPage') !== false && 
            strpos($html, 'schema.org/Question') !== false) {
            $score += 10;
        } else if (strpos($html, '<section id="faq"') !== false) {
            $score += 5;
        }
        
        // 7. Images optimisées (8 points)
        $imgCount = substr_count($html, '<img');
        $imgWithAlt = substr_count($html, 'alt="');
        $imgWithLazy = substr_count($html, 'loading="lazy"');
        if ($imgCount > 0 && $imgWithAlt === $imgCount && $imgWithLazy === $imgCount) {
            $score += 8;
        } else if ($imgCount > 0 && $imgWithAlt === $imgCount) {
            $score += 5;
        } else if ($imgCount > 0) {
            $score += 3;
        }
        
        // 8. Liens internes (7 points)
        $internalLinks = substr_count($html, '<a href=');
        if ($internalLinks >= 6) {
            $score += 7;
        } else if ($internalLinks >= 3) {
            $score += 5;
        } else if ($internalLinks >= 1) {
            $score += 3;
        }
        
        // 9. Longueur titre (5 points)
        $titreLength = strlen($titre);
        if ($titreLength >= 50 && $titreLength <= 60) {
            $score += 5;
        } else if ($titreLength >= 45 && $titreLength <= 65) {
            $score += 3;
        }
        
        // 10. Longueur meta description (5 points)
        $metaLength = strlen($metaDescription);
        if ($metaLength >= 150 && $metaLength <= 160) {
            $score += 5;
        } else if ($metaLength >= 140 && $metaLength <= 165) {
            $score += 3;
        }
        
        return min($maxScore, $score);
    }
    
    /**
     * Générer un slug optimisé
     */
    protected function generateOptimizedSlug($titre, $keyword)
    {
        $slug = \Illuminate\Support\Str::slug($titre);
        
        // S'assurer que le mot-clé est dans le slug
        $keywordSlug = \Illuminate\Support\Str::slug($keyword);
        if (strpos($slug, $keywordSlug) === false) {
            // Préfixer avec le mot-clé
            $slug = $keywordSlug . '-' . $slug;
        }
        
        // Limiter la longueur (max 80 caractères pour URL propre)
        if (strlen($slug) > 80) {
            $slug = substr($slug, 0, 77) . '...';
            $slug = rtrim($slug, '-.');
        }
        
        return $slug;
    }
    
    /**
     * Construire le prompt avancé pour contenu HTML
     */
    protected function buildAdvancedHtmlPrompt($keyword, $city, $serpResults, $keywordImages, $titre, $semanticAnalysis)
    {
        $companyName = config('app.name', 'Notre Entreprise');
        $companyDescription = Setting::where('key', 'company_description')->value('value') ?? '';
        $siteUrl = config('app.url', 'https://example.com');
        $companyPhone = Setting::where('key', 'company_phone')->value('value') ?? '';
        $companyPhoneRaw = Setting::where('key', 'company_phone_raw')->value('value') ?? $companyPhone;
        
        $devisUrl = route('form.step', 'propertyType');
        $contactUrl = route('contact');
        
        $serpInsights = $this->extractSerpInsights($serpResults);
        $competitorTopics = $serpInsights['topics'] ?? [];
        $commonQuestions = $serpInsights['questions'] ?? [];
        $targetWordCount = $semanticAnalysis['content_depth_required'] ?? 2200;
        $competitorGaps = $semanticAnalysis['competitor_weaknesses'] ?? [];
        $relatedKeywords = $semanticAnalysis['related_keywords'] ?? [];
        $userIntent = $semanticAnalysis['user_intent'] ?? 'informational';
        
        $imagesContext = $this->buildImagesContext($keywordImages, $keyword, $city);
        $internalLinksContext = $this->buildInternalLinksContext($keyword, $city);
        $relatedKeywordsContext = $this->buildRelatedKeywordsContext($relatedKeywords);
        
        $currentYear = date('Y');
        
        $prompt = <<<EOT
🎯 **MISSION CRITIQUE : Article SEO Score 95%+ Garanti**

Tu vas créer l'article le PLUS COMPLET et le MIEUX OPTIMISÉ jamais rédigé sur ce sujet.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 **DONNÉES STRATÉGIQUES**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Titre H1 :** {$titre}
**Mot-clé cible :** {$keyword}
**Localisation :** {$city}
**Entreprise :** {$companyName}
**Intention utilisateur :** {$userIntent}
**Objectif longueur :** {$targetWordCount}+ mots
**Année de référence :** {$currentYear}

**À propos de {$companyName} :**
{$companyDescription}

{$relatedKeywordsContext}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔍 **ÉTAPE 1 : RECHERCHE APPROFONDIE OBLIGATOIRE (AVANT TOUT)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ **CRITIQUE : AVANT de commencer à rédiger, tu DOIS effectuer une recherche approfondie complète.**

**INSTRUCTIONS DE RECHERCHE :**

1. **ANALYSE DU MOT-CLÉ ET DE L'INTENTION :**
   - Comprendre en profondeur ce que recherche l'utilisateur avec "{$keyword}"
   - Identifier les questions sous-jacentes (pourquoi, comment, combien, où, quand, qui)
   - Déterminer le niveau d'expertise attendu (débutant, intermédiaire, expert)
   - Analyser l'intention : information, comparaison, achat, localisation

2. **ANALYSE APPROFONDIE DES CONCURRENTS :**
   - Examiner CHAQUE résultat SERP fourni ci-dessous en détail
   - Identifier les sujets traités par chaque concurrent
   - Repérer les angles d'approche utilisés
   - Noter les informations manquantes ou superficielles
   - Détecter les erreurs ou imprécisions dans leurs contenus
   - Analyser leur structure (comment ils organisent l'information)
   - Identifier leurs points forts et leurs faiblesses

**RÉSULTATS SERP DES CONCURRENTS À ANALYSER :**
{$this->formatSerpResultsForAnalysis($serpResults, $keyword)}

**QUESTIONS FRÉQUENTES IDENTIFIÉES :**
{$this->formatQuestions($commonQuestions)}

3. **SYNTHÈSE DE RECHERCHE :**
   Après avoir analysé tous les concurrents, créer une synthèse qui identifie :
   - Les sujets les plus importants à couvrir (basés sur ce que les concurrents traitent)
   - Les angles uniques à développer (ce que les concurrents ne font pas bien)
   - Les informations manquantes ou incomplètes chez les concurrents
   - La meilleure structure d'article (basée sur ce qui fonctionne, mais améliorée)
   - Les questions non répondues ou mal répondues par les concurrents

**🎯 STRATÉGIE DE DOMINATION :**
1. Créer un contenu qui SURPASSE tous les concurrents en profondeur et qualité
2. Combler TOUS les gaps identifiés dans l'analyse concurrentielle
3. Ajouter perspective unique : spécificités locales {$city}, tendances {$currentYear}, innovations
4. Intégrer expertise terrain : erreurs courantes, conseils pro, cas réels
5. Fournir outils actionnables : checklists, calculateurs mentaux, guides étape par étape
6. Répondre à TOUTES les questions fréquentes de manière plus complète que les concurrents

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
💰 **RECHERCHE APPROFONDIE DES PRIX - OBLIGATOIRE**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ **CRITIQUE : Pour toute mention de prix, tarif ou coût dans l'article, tu DOIS effectuer une recherche approfondie sur les tarifs réels du marché français en {$currentYear}.**

**INSTRUCTIONS POUR LA RECHERCHE DES PRIX :**
1. Effectue une recherche approfondie sur les tarifs moyens du marché français pour {$keyword} en {$currentYear}
2. Considère les variations régionales (prix peuvent être 10-30% plus élevés dans certaines régions comme l'Île-de-France)
3. Prends en compte les différents types de prestations :
   - Petite réparation / intervention ponctuelle
   - Réparation moyenne / réfection partielle
   - Rénovation complète
   - Installation neuve
4. Considère les différents matériaux et leur impact sur le prix (économique, standard, premium)
5. Fournis des FOURCHETTES LARGES et RÉALISTES basées sur tes recherches

**EXEMPLES DE FOURCHETTES RÉALISTES POUR "COUVREUR PROFESSIONNEL" / "RÉNOVATION TOITURE" :**
- Petite réparation (remplacement de quelques tuiles, réparation ponctuelle) : 500€ à 2000€
- Réparation moyenne (réfection partielle, zinguerie, remplacement d'une section) : 2000€ à 8000€
- Rénovation complète toiture (dépose ancienne couverture, charpente si nécessaire, couverture neuve) : 8000€ à 25000€ pour une maison moyenne (100-150m²)
- Rénovation complète avec isolation thermique : 12000€ à 35000€
- Installation neuve (construction) : 10000€ à 30000€ selon la superficie

**EXEMPLES POUR "ISOLATION THERMIQUE" :**
- Isolation combles perdus : 30€ à 80€/m²
- Isolation sous toiture : 50€ à 120€/m²
- Isolation murs intérieurs : 40€ à 100€/m²
- Isolation complète maison : 8000€ à 25000€ selon la superficie

**EXEMPLES POUR "RÉNOVATION FAÇADE" :**
- Nettoyage et hydrofuge : 15€ à 40€/m²
- Rénovation complète (enduit, peinture) : 50€ à 120€/m²
- Rénovation complète maison moyenne : 5000€ à 15000€

**RÈGLES ABSOLUES POUR LES PRIX :**
- ❌ JAMAIS de fourchette trop étroite (ex: 1500€-5000€ pour une rénovation complète)
- ✅ TOUJOURS fournir plusieurs fourchettes selon le type de prestation
- ✅ Mentionner les facteurs qui influencent le prix (superficie, matériaux, complexité, accessibilité)
- ✅ Utiliser des exemples concrets avec des chiffres réalistes
- ✅ Adapter les prix à la région {$city} si pertinente

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🏗️ **ÉTAPE 2 : CRÉATION D'UNE STRUCTURE NATURELLE ET UNIQUE**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

⚠️ **CRITIQUE : Ne JAMAIS utiliser une structure prédéfinie ou répétitive.**

**RÈGLES ABSOLUES POUR LA STRUCTURE :**

1. **CRÉER UNE STRUCTURE UNIQUE** basée sur :
   - L'analyse approfondie des concurrents (étape 1)
   - Les sujets réellement importants pour "{$keyword}"
   - Les questions fréquentes identifiées
   - L'intention de l'utilisateur
   - Les gaps concurrentiels détectés

2. **INTERDICTIONS ABSOLUES :**
   - ❌ JAMAIS utiliser une structure prédéfinie comme "Les avantages d'un...", "Comment obtenir votre devis ?", "Choix de matériaux et techniques", etc.
   - ❌ JAMAIS créer un sommaire avec les mêmes sections que les autres articles
   - ❌ JAMAIS répéter des titres de sections génériques
   - ❌ JAMAIS utiliser des formules toutes faites

3. **CE QUI EST OBLIGATOIRE :**
   - ✅ Créer une structure adaptée au sujet spécifique "{$keyword}"
   - ✅ Développer les sections les plus pertinentes pour répondre à l'intention de recherche
   - ✅ Organiser le contenu de manière logique et naturelle
   - ✅ Chaque section doit avoir un titre unique et pertinent (pas de copier-coller)
   - ✅ Le sommaire doit refléter la structure réelle de l'article

**STRUCTURE DE BASE (À ADAPTER SELON LE SUJET) :**

**1. INTRODUCTION MAGNÉTIQUE** (250-400 mots)
```html
<div class="article-intro">
  <p><strong>[Accroche émotionnelle : problème concret du lecteur]</strong></p>
  <p>[Développement du problème avec données chiffrées]... Le {$keyword} à {$city} [contexte local spécifique]...</p>
  <p>[Promesse de valeur : ce que l'article va apporter]... Dans ce guide expert complet, vous découvrirez...</p>
  <p>[Établir crédibilité] {$companyName}, fort de [X années] d'expérience dans le secteur...</p>
</div>
```

**Checklist introduction :**
✅ Mot-clé "{$keyword}" dans les 100 premiers caractères
✅ Mention "{$city}" dans le contexte
✅ Hook émotionnel (peur, désir, curiosité)
✅ Statistique ou donnée surprenante
✅ Promesse claire (3-5 bénéfices listés)
✅ Ton empathique et professionnel

**2. SOMMAIRE CLIQUABLE** (Créé APRÈS avoir défini la structure)
```html
<nav class="table-of-contents" aria-label="Sommaire de l'article">
  <h2>📑 Au Sommaire</h2>
  <ul>
    <li><a href="#section-1">[Titre section 1 UNIQUE et PERTINENT]</a></li>
    <li><a href="#section-2">[Titre section 2 UNIQUE et PERTINENT]</a></li>
    <li><a href="#section-3">[Titre section 3 UNIQUE et PERTINENT]</a></li>
    <!-- Ajouter autant de sections que nécessaire selon l'analyse -->
    <li><a href="#faq">Questions Fréquentes</a></li>
  </ul>
</nav>
```

**⚠️ IMPORTANT : Le sommaire doit être créé APRÈS avoir analysé les concurrents et défini la structure unique de l'article. Ne JAMAIS utiliser un sommaire générique.**

**3. SECTIONS PRINCIPALES** (6-10 sections H2 selon le sujet)

**🚨 RÈGLES ABSOLUES CRITIQUES - À RESPECTER IMPÉRATIVEMENT :**

**1. CRÉER DES SECTIONS UNIQUES ET PERTINENTES :**
   - Baser les sections sur l'analyse approfondie des concurrents (étape 1)
   - Chaque section doit répondre à une question ou un besoin réel identifié
   - Les titres de sections doivent être spécifiques au sujet "{$keyword}"
   - Éviter les titres génériques ou répétitifs

**2. DÉVELOPPEMENT COMPLET DE CHAQUE SECTION :**
   - TOUTES les sections listées dans le sommaire DOIVENT être développées intégralement (600-1000 mots chacune)
   - Les sections DOIVENT être développées DANS L'ORDRE du sommaire, sans exception
   - AUCUNE section ne doit être omise, même si l'article devient très long (3000+ mots)
   - CHAQUE section doit faire MINIMUM 600 mots (pas de section avec 2-3 lignes ou vide)
   - INTERDIT ABSOLUMENT de créer un titre H2 suivi de rien ou de seulement 2-3 lignes
   - INTERDIT de mettre des placeholders comme "Cette section sera développée" ou "Contenu à venir"
   - Si une section est trop courte, AJOUTER immédiatement : exemples, détails techniques, conseils, données chiffrées, cas pratiques, procédures étape par étape, avantages/inconvénients, coûts, durées, matériaux, techniques, normes, réglementations
   - CHAQUE section doit contenir IMMÉDIATEMENT après le titre H2 : 3-5 paragraphes de 100-150 mots, des exemples concrets, des informations pratiques, des conseils d'experts

**3. EXEMPLES DE SECTIONS PERTINENTES (À ADAPTER SELON LE SUJET) :**
   Les sections suivantes sont des EXEMPLES. Tu DOIS créer des sections adaptées au sujet "{$keyword}" basées sur :
   - L'analyse des concurrents
   - Les questions fréquentes identifiées
   - Les gaps concurrentiels
   - L'intention de recherche
   
   **SECTIONS RECOMMANDÉES (inspirées d'articles à fort potentiel) :**
   
   **A. Section "Nos prestations" ou "Services proposés"** (si pertinent pour le sujet)
   - Liste détaillée des prestations liées à {$keyword}
   - Chaque prestation avec description, avantages, prix indicatifs
   - Format : liste à puces avec descriptions complètes (100-150 mots par prestation)
   - Exemple de structure :
     ```html
     <section id="prestations">
       <h2>Nos prestations de {$keyword}</h2>
       <p>[Introduction 100-150 mots]</p>
       <ul>
         <li><strong>[Prestation 1] :</strong> [Description détaillée 100-150 mots avec prix, délais, avantages]</li>
         <li><strong>[Prestation 2] :</strong> [Description détaillée 100-150 mots]</li>
         <!-- 8-12 prestations au total -->
       </ul>
     </section>
     ```
   
   **B. Section "Pourquoi choisir un artisan local ?" ou "Pourquoi faire appel à {$companyName} ?"**
   - Avantages de choisir un professionnel local
   - Différences avec les grandes entreprises
   - Garanties et assurances (décennale, biennale, RC pro)
   - Tarifs compétitifs, réactivité, service personnalisé
   - Format : paragraphes avec liste d'avantages (600-800 mots)
   
   **C. Section "Entretien régulier et durabilité"** (si pertinent)
   - Importance de l'entretien
   - Fréquence recommandée
   - Coûts d'entretien vs coûts de réparation
   - Conseils pratiques pour prolonger la durée de vie
   - Format : paragraphes détaillés avec conseils actionnables (600-800 mots)
   
   **D. Section "Labels qualité et garanties"**
   - Certifications (RGE, Qualibat, etc.)
   - Garanties légales (décennale, biennale)
   - Assurance professionnelle
   - Aides financières disponibles (MaPrimeRénov', CEE, etc.)
   - Format : paragraphes avec détails sur chaque label/garantie (600-800 mots)
   
   **E. Section "Aides financières et subventions"** (si pertinent)
   - Liste des aides disponibles (MaPrimeRénov', CEE, éco-PTZ, etc.)
   - Conditions d'éligibilité
   - Montants indicatifs
   - Processus de demande
   - Format : paragraphes détaillés avec exemples concrets (600-800 mots)
   
   **F. Section "Notre zone d'intervention"**
   - Liste des villes/communes couvertes
   - Spécificités locales (climat, architecture, réglementations)
   - Réactivité et proximité
   - Format : paragraphes avec liste des zones (400-600 mots)
   
   **Autres sections possibles (à adapter) :**
   - Si le sujet concerne les prix : "Quel est le coût réel de {$keyword} à {$city} en {$currentYear} ?"
   - Si le sujet concerne les matériaux : "Quels matériaux choisir pour {$keyword} ? Guide comparatif complet"
   - Si le sujet concerne les erreurs : "Les erreurs à éviter lors de {$keyword} : conseils d'un expert"
   - Si le sujet concerne les techniques : "Techniques modernes de {$keyword} : innovations {$currentYear}"
   - Si le sujet concerne les réglementations : "Normes et réglementations {$keyword} : ce qu'il faut savoir en {$currentYear}"
   - Si le sujet concerne l'entretien : "Maintenance et entretien {$keyword} : guide pratique"
   - Si le sujet concerne le processus : "Comment se déroule un projet de {$keyword} ? Étapes détaillées"
   - Si le sujet concerne les avantages : "Pourquoi opter pour {$keyword} ? Bénéfices et retours sur investissement"
   
   **⚠️ IMPORTANT :**
   - Ces sections sont des GUIDES. Tu DOIS créer des sections UNIQUES adaptées au sujet spécifique "{$keyword}"
   - Intègre TOUJOURS {$companyName} de manière naturelle dans les sections pertinentes
   - Mettez en avant l'entreprise, ses compétences, ses garanties, son expertise locale
   - Adapte le style et le ton de l'exemple fourni : professionnel, détaillé, avec des sections structurées

**4. QUESTIONS FRÉQUENTES** (10-15 questions avec réponses détaillées 80-200 mots chacune)

**⚠️ INTERDICTIONS ABSOLUES - VIOLATION = ÉCHEC TOTAL :**
- ❌ JAMAIS de section avec seulement 2-3 lignes ou vide
- ❌ JAMAIS de section manquante du sommaire
- ❌ JAMAIS de contenu superficiel ou vague
- ❌ JAMAIS de titre H2 suivi de rien (section vide)
- ❌ JAMAIS de placeholder ou "Contenu à venir"
- ❌ JAMAIS de sauter une section même si elle semble difficile
- ✅ CHAQUE section doit être complète, détaillée et actionnable (700-900 mots minimum)
- ✅ CHAQUE section doit avoir du contenu réel et utile immédiatement après le titre H2

**TEMPLATE SECTION PARFAITE (700-900 mots minimum) :**
```html
<section id="section-X">
  <h2>[Titre H2 avec variante mot-clé naturelle]</h2>
  
  <p>[Paragraphe intro 100-120 mots] Contextualisation approfondie du sujet avec données terrain, statistiques, enjeux...</p>
  
  <h3>[Sous-titre H3 spécifique #1]</h3>
  <p>[Développement 150-200 mots] Explication détaillée avec exemples concrets, cas pratiques, données chiffrées...</p>
  <p>[Paragraphe complémentaire 100-120 mots] Approfondissement avec spécificités locales {$city}, tendances {$currentYear}...</p>
  
  <div class="info-box">
    <h4>💡 Conseil d'Expert Pro</h4>
    <p>[Astuce actionnable immédiate 80-100 mots] Basé sur [X] années d'expérience terrain, avec exemple concret...</p>
  </div>
  
  <h3>[Sous-titre H3 spécifique #2]</h3>
  <p>[Développement 150-200 mots] Approfondissement avec processus détaillé, étapes, précautions...</p>
  
  <ul class="checklist">
    <li><strong>[Point 1] :</strong> [Explication détaillée 40-60 mots avec bénéfice concret et exemple]</li>
    <li><strong>[Point 2] :</strong> [Conseil actionnable 40-60 mots avec mise en contexte]</li>
    <li><strong>[Point 3] :</strong> [Donnée chiffrée ou statistique 40-60 mots avec explication]</li>
    <li><strong>[Point 4] :</strong> [Mise en garde importante 40-60 mots avec conséquences]</li>
    <li><strong>[Point 5] :</strong> [Recommandation pro 40-60 mots avec justification]</li>
  </ul>
  
  <h3>[Sous-titre H3 spécifique #3]</h3>
  <p>[Développement 150-200 mots] Transition naturelle vers aspect complémentaire...</p>
  
  <table class="comparison-table">
    <thead>
      <tr>
        <th>Critère</th>
        <th>Option A</th>
        <th>Option B</th>
        <th>Recommandation</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>[Critère 1 détaillé]</td>
        <td>[Valeur A avec explication]</td>
        <td>[Valeur B avec explication]</td>
        <td>✅ [Recommandation justifiée]</td>
      </tr>
      <!-- 5-7 lignes de comparaison détaillées -->
    </tbody>
  </table>
  
  <h3>[Sous-titre H3 spécifique #4]</h3>
  <p>[Développement 150-200 mots] Approfondissement supplémentaire avec exemples concrets...</p>
  
  <blockquote class="expert-quote">
    <p>« [Citation professionnelle authentique 60-80 mots] Les clients qui [action] économisent en moyenne [chiffre]% sur [durée]. [Explication complémentaire]. »</p>
    <cite>— Expert {$companyName}, spécialiste {$keyword} depuis [X] ans</cite>
  </blockquote>
  
  <h3>[Sous-titre H3 spécifique #5]</h3>
  <p>[Développement final 150-200 mots] Conclusion de section avec synthèse et prochaines étapes...</p>
</section>
```

**🚨 VÉRIFICATION OBLIGATOIRE AVANT CHAQUE SECTION (NON NÉGOCIABLE) :**
1. **Vérifier que la section précédente fait minimum 700 mots** (compter les mots avant de passer à la suivante)
2. **Si une section fait moins de 700 mots, AJOUTER immédiatement** : exemples concrets, détails techniques approfondis, conseils d'experts, données chiffrées précises, cas pratiques réels, comparaisons détaillées, procédures étape par étape, avantages/inconvénients détaillés, coûts et durées, matériaux et techniques, normes et réglementations
3. **Vérifier que TOUTES les sections du sommaire sont présentes** (compter les sections H2 et comparer avec le sommaire)
4. **Vérifier que les sections sont dans l'ORDRE du sommaire** (section-1, section-2, section-3, etc.)
5. **AUCUNE section ne doit être sautée ou omise**, même si cela rend l'article très long
6. **AUCUNE section ne doit être vide** - Si tu ne sais pas quoi écrire pour une section, développe quand même avec : contexte général, processus détaillé, conseils pratiques, exemples concrets, informations utiles
7. **AVANT de créer un titre H2, s'assurer d'avoir au moins 700 mots de contenu prêt à écrire** pour cette section

**EXIGENCES PAR SECTION (CRITIQUE - RESPECTER STRICTEMENT) :**
- 🚨 **700-900 mots MINIMUM par section H2** (pas de section avec seulement 2-3 lignes - INTERDIT)
- 🚨 **TOUTES les sections du sommaire DOIVENT être développées en profondeur** (aucune exception)
- 🚨 **AUCUNE section ne doit être manquante ou trop courte** (vérifier avant de terminer)
- 🚨 **Les sections DOIVENT être dans l'ORDRE du sommaire** (section-1, puis section-2, puis section-3, etc.)
- 🚨 **Ne JAMAIS passer à la section suivante si la section actuelle fait moins de 700 mots**
- 4-6 sous-titres H3 par section (pour approfondir chaque aspect)
- Au moins 2 listes (puces ou numérotées) par section
- 2-3 éléments enrichis (encadrés, tableaux, citations) par section
- 3-4 variantes sémantiques du mot-clé par section
- Transitions fluides entre paragraphes
- Exemples concrets et données chiffrées dans chaque section

**4. CTA STRATÉGIQUES** (2-3 dans l'article)

**CTA Milieu d'article (après 40% contenu) :**
```html
<div class="cta-inline">
  <p>💼 <strong>Projet de {$keyword} à {$city} ?</strong> Nos experts certifiés vous accompagnent de A à Z pour un résultat parfait et durable.</p>
  <p class="cta-buttons">
    <a href="{$devisUrl}" class="btn-secondary">📝 Devis gratuit personnalisé</a>
    <span class="cta-phone">ou appelez <a href="tel:{$companyPhoneRaw}">{$companyPhone}</a></span>
  </p>
</div>
```

**⚠️ IMPORTANT - CTA FINAL :**
❌ NE PAS générer de section CTA finale avec "Lancez Votre Projet" ou "En Résumé"
❌ NE PAS inclure de section "article-conclusion" ou "cta-final"
✅ Ces sections sont gérées automatiquement par le système et seront ajoutées après le contenu
✅ Terminer l'article directement après la FAQ, sans CTA ni conclusion

**5. FAQ SCHEMA.ORG** (10-12 questions MINIMUM)

```html
<section id="faq" itemscope itemtype="https://schema.org/FAQPage">
  <h2>❓ Questions Fréquentes : {$keyword} à {$city}</h2>
  
  <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
    <h3 itemprop="name">Quel est le prix moyen d'un {$keyword} à {$city} en {$currentYear} ?</h3>
    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
      <p itemprop="text">Le tarif pour {$keyword} à {$city} varie considérablement selon l'ampleur et le type de travaux. 

**IMPORTANT :** Utilise les exemples de fourchettes réalistes fournis dans la section "RECHERCHE APPROFONDIE DES PRIX" du prompt pour générer une réponse précise avec PLUSIEURS fourchettes selon le type de prestation.

**Format de réponse attendu :**
- Commencer par expliquer que les prix varient selon le type de prestation
- Fournir PLUSIEURS fourchettes détaillées (petite réparation, réparation moyenne, rénovation complète, etc.)
- Mentionner les facteurs qui influencent le prix (superficie, matériaux, complexité, accessibilité)
- Adapter les prix à la région {$city} si pertinente
- Conclure en mentionnant que {$companyName} propose des devis gratuits et transparents

**Exemple de structure :**
Pour une petite réparation (remplacement de quelques tuiles, réparation ponctuelle), comptez entre 500€ et 2000€. Pour une réparation moyenne (réfection partielle, zinguerie), la fourchette se situe entre 2000€ et 8000€. Pour une rénovation complète de toiture (dépose ancienne couverture, charpente si nécessaire, couverture neuve), l'investissement varie entre 8000€ et 25000€ pour une maison moyenne de 100-150m². Si vous optez pour une rénovation complète avec isolation thermique, prévoyez entre 12000€ et 35000€. Les prix dépendent de nombreux facteurs : la superficie à traiter, les matériaux choisis (tuiles, ardoise, zinc, etc.), la complexité technique (accessibilité, pente, hauteur), l'état initial de la charpente, et les finitions souhaitées. {$companyName} propose des devis gratuits et transparents détaillant chaque poste de dépense, permettant ainsi de comprendre précisément l'investissement nécessaire pour votre projet à {$city}.</p>
    </div>
  </div>
  
  <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
    <h3 itemprop="name">Quels sont les délais d'intervention pour {$keyword} à {$city} ?</h3>
    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
      <p itemprop="text">Les délais standards pour {$keyword} à {$city} sont de [X] à [Y] jours ouvrés après validation du devis. En cas d'urgence (fuite, sinistre), {$companyName} intervient sous 24-48h. La durée des travaux elle-même varie de [A] à [B] jours selon l'ampleur du projet. Nous privilégions la qualité à la vitesse pour garantir un résultat durable.</p>
    </div>
  </div>
  
  <!-- AJOUTER 8-10 QUESTIONS SUPPLÉMENTAIRES -->
  <!-- Questions recommandées : certifications, zone d'intervention, garanties, matériaux recommandés, saison idéale, entretien, aides financières, assurances, durée de vie, SAV -->
  
</section>
```

**⚠️ RÈGLES ABSOLUES FAQ :**
- 10-12 questions minimum
- Réponses complètes 60-120 mots chacune
- Format Schema.org PARFAIT (balises complètes et fermées)
- Intégrer naturellement mot-clé + ville
- Répondre précisément (chiffres, dates, faits)
- Couvrir objections clients (prix, délais, qualité, garanties)

**6. FIN DE L'ARTICLE :**
⚠️ **NE PAS générer de section conclusion ou CTA final**
⚠️ **Terminer l'article directement après la FAQ**
✅ La section CTA finale et conclusion sont gérées automatiquement par le système
✅ Arrêter le contenu après la balise </section> de la FAQ

{$imagesContext}

{$internalLinksContext}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ **CHECKLIST QUALITÉ SEO 95%+ (NON NÉGOCIABLE)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**OPTIMISATION MOTS-CLÉS :**
✅ Mot-clé principal "{$keyword}" : 8-12 occurrences (densité 0.8-1.2%)
✅ Première occurrence dans les 100 premiers mots
✅ Présence dans 60-70% des titres H2
✅ 15-25 variantes sémantiques naturelles
✅ Localisation "{$city}" : 10-15 occurrences (mais JAMAIS répétée deux fois dans la même phrase)
✅ ZÉRO sur-optimisation (chaque phrase sonne naturelle)
✅ **INTERDIT de répéter la ville** : "à {$city} à {$city}" ou "{$city} à {$city}" est FORMELLEMENT INTERDIT

**STRUCTURE & LISIBILITÉ :**
✅ 6-8 sections H2 avec IDs uniques
✅ 12-20 sous-sections H3
✅ Paragraphes 3-5 lignes maximum
✅ Phrases 15-25 mots en moyenne (80%+ des phrases)
✅ Voix active 85%+ du temps
✅ Transitions fluides (connecteurs logiques)
✅ 5-8 listes à puces/numérotées
✅ 2-4 tableaux comparatifs
✅ 3-5 encadrés enrichis (info-box, tip-box, warning-box)

**E-E-A-T (Expertise, Experience, Authority, Trust) :**
✅ 4-6 exemples concrets/cas pratiques
✅ 5-8 données chiffrées précises
✅ 3-5 citations d'expert ou témoignages
✅ Mentions normes/réglementations (DTU, RGE, RT2020)
✅ Transparence totale (prix, délais, processus)
✅ Preuves sociales ([X] clients, [Y] ans d'expérience)

**ÉLÉMENTS TECHNIQUES :**
✅ HTML5 sémantique valide W3C
✅ Toutes images avec alt="[description] - {$keyword} à {$city}" + loading="lazy"
✅ 6-10 liens internes pertinents
✅ CTA stratégiques (2-3) avec URLs correctes
✅ FAQ Schema.org parfaitement formé (10-12 questions)
✅ Attributs accessibilité (aria-label sur <nav>)

**ENGAGEMENT & CONVERSION :**
✅ Ton professionnel mais accessible
✅ Langage bénéfices client (pas features produit)
✅ Appels à l'action clairs et motivants
✅ Réponses complètes aux objections
✅ Guidage vers décision d'achat subtil

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
❌ **INTERDICTIONS ABSOLUES**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🚫 JAMAIS de contenu de remplissage (fluff)
🚫 JAMAIS de duplication concurrentielle
🚫 JAMAIS de keyword stuffing mécanique
🚫 JAMAIS de promesses exagérées non vérifiables
🚫 JAMAIS de phrases >30 mots
🚫 JAMAIS de jargon non expliqué
🚫 JAMAIS de HTML mal formé ou invalide
🚫 JAMAIS de balises Schema.org cassées
🚫 JAMAIS de texte méta type "Ce contenu HTML intègre..." (INTERDIT)
🚫 JAMAIS de texte type "Ce modèle HTML est conçu..." (INTERDIT)
🚫 JAMAIS de texte type "système de gestion de contenu" ou "directives SEO" (INTERDIT)
🚫 JAMAIS de placeholders [À remplir] ou [Exemple]
🚫 JAMAIS d'informations génériques non spécifiques à {$city}
🚫 JAMAIS de répétition de la ville dans la même phrase (ex: "à {$city} à {$city}" est INTERDIT)
🚫 JAMAIS de phrases contenant "modèle HTML", "système de gestion", "intégré dans un CMS" (INTERDIT)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📝 **FORMAT DE SORTIE STRICT**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Retourne UNIQUEMENT le HTML pur, sans :
- ❌ Balises <html>, <head>, <body>, <!DOCTYPE>
- ❌ Titre H1 (géré séparément)
- ❌ Scripts, styles CSS inline
- ❌ Commentaires "Ce contenu HTML..." ou méta-descriptions de l'article
- ❌ Texte avant/après le HTML
- ❌ Textes méta type "Ce modèle HTML est conçu pour être intégré..."
- ❌ Textes type "système de gestion de contenu", "directives SEO", "meilleures pratiques"
- ❌ Répétitions de la ville dans la même phrase (ex: "à {$city} à {$city}")

**Structure finale attendue :**
```html
<div class="article-intro">...</div>
<nav class="table-of-contents">...</nav>
<section id="section-1">...</section>
<section id="section-2">...</section>
<!-- ... autres sections ... -->
<div class="cta-inline">...</div>
<section id="section-5">...</section>
<section id="faq" itemscope itemtype="https://schema.org/FAQPage">...</section>
<!-- FIN - Ne pas ajouter de CTA final ni conclusion, c'est géré automatiquement -->
```

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎯 **TON OBJECTIF ULTIME**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Créer l'article de référence ABSOLU sur "{$keyword}" à {$city} :
- Qui se classe #1 Google dans les 3 mois
- Qui convertit 5-12% des visiteurs en prospects
- Qui obtient 6-9 minutes de temps de lecture
- Qui génère des partages et backlinks naturels
- Qui fait dire aux lecteurs : "C'est LE meilleur guide que j'ai lu"

**🚨 RAPPEL CRITIQUE ABSOLU AVANT RÉDACTION - RÈGLES NON NÉGOCIABLES :**

**⚠️ INTERDICTION ABSOLUE DE CRÉER DES SECTIONS VIDES OU INCOMPLÈTES :**

1. **TOUTES les sections listées dans le sommaire DOIVENT être développées intégralement (700-900 mots chacune)**
2. **Les sections DOIVENT être développées DANS L'ORDRE du sommaire** (section-1, puis section-2, puis section-3, etc.)
3. **AUCUNE section ne doit être omise, même si l'article devient très long (3000+ mots si nécessaire)**
4. **AUCUNE section ne doit avoir seulement 2-3 lignes ou être vide** (INTERDIT ABSOLUMENT - chaque section doit être complète)
5. **INTERDIT de mettre seulement un titre H2 sans contenu en dessous** (exemple INTERDIT : `<h2>Comment obtenir votre devis ?</h2>` suivi de rien)
6. **INTERDIT de mettre des placeholders ou des descriptions** (exemple INTERDIT : "Cette section sera développée plus tard" ou "Contenu à venir")
7. **Vérifier que chaque section H2 fait minimum 700 mots AVANT de passer à la suivante** (compter les mots)
8. **Si une section est trop courte, AJOUTER immédiatement** : exemples concrets détaillés, détails techniques approfondis, conseils d'experts, données chiffrées précises, cas pratiques réels, comparaisons détaillées, témoignages, statistiques, procédures étape par étape, avantages/inconvénients, coûts détaillés, durées, matériaux, techniques, normes, réglementations
9. **L'article total doit faire minimum {$targetWordCount} mots** (idéalement 3500-4500 mots pour couvrir toutes les sections)
10. **Ne JAMAIS terminer l'article avant d'avoir développé TOUTES les sections du sommaire**
11. **Chaque section doit contenir au minimum :**
    - 3-5 paragraphes de 100-150 mots chacun
    - Des exemples concrets et détaillés
    - Des informations pratiques et actionnables
    - Des données chiffrées ou statistiques si pertinentes
    - Des conseils d'experts ou des bonnes pratiques
    - Des sous-sections H3 si nécessaire pour structurer

**PROCESSUS DE VÉRIFICATION OBLIGATOIRE AVANT CHAQUE SECTION :**
1. Lire le titre de la section dans le sommaire
2. Créer le titre H2 correspondant avec l'ID unique (section-1, section-2, etc.)
3. ÉCRIRE IMMÉDIATEMENT le contenu complet (700-900 mots minimum)
4. Compter les mots de la section (doit être ≥ 700)
5. Si < 700 mots, AJOUTER immédiatement : détails supplémentaires, exemples, conseils, données
6. Vérifier que la section contient du contenu réel et utile (pas de remplissage vide)
7. SEULEMENT APRÈS avoir complété la section, passer à la suivante

**PROCESSUS DE VÉRIFICATION FINALE OBLIGATOIRE AVANT DE TERMINER :**
1. Compter toutes les sections H2 dans l'article
2. Comparer avec le nombre de sections dans le sommaire
3. Vérifier que CHAQUE section du sommaire a son équivalent H2 développé dans l'article
4. Vérifier que CHAQUE section H2 fait minimum 700 mots
5. Si une section manque ou est trop courte, AJOUTER du contenu immédiatement
6. Ne JAMAIS envoyer l'article si une section est manquante ou incomplète

**EXEMPLE DE CE QUI EST INTERDIT (NE JAMAIS FAIRE CELA) :**
```html
<h2 id="section-2">Comment obtenir votre devis ?</h2>
<!-- Section vide - INTERDIT -->
```

**EXEMPLE DE CE QUI EST OBLIGATOIRE (FAIRE TOUJOURS CELA) :**
```html
<h2 id="section-2">Comment obtenir votre devis ?</h2>
<p>Pour obtenir un devis personnalisé pour votre projet de zinguerie moderne à Chevigny-Saint-Sauveur, plusieurs options s'offrent à vous...</p>
<p>La première étape consiste à...</p>
<!-- Minimum 700 mots de contenu détaillé et utile -->
```

**RÉDIGE MAINTENANT** cet article exceptionnel de {$targetWordCount}+ mots. Chaque mot doit apporter de la valeur. Chaque section doit éduquer ET persuader. Chaque élément doit être optimisé pour le SEO ET l'humain. **TOUTES les sections du sommaire doivent être complètes et détaillées (700-900 mots chacune). AUCUNE section vide ou incomplète ne sera acceptée.**

🚀 **C'EST PARTI. Produis le meilleur contenu SEO jamais créé sur ce sujet.**
EOT;

        return $prompt;
    }
    
    /**
     * Construire le contexte des images
     */
    protected function buildImagesContext($keywordImages, $keyword, $city)
    {
        if (empty($keywordImages)) {
            return '';
        }
        
        $context = "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "📸 **IMAGES DISPONIBLES (INTÉGRATION OBLIGATOIRE)**\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        foreach ($keywordImages as $index => $img) {
            $title = $img['title'] ?? 'Image';
            $path = $img['path'] ?? '';
            $context .= "**Image #" . ($index + 1) . " :**\n";
            $context .= "- Titre : {$title}\n";
            $context .= "- Chemin : {$path}\n";
            $context .= "- ALT optimisé : \"{$title} - {$keyword} à {$city}\"\n\n";
        }
        
        $context .= "**📌 RÈGLES D'INTÉGRATION :**\n";
        $context .= "1. Intégrer TOUTES les images stratégiquement (après intro, milieu sections, avant FAQ)\n";
        $context .= "2. Format exact : `<img src=\"{PATH}\" alt=\"{TITLE} - {$keyword} à {$city}\" class=\"article-image\" loading=\"lazy\" />`\n";
        $context .= "3. Placer chaque image APRÈS le paragraphe qui l'introduit\n";
        $context .= "4. Espacer les images (1 image tous les 500-700 mots)\n";
        $context .= "5. ALT text descriptif et optimisé SEO\n\n";
        
        $context .= "**Exemple d'intégration correcte :**\n";
        $context .= "```html\n";
        $context .= "<p>Le choix des matériaux est crucial pour la durabilité de votre {$keyword}...</p>\n";
        $context .= "<img src=\"/storage/images/exemple.jpg\" alt=\"Matériaux premium pour {$keyword} à {$city}\" class=\"article-image\" loading=\"lazy\" />\n";
        $context .= "<p>Comme vous pouvez le constater sur l'image ci-dessus...</p>\n";
        $context .= "```\n";
        
        return $context;
    }
    
    /**
     * Construire le contexte des mots-clés connexes
     */
    protected function buildRelatedKeywordsContext($relatedKeywords)
    {
        if (empty($relatedKeywords)) {
            return '';
        }
        
        $context = "\n**🔑 MOTS-CLÉS SÉMANTIQUES À INTÉGRER NATURELLEMENT :**\n";
        $context .= "_(Ces termes renforcent la pertinence thématique et le champ lexical)_\n\n";
        
        $chunks = array_chunk($relatedKeywords, 8);
        foreach ($chunks as $chunk) {
            $context .= "- " . implode(", ", $chunk) . "\n";
        }
        
        $context .= "\n**📍 MODE D'EMPLOI :**\n";
        $context .= "- Intégrer 12-18 de ces termes naturellement dans le contenu\n";
        $context .= "- Ne JAMAIS forcer leur utilisation (priorité à la fluidité)\n";
        $context .= "- Les utiliser dans les contextes appropriés\n";
        $context .= "- Varier les formes grammaticales (singulier/pluriel, verbe/nom)\n";
        
        return $context;
    }
    
    /**
     * Formater les gaps concurrentiels
     */
    protected function formatCompetitorGaps($gaps)
    {
        if (empty($gaps)) {
            return "Tous les sujets principaux sont couverts par les concurrents. Différenciation par la PROFONDEUR et la QUALITÉ.";
        }
        
        $formatted = "**Sujets NON ou MAL traités par les concurrents (opportunités en OR) :**\n";
        foreach ($gaps as $index => $gap) {
            $formatted .= "🎯 " . ($index + 1) . ". {$gap} — **CRÉER UNE SECTION DÉDIÉE**\n";
        }
        
        return $formatted;
    }
    
    /**
     * Formater les résultats SERP pour l'analyse approfondie dans le prompt
     */
    protected function formatSerpResultsForAnalysis($serpResults, $keyword = '')
    {
        if (empty($serpResults)) {
            $keywordText = !empty($keyword) ? "le sujet '{$keyword}'" : "ce sujet";
            return "Aucun résultat SERP fourni. Effectue une recherche approfondie sur {$keywordText} pour identifier les sujets importants, les questions fréquentes, et les angles d'approche utilisés par les concurrents.";
        }
        
        $formatted = "**Analyse détaillée de chaque résultat concurrent :**\n\n";
        
        foreach ($serpResults as $index => $result) {
            $title = $result['title'] ?? 'Sans titre';
            $snippet = $result['snippet'] ?? 'Aucun extrait disponible';
            $link = $result['link'] ?? '';
            
            $formatted .= "**Concurrent #" . ($index + 1) . " :**\n";
            $formatted .= "- **Titre :** {$title}\n";
            $formatted .= "- **Extrait :** {$snippet}\n";
            if (!empty($link)) {
                $formatted .= "- **URL :** {$link}\n";
            }
            $formatted .= "\n";
            $formatted .= "**À analyser :**\n";
            $formatted .= "- Quels sujets sont traités dans ce résultat ?\n";
            $formatted .= "- Quel angle d'approche est utilisé ?\n";
            $formatted .= "- Quelles informations sont présentes ou manquantes ?\n";
            $formatted .= "- Quelle est la structure apparente du contenu ?\n";
            $formatted .= "- Quelles questions sont abordées ou non abordées ?\n";
            $formatted .= "\n";
        }
        
        $formatted .= "**🎯 SYNTHÈSE À FAIRE :**\n";
        $formatted .= "Après avoir analysé tous les concurrents ci-dessus, identifie :\n";
        $formatted .= "1. Les sujets les plus importants à couvrir (basés sur ce que les concurrents traitent)\n";
        $formatted .= "2. Les angles uniques à développer (ce que les concurrents ne font pas bien)\n";
        $formatted .= "3. Les informations manquantes ou incomplètes chez les concurrents\n";
        $formatted .= "4. La meilleure structure d'article (basée sur ce qui fonctionne, mais améliorée)\n";
        $formatted .= "5. Les questions non répondues ou mal répondues par les concurrents\n";
        $formatted .= "\n";
        $formatted .= "**⚠️ CRITIQUE :** Utilise cette analyse pour créer une structure UNIQUE et PERTINENTE, pas une structure générique ou répétitive.\n";
        
        return $formatted;
    }
    
    /**
     * Imploder les mots-clés pour affichage
     */
    protected function implodeKeywords($keywords)
    {
        if (empty($keywords)) {
            return "artisan, professionnel, expert, certifié";
        }
        return implode(", ", array_slice($keywords, 0, 6));
    }
    
    /**
     * Extraire des insights des résultats SERP (version améliorée)
     */
    protected function extractSerpInsights($serpResults)
    {
        $topics = [];
        $questions = [];
        $wordCounts = [];
        
        if (empty($serpResults)) {
            return [
                'topics' => [
                    'Présentation complète des services et expertise métier',
                    'Grille tarifaire détaillée et facteurs de prix',
                    'Zone d\'intervention et disponibilités rapides',
                    'Certifications professionnelles et garanties décennales',
                    'Processus de réalisation détaillé étape par étape',
                    'Guide de sélection des matériaux et technologies',
                    'Réglementation et normes en vigueur',
                    'Aides financières et solutions de financement',
                ],
                'questions' => [
                    'Quel est le coût moyen pour ce service ?',
                    'Quels sont les délais d\'intervention standards ?',
                    'Quelles certifications et assurances possédez-vous ?',
                    'Quelle est votre zone d\'intervention géographique ?',
                    'Quelles garanties proposez-vous sur les travaux ?',
                    'Quels matériaux recommandez-vous et pourquoi ?',
                    'Comment se déroule concrètement le chantier ?',
                    'Peut-on bénéficier d\'aides financières ou de subventions ?',
                    'Quelle est la durée de vie moyenne des installations ?',
                    'Proposez-vous un service après-vente et un suivi ?',
                ],
                'avg_word_count' => 2000
            ];
        }
        
        // Patterns améliorés pour extraction
        $topicPatterns = [
            'prix|tarif|coût|budget|financement' => 'Tarification transparente et options de financement',
            'étape|processus|déroulement|procédure' => 'Processus de réalisation détaillé',
            'matériau|matière|produit|équipement' => 'Guide des matériaux et équipements',
            'comparatif|meilleur|top|choix' => 'Comparatifs et recommandations d\'experts',
            'certification|rge|qualibat|label' => 'Certifications et qualifications professionnelles',
            'garantie|assurance|décennale' => 'Garanties et couvertures assurantielles',
            'aide|subvention|crédit|prime' => 'Aides financières et dispositifs de soutien',
            'réglementation|norme|dtu|rt2020' => 'Normes et réglementations en vigueur',
            'erreur|éviter|piège|attention' => 'Erreurs courantes et pièges à éviter',
            'entretien|maintenance|durée' => 'Entretien et maintenance préventive',
        ];
        
        foreach ($serpResults as $result) {
            $content = strtolower(($result['title'] ?? '') . ' ' . ($result['snippet'] ?? ''));
            
            // Extraction topics
            foreach ($topicPatterns as $pattern => $topic) {
                if (preg_match('/\b(' . $pattern . ')\b/i', $content)) {
                    $topics[] = $topic;
                }
            }
            
            // Extraction questions améliorée
            $questionPatterns = [
                '/\b(combien (coûte|coute)|quel (est le )?(prix|tarif|coût))[^.?!]{5,80}[?]/ui',
                '/\b(quels? sont les? (délais?|temps))[^.?!]{5,80}[?]/ui',
                '/\b(comment (choisir|faire|procéder))[^.?!]{5,80}[?]/ui',
                '/\b(pourquoi (faire|choisir))[^.?!]{5,80}[?]/ui',
                '/\b(où (trouver|acheter))[^.?!]{5,80}[?]/ui',
                '/\b(qui (contacter|appeler))[^.?!]{5,80}[?]/ui',
            ];
            
            foreach ($questionPatterns as $pattern) {
                if (preg_match_all($pattern, $result['snippet'] ?? '', $matches)) {
                    foreach ($matches[0] as $question) {
                        $question = trim($question);
                        if (strlen($question) > 15 && strlen($question) < 150) {
                            $questions[] = ucfirst($question);
                        }
                    }
                }
            }
            
            // Estimation word count
            if (isset($result['word_count']) && $result['word_count'] > 0) {
                $wordCounts[] = $result['word_count'];
            } else if (isset($result['snippet'])) {
                $estimatedCount = str_word_count($result['snippet']) * 18;
                if ($estimatedCount > 800 && $estimatedCount < 5000) {
                    $wordCounts[] = $estimatedCount;
                }
            }
        }
        
        $topics = array_unique($topics);
        $questions = array_unique($questions);
        $questions = array_slice($questions, 0, 12);
        
        // Questions par défaut si insuffisantes
        $defaultQuestions = [
            'Combien coûte ce service en moyenne ?',
            'Quels sont les délais d\'intervention habituels ?',
            'Êtes-vous certifiés et assurés ?',
            'Quelle zone géographique couvrez-vous ?',
            'Quelles garanties proposez-vous ?',
            'Quels matériaux utilisez-vous et recommandez-vous ?',
            'Comment se déroule le chantier concrètement ?',
            'Peut-on bénéficier d\'aides financières ?',
            'Quelle est la durée de vie moyenne ?',
            'Proposez-vous un service après-vente ?',
        ];
        
        if (count($questions) < 8) {
            $questions = array_merge($questions, $defaultQuestions);
            $questions = array_unique($questions);
            $questions = array_slice($questions, 0, 12);
        }
        
        $avgWordCount = !empty($wordCounts) ? (int)(array_sum($wordCounts) / count($wordCounts)) : 2000;
        $avgWordCount = max(1500, min(3500, $avgWordCount));
        
        return [
            'topics' => array_values($topics),
            'questions' => array_values($questions),
            'avg_word_count' => $avgWordCount
        ];
    }
    
    /**
     * Construire le contexte des liens internes (version améliorée)
     */
    protected function buildInternalLinksContext($keyword, $city)
    {
        $servicesData = Setting::where('key', 'services')->value('value');
        $services = [];
        
        if (!empty($servicesData)) {
            $decoded = json_decode($servicesData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $services = $decoded;
            }
        }
        
        $context = "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        $context .= "🔗 **LIENS INTERNES STRATÉGIQUES (MAILLAGE SEO)**\n";
        $context .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        $context .= "**OBJECTIF :** Intégrer 6-10 liens internes pertinents pour améliorer le maillage SEO.\n\n";
        
        if (!empty($services)) {
            $context .= "**🏗️ SERVICES CONNEXES DISPONIBLES :**\n";
            foreach (array_slice($services, 0, 10) as $service) {
                $serviceName = $service['name'] ?? 'Service';
                $serviceSlug = \Illuminate\Support\Str::slug($serviceName);
                try {
                    $serviceUrl = route('services.show', ['slug' => $serviceSlug]);
                } catch (\Exception $e) {
                    $serviceUrl = url('/services/' . $serviceSlug);
                }
                $context .= "- **{$serviceName}** → `<a href=\"{$serviceUrl}\">{$serviceName} à {$city}</a>`\n";
            }
            $context .= "\n";
        }
        
        $context .= "**📄 PAGES PRINCIPALES :**\n";
        try {
            $contactUrl = route('contact');
            $portfolioUrl = route('portfolio.index');
            $blogUrl = route('blog.index');
            $servicesIndexUrl = route('services.index');
        } catch (\Exception $e) {
            $contactUrl = url('/contact');
            $portfolioUrl = url('/nos-realisations');
            $blogUrl = url('/blog');
            $servicesIndexUrl = url('/services');
        }
        
        $context .= "- Contact → `<a href=\"{$contactUrl}\">Demandez votre devis gratuit</a>`\n";
        $context .= "- Réalisations → `<a href=\"{$portfolioUrl}\">Consultez nos projets récents à {$city}</a>`\n";
        $context .= "- Blog → `<a href=\"{$blogUrl}\">Tous nos conseils d'experts</a>`\n";
        $context .= "- Services → `<a href=\"{$servicesIndexUrl}\">Découvrez tous nos services</a>`\n\n";
        
        $context .= "**📋 RÈGLES DE MAILLAGE INTERNE :**\n";
        $context .= "1. **Ancres descriptives** : Jamais \"cliquez ici\" ou \"en savoir plus\"\n";
        $context .= "2. **Intégration naturelle** : Dans le flux du texte, pas artificiellement\n";
        $context .= "3. **Répartition équilibrée** : 1 lien tous les 300-400 mots\n";
        $context .= "4. **Pertinence absolue** : Lier seulement si connexion logique\n";
        $context .= "5. **Variété des ancres** : Ne jamais répéter la même ancre\n";
        $context .= "6. **Valeur ajoutée** : Le lien doit enrichir l'expérience lecteur\n\n";
        
        $context .= "**✅ EXEMPLES D'INTÉGRATION PARFAITE :**\n";
        $context .= "```html\n";
        $context .= "<!-- Bon : Ancre descriptive, contexte naturel -->\n";
        $context .= "<p>Pour compléter votre projet, découvrez nos <a href=\"{$servicesIndexUrl}\">solutions d'isolation thermique des combles</a> qui s'intègrent parfaitement avec {$keyword}.</p>\n\n";
        $context .= "<!-- Bon : Lien vers réalisations pour preuve sociale -->\n";
        $context .= "<p>Notre équipe a réalisé plus de 200 projets similaires. Consultez <a href=\"{$portfolioUrl}\">nos dernières réalisations de {$keyword} à {$city}</a> pour vous inspirer.</p>\n\n";
        $context .= "<!-- Bon : CTA vers contact dans contexte approprié -->\n";
        $context .= "<p>Besoin d'un conseil personnalisé pour votre projet ? <a href=\"{$contactUrl}\">Contactez nos experts certifiés</a> pour un diagnostic gratuit.</p>\n";
        $context .= "```\n\n";
        
        $context .= "**❌ À ÉVITER ABSOLUMENT :**\n";
        $context .= "```html\n";
        $context .= "<!-- Mauvais : Ancre générique -->\n";
        $context .= "<p>Pour en savoir plus, <a href=\"/services\">cliquez ici</a>.</p>\n\n";
        $context .= "<!-- Mauvais : Lien non pertinent -->\n";
        $context .= "<p>Les tuiles sont importantes. Visitez <a href=\"/plomberie\">notre page plomberie</a>.</p>\n\n";
        $context .= "<!-- Mauvais : Sur-optimisation -->\n";
        $context .= "<p>Notre <a href=\"/keyword\">{$keyword}</a> à <a href=\"/keyword\">{$city}</a> est le meilleur <a href=\"/keyword\">{$keyword}</a>.</p>\n";
        $context .= "```\n";
        
        return $context;
    }
    
    /**
     * Formater les topics pour le prompt
     */
    protected function formatTopics($topics)
    {
        if (empty($topics)) {
            return "- Services et prestations détaillées\n- Tarification et options de financement\n- Zone d'intervention et disponibilités\n- Certifications et garanties professionnelles\n- Processus de réalisation complet\n- Matériaux et techniques utilisés";
        }
        
        $formatted = '';
        foreach (array_slice($topics, 0, 10) as $index => $topic) {
            $formatted .= "✓ " . ($index + 1) . ". {$topic}\n";
        }
        
        return rtrim($formatted);
    }
    
    /**
     * Formater les questions pour le prompt
     */
    protected function formatQuestions($questions)
    {
        if (empty($questions)) {
            return "1. Combien coûte ce service en moyenne ?\n2. Quels sont les délais d'intervention ?\n3. Êtes-vous certifiés et assurés ?\n4. Quelle est votre zone d'intervention ?\n5. Proposez-vous des garanties décennales ?\n6. Quels matériaux recommandez-vous ?\n7. Comment se déroule le chantier ?\n8. Peut-on bénéficier d'aides financières ?";
        }
        
        $formatted = '';
        foreach (array_slice($questions, 0, 12) as $index => $question) {
            $question = trim($question);
            if (!empty($question)) {
                $formatted .= "❓ " . ($index + 1) . ". {$question}\n";
            }
        }
        
        return rtrim($formatted);
    }
}