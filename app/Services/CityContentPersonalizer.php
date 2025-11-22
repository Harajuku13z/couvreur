<?php

namespace App\Services;

use App\Models\City;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Service de personnalisation avancée du contenu par ville
 * Génère du contenu UNIQUE pour chaque ville en utilisant l'IA
 */
class CityContentPersonalizer
{
    /**
     * Générer du contenu UNIQUE et personnalisé pour une ville
     * Ne plus utiliser de simples variables [VILLE] mais générer du vrai contenu local
     */
    public function generatePersonalizedContent($templateContent, $service, City $city)
    {
        try {
            Log::info('Personnalisation contenu ville', [
                'city' => $city->name,
                'service' => $service['name'] ?? 'unknown'
            ]);
            
            // Clé de cache unique pour cette combinaison
            $cacheKey = 'personalized_content_' . md5($service['name'] . '_' . $city->id . '_' . substr($templateContent, 0, 100));
            
            // Vérifier le cache (1 mois)
            $cached = Cache::get($cacheKey);
            if ($cached) {
                Log::info('Contenu personnalisé trouvé en cache');
                return $cached;
            }
            
            // Extraire les informations locales de la ville
            $cityContext = $this->buildCityContext($city);
            
            // Créer un prompt pour générer du contenu VRAIMENT personnalisé
            $prompt = $this->buildPersonalizationPrompt($templateContent, $service, $city, $cityContext);
            
            // Appeler l'IA pour personnaliser le contenu
            $result = AiService::callAI($prompt, 
                "Tu es un expert en rédaction locale et personnalisation de contenu. Tu dois créer du contenu 100% UNIQUE pour {$city->name}, en évitant tout contenu générique.",
                [
                    'max_tokens' => 3000,
                    'temperature' => 0.8, // Plus de créativité pour éviter duplication
                    'timeout' => 90
                ]
            );
            
            if (!$result || empty($result['content'])) {
                Log::warning('IA n\'a pas retourné de contenu, utilisation du template de base');
                return $this->fallbackPersonalization($templateContent, $city, $service['name'] ?? '');
            }
            
            $personalizedContent = $result['content'];
            
            // Post-traitement: s'assurer que les URLs et contacts sont corrects
            $personalizedContent = $this->postProcessContent($personalizedContent, $city);
            
            // Mettre en cache
            Cache::put($cacheKey, $personalizedContent, 30 * 24 * 60); // 30 jours
            
            Log::info('Contenu personnalisé généré avec succès', [
                'city' => $city->name,
                'length' => strlen($personalizedContent)
            ]);
            
            return $personalizedContent;
            
        } catch (\Exception $e) {
            Log::error('Erreur personnalisation contenu', [
                'city' => $city->name,
                'error' => $e->getMessage()
            ]);
            
            // Fallback sur méthode basique
            return $this->fallbackPersonalization($templateContent, $city, $service['name'] ?? '');
        }
    }
    
    /**
     * Construire le contexte local de la ville
     */
    protected function buildCityContext(City $city)
    {
        $context = [
            'name' => $city->name,
            'postal_code' => $city->postal_code,
            'department' => $city->department ?? 'Inconnu',
            'region' => $city->region ?? 'Inconnue',
            'population' => $city->population ?? null,
        ];
        
        // Informations géographiques
        $context['area_type'] = $this->determineAreaType($city);
        
        // Caractéristiques climatiques approximatives par région
        $context['climate'] = $this->getRegionalClimate($city->region);
        
        // Style architectural typique
        $context['architecture'] = $this->getRegionalArchitecture($city->region, $city->department);
        
        return $context;
    }
    
    /**
     * Déterminer le type de zone (urbaine, périurbaine, rurale)
     */
    protected function determineAreaType(City $city)
    {
        $population = $city->population ?? 0;
        
        if ($population > 100000) {
            return 'grande ville';
        } elseif ($population > 20000) {
            return 'ville moyenne';
        } elseif ($population > 5000) {
            return 'petite ville';
        } else {
            return 'commune rurale';
        }
    }
    
    /**
     * Obtenir des informations climatiques régionales
     */
    protected function getRegionalClimate($region)
    {
        $climates = [
            'Bretagne' => [
                'type' => 'océanique tempéré',
                'precipitation' => 'élevée (800-1200mm/an)',
                'challenges' => ['humidité constante', 'vents forts', 'sel marin près des côtes'],
                'materials' => ['ardoise bretonne', 'zinc prépatiné résistant à la corrosion', 'tuiles mécaniques étanches']
            ],
            'Île-de-France' => [
                'type' => 'océanique dégradé',
                'precipitation' => 'modérée (600-700mm/an)',
                'challenges' => ['pollution urbaine', 'variations thermiques importantes', 'infiltrations eaux pluviales'],
                'materials' => ['tuiles terre cuite mécaniques', 'zinc', 'ardoise synthétique']
            ],
            'Provence-Alpes-Côte d\'Azur' => [
                'type' => 'méditerranéen',
                'precipitation' => 'faible (500-600mm/an) mais épisodes intenses',
                'challenges' => ['chaleur intense 35-40°C', 'mistral violent', 'ensoleillement fort UV', 'orages violents'],
                'materials' => ['tuiles canal traditionnelles', 'tuiles romanes', 'terre cuite résistante UV']
            ],
            'Auvergne-Rhône-Alpes' => [
                'type' => 'semi-continental montagnard',
                'precipitation' => 'variable (700-1200mm/an)',
                'challenges' => ['neige abondante en altitude', 'gel intense', 'charge neigeuse importante'],
                'materials' => ['bac acier anti-neige', 'ardoise épaisse', 'tuiles mécaniques grand moule']
            ],
            'Bourgogne-Franche-Comté' => [
                'type' => 'semi-continental',
                'precipitation' => 'modérée (700-900mm/an)',
                'challenges' => ['hivers rigoureux', 'gel/dégel', 'variations thermiques'],
                'materials' => ['tuiles terre cuite plates', 'tuiles mécaniques grand moule', 'ardoise']
            ],
            'Grand Est' => [
                'type' => 'semi-continental',
                'precipitation' => 'modérée (600-800mm/an)',
                'challenges' => ['hivers froids', 'neige régulière', 'gel prolongé'],
                'materials' => ['tuiles plates traditionnelles', 'ardoise', 'zinc']
            ],
            'Hauts-de-France' => [
                'type' => 'océanique dégradé',
                'precipitation' => 'modérée à élevée (700-900mm/an)',
                'challenges' => ['humidité élevée', 'vents forts', 'gel hivernal'],
                'materials' => ['tuiles flamandes traditionnelles', 'ardoise', 'zinc']
            ],
            'Normandie' => [
                'type' => 'océanique franc',
                'precipitation' => 'élevée (800-1000mm/an)',
                'challenges' => ['pluies fréquentes', 'humidité', 'vents côtiers'],
                'materials' => ['ardoise', 'tuiles mécaniques étanches', 'zinc']
            ],
            'Nouvelle-Aquitaine' => [
                'type' => 'océanique à aquitain',
                'precipitation' => 'modérée à élevée (800-1100mm/an)',
                'challenges' => ['tempêtes atlantiques', 'humidité côtière', 'orages d\'été'],
                'materials' => ['tuiles canal', 'tuiles mécaniques', 'ardoise']
            ],
            'Occitanie' => [
                'type' => 'méditerranéen à montagnard',
                'precipitation' => 'variable (500-1000mm/an)',
                'challenges' => ['orages cévenols violents', 'vent d\'autan', 'chaleur estivale'],
                'materials' => ['tuiles canal traditionnelles', 'tuiles romanes', 'ardoise en montagne']
            ],
            'Pays de la Loire' => [
                'type' => 'océanique tempéré',
                'precipitation' => 'modérée (700-900mm/an)',
                'challenges' => ['vents océaniques', 'humidité', 'pluies fréquentes'],
                'materials' => ['ardoise d\'Angers', 'tuiles mécaniques', 'zinc']
            ],
            'Centre-Val de Loire' => [
                'type' => 'océanique dégradé',
                'precipitation' => 'modérée (650-750mm/an)',
                'challenges' => ['variations thermiques', 'gel hivernal', 'orages d\'été'],
                'materials' => ['tuiles plates traditionnelles', 'tuiles mécaniques', 'ardoise']
            ],
            'Corse' => [
                'type' => 'méditerranéen insulaire',
                'precipitation' => 'faible en été, forte en hiver (500-900mm/an)',
                'challenges' => ['vents violents (libeccio)', 'sel marin', 'soleil intense', 'maquis (feux)'],
                'materials' => ['tuiles canal', 'tuiles génoises', 'terre cuite résistante']
            ],
        ];
        
        return $climates[$region] ?? [
            'type' => 'tempéré français',
            'precipitation' => 'modérée (700mm/an)',
            'challenges' => ['variations saisonnières', 'gel hivernal', 'pluies printanières'],
            'materials' => ['tuiles mécaniques', 'ardoise', 'zinc']
        ];
    }
    
    /**
     * Obtenir le style architectural régional
     */
    protected function getRegionalArchitecture($region, $department)
    {
        $architectures = [
            'Bretagne' => 'longères bretonnes et maisons en pierre avec toitures à forte pente en ardoise grise, lucarnes typiques et souches de cheminée en pierre',
            'Île-de-France' => 'architecture haussmannienne en pierre de taille pour Paris, pavillons de banlieue avec meulière et tuiles mécaniques, immeubles résidentiels modernes',
            'Provence-Alpes-Côte d\'Azur' => 'mas provençaux et bastides en pierre avec toitures à faible pente en tuiles canal romanes, génoise et couleur ocre',
            'Auvergne-Rhône-Alpes' => 'chalets et fermes montagnardes avec toitures à forte pente pour évacuation neige, lauzes ou ardoise, maisons lyonnaises en pierre dorée',
            'Bourgogne-Franche-Comté' => 'maisons bourguignonnes en pierre calcaire, toitures aux tuiles vernissées polychromes typiques (tuiles de Bourgogne), forte pente',
            'Grand Est' => 'maisons alsaciennes à colombages avec toitures pentues en tuiles plates mécaniques, architecture lorraine en pierre et brique, fermes vosgiennes',
            'Hauts-de-France' => 'maisons en brique rouge typiques du Nord, toitures pentues en ardoise ou tuiles flamandes, architecture minière, longères picardes',
            'Normandie' => 'maisons normandes à colombages et torchis, manoirs en pierre avec toitures en ardoise, chaumières traditionnelles (rares)',
            'Nouvelle-Aquitaine' => 'maisons girondines en pierre blonde, charentaises avec toitures en tuiles canal, architecture basque avec toits débordants, fermes périgourdines',
            'Occitanie' => 'mas languedociens et toulousains en brique rose et pierre avec tuiles canal, architecture caussenarde en pierre sèche, toitures faible pente',
            'Pays de la Loire' => 'longères angevines en tuffeau avec toitures en ardoise d\'Angers, maisons vendéennes et nantaises, architecture ligérienne en pierre',
            'Centre-Val de Loire' => 'maisons de Sologne en brique et bois, demeures ligériennes en tuffeau blanc, toitures en tuiles plates ou ardoise, architecture berrichonne',
            'Corse' => 'maisons corses en pierre de granit avec toitures en lauzes (teghje) ou tuiles canal, génoises décoratives, architecture insulaire méditerranéenne',
        ];
        
        return $architectures[$region] ?? 'architecture locale traditionnelle française avec toitures adaptées au climat régional';
    }
    
    /**
     * Construire le prompt de personnalisation
     */
    protected function buildPersonalizationPrompt($templateContent, $service, City $city, $cityContext)
    {
        $serviceName = $service['name'] ?? 'service';
        $companyName = config('app.name', 'Notre Entreprise');
        $challengesList = isset($cityContext['climate']['challenges']) && is_array($cityContext['climate']['challenges'])
            ? implode(', ', $cityContext['climate']['challenges'])
            : '';
        $materialsList = isset($cityContext['climate']['materials']) && is_array($cityContext['climate']['materials'])
            ? implode(', ', $cityContext['climate']['materials'])
            : '';
        $currentYear = date('Y');
        
        return <<<EOT
🎯 **MISSION : Créer un contenu 100% UNIQUE et PERSONNALISÉ pour {$city->name}**

Tu as un template de contenu pour "{$serviceName}" qui est générique. Tu dois le RÉINVENTER complètement pour {$city->name} en créant du contenu UNIQUE qui ne peut exister que pour cette ville.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📍 **CONTEXTE LOCAL DE {$city->name}**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**Localisation :**
- Ville : {$city->name}
- Code postal : {$cityContext['postal_code']}
- Département : {$cityContext['department']}
- Région : {$cityContext['region']}
- Type de zone : {$cityContext['area_type']}

**Climat et environnement :**
- Type climatique : {$cityContext['climate']['type']}
- Précipitations : {$cityContext['climate']['precipitation']}
- Défis locaux : {$challengesList}
- Matériaux recommandés : {$materialsList}

**Architecture locale :**
{$cityContext['architecture']}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📝 **TEMPLATE DE BASE (À PERSONNALISER)**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

{$templateContent}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 **TES INSTRUCTIONS DE PERSONNALISATION**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**CRITIQUE : NE PAS faire de simple remplacement [VILLE] → {$city->name}**

À la place, tu dois :

1. **Adapter chaque paragraphe au contexte local :**
   - Mentionner les spécificités climatiques de {$cityContext['region']}
   - Parler de l'architecture typique de {$city->name}
   - Évoquer les défis locaux : {$cityContext['climate']['challenges'][0]} et {$cityContext['climate']['challenges'][1]}
   - Recommander les matériaux adaptés au climat local
   
2. **Créer des exemples concrets et locaux :**
   - "À {$city->name}, les toitures doivent résister à..."
   - "Dans le {$cityContext['department']}, les propriétaires de {$cityContext['area_type']} font face à..."
   - "Le climat {$cityContext['climate']['type']} de {$cityContext['region']} nécessite..."

3. **Personnaliser les problématiques :**
   - Parler des problèmes SPÉCIFIQUES à {$city->name} et sa région
   - Mentionner les réglementations locales si pertinent
   - Évoquer les aides régionales disponibles dans {$cityContext['region']}

4. **Ajouter du contenu local authentique :**
   - Décrire brièvement le paysage urbain/rural de {$city->name}
   - Parler du type d'habitat predominant
   - Mentionner les quartiers si c'est une grande ville

5. **Éviter absolument :**
   - Les phrases génériques qui pourraient s'appliquer à n'importe quelle ville
   - Les formulations type "[VILLE]" ou remplacements mécaniques
   - Le contenu copié-collé sans adaptation

**FORMAT DE SORTIE :**
Retourne UNIQUEMENT le HTML personnalisé, sans introduction, sans commentaire, juste le contenu HTML pur et personnalisé.

Le HTML doit être:
- Structuré avec des titres h2, h3
- Riche en paragraphes détaillés
- Contenir des listes à puces pour la lisibilité
- Inclure des appels à l'action pertinents

**LONGUEUR MINIMALE :** Le contenu personnalisé doit faire AU MINIMUM 1500 mots (environ 10000 caractères).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🏆 **E-E-A-T & FEATURED SNIPPETS**
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

**DÉMONTRER L'EXPERTISE LOCALE :**
1. **Expérience terrain** : Mentionner situations courantes observées à {$city->name}
   - Ex: "À {$city->name}, nous intervenons régulièrement sur..."
   - Ex: "Les propriétaires de {$city->name} font souvent face à..."

2. **Expertise technique adaptée au local** :
   - Citer normes DTU pertinentes pour {$serviceName}
   - Mentionner certification RGE et garanties
   - Parler des matériaux adaptés au climat {$cityContext['climate']['type']}

3. **Autorité & Crédibilité** :
   - Référencer ADEME, ANAH, FFB pour données officielles
   - Mentionner aides Ma Prime Rénov' {$currentYear}
   - Citer réglementation RE2020 si pertinent

4. **Confiance & Transparence** :
   - Fourchettes de prix RÉALISTES et DÉTAILLÉES
   - Mentionner garantie décennale obligatoire
   - Prévenir sur arnaques courantes dans le secteur

**OPTIMISATION FEATURED SNIPPETS :**
- Répondre directement aux questions en 40-60 mots au début de chaque section
- Utiliser des listes de 5-8 éléments (optimal pour snippets)
- Créer des tableaux comparatifs HTML simples
- Format questions/réponses clair pour FAQ snippets

**VARIATION & UNICITÉ MAXIMALE :**
- Éviter absolument les phrases répétitives ou templates visibles
- Varier les tournures, le vocabulaire, les exemples
- Créer des angles d'approche différents pour chaque ville
- NE JAMAIS copier-coller des phrases du template original

**COMMENCE MAINTENANT LA PERSONNALISATION PREMIUM POUR {$city->name}.**
EOT;
    }
    
    /**
     * Post-traitement du contenu personnalisé
     */
    protected function postProcessContent($content, City $city)
    {
        $devisUrl = route('form.step', 'propertyType');
        $contactUrl = route('contact');
        $companyPhone = Setting::get('company_phone', '');
        
        // S'assurer que les URLs sont correctes
        $content = str_replace('[FORM_URL]', $devisUrl, $content);
        $content = str_replace('[CONTACT_URL]', $contactUrl, $content);
        $content = str_replace('[PHONE]', $companyPhone, $content);
        
        // Variables de base (au cas où l'IA les aurait utilisées)
        $content = str_replace('[VILLE]', $city->name, $content);
        $content = str_replace('[CODE_POSTAL]', $city->postal_code, $content);
        $content = str_replace('[DÉPARTEMENT]', $city->department ?? '', $content);
        $content = str_replace('[RÉGION]', $city->region ?? '', $content);
        
        return $content;
    }
    
    /**
     * Fallback si l'IA échoue : personnalisation basique mais améliorée
     */
    protected function fallbackPersonalization($templateContent, City $city, string $serviceName = '')
    {
        $replacements = [
            '[VILLE]' => $city->name,
            '[CODE_POSTAL]' => $city->postal_code,
            '[DÉPARTEMENT]' => $city->department ?? '',
            '[RÉGION]' => $city->region ?? '',
            '[FORM_URL]' => route('form.step', 'propertyType'),
            '[CONTACT_URL]' => route('contact'),
            '[PHONE]' => Setting::get('company_phone', ''),
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $templateContent);
        
        // Ajouter au minimum un paragraphe unique sur la ville
        $cityContext = $this->buildCityContext($city);
        $localParagraph = "<p>À {$city->name} ({$cityContext['postal_code']}), située en {$cityContext['region']}, notre entreprise {$this->getServiceVerb($serviceName)} en tenant compte du climat {$cityContext['climate']['type']} caractéristique de la région. Les bâtiments de type {$cityContext['architecture']} nécessitent une attention particulière aux {$cityContext['climate']['challenges'][0]} typiques de cette zone géographique.</p>";
        
        // Insérer ce paragraphe au début du contenu
        if (preg_match('/<p>/', $content)) {
            $content = preg_replace('/<p>/', $localParagraph . '<p>', $content, 1);
        } else {
            $content = $localParagraph . $content;
        }
        
        return $content;
    }
    
    /**
     * Obtenir le verbe approprié pour le service
     */
    protected function getServiceVerb($serviceName)
    {
        $verbs = [
            'toiture' => 'intervient sur vos toitures',
            'façade' => 'rénove vos façades',
            'isolation' => 'améliore l\'isolation de vos bâtiments',
            'charpente' => 'répare et rénove vos charpentes',
            'couverture' => 'assure tous vos travaux de couverture',
            'zinguerie' => 'réalise vos travaux de zinguerie',
        ];
        
        foreach ($verbs as $key => $verb) {
            if (stripos($serviceName, $key) !== false) {
                return $verb;
            }
        }
        
        return 'intervient pour vos travaux';
    }
    
    /**
     * Générer des métadonnées personnalisées pour une ville
     */
    public function generatePersonalizedMeta($serviceName, City $city, $templateMeta)
    {
        try {
            $cityContext = $this->buildCityContext($city);
            
            $prompt = <<<EOT
Génère des métadonnées SEO UNIQUES et OPTIMISÉES pour :
- Service : {$serviceName}
- Ville : {$city->name}
- Région : {$cityContext['region']}
- Type de zone : {$cityContext['area_type']}

**Meta Title** (50-60 caractères) :
- DOIT inclure : {$serviceName} + {$city->name}
- DOIT être unique et accrocheur
- DOIT inclure un élément différenciateur (ex: "Certifié RGE", "Devis Gratuit", "Expert Local")

**Meta Description** (150-160 caractères) :
- DOIT être persuasive et locale
- DOIT mentionner un bénéfice concret pour {$city->name}
- DOIT inclure un appel à l'action
- DOIT se démarquer des concurrents

**Meta Keywords** (10-15 mots-clés) :
- Combiner {$serviceName} avec {$city->name}, {$cityContext['department']}, {$cityContext['region']}
- Inclure des variations locales
- Inclure des termes liés au climat local : {$cityContext['climate']['type']}

Retourne UNIQUEMENT un JSON :
{
  "meta_title": "...",
  "meta_description": "...",
  "meta_keywords": "..."
}
EOT;
            
            $result = AiService::callAI($prompt, 
                "Tu es un expert SEO spécialisé en référencement local.",
                [
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                    'timeout' => 30
                ]
            );
            
            if ($result && !empty($result['content'])) {
                // Parser le JSON
                $cleaned = trim($result['content']);
                $cleaned = preg_replace('/^```json\s*/m', '', $cleaned);
                $cleaned = preg_replace('/\s*```$/m', '', $cleaned);
                
                $meta = json_decode($cleaned, true);
                
                if ($meta && isset($meta['meta_title'])) {
                    return $meta;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur génération meta personnalisées', [
                'city' => $city->name,
                'error' => $e->getMessage()
            ]);
        }
        
        // Fallback : personnalisation basique
        return [
            'meta_title' => $serviceName . ' à ' . $city->name . ' | ' . config('app.name'),
            'meta_description' => "Expert en " . strtolower($serviceName) . " à " . $city->name . " (" . $city->postal_code . "). Devis gratuit, intervention rapide. Certifié RGE.",
            'meta_keywords' => $serviceName . ' ' . $city->name . ', ' . strtolower($serviceName) . ' ' . $city->postal_code . ', artisan ' . $city->name
        ];
    }
}

