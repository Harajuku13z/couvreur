<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BulkAdsController extends Controller
{
    /**
     * Afficher la page de génération en masse
     */
    public function index()
    {
        // Récupérer les services
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }

        // Récupérer toutes les villes
        $cities = City::orderBy('name')->get();
        
        // Récupérer les villes favorites depuis la colonne is_favorite
        $favoriteCities = City::where('is_favorite', true)->orderBy('name')->get();
        
        // Si pas de villes favorites configurées, utiliser les 10 premières villes
        if ($favoriteCities->isEmpty()) {
            $favoriteCities = $cities->take(10);
        }
        
        return view('admin.ads.bulk-ads', compact('services', 'cities', 'favoriteCities'));
    }

    /**
     * Générer des annonces pour toutes les villes d'un service
     */
    public function generateBulkAds(Request $request)
    {
        $request->validate([
            'service_slug' => 'required|string',
            'ai_prompt' => 'nullable|string|max:5000',
            'batch_size' => 'nullable|integer|min:1|max:100',
            'include_all_cities' => 'boolean'
        ]);

        try {
            // Récupérer le service
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $service = collect($services)->firstWhere('slug', $request->service_slug);
            
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service non trouvé'
                ], 404);
            }

            // Récupérer toutes les villes ou seulement les favorites
            if ($request->boolean('include_all_cities')) {
                $cities = City::orderBy('name')->get();
            } else {
                $cities = City::where('is_favorite', true)->orderBy('name')->get();
                
                // Si pas de villes favorites configurées, utiliser les 10 premières villes
                if ($cities->isEmpty()) {
                    $cities = City::orderBy('name')->take(10)->get();
                }
            }

            $batchSize = $request->input('batch_size', 10);
            $aiPrompt = $request->input('ai_prompt');
            
            Log::info('Début génération en masse', [
                'service' => $service['name'],
                'cities_count' => $cities->count(),
                'batch_size' => $batchSize,
                'include_all_cities' => $request->boolean('include_all_cities')
            ]);

            // Le contenu sera généré individuellement pour chaque ville via l'IA
            
            $createdAds = 0;
            $skippedAds = 0;
            $errors = [];

            // Traiter les villes par batch
            $cities->chunk($batchSize)->each(function ($cityBatch) use ($service, $template, &$createdAds, &$skippedAds, &$errors) {
                foreach ($cityBatch as $city) {
                    try {
                        // Vérifier si une annonce existe déjà
                        $existingAd = Ad::where('keyword', $service['name'])
                            ->where('city_id', $city->id)
                            ->first();

                        if ($existingAd) {
                            $skippedAds++;
                            Log::info('Annonce déjà existante', [
                                'service' => $service['name'],
                                'city' => $city->name
                            ]);
                            continue;
                        }

                        // Générer le contenu personnalisé via l'IA pour cette ville
                        $content = $this->generateAdContentWithAI($service, $city, $aiPrompt);
                        
                        // Créer l'annonce
                        $ad = Ad::create([
                            'title' => $service['name'] . ' à ' . $city->name,
                            'keyword' => $service['name'],
                            'city_id' => $city->id,
                            'slug' => Str::slug($service['name'] . '-' . $city->name),
                            'status' => 'published',
                            'meta_title' => $service['name'] . ' à ' . $city->name . ' | Devis Gratuit',
                            'meta_description' => 'Service professionnel de ' . $service['name'] . ' à ' . $city->name . '. Devis gratuit et intervention rapide.',
                            'content_html' => $content,
                            'content_json' => json_encode([
                                'service' => $service,
                                'city' => $city->toArray(),
                                'template_type' => 'bulk_generated',
                                'generated_at' => now()->toISOString()
                            ])
                        ]);

                        $createdAds++;
                        
                        Log::info('Annonce créée', [
                            'ad_id' => $ad->id,
                            'service' => $service['name'],
                            'city' => $city->name,
                            'slug' => $ad->slug
                        ]);

                        // Pause pour éviter de surcharger
                        usleep(100000); // 0.1 seconde

                    } catch (\Exception $e) {
                        $errors[] = [
                            'city' => $city->name,
                            'error' => $e->getMessage()
                        ];
                        Log::error('Erreur création annonce', [
                            'city' => $city->name,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('Génération en masse terminée', [
                'created_ads' => $createdAds,
                'skipped_ads' => $skippedAds,
                'errors_count' => count($errors)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Génération terminée : {$createdAds} annonces créées, {$skippedAds} ignorées",
                'data' => [
                    'created_ads' => $createdAds,
                    'skipped_ads' => $skippedAds,
                    'errors_count' => count($errors),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur génération en masse: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer du contenu personnalisé via l'IA pour un service et une ville
     */
    private function generateAdContentWithAI($service, $city, $aiPrompt = null)
    {
        try {
            // Récupérer l'URL du site et du formulaire
            $siteUrl = setting('site_url', config('app.url'));
            if (!str_starts_with($siteUrl, 'http')) {
                $siteUrl = 'https://' . $siteUrl;
            }
            $formUrl = $siteUrl . '/form/propertyType';
            $adUrl = $siteUrl . '/annonces/' . Str::slug($service['name'] . '-' . $city->name);
            $adTitle = $service['name'] . ' à ' . $city->name;
            
            // Construire le prompt personnalisé
            $prompt = $this->buildAdPrompt($service['name'], $city, $aiPrompt);
            
            // Appel à l'API OpenAI
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . setting('openai_api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiContent = $data['choices'][0]['message']['content'] ?? '';
                
                // Nettoyer et valider le contenu
                $content = $this->validateAndCleanAIData($aiContent, $service['name'], $city);
                
                // Remplacer les variables dans le contenu
                $content = str_replace([
                    '[FORM_URL]',
                    '[URL]',
                    '[TITRE]'
                ], [
                    $formUrl,
                    $adUrl,
                    $adTitle
                ], $content);
                
                return $content;
            } else {
                Log::error('Erreur API OpenAI pour génération annonce', [
                    'service' => $service['name'],
                    'city' => $city->name,
                    'response' => $response->body()
                ]);
                return $this->generateFallbackContent($service['name'], $city);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération contenu IA pour annonce', [
                'service' => $service['name'],
                'city' => $city->name,
                'error' => $e->getMessage()
            ]);
            return $this->generateFallbackContent($service['name'], $city);
        }
    }

    /**
     * Construire le prompt personnalisé pour une annonce
     */
    private function buildAdPrompt($serviceName, $city, $aiPrompt = null)
    {
        $basePrompt = "Tu es un expert en rédaction web pour les services de rénovation.

INFORMATIONS SUR LE SERVICE:
- Type de service : {$serviceName}
- Ville : {$city->name}
- Département : " . ($city->region ?? '') . "
- Objectif : créer un contenu web professionnel, engageant et optimisé pour le SEO, adapté à ce type de service dans cette ville spécifique.

TÂCHE:
Génère un contenu web complet comprenant :

1. Une description courte (120-140 caractères) accrocheuse et SEO.
2. Une longue description générale expliquant le service et les bénéfices pour le client.
3. Une icône Font Awesome adaptée au service.
4. Un titre SEO optimisé (50-60 caractères).
5. Une description SEO (150-160 caractères).
6. Des mots-clés pertinents séparés par des virgules.
7. Un contenu HTML complet structuré professionnellement, avec exactement **10 champs** :
   - **Champ 1 :** Introduction courte et longue
   - **Champ 2 :** Engagement / garanties
   - **Champ 3 :** Prestations / services (exactement 10 prestations spécifiques au service {$serviceName})
   - **Champ 4 :** Pourquoi choisir ce service
   - **Champ 5 :** Expertise / expérience
   - **Champ 6 :** CTA : demande de devis
   - **Champ 7 :** Informations pratiques
   - **Champ 8 :** FAQ (3 questions-réponses minimum)
   - **Champ 9 :** Financement / aides disponibles
   - **Champ 10 :** Partage social

STRUCTURE HTML OBLIGATOIRE (utilise exactement cette structure):
<div class=\"grid md:grid-cols-2 gap-8\">

  <!-- Colonne gauche : Contenu principal -->
  <div class=\"space-y-6\">

    <!-- Champ 1 : Introduction / description courte + longue -->
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">[Description courte du service {$serviceName} à {$city->name} en 250 caractères maximum]</p>
      <p class=\"text-lg leading-relaxed\">[Longue description détaillée du service {$serviceName}, bénéfices, explications techniques, avantages, matériaux utilisés, spécifique à {$city->name}]</p>
    </div>

    <!-- Champ 2 : Engagement / garanties -->
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">[Titre de l'engagement ou garantie pour {$serviceName}]</h3>
      <p class=\"leading-relaxed mb-3\">[Description de l'engagement]</p>
      <p class=\"leading-relaxed\">[Détails techniques ou matériaux utilisés]</p>
    </div>

    <!-- Champ 3 : Prestations / services -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$serviceName}</h3>
    <ul class=\"space-y-3\">
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 1 spécifique à {$serviceName}]</strong> - [Description détaillée]</span>
      </li>
      <!-- Répéter jusqu'à 10 prestations spécifiques à {$serviceName} -->
    </ul>

    <!-- Champ 8 : FAQ -->
    <div class=\"bg-gray-50 p-6 rounded-lg mt-6\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">FAQ</h4>
      <div class=\"space-y-2\">
        <p><strong>Q1 : [Question fréquente sur {$serviceName}]</strong></p>
        <p>A : [Réponse détaillée]</p>
        <p><strong>Q2 : [Question fréquente sur {$serviceName}]</strong></p>
        <p>A : [Réponse détaillée]</p>
        <p><strong>Q3 : [Question fréquente sur {$serviceName}]</strong></p>
        <p>A : [Réponse détaillée]</p>
      </div>
    </div>

  </div>

  <!-- Colonne droite : Informations complémentaires -->
  <div class=\"space-y-6\">

    <!-- Champ 4 : Pourquoi choisir ce service -->
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi choisir ce service</h3>
      <p class=\"leading-relaxed\">[Points forts et avantages du service {$serviceName} à {$city->name}]</p>
    </div>

    <!-- Champ 5 : Expertise / expérience -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">[Description de l'expertise et expérience en {$serviceName} à {$city->name}]</p>

    <!-- Champ 9 : Financement / aides disponibles -->
    <div class=\"bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Financement et aides</h4>
      <p>[Informations sur les aides financières et options de financement pour {$serviceName}]</p>
    </div>

    <!-- Champ 6 : CTA - demande de devis -->
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un devis ?</h4>
      <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour {$serviceName} à {$city->name}.</p>
      <a href=\"[FORM_URL]\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
    </div>

    <!-- Champ 7 : Informations pratiques -->
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 1 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 2 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 3 pour {$serviceName}]</span></li>
      </ul>
    </div>

    <!-- Champ 10 : Partage social -->
    <div class=\"mt-8 pt-6 border-t border-gray-200\">
      <div class=\"text-center\">
        <h4 class=\"text-lg font-semibold text-gray-800 mb-4\">Partager ce service</h4>
        <div class=\"flex justify-center items-center space-x-4\">
          <a href=\"https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fab fa-facebook-f text-lg\"></i>
            <span class=\"font-medium\">Facebook</span>
          </a>
          <a href=\"https://wa.me/?text=[TITRE] - [URL]\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fab fa-whatsapp text-lg\"></i>
            <span class=\"font-medium\">WhatsApp</span>
          </a>
          <a href=\"mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]\" class=\"bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fas fa-envelope text-lg\"></i>
            <span class=\"font-medium\">Email</span>
          </a>
        </div>
      </div>
    </div>

  </div>

</div>

INSTRUCTIONS IMPORTANTES:
- Le contenu doit être UNIQUE, professionnel et engageant.
- Utilise un vocabulaire technique adapté à {$serviceName}.
- PERSONNALISE le contenu pour {$city->name} et sa région.
- Inclue les aides financières et options de financement possibles.
- Fournis les informations pratiques, conseils et suivi après travaux.
- Ne mentionne jamais le nom de l'entreprise ni la localisation.
- Garde la structure HTML exacte.
- Génère des prestations détaillées avec descriptions explicites spécifiques à {$serviceName}.
- OBLIGATOIRE : Génère une LONGUE DESCRIPTION détaillée (minimum 300 caractères) dans le champ long_description
- La longue description doit expliquer les bénéfices, techniques utilisées, matériaux, avantages du service {$serviceName}
- NE PAS inclure de bouton \"En savoir plus\" ou lien externe dans le contenu
- REMPLACE [FORM_URL] par l'URL du formulaire de devis
- GÉNÈRE exactement 10 prestations spécifiques au service {$serviceName}
- PERSONNALISE chaque prestation selon le type de service
- UTILISE un vocabulaire technique approprié au secteur
- Fournis le résultat en **JSON** valide avec les champs suivants :

{
\"description\": \"[HTML complet avec structure exacte]\",
\"short_description\": \"[Description courte 120-140 caractères]\",
\"long_description\": \"[Longue description détaillée du service {$serviceName}, bénéfices, techniques, matériaux, avantages - minimum 300 caractères]\",
\"icon\": \"fas fa-[icône appropriée]\",
\"meta_title\": \"[Titre SEO 50-60 caractères]\",
\"meta_description\": \"[Description SEO 150-160 caractères]\",
\"og_title\": \"[Titre Open Graph 50-60 caractères]\",
\"og_description\": \"[Description Open Graph 150-160 caractères]\",
\"twitter_title\": \"[Titre Twitter 50-60 caractères]\",
\"twitter_description\": \"[Description Twitter 150-160 caractères]\",
\"meta_keywords\": \"[Mots-clés pertinents séparés par virgules]\"
}";

        // Ajouter le prompt personnalisé si fourni
        if ($aiPrompt) {
            $basePrompt .= "\n\nINSTRUCTIONS PERSONNALISÉES SUPPLÉMENTAIRES:\n" . $aiPrompt;
        }

        return $basePrompt;
    }

    /**
     * Valider et nettoyer les données générées par l'IA
     */
    private function validateAndCleanAIData($aiContent, $serviceName, $city)
    {
        try {
            // Nettoyer le contenu pour extraire le JSON
            $cleanContent = $this->cleanHtmlContent($aiContent);
            
            // Parser le JSON
            $aiData = json_decode($cleanContent, true);
            
            if (!$aiData || !isset($aiData['description'])) {
                Log::warning('Données IA invalides, utilisation du contenu brut', [
                    'service' => $serviceName,
                    'city' => $city->name,
                    'content' => substr($aiContent, 0, 200)
                ]);
                return $this->generateFallbackContent($serviceName, $city);
            }
            
            // Fonction pour nettoyer le texte
            $cleanText = function($text, $maxLength = null) {
                $text = strip_tags($text);
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);
                if ($maxLength && strlen($text) > $maxLength) {
                    $text = substr($text, 0, $maxLength) . '...';
                }
                return $text;
            };
            
            // Valider et nettoyer les données
            $validatedData = [
                'description' => $aiData['description'] ?? '',
                'short_description' => $cleanText($aiData['short_description'] ?? '', 140),
                'long_description' => $cleanText($aiData['long_description'] ?? '', 500),
                'icon' => $aiData['icon'] ?? 'fas fa-tools',
                'meta_title' => $cleanText($aiData['meta_title'] ?? '', 60),
                'meta_description' => $cleanText($aiData['meta_description'] ?? '', 160),
                'og_title' => $cleanText($aiData['og_title'] ?? '', 60),
                'og_description' => $cleanText($aiData['og_description'] ?? '', 160),
                'twitter_title' => $cleanText($aiData['twitter_title'] ?? '', 60),
                'twitter_description' => $cleanText($aiData['twitter_description'] ?? '', 160),
                'meta_keywords' => $cleanText($aiData['meta_keywords'] ?? '', 200)
            ];
            
            // Vérifier que le contenu HTML est valide
            if (empty($validatedData['description']) || strlen($validatedData['description']) < 100) {
                Log::warning('Contenu HTML trop court, utilisation du fallback', [
                    'service' => $serviceName,
                    'city' => $city->name
                ]);
                return $this->generateFallbackContent($serviceName, $city);
            }
            
            return $validatedData['description'];
            
        } catch (\Exception $e) {
            Log::error('Erreur validation données IA', [
                'service' => $serviceName,
                'city' => $city->name,
                'error' => $e->getMessage()
            ]);
            return $this->generateFallbackContent($serviceName, $city);
        }
    }
    
    /**
     * Nettoyer le contenu HTML généré par l'IA
     */
    private function cleanHtmlContent($content)
    {
        // Supprimer les balises markdown
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = preg_replace('/```html\s*/', '', $content);
        
        // Nettoyer les caractères d'échappement
        $content = str_replace(['\"', '\\n', '\\t'], ['"', "\n", "\t"], $content);
        
        return trim($content);
    }
    
    /**
     * Générer un contenu de fallback en cas d'erreur IA
     */
    private function generateFallbackContent($serviceName, $city)
    {
        $siteUrl = setting('site_url', config('app.url'));
        if (!str_starts_with($siteUrl, 'http')) {
            $siteUrl = 'https://' . $siteUrl;
        }
        $formUrl = $siteUrl . '/form/propertyType';
        $adUrl = $siteUrl . '/annonces/' . Str::slug($serviceName . '-' . $city->name);
        $adTitle = $serviceName . ' à ' . $city->name;
        
        return '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">Service professionnel de ' . $serviceName . ' à ' . $city->name . ', une expertise reconnue dans ' . ($city->region ?? '') . '. Notre entreprise spécialisée intervient sur tous types de bâtiments pour des travaux de ' . $serviceName . ' durables et esthétiques, adaptés aux spécificités climatiques locales.</p>
      <p class="text-lg leading-relaxed">Spécialistes en travaux de ' . $serviceName . ' pour une rénovation de qualité supérieure. Nous maîtrisons les techniques modernes de pose, de réparation et de rénovation, garantissant des résultats durables et performants pour votre habitation.</p>
    </div>
    
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
      <p class="leading-relaxed mb-3">Chez ' . setting('company_name', 'Notre Entreprise') . ', nous garantissons la satisfaction totale de nos clients à ' . $city->name . ' et dans toute la région de ' . ($city->region ?? '') . '. Chaque intervention de ' . $serviceName . ' est réalisée selon les normes professionnelles les plus strictes et les réglementations en vigueur.</p>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $serviceName . '</h3>
    <ul class="space-y-3">
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réparation et maintenance</strong> - Diagnostic précis et traitement adapté pour restaurer l\'intégrité de votre ' . $serviceName . ', avec intervention rapide et efficace</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Rénovation complète</strong> - Remplacement intégral avec matériaux de qualité et techniques modernes, garantissant une performance optimale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation professionnelle</strong> - Pose selon les normes en vigueur, avec choix de matériaux adaptés à votre région</span>
      </li>
    </ul>
  </div>
  
  <div class="space-y-6">
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
    <p class="leading-relaxed">Une connaissance approfondie des exigences climatiques locales pour chaque projet de ' . $serviceName . ' à ' . $city->name . '.</p>
    
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit et personnalisé pour vos travaux de ' . $serviceName . '.</p>
      <a href="' . $formUrl . '" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Financement possible pour les travaux de ' . $serviceName . ' avec nos partenaires bancaires</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Garantie de 10 ans sur nos interventions de ' . $serviceName . ' et matériaux utilisés</span>
        </li>
      </ul>
    </div>
    
    <!-- Boutons de partage social -->
    <div class="mt-8 pt-6 border-t border-gray-200">
      <div class="text-center">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Partager ce service</h4>
        <div class="flex justify-center items-center space-x-4">
          <a href="https://www.facebook.com/sharer/sharer.php?u=' . $adUrl . '&quote=' . urlencode($adTitle) . '" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fab fa-facebook-f text-lg"></i>
            <span class="font-medium">Facebook</span>
          </a>
          <a href="https://wa.me/?text=' . urlencode($adTitle . ' - ' . $adUrl) . '" target="_blank" rel="noopener noreferrer" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fab fa-whatsapp text-lg"></i>
            <span class="font-medium">WhatsApp</span>
          </a>
          <a href="mailto:?subject=' . urlencode($adTitle) . '&body=' . urlencode('Je vous partage ce service intéressant : ' . $adUrl) . '" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fas fa-envelope text-lg"></i>
            <span class="font-medium">Email</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>';
    }
    
    /**
     * Générer du contenu personnalisé via l'IA pour un mot-clé et une ville
     */
    private function generateKeywordAdContentWithAI($keyword, $city, $aiPrompt = null)
    {
        try {
            // Récupérer l'URL du site et du formulaire
            $siteUrl = setting('site_url', config('app.url'));
            if (!str_starts_with($siteUrl, 'http')) {
                $siteUrl = 'https://' . $siteUrl;
            }
            $formUrl = $siteUrl . '/form/propertyType';
            $adUrl = $siteUrl . '/annonces/' . Str::slug($keyword . '-' . $city->name);
            $adTitle = ucfirst($keyword) . ' à ' . $city->name;
            
            // Construire le prompt personnalisé pour le mot-clé
            $prompt = $this->buildKeywordAdPrompt($keyword, $city, $aiPrompt);
            
            // Appel à l'API OpenAI
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . setting('openai_api_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiContent = $data['choices'][0]['message']['content'] ?? '';
                
                // Nettoyer et valider le contenu
                $content = $this->validateAndCleanAIData($aiContent, $keyword, $city);
                
                // Remplacer les variables dans le contenu
                $content = str_replace([
                    '[FORM_URL]',
                    '[URL]',
                    '[TITRE]'
                ], [
                    $formUrl,
                    $adUrl,
                    $adTitle
                ], $content);
                
                return $content;
            } else {
                Log::error('Erreur API OpenAI pour génération annonce mot-clé', [
                    'keyword' => $keyword,
                    'city' => $city->name,
                    'response' => $response->body()
                ]);
                return $this->generateKeywordFallbackContent($keyword, $city);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération contenu IA pour annonce mot-clé', [
                'keyword' => $keyword,
                'city' => $city->name,
                'error' => $e->getMessage()
            ]);
            return $this->generateKeywordFallbackContent($keyword, $city);
        }
    }
    
    /**
     * Construire le prompt personnalisé pour une annonce mot-clé
     */
    private function buildKeywordAdPrompt($keyword, $city, $aiPrompt = null)
    {
        $basePrompt = "Tu es un expert en rédaction web pour les services de rénovation.

INFORMATIONS SUR LE SERVICE:
- Type de service : {$keyword}
- Ville : {$city->name}
- Département : " . ($city->region ?? '') . "
- Objectif : créer un contenu web professionnel, engageant et optimisé pour le SEO, adapté à ce type de service dans cette ville spécifique.

TÂCHE:
Génère un contenu web complet comprenant :

1. Une description courte (120-140 caractères) accrocheuse et SEO.
2. Une longue description générale expliquant le service et les bénéfices pour le client.
3. Une icône Font Awesome adaptée au service.
4. Un titre SEO optimisé (50-60 caractères).
5. Une description SEO (150-160 caractères).
6. Des mots-clés pertinents séparés par des virgules.
7. Un contenu HTML complet structuré professionnellement, avec exactement **10 champs** :
   - **Champ 1 :** Introduction courte et longue
   - **Champ 2 :** Engagement / garanties
   - **Champ 3 :** Prestations / services (exactement 10 prestations spécifiques au service {$keyword})
   - **Champ 4 :** Pourquoi choisir ce service
   - **Champ 5 :** Expertise / expérience
   - **Champ 6 :** CTA : demande de devis
   - **Champ 7 :** Informations pratiques
   - **Champ 8 :** FAQ (3 questions-réponses minimum)
   - **Champ 9 :** Financement / aides disponibles
   - **Champ 10 :** Partage social

STRUCTURE HTML OBLIGATOIRE (utilise exactement cette structure):
<div class=\"grid md:grid-cols-2 gap-8\">

  <!-- Colonne gauche : Contenu principal -->
  <div class=\"space-y-6\">

    <!-- Champ 1 : Introduction / description courte + longue -->
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">[Description courte du service {$keyword} à {$city->name} en 250 caractères maximum]</p>
      <p class=\"text-lg leading-relaxed\">[Longue description détaillée du service {$keyword}, bénéfices, explications techniques, avantages, matériaux utilisés, spécifique à {$city->name}]</p>
    </div>

    <!-- Champ 2 : Engagement / garanties -->
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">[Titre de l'engagement ou garantie pour {$keyword}]</h3>
      <p class=\"leading-relaxed mb-3\">[Description de l'engagement]</p>
      <p class=\"leading-relaxed\">[Détails techniques ou matériaux utilisés]</p>
    </div>

    <!-- Champ 3 : Prestations / services -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$keyword}</h3>
    <ul class=\"space-y-3\">
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 1 spécifique à {$keyword}]</strong> - [Description détaillée]</span>
      </li>
      <!-- Répéter jusqu'à 10 prestations spécifiques à {$keyword} -->
    </ul>

    <!-- Champ 8 : FAQ -->
    <div class=\"bg-gray-50 p-6 rounded-lg mt-6\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">FAQ</h4>
      <div class=\"space-y-2\">
        <p><strong>Q1 : [Question fréquente sur {$keyword}]</strong></p>
        <p>A : [Réponse détaillée]</p>
        <p><strong>Q2 : [Question fréquente sur {$keyword}]</strong></p>
        <p>A : [Réponse détaillée]</p>
        <p><strong>Q3 : [Question fréquente sur {$keyword}]</strong></p>
        <p>A : [Réponse détaillée]</p>
      </div>
    </div>

  </div>

  <!-- Colonne droite : Informations complémentaires -->
  <div class=\"space-y-6\">

    <!-- Champ 4 : Pourquoi choisir ce service -->
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi choisir ce service</h3>
      <p class=\"leading-relaxed\">[Points forts et avantages du service {$keyword} à {$city->name}]</p>
    </div>

    <!-- Champ 5 : Expertise / expérience -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">[Description de l'expertise et expérience en {$keyword} à {$city->name}]</p>

    <!-- Champ 9 : Financement / aides disponibles -->
    <div class=\"bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Financement et aides</h4>
      <p>[Informations sur les aides financières et options de financement pour {$keyword}]</p>
    </div>

    <!-- Champ 6 : CTA - demande de devis -->
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un devis ?</h4>
      <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour {$keyword} à {$city->name}.</p>
      <a href=\"[FORM_URL]\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
    </div>

    <!-- Champ 7 : Informations pratiques -->
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 1 pour {$keyword}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 2 pour {$keyword}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 3 pour {$keyword}]</span></li>
      </ul>
    </div>

    <!-- Champ 10 : Partage social -->
    <div class=\"mt-8 pt-6 border-t border-gray-200\">
      <div class=\"text-center\">
        <h4 class=\"text-lg font-semibold text-gray-800 mb-4\">Partager ce service</h4>
        <div class=\"flex justify-center items-center space-x-4\">
          <a href=\"https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fab fa-facebook-f text-lg\"></i>
            <span class=\"font-medium\">Facebook</span>
          </a>
          <a href=\"https://wa.me/?text=[TITRE] - [URL]\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fab fa-whatsapp text-lg\"></i>
            <span class=\"font-medium\">WhatsApp</span>
          </a>
          <a href=\"mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]\" class=\"bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fas fa-envelope text-lg\"></i>
            <span class=\"font-medium\">Email</span>
          </a>
        </div>
      </div>
    </div>

  </div>

</div>

INSTRUCTIONS IMPORTANTES:
- Le contenu doit être UNIQUE, professionnel et engageant.
- Utilise un vocabulaire technique adapté à {$keyword}.
- PERSONNALISE le contenu pour {$city->name} et sa région.
- Inclue les aides financières et options de financement possibles.
- Fournis les informations pratiques, conseils et suivi après travaux.
- Ne mentionne jamais le nom de l'entreprise ni la localisation.
- Garde la structure HTML exacte.
- Génère des prestations détaillées avec descriptions explicites spécifiques à {$keyword}.
- OBLIGATOIRE : Génère une LONGUE DESCRIPTION détaillée (minimum 300 caractères) dans le champ long_description
- La longue description doit expliquer les bénéfices, techniques utilisées, matériaux, avantages du service {$keyword}
- NE PAS inclure de bouton \"En savoir plus\" ou lien externe dans le contenu
- REMPLACE [FORM_URL] par l'URL du formulaire de devis
- GÉNÈRE exactement 10 prestations spécifiques au service {$keyword}
- PERSONNALISE chaque prestation selon le type de service
- UTILISE un vocabulaire technique approprié au secteur
- Fournis le résultat en **JSON** valide avec les champs suivants :

{
\"description\": \"[HTML complet avec structure exacte]\",
\"short_description\": \"[Description courte 120-140 caractères]\",
\"long_description\": \"[Longue description détaillée du service {$keyword}, bénéfices, techniques, matériaux, avantages - minimum 300 caractères]\",
\"icon\": \"fas fa-[icône appropriée]\",
\"meta_title\": \"[Titre SEO 50-60 caractères]\",
\"meta_description\": \"[Description SEO 150-160 caractères]\",
\"og_title\": \"[Titre Open Graph 50-60 caractères]\",
\"og_description\": \"[Description Open Graph 150-160 caractères]\",
\"twitter_title\": \"[Titre Twitter 50-60 caractères]\",
\"twitter_description\": \"[Description Twitter 150-160 caractères]\",
\"meta_keywords\": \"[Mots-clés pertinents séparés par virgules]\"
}";

        // Ajouter le prompt personnalisé si fourni
        if ($aiPrompt) {
            $basePrompt .= "\n\nINSTRUCTIONS PERSONNALISÉES SUPPLÉMENTAIRES:\n" . $aiPrompt;
        }

        return $basePrompt;
    }
    
    /**
     * Générer un contenu de fallback pour les mots-clés
     */
    private function generateKeywordFallbackContent($keyword, $city)
    {
        $siteUrl = setting('site_url', config('app.url'));
        if (!str_starts_with($siteUrl, 'http')) {
            $siteUrl = 'https://' . $siteUrl;
        }
        $formUrl = $siteUrl . '/form/propertyType';
        $adUrl = $siteUrl . '/annonces/' . Str::slug($keyword . '-' . $city->name);
        $adTitle = ucfirst($keyword) . ' à ' . $city->name;
        
        return '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">Service professionnel de ' . $keyword . ' à ' . $city->name . ', une expertise reconnue dans ' . ($city->region ?? '') . '. Notre entreprise spécialisée intervient sur tous types de bâtiments pour des travaux de ' . $keyword . ' durables et esthétiques, adaptés aux spécificités climatiques locales.</p>
      <p class="text-lg leading-relaxed">Spécialistes en travaux de ' . $keyword . ' pour une rénovation de qualité supérieure. Nous maîtrisons les techniques modernes de pose, de réparation et de rénovation, garantissant des résultats durables et performants pour votre habitation.</p>
    </div>
    
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
      <p class="leading-relaxed mb-3">Chez ' . setting('company_name', 'Notre Entreprise') . ', nous garantissons la satisfaction totale de nos clients à ' . $city->name . ' et dans toute la région de ' . ($city->region ?? '') . '. Chaque intervention de ' . $keyword . ' est réalisée selon les normes professionnelles les plus strictes et les réglementations en vigueur.</p>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $keyword . '</h3>
    <ul class="space-y-3">
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réparation et maintenance</strong> - Diagnostic précis et traitement adapté pour restaurer l\'intégrité de votre ' . $keyword . ', avec intervention rapide et efficace</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Rénovation complète</strong> - Remplacement intégral avec matériaux de qualité et techniques modernes, garantissant une performance optimale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation professionnelle</strong> - Pose selon les normes en vigueur, avec choix de matériaux adaptés à votre région</span>
      </li>
    </ul>
  </div>
  
  <div class="space-y-6">
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
    <p class="leading-relaxed">Une connaissance approfondie des exigences climatiques locales pour chaque projet de ' . $keyword . ' à ' . $city->name . '.</p>
    
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit et personnalisé pour vos travaux de ' . $keyword . '.</p>
      <a href="' . $formUrl . '" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Financement possible pour les travaux de ' . $keyword . ' avec nos partenaires bancaires</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Garantie de 10 ans sur nos interventions de ' . $keyword . ' et matériaux utilisés</span>
        </li>
      </ul>
    </div>
    
    <!-- Boutons de partage social -->
    <div class="mt-8 pt-6 border-t border-gray-200">
      <div class="text-center">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Partager ce service</h4>
        <div class="flex justify-center items-center space-x-4">
          <a href="https://www.facebook.com/sharer/sharer.php?u=' . $adUrl . '&quote=' . urlencode($adTitle) . '" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fab fa-facebook-f text-lg"></i>
            <span class="font-medium">Facebook</span>
          </a>
          <a href="https://wa.me/?text=' . urlencode($adTitle . ' - ' . $adUrl) . '" target="_blank" rel="noopener noreferrer" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fab fa-whatsapp text-lg"></i>
            <span class="font-medium">WhatsApp</span>
          </a>
          <a href="mailto:?subject=' . urlencode($adTitle) . '&body=' . urlencode('Je vous partage ce service intéressant : ' . $adUrl) . '" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i class="fas fa-envelope text-lg"></i>
            <span class="font-medium">Email</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>';
    }

    /**
     * Récupérer les villes favorites
     */
    public function getFavoriteCities()
    {
        $favoriteCityIds = Setting::get('favorite_cities', []);
        $cities = City::whereIn('id', $favoriteCityIds)->orderBy('name')->get();
        
        return response()->json($cities);
    }

    /**
     * Récupérer les villes par région
     */
    public function getCitiesByRegion(Request $request)
    {
        $region = $request->input('region');
        
        if ($region) {
            $cities = City::where('region', $region)->orderBy('name')->get();
        } else {
            $cities = City::orderBy('name')->get();
        }
        
        return response()->json($cities);
    }

    /**
     * Générer des annonces en masse par mot-clé
     */
    public function generateBulkAdsByKeyword(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:100',
            'keyword_ai_prompt' => 'nullable|string|max:5000',
            'keyword_batch_size' => 'nullable|integer|min:1|max:100',
            'include_all_cities' => 'boolean'
        ]);

        try {
            $keyword = $request->input('keyword');
            
            // Récupérer toutes les villes ou seulement les favorites
            if ($request->boolean('include_all_cities')) {
                $cities = City::orderBy('name')->get();
            } else {
                $cities = City::where('is_favorite', true)->orderBy('name')->get();
                
                // Si pas de villes favorites configurées, utiliser les 10 premières villes
                if ($cities->isEmpty()) {
                    $cities = City::orderBy('name')->take(10)->get();
                    Log::info('Aucune ville favorite configurée, utilisation des 10 premières villes');
                }
            }

            $batchSize = $request->input('keyword_batch_size', 10);
            $aiPrompt = $request->input('keyword_ai_prompt');
            
            Log::info('Début génération en masse par mot-clé', [
                'keyword' => $keyword,
                'cities_count' => $cities->count(),
                'batch_size' => $batchSize,
                'include_all_cities' => $request->boolean('include_all_cities')
            ]);

            // Le contenu sera généré individuellement pour chaque ville via l'IA
            
            $createdAds = 0;
            $skippedAds = 0;
            $errors = [];

            // Traiter les villes par batch
            $cities->chunk($batchSize)->each(function ($cityBatch) use ($keyword, $template, &$createdAds, &$skippedAds, &$errors) {
                foreach ($cityBatch as $city) {
                    try {
                        // Vérifier si une annonce existe déjà
                        $existingAd = Ad::where('keyword', $keyword)
                            ->where('city_id', $city->id)
                            ->first();

                        if ($existingAd) {
                            $skippedAds++;
                            Log::info('Annonce déjà existante', [
                                'keyword' => $keyword,
                                'city' => $city->name
                            ]);
                            continue;
                        }

                        // Générer le contenu personnalisé via l'IA pour cette ville
                        $content = $this->generateKeywordAdContentWithAI($keyword, $city, $aiPrompt);
                        
                        // Créer l'annonce
                        $ad = Ad::create([
                            'title' => ucfirst($keyword) . ' à ' . $city->name,
                            'keyword' => $keyword,
                            'city_id' => $city->id,
                            'slug' => Str::slug($keyword . '-' . $city->name),
                            'status' => 'published',
                            'meta_title' => ucfirst($keyword) . ' à ' . $city->name . ' | Devis Gratuit',
                            'meta_description' => 'Service professionnel de ' . $keyword . ' à ' . $city->name . '. Devis gratuit et intervention rapide.',
                            'content_html' => $content,
                            'content_json' => json_encode([
                                'keyword' => $keyword,
                                'city' => $city->toArray(),
                                'template_type' => 'bulk_generated_keyword',
                                'generated_at' => now()->toISOString()
                            ])
                        ]);

                        $createdAds++;
                        
                        Log::info('Annonce créée par mot-clé', [
                            'ad_id' => $ad->id,
                            'keyword' => $keyword,
                            'city' => $city->name,
                            'slug' => $ad->slug
                        ]);

                        // Pause pour éviter de surcharger
                        usleep(100000); // 0.1 seconde

                    } catch (\Exception $e) {
                        $errors[] = [
                            'city' => $city->name,
                            'error' => $e->getMessage()
                        ];
                        Log::error('Erreur création annonce par mot-clé', [
                            'city' => $city->name,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('Génération en masse par mot-clé terminée', [
                'keyword' => $keyword,
                'created_ads' => $createdAds,
                'skipped_ads' => $skippedAds,
                'errors_count' => count($errors)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Génération terminée : {$createdAds} annonces créées pour le mot-clé '{$keyword}', {$skippedAds} ignorées",
                'data' => [
                    'keyword' => $keyword,
                    'created_ads' => $createdAds,
                    'skipped_ads' => $skippedAds,
                    'errors_count' => count($errors),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur génération en masse par mot-clé: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ], 500);
        }
    }

}
