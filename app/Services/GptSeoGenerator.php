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
        $this->maxTokens = 4000; // Pour des articles longs et détaillés
        $this->temperature = 0.7; // Équilibre créativité/précision
    }
    
    /**
     * Générer un article SEO complet optimisé
     */
    public function generateSeoArticle($keyword, $city, $serpResults = [], $keywordImages = [])
    {
        try {
            Log::info('Génération article SEO', [
                'keyword' => $keyword,
                'city' => $city,
                'serp_count' => count($serpResults),
                'images_count' => count($keywordImages)
            ]);
            
            // Étape 1 : Générer le titre optimisé
            $titre = $this->generateTitle($keyword, $city);
            
            // Étape 2 : Générer la meta description
            $metaDescription = $this->generateMetaDescription($keyword, $city, $titre);
            
            // Étape 3 : Générer le contenu HTML complet
            $contenuHtml = $this->generateHtmlContent($keyword, $city, $serpResults, $keywordImages, $titre);
            
            // Étape 4 : Générer le slug
            $slug = \Illuminate\Support\Str::slug($titre);
            
            return [
                'titre' => $titre,
                'slug' => $slug,
                'meta_description' => $metaDescription,
                'contenu_html' => $contenuHtml,
                'keyword' => $keyword,
                'city' => $city,
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
     * Générer un titre SEO optimisé et accrocheur
     */
    protected function generateTitle($keyword, $city)
    {
        $prompt = <<<EOT
Génère un titre SEO parfait pour un article sur "{$keyword}" à {$city}.

**Critères obligatoires :**
- Longueur : 50-60 caractères (optimal pour SERP)
- Intégrer naturellement le mot-clé principal "{$keyword}"
- Inclure la localisation "{$city}"
- Être accrocheur et inciter au clic
- Formulé de manière naturelle et professionnelle
- Transmettre une promesse de valeur claire

**Formats gagnants :**
- "{$keyword} à {$city} : Guide Complet [Année]"
- "{$keyword} {$city} | Expert Certifié [Spécialité]"
- "Tout Savoir sur {$keyword} à {$city}"
- "{$keyword} à {$city} : Prix, Devis & Conseils Pro"

**Exemples de titres optimisés :**
- "Rénovation Toiture Paris : Guide Expert 2025"
- "Couvreur Dijon | Artisan Certifié RGE"
- "Isolation Combles Lyon : Prix & Devis Gratuit"

Retourne UNIQUEMENT le titre, sans guillemets, sans explications.
EOT;

        $systemMessage = "Tu es un expert en rédaction de titres SEO percutants pour le secteur du bâtiment.";
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 100,
            'temperature' => 0.8,
        ]);
        
        $titre = trim($result['content'] ?? '');
        $titre = trim($titre, '"\'');
        
        // Fallback si échec
        if (empty($titre)) {
            $titre = ucfirst($keyword) . " à " . $city . " : Guide Expert " . date('Y');
        }
        
        // Vérifier la longueur (max 60 caractères)
        if (strlen($titre) > 60) {
            $titre = substr($titre, 0, 57) . '...';
        }
        
        Log::info('Titre généré', ['titre' => $titre, 'length' => strlen($titre)]);
        
        return $titre;
    }
    
    /**
     * Générer une meta description optimisée
     */
    protected function generateMetaDescription($keyword, $city, $titre)
    {
        $companyName = config('app.name', 'Notre Entreprise');
        
        $prompt = <<<EOT
Génère une meta description SEO parfaite pour cet article :

**Titre :** {$titre}
**Mot-clé :** {$keyword}
**Ville :** {$city}
**Entreprise :** {$companyName}

**Critères obligatoires :**
- Longueur : 150-160 caractères (optimal pour SERP)
- Intégrer naturellement le mot-clé "{$keyword}"
- Inclure la localisation "{$city}"
- Inclure un appel à l'action subtil (devis, contact, conseil)
- Être persuasive et inciter au clic
- Transmettre la valeur unique de l'article

**Structure gagnante :**
[Promesse de valeur] + {$keyword} à {$city} + [Bénéfice client] + [CTA]

**Exemples optimisés :**
- "Découvrez notre expertise en {$keyword} à {$city}. Devis gratuit, artisans certifiés, intervention rapide. Contactez-nous !"
- "Expert {$keyword} à {$city} : conseils, tarifs transparents, garanties. Obtenez votre devis gratuit en 24h."
- "{$keyword} à {$city} : guide complet par des professionnels. Prix, délais, conseils d'experts. Devis gratuit."

Retourne UNIQUEMENT la meta description, sans guillemets, sans explications.
EOT;

        $systemMessage = "Tu es un expert en rédaction de meta descriptions SEO pour le secteur du bâtiment.";
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 150,
            'temperature' => 0.7,
        ]);
        
        $metaDescription = trim($result['content'] ?? '');
        $metaDescription = trim($metaDescription, '"\'');
        
        // Fallback si échec
        if (empty($metaDescription)) {
            $metaDescription = "Expert en {$keyword} à {$city}. Devis gratuit, artisans qualifiés, intervention rapide. Contactez {$companyName} pour tous vos projets.";
        }
        
        // Vérifier la longueur (max 160 caractères)
        if (strlen($metaDescription) > 160) {
            $metaDescription = substr($metaDescription, 0, 157) . '...';
        }
        
        Log::info('Meta description générée', ['length' => strlen($metaDescription)]);
        
        return $metaDescription;
    }
    
    /**
     * Générer le contenu HTML complet de l'article
     */
    protected function generateHtmlContent($keyword, $city, $serpResults, $keywordImages, $titre)
    {
        $prompt = $this->buildHtmlContentPrompt($keyword, $city, $serpResults, $keywordImages, $titre);
        
        $systemMessage = "Tu es un rédacteur SEO senior expert en bâtiment et rénovation. Tu maîtrises parfaitement le HTML sémantique, l'optimisation SEO 2025, et la rédaction persuasive. Tu crées du contenu unique, détaillé et de qualité supérieure qui se classe en première page Google.";
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ]);
        
        $contenuHtml = trim($result['content'] ?? '');
        
        // Nettoyer le HTML (retirer les balises markdown si présentes)
        $contenuHtml = preg_replace('/```html\n?/', '', $contenuHtml);
        $contenuHtml = preg_replace('/```\n?/', '', $contenuHtml);
        $contenuHtml = trim($contenuHtml);
        
        // Corriger les balises FAQ Schema.org mal formées
        // Supprimer les fragments de balises comme "https://schema.org/FAQPage">" orphelins
        $contenuHtml = preg_replace('/https:\/\/schema\.org\/[^>]*">\s*/', '', $contenuHtml);
        // Corriger les balises FAQ incomplètes
        $contenuHtml = preg_replace('/<section[^>]*itemtype="https:\/\/schema\.org\/FAQPage"[^>]*>\s*https:\/\/schema\.org\/[^>]*">/i', '<section id="faq" itemscope itemtype="https://schema.org/FAQPage">', $contenuHtml);
        // Supprimer les balises orphelines schema.org
        $contenuHtml = preg_replace('/<https:\/\/schema\.org\/[^>]*>/i', '', $contenuHtml);
        
        // Validation basique du HTML
        if (empty($contenuHtml) || strlen($contenuHtml) < 500) {
            Log::error('Contenu HTML généré trop court', ['length' => strlen($contenuHtml)]);
            throw new \Exception('Le contenu généré est trop court ou vide.');
        }
        
        Log::info('Contenu HTML généré', [
            'length' => strlen($contenuHtml),
            'word_count' => str_word_count(strip_tags($contenuHtml))
        ]);
        
        return $contenuHtml;
    }
    
    /**
     * Construire le prompt ultra-optimisé pour le contenu HTML
     */
    protected function buildHtmlContentPrompt($keyword, $city, $serpResults, $keywordImages, $titre)
    {
        // Récupérer les informations de l'entreprise
        $companyName = config('app.name', 'Notre Entreprise');
        $companyDescription = Setting::where('key', 'company_description')->value('value') ?? '';
        $siteUrl = config('app.url', 'https://example.com');
        
        // Récupérer le numéro de téléphone
        $companyPhone = Setting::where('key', 'company_phone')->value('value') ?? '';
        $companyPhoneRaw = Setting::where('key', 'company_phone_raw')->value('value') ?? $companyPhone;
        
        // Construire les URLs correctes
        $devisUrl = route('form.step', 'propertyType');
        $contactUrl = route('contact');
        
        // Analyser les résultats SERP pour extraire les insights
        $serpInsights = $this->extractSerpInsights($serpResults);
        $competitorTopics = $serpInsights['topics'] ?? [];
        $commonQuestions = $serpInsights['questions'] ?? [];
        $avgWordCount = $serpInsights['avg_word_count'] ?? 1500;
        $targetWordCount = max(1800, $avgWordCount + 300); // Dépasser la moyenne de 300 mots
        
        // Préparer les images disponibles
        $imagesContext = '';
        if (!empty($keywordImages)) {
            $imagesContext = "\n\n📸 **IMAGES DISPONIBLES À INTÉGRER :**\n";
            foreach ($keywordImages as $img) {
                $title = $img['title'] ?? 'Image';
                $path = $img['path'] ?? '';
                $imagesContext .= "- Image : {$title}\n  Chemin : {$path}\n  ALT à utiliser : \"{$title} - {$keyword} à {$city}\"\n\n";
            }
            $imagesContext .= "⚠️ Intègre ces images de manière stratégique dans l'article avec des balises <img> correctement optimisées :\n";
            $imagesContext .= "```html\n<img src=\"{$path}\" alt=\"{$title} - {$keyword} à {$city}\" class=\"article-image\" loading=\"lazy\" />\n```\n";
        }
        
        // Construire le contexte des liens internes
        $internalLinksContext = $this->buildInternalLinksContext($keyword, $city);
        
        // Construire le prompt ultra-optimisé
        $prompt = <<<EOT
🎯 **MISSION : Rédiger un article SEO de qualité EXCEPTIONNELLE et 100% UNIQUE**

Tu es un rédacteur SEO expert spécialisé dans le secteur du bâtiment et de la rénovation. Ta mission est de créer un article professionnel, ultra-détaillé et parfaitement optimisé pour :
1. Se classer en **première page Google** pour "{$keyword}"
2. **Convertir les visiteurs** en prospects qualifiés
3. Établir **{$companyName}** comme référence incontestée du secteur
4. Offrir une **valeur réelle et actionnable** aux lecteurs

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 **INFORMATIONS DE BASE**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Titre de l'article :** {$titre}
**Mot-clé principal :** {$keyword}
**Localisation cible :** {$city}
**Entreprise :** {$companyName}
**Site web :** {$siteUrl}
**Année actuelle :** 2025

**À propos de l'entreprise :**
{$companyDescription}

**Objectif de longueur :** {$targetWordCount} mots minimum (viser **2000-2800 mots** pour un contenu ultra-complet)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔍 **ANALYSE CONCURRENTIELLE (SERP)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Sujets traités par les concurrents :**
{$this->formatTopics($competitorTopics)}

**Questions fréquentes identifiées :**
{$this->formatQuestions($commonQuestions)}

**Longueur moyenne des articles concurrents :** {$avgWordCount} mots
**Notre stratégie :** SURPASSER cette moyenne avec **{$targetWordCount}+ mots** de contenu à **forte valeur ajoutée**.

**🎯 Angles de différenciation obligatoires :**
1. Traiter des aspects NON couverts par les concurrents
2. Approfondir avec des détails techniques précis
3. Intégrer des spécificités locales de {$city}
4. Fournir des conseils actionnables et des checklists
5. Ajouter des exemples concrets et cas réels
6. Mentionner les tendances et innovations 2025

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎨 **STRUCTURE HTML ULTRA-OPTIMISÉE (Respecter STRICTEMENT)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. INTRODUCTION PERCUTANTE** (200-250 mots)
```html
<div class="article-intro">
  <p><strong>Le mot-clé principal doit apparaître dans les 100 premiers mots.</strong></p>
  <p>Accrocher avec un problème concret que rencontrent les lecteurs...</p>
  <p>Présenter la promesse de valeur : ce que l'article va apporter...</p>
  <p>Établir la crédibilité de {$companyName} dès l'introduction...</p>
</div>
```

**Structure :**
- ✅ Hook émotionnel (problème/douleur du client)
- ✅ Mot-clé principal "{$keyword}" dans le 1er paragraphe
- ✅ Mention de {$city} dans le contexte
- ✅ Promesse de valeur claire
- ✅ Ton professionnel mais chaleureux

**2. SOMMAIRE INTERACTIF** (Table des matières cliquable)
```html
<nav class="table-of-contents" aria-label="Table des matières">
  <h2>📑 Sommaire</h2>
  <ul>
    <li><a href="#section-1">Titre section 1</a></li>
    <li><a href="#section-2">Titre section 2</a></li>
    <li><a href="#section-3">Titre section 3</a></li>
    <!-- ... 4-6 sections au total -->
    <li><a href="#faq">Questions Fréquentes</a></li>
  </ul>
</nav>
```

**3. SECTIONS PRINCIPALES** (5-7 sections H2)

**Chaque section doit contenir :**
- 1 titre H2 avec ID unique : `<h2 id="section-X">Titre avec variante du mot-clé</h2>`
- 400-600 mots de contenu riche
- 2-4 sous-sections H3 pour approfondir
- Au moins 1 liste (à puces ou numérotée)
- 1 élément visuel ou encadré (si pertinent)

**Exemple de structure section :**
```html
<section id="section-1">
  <h2>Pourquoi Choisir un Professionnel pour {$keyword} à {$city} ?</h2>
  
  <p>Paragraphe d'introduction de la section (60-80 mots)...</p>
  
  <h3>Les Risques du Bricolage Amateur</h3>
  <p>Développement avec exemples concrets...</p>
  <ul>
    <li><strong>Risque 1 :</strong> Explication détaillée avec conséquences</li>
    <li><strong>Risque 2 :</strong> Exemple chiffré ou témoignage</li>
    <li><strong>Risque 3 :</strong> Impact sur la durabilité</li>
  </ul>
  
  <h3>Les Avantages d'un Expert Certifié</h3>
  <p>Développement persuasif...</p>
  <div class="highlight-box">
    <p><strong>💡 Le saviez-vous ?</strong> Information clé, statistique ou conseil d'expert.</p>
  </div>
  
  <h3>Certifications et Garanties Essentielles</h3>
  <p>Liste des qualifications importantes...</p>
  <ol>
    <li>Certification RGE (Reconnu Garant de l'Environnement)</li>
    <li>Assurance décennale obligatoire</li>
    <li>Label Qualibat ou équivalent</li>
  </ol>
</section>
```

**4. ÉLÉMENTS ENRICHIS OBLIGATOIRES** (à répartir dans l'article)

**Listes à puces stratégiques :**
```html
<ul class="checklist">
  <li>✅ Point actionnable avec valeur concrète</li>
  <li>✅ Conseil pratique immédiatement applicable</li>
  <li>✅ Information technique précise</li>
</ul>
```

**Listes numérotées (étapes/processus) :**
```html
<ol class="process-steps">
  <li><strong>Étape 1 - Diagnostic initial :</strong> Description détaillée de cette phase...</li>
  <li><strong>Étape 2 - Devis personnalisé :</strong> Explications sur les éléments inclus...</li>
  <li><strong>Étape 3 - Réalisation :</strong> Déroulement des travaux...</li>
</ol>
```

**Citations d'expert :**
```html
<blockquote class="expert-quote">
  <p>« Citation professionnelle authentique montrant l'expertise. Les clients qui investissent dans {$keyword} de qualité économisent 30% sur le long terme. »</p>
  <cite>— Expert {$companyName}, spécialiste depuis 15 ans</cite>
</blockquote>
```

**Encadrés importants :**
```html
<div class="info-box">
  <h4>⚠️ Point d'Attention Important</h4>
  <p>Information critique que le lecteur doit absolument connaître...</p>
</div>

<div class="tip-box">
  <h4>💡 Conseil d'Expert Pro</h4>
  <p>Astuce professionnelle pour optimiser le résultat...</p>
</div>
```

**Tableaux comparatifs :**
```html
<table class="comparison-table">
  <thead>
    <tr>
      <th>Critère</th>
      <th>Option A</th>
      <th>Option B</th>
      <th>Notre Recommandation</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Durabilité</td>
      <td>10-15 ans</td>
      <td>25-30 ans</td>
      <td>✅ Option B</td>
    </tr>
    <!-- Ajouter 3-5 lignes de comparaison -->
  </tbody>
</table>
```

**5. SECTION FAQ** (OBLIGATOIRE - Format Schema.org CORRECT)
```html
<section id="faq" itemscope itemtype="https://schema.org/FAQPage">
  <h2>❓ Questions Fréquentes sur {$keyword} à {$city}</h2>
  
  <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
    <h3 itemprop="name">Combien coûte {$keyword} à {$city} en 2025 ?</h3>
    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
      <p itemprop="text">Réponse complète et précise avec fourchette de prix, facteurs influençant le tarif, et conseils pour optimiser le budget. En moyenne, comptez entre X€ et Y€ selon la superficie et les matériaux choisis...</p>
    </div>
  </div>
  
  <div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
    <h3 itemprop="name">Quels sont les délais d'intervention pour {$keyword} à {$city} ?</h3>
    <div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
      <p itemprop="text">Les délais varient selon la saison et l'urgence. Pour une intervention standard, comptez 5 à 10 jours. En cas d'urgence (fuite, dégâts), nous intervenons sous 24-48h...</p>
    </div>
  </div>
  
  <!-- AJOUTER 6-10 QUESTIONS AU TOTAL -->
  <!-- Questions recommandées : prix, délais, certifications, garanties, entretien, durée de vie, matériaux, zone d'intervention, aides financières, saison idéale -->
  
</section>
```

**⚠️ CRITIQUE - Format FAQ Schema.org :**
- Utilise EXACTEMENT le format ci-dessus avec les balises HTML COMPLÈTES et FERMÉES
- Chaque question DOIT être dans un `<div itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">` COMPLET
- Chaque réponse DOIT être dans un `<div itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">` COMPLET
- NE JAMAIS générer de balises HTML cassées ou incomplètes comme `https://schema.org/FAQPage">` seule
- NE JAMAIS mettre les attributs schema.org sur des balises orphelines ou des fragments de HTML
- TOUTES les balises doivent être complètes : `<section>`, `</section>`, `<div>`, `</div>`, `<h3>`, `</h3>`, `<p>`, `</p>`
- Le format DOIT être du HTML valide et bien formé

**6. CALL-TO-ACTION STRATÉGIQUES**

**CTA intermédiaire (après 40% du contenu) :**
```html
<div class="cta-inline">
  <p>🎯 <strong>Vous avez un projet de {$keyword} à {$city} ?</strong> Nos experts vous accompagnent de A à Z pour une réalisation parfaite.</p>
  <p><a href="{$devisUrl}" class="btn-secondary">📞 Demander un devis gratuit</a> ou appelez-nous au <a href="tel:{$companyPhoneRaw}">{$companyPhone}</a></p>
</div>
```

**CTA principal (fin d'article) :**
```html
<div class="cta-final">
  <h3>🚀 Prêt à Lancer Votre Projet de {$keyword} à {$city} ?</h3>
  <p>Faites confiance à <strong>{$companyName}</strong>, votre expert local certifié. Nous vous garantissons :</p>
  <ul>
    <li>✅ Devis gratuit et détaillé sous 24h</li>
    <li>✅ Artisans qualifiés et assurés</li>
    <li>✅ Matériaux de première qualité</li>
    <li>✅ Garantie décennale incluse</li>
    <li>✅ Respect des délais et du budget</li>
  </ul>
  <p class="cta-buttons">
    <a href="{$devisUrl}" class="btn-primary">📝 Obtenir mon devis gratuit</a>
    <a href="tel:{$companyPhoneRaw}" class="btn-secondary">📞 Appelez-nous maintenant</a>
  </p>
</div>
```

**⚠️ IMPORTANT - Liens CTA :**
- Utilise EXACTEMENT les URLs suivantes :
  - Devis : `{$devisUrl}` (route vers le formulaire de devis)
  - Téléphone : `tel:{$companyPhoneRaw}` avec le texte `{$companyPhone}`
- NE PAS utiliser de placeholders comme `/contact` ou `tel:+33XXXXXXXXX`
- Les liens doivent être fonctionnels et pointer vers les bonnes routes

**7. CONCLUSION ENGAGEANTE** (200-250 mots)
```html
<section class="article-conclusion">
  <h2>En Résumé : Votre Guide Complet sur {$keyword} à {$city}</h2>
  
  <p>Récapitulatif des 3-5 points clés de l'article (sans répéter mot pour mot)...</p>
  
  <p>Rappel de la valeur unique de {$companyName} : expertise locale, certifications, satisfaction client...</p>
  
  <p>Encouragement à l'action avec bénéfice final : "En choisissant {$companyName} pour votre projet de {$keyword} à {$city}, vous optez pour la tranquillité d'esprit et un résultat durable qui valorisera votre patrimoine."</p>
  
  <p><strong>Intégration naturelle du mot-clé principal une dernière fois.</strong></p>
</section>
```

{$imagesContext}

{$internalLinksContext}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ **CRITÈRES DE QUALITÉ SEO 2025 (NON NÉGOCIABLES)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**1. OPTIMISATION MOTS-CLÉS (Naturelle et Stratégique)**
   ✅ **Mot-clé principal "{$keyword}"** : 6-10 occurrences naturelles (densité 0.5-1%)
   ✅ **Variantes sémantiques** : 12-18 variantes différentes
      - Exemple : "{$keyword}" → "spécialiste {$keyword}", "expert en {$keyword}", "professionnel {$keyword}", "entreprise de {$keyword}", "artisan {$keyword}", etc.
   ✅ **Mots-clés secondaires** : 8-12 termes connexes du secteur
   ✅ **Mots-clés longue traîne** : 5-8 questions/expressions spécifiques
   ✅ **Localisation** : Mentionner {$city} 8-12 fois + variantes (région, département, zone)
   ⚠️ **ZÉRO keyword stuffing** : Chaque phrase doit sonner 100% naturelle

**2. ENTITÉS SÉMANTIQUES (Contexte Riche)**
   ✅ **Matériaux du secteur** : Tuiles, ardoise, zinc, membrane EPDM, laine de verre, etc.
   ✅ **Techniques professionnelles** : Charpente traditionnelle, isolation thermique, étanchéité, zinguerie, etc.
   ✅ **Normes et réglementations** : RT2020, DTU (Documents Techniques Unifiés), RGE, Qualibat, assurance décennale
   ✅ **Contexte géographique** : {$city}, quartiers environnants, département, climat local, architecture régionale
   ✅ **Concepts métier** : Devis, garanties, certifications, diagnostic, entretien préventif

**3. INTENTION DE RECHERCHE (Satisfaction Maximale)**
   ✅ Identifier l'intention : Informationnelle OU Commerciale OU Transactionnelle OU Locale
   ✅ Répondre à TOUTES les questions implicites de l'utilisateur
   ✅ Fournir des solutions concrètes, chiffrées, applicables
   ✅ Anticiper les objections (prix, délais, qualité) et y répondre
   ✅ Guider vers la décision (comparaisons, conseils de choix)

**4. E-E-A-T (Experience, Expertise, Authority, Trust)**
   ✅ **Expérience** : 3-5 exemples concrets, retours terrain, situations vécues
   ✅ **Expertise** : Vocabulaire technique maîtrisé (sans jargon incompréhensible), détails précis, processus expliqués
   ✅ **Autorité** : Références aux normes (DTU, RT2020), statistiques secteur, meilleures pratiques professionnelles
   ✅ **Confiance** : Transparence (prix indicatifs), certifications visibles, garanties mentionnées

**5. LISIBILITÉ ET ENGAGEMENT (UX Optimale)**
   ✅ **Phrases courtes** : 15-25 mots maximum par phrase (80%+ des phrases)
   ✅ **Paragraphes aérés** : 3-5 lignes maximum par paragraphe
   ✅ **Transitions fluides** : Connecteurs logiques entre sections (De plus, Par ailleurs, En outre, Ainsi, etc.)
   ✅ **Ton professionnel accessible** : Éviter jargon excessif OU expliquer les termes techniques
   ✅ **Voix active privilégiée** : 80%+ des phrases en voix active
   ✅ **Storytelling** : 2-3 anecdotes ou exemples concrets pour illustrer
   ✅ **Données chiffrées** : Statistiques, fourchettes de prix, durées, pourcentages pour crédibilité

**6. STRUCTURE SÉMANTIQUE HTML (Hiérarchie Parfaite)**
   ✅ **H2** : 5-7 sections principales (chacune avec variante du mot-clé)
   ✅ **H3** : 10-15 sous-sections pour approfondir
   ✅ **H4** : Optionnels pour détails très spécifiques
   ✅ **Balises sémantiques** : <section>, <article>, <aside>, <nav>
   ✅ **Attributs accessibilité** : aria-label, role quand approprié

**7. ENRICHISSEMENTS MULTIMÉDIAS**
   ✅ Intégrer TOUTES les images fournies avec ALT optimisés
   ✅ Placer images stratégiquement (après introduction, milieu sections, avant FAQ)
   ✅ ALT text format : "Description précise - {$keyword} à {$city}"
   ✅ Attribut loading="lazy" pour performance

**8. LIENS STRATÉGIQUES**
   ✅ **Liens internes** : 6-10 liens vers pages connexes (utiliser contexte fourni)
   ✅ **Ancres descriptives** : "découvrez nos services de [service]", "en savoir plus sur [sujet]"
   ✅ **Répartition naturelle** : 1 lien tous les 300-400 mots
   ✅ **Jamais** : "cliquez ici", "voir ici", ancres génériques

**9. FEATURED SNIPPETS (Position 0)**
   ✅ **Paragraphes quotables** : Chaque paragraphe autonome et complet
   ✅ **Réponses directes** : Format question → réponse immédiate en 40-60 mots
   ✅ **Listes structurées** : Étapes numérotées pour processus, puces pour avantages
   ✅ **Tableaux** : Comparaisons claires (matériaux, prix, durée de vie)
   ✅ **Définitions claires** : Expliquer termes techniques en 1-2 phrases

**10. CONVERSIONS ET ACTIONS**
   ✅ **CTAs visibles** : 2-3 CTA répartis stratégiquement
   ✅ **Proposition de valeur** : Avantages concrets pour le client
   ✅ **Urgence subtile** : "Places limitées ce mois-ci", "Profitez des aides 2025"
   ✅ **Facilité contact** : Téléphone, formulaire, chat mentionnés
   ✅ **Preuves sociales** : "500+ clients satisfaits", "15 ans d'expérience"

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
❌ **ERREURS À ÉVITER ABSOLUMENT**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🚫 **Contenu de remplissage** : Chaque phrase doit apporter de la valeur réelle
🚫 **Sur-optimisation** : Répétition mécanique du mot-clé, ancres sur-optimisées
🚫 **Duplication concurrence** : Ne JAMAIS paraphraser les concurrents
🚫 **Promesses exagérées** : Rester factuel et réaliste (prix, délais, résultats)
🚫 **Fautes** : Orthographe, grammaire, ponctuation irréprochables
🚫 **Phrases-fleuves** : Aucune phrase >30 mots
🚫 **Structure plate** : Varier longueur paragraphes, alterner listes/texte
🚫 **Vague et général** : Toujours donner exemples concrets, chiffres précis
🚫 **HTML mal formé** : Balises fermées, hiérarchie respectée
🚫 **Oublier localisation** : {$city} doit être présente tout au long

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎯 **STRATÉGIE DE DIFFÉRENCIATION (Surpasser Concurrents)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Pour devenir LA référence sur "{$keyword}" à {$city}, cet article DOIT :

1. **Être 30% plus complet** que le meilleur concurrent
   - Traiter angles non couverts identifiés dans l'analyse SERP
   - Approfondir sections superficielles chez concurrents
   - Ajouter section unique (innovation 2025, réglementation locale, cas d'usage spécifiques)

2. **Être ultra-actionnable**
   - Checklists téléchargeables mentalement (ex: "Les 10 points à vérifier avant de...")
   - Guide étape par étape pour choisir/comparer
   - Calculs simples pour estimer budget/durée
   - Conseils d'entretien ou préparation

3. **Être hyper-local**
   - Spécificités climatiques de {$city} (pluie, vent, gel)
   - Réglementations PLU (Plan Local d'Urbanisme) si applicables
   - Architecture typique de la région
   - Aides locales/régionales disponibles en 2025
   - Témoignage client de {$city} (anonymisé si besoin)

4. **Démontrer expertise supérieure**
   - Détails techniques précis (normes DTU spécifiques, calculs thermiques)
   - Explications processus étape par étape
   - Erreurs courantes à éviter (vu sur le terrain)
   - Innovations et tendances 2025 du secteur
   - Certifications et labels expliqués

5. **Être plus engageant**
   - Storytelling : Commencer sections par mini-scénarios ("Imaginez...", "Marie et Pierre avaient ce problème...")
   - Ton conversationnel pro (tutoiement ou vouvoiement selon contexte)
   - Questions rhétoriques pour engager ("Vous vous demandez sûrement...")
   - Analogies simples pour concepts techniques

6. **Être plus récent et à jour**
   - Mentionner "2025" 3-5 fois naturellement
   - Nouvelles normes/réglementations 2024-2025
   - Évolution des prix récente
   - Tendances actuelles du marché
   - Technologies/matériaux dernière génération

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📝 **FORMAT DE SORTIE STRICTEMENT REQUIS**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Retourne UNIQUEMENT le contenu HTML pur, SANS :**
- ❌ Balises <html>, <head>, <body>, <!DOCTYPE>
- ❌ Titre H1 (déjà géré ailleurs)
- ❌ Scripts JavaScript
- ❌ Styles CSS inline (sauf classes)
- ❌ Commentaires HTML (sauf suggestions visuelles type <!-- Suggestion: Ajouter vidéo ici -->)
- ❌ Texte avant/après le HTML (pas d'introduction "Voici l'article...")

**Le HTML doit être :**
✅ Bien indenté (2 espaces par niveau)
✅ Sémantiquement correct (HTML5)
✅ Prêt à insérer dans un <div class="article-content">
✅ Tous attributs présents (id, class, itemscope, href, src, alt, loading)

**Structure de sortie attendue :**
```html
<div class="article-intro">
  <!-- Introduction -->
</div>

<nav class="table-of-contents">
  <!-- Sommaire -->
</nav>

<section id="section-1">
  <!-- Section 1 -->
</section>

<!-- ... autres sections ... -->

<section id="faq" itemscope itemtype="https://schema.org/FAQPage">
  <!-- FAQ -->
</section>

<div class="cta-final">
  <!-- CTA final -->
</div>

<section class="article-conclusion">
  <!-- Conclusion -->
</section>
```

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 **PROCESSUS DE RÉDACTION OPTIMAL**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**ÉTAPE 1 - ANALYSE (Mental)**
- Comprendre intention recherche derrière "{$keyword}"
- Identifier persona cible (propriétaire, syndic, particulier, professionnel)
- Définir problèmes principaux à résoudre
- Repérer opportunités de différenciation vs concurrents

**ÉTAPE 2 - PLANIFICATION (Mental)**
- Créer plan détaillé : 5-7 sections H2 logiques
- Répartir variantes mot-clé entre sections
- Positionner CTAs stratégiquement
- Prévoir emplacements images

**ÉTAPE 3 - RÉDACTION**
- Introduction accrocheuse (mot-clé dans 100 premiers mots)
- Développer chaque section avec profondeur
- Alterner formats : texte, listes, tableaux, encadrés
- Intégrer naturellement mots-clés et variantes
- Maintenir ton professionnel mais accessible

**ÉTAPE 4 - ENRICHISSEMENT**
- Ajouter données chiffrées, statistiques
- Insérer exemples concrets et anecdotes
- Créer FAQ riche (8-10 questions)
- Optimiser CTAs avec propositions de valeur claires

**ÉTAPE 5 - OPTIMISATION**
- Vérifier densité mots-clés (0.5-1%)
- Contrôler longueur paragraphes/phrases
- Valider structure HTML (h2>h3>h4)
- S'assurer liens internes présents
- Confirmer images intégrées avec ALT

**ÉTAPE 6 - QUALITÉ FINALE**
- Relire pour fluidité et cohérence
- Vérifier transitions entre sections
- Confirmer valeur actionnable apportée
- S'assurer différenciation vs concurrents

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎯 **OBJECTIFS MESURABLES DE CET ARTICLE**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Cet article doit permettre de :
✅ **Se classer top 3** sur "{$keyword}" + {$city} dans les 3 mois
✅ **Générer 5-15 demandes de devis** par mois via CTAs
✅ **Temps lecture moyen** : 4-7 minutes (engagement fort)
✅ **Taux rebond** : <50% (grâce à sommaire et structure)
✅ **Partages sociaux** : 3-10 par mois (contenu de valeur)
✅ **Featured snippet** : Capturer position 0 sur 2-3 requêtes

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✨ **COMMENCER LA RÉDACTION MAINTENANT**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Tu as toutes les informations nécessaires. Génère maintenant un article HTML exceptionnel de **{$targetWordCount}+ mots** qui :

🏆 Établit {$companyName} comme LA référence pour {$keyword} à {$city}
🏆 Surpasse tous les concurrents en profondeur et qualité
🏆 Convertit les visiteurs en clients
🏆 Se classe en première page Google

**Ton article doit être tellement bon que :**
- Les lecteurs le bookmarkent comme ressource de référence
- Les concurrents voudraient l'avoir écrit
- Google le met en featured snippet
- Les prospects appellent après l'avoir lu

**RÉDIGE MAINTENANT.** Produis le meilleur contenu SEO jamais créé sur ce sujet.
EOT;

        return $prompt;
    }
    
    /**
     * Extraire des insights des résultats SERP
     */
    protected function extractSerpInsights($serpResults)
    {
        $topics = [];
        $questions = [];
        $wordCounts = [];
        
        if (empty($serpResults)) {
            return [
                'topics' => [
                    'Présentation des services et expertise',
                    'Tarifs détaillés et options de financement',
                    'Zone d\'intervention et disponibilités',
                    'Certifications professionnelles et garanties',
                    'Processus de réalisation étape par étape',
                    'Matériaux utilisés et leurs avantages'
                ],
                'questions' => [
                    'Combien coûte ce service en moyenne ?',
                    'Quels sont les délais d\'intervention habituels ?',
                    'Quelles certifications possédez-vous ?',
                    'Quelle est votre zone d\'intervention ?',
                    'Proposez-vous des garanties ?',
                    'Quels matériaux recommandez-vous ?',
                    'Comment se déroule le chantier ?',
                    'Peut-on bénéficier d\'aides financières ?'
                ],
                'avg_word_count' => 1500
            ];
        }
        
        foreach ($serpResults as $result) {
            // Extraire les sujets des titres et snippets
            if (isset($result['title'])) {
                $title = strtolower($result['title']);
                
                // Identifier les thèmes récurrents
                if (strpos($title, 'prix') !== false || strpos($title, 'tarif') !== false || strpos($title, 'coût') !== false) {
                    $topics[] = 'Prix et tarification détaillée';
                }
                if (strpos($title, 'comment') !== false || strpos($title, 'guide') !== false) {
                    $topics[] = 'Guide pratique et conseils';
                }
                if (strpos($title, 'meilleur') !== false || strpos($title, 'comparatif') !== false) {
                    $topics[] = 'Comparaisons et recommandations';
                }
            }
            
            if (isset($result['snippet'])) {
                $snippet = $result['snippet'];
                
                // Identifier les questions dans les snippets
                if (preg_match_all('/\b(comment|pourquoi|quand|où|quel|combien|qui|quoi)[^.?!]{5,}[?]/ui', $snippet, $matches)) {
                    foreach ($matches[0] as $question) {
                        $question = trim($question);
                        if (strlen($question) > 10 && strlen($question) < 150) {
                            $questions[] = ucfirst($question);
                        }
                    }
                }
                
                // Estimer le nombre de mots (approximatif)
                $wordCount = str_word_count($snippet) * 15; // Le snippet représente ~1/15 de l'article
                if ($wordCount > 500 && $wordCount < 5000) {
                    $wordCounts[] = $wordCount;
                }
            }
            
            // Extraire word count si disponible
            if (isset($result['word_count']) && $result['word_count'] > 0) {
                $wordCounts[] = $result['word_count'];
            }
        }
        
        // Déduplication et nettoyage
        $topics = array_unique($topics);
        $questions = array_unique($questions);
        $questions = array_slice($questions, 0, 10);
        
        // Ajouter des questions par défaut si peu trouvées
        if (count($questions) < 5) {
            $defaultQuestions = [
                'Combien coûte ce service ?',
                'Quels sont les délais d\'intervention ?',
                'Êtes-vous certifiés et assurés ?',
                'Quelle zone couvrez-vous ?',
                'Proposez-vous des garanties ?',
                'Quels matériaux utilisez-vous ?'
            ];
            $questions = array_merge($questions, $defaultQuestions);
            $questions = array_unique($questions);
            $questions = array_slice($questions, 0, 10);
        }
        
        $avgWordCount = !empty($wordCounts) ? (int) (array_sum($wordCounts) / count($wordCounts)) : 1500;
        
        // Limiter entre 1200 et 3000 mots
        $avgWordCount = max(1200, min(3000, $avgWordCount));
        
        return [
            'topics' => array_values($topics),
            'questions' => array_values($questions),
            'avg_word_count' => $avgWordCount
        ];
    }
    
    /**
     * Formater les sujets pour le prompt
     */
    protected function formatTopics($topics)
    {
        if (empty($topics)) {
            return "- Services et prestations détaillées\n- Tarifs et options de financement\n- Zone d'intervention et disponibilités\n- Certifications et garanties professionnelles\n- Processus de réalisation\n- Matériaux et techniques utilisés";
        }
        
        $formatted = '';
        foreach (array_slice($topics, 0, 8) as $index => $topic) {
            $formatted .= ($index + 1) . ". {$topic}\n";
        }
        
        return rtrim($formatted);
    }
    
    /**
     * Formater les questions pour le prompt
     */
    protected function formatQuestions($questions)
    {
        if (empty($questions)) {
            return "1. Combien coûte ce service en moyenne ?\n2. Quels sont les délais d'intervention ?\n3. Êtes-vous certifiés et assurés ?\n4. Quelle est votre zone d'intervention ?\n5. Proposez-vous des garanties décennales ?\n6. Quels matériaux recommandez-vous ?";
        }
        
        $formatted = '';
        foreach (array_slice($questions, 0, 10) as $index => $question) {
            $question = trim($question);
            if (!empty($question)) {
                $formatted .= ($index + 1) . ". {$question}\n";
            }
        }
        
        return rtrim($formatted);
    }
    
    /**
     * Construire le contexte des liens internes
     */
    protected function buildInternalLinksContext($keyword, $city)
    {
        // Récupérer les services disponibles
        $servicesData = Setting::where('key', 'services')->value('value');
        $services = [];
        
        if (!empty($servicesData)) {
            $decoded = json_decode($servicesData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $services = $decoded;
            }
        }
        
        $linksContext = "\n\n🔗 **LIENS INTERNES À INTÉGRER NATURELLEMENT**\n\n";
        $linksContext .= "**IMPORTANT :** Intègre 6-10 liens internes pertinents dans le contenu de manière fluide et naturelle.\n\n";
        
        if (!empty($services)) {
            $linksContext .= "**Services connexes disponibles sur le site :**\n";
            foreach (array_slice($services, 0, 8) as $service) {
                $serviceName = $service['name'] ?? 'Service';
                $serviceSlug = \Illuminate\Support\Str::slug($serviceName);
                $linksContext .= "- **{$serviceName}** : <a href=\"/services/{$serviceSlug}\">{$serviceName} à {$city}</a>\n";
            }
            $linksContext .= "\n";
        }
        
        $linksContext .= "**Pages principales du site :**\n";
        $linksContext .= "- À propos : <a href=\"/about\">Découvrez notre entreprise et notre expertise</a>\n";
        $linksContext .= "- Contact : <a href=\"/contact\">Demandez votre devis gratuit personnalisé</a>\n";
        $linksContext .= "- Réalisations : <a href=\"/realisations\">Consultez nos projets récents</a>\n";
        $linksContext .= "- Zone d'intervention : <a href=\"/zone-intervention\">Vérifie si nous intervenons chez vous</a>\n";
        $linksContext .= "- Blog : <a href=\"/blog\">Tous nos conseils d'experts</a>\n";
        
        $linksContext .= "\n**🎯 Règles d'intégration des liens :**\n";
        $linksContext .= "1. Les ancres doivent être **descriptives et naturelles** (jamais \"cliquez ici\" ou \"en savoir plus\")\n";
        $linksContext .= "2. Intégrer les liens **dans le flux naturel** du texte, pas en fin de phrase artificiellement\n";
        $linksContext .= "3. Répartir équitablement : **1 lien tous les 300-400 mots** environ\n";
        $linksContext .= "4. Privilégier les liens vers services **connexes et pertinents** pour le sujet traité\n";
        $linksContext .= "5. Varier les ancres : ne pas utiliser le même texte d'ancre plusieurs fois\n";
        
        $linksContext .= "\n**Exemples d'intégration réussie :**\n";
        $linksContext .= "✅ \"Pour compléter votre projet, découvrez nos <a href=\"/services/isolation-combles\">solutions d'isolation des combles à {$city}</a>.\"\n";
        $linksContext .= "✅ \"Notre équipe réalise également des <a href=\"/services/charpente\">travaux de charpente traditionnelle</a> dans toute la région.\"\n";
        $linksContext .= "✅ \"Consultez <a href=\"/realisations\">nos dernières réalisations de {$keyword}</a> pour vous inspirer.\"\n";
        
        $linksContext .= "\n❌ À éviter :\n";
        $linksContext .= "❌ \"Pour en savoir plus, <a href=\"/services\">cliquez ici</a>.\"\n";
        $linksContext .= "❌ \"Découvrez nos <a href=\"/about\">services</a>.\" (ancre trop générique)\n";
        
        return $linksContext;
    }
}