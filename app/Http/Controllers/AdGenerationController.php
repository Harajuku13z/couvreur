<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Models\GenerationJob;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdGenerationController extends Controller
{
    /**
     * Génération d'annonces par service et villes
     */
    public function generateByServiceCities(Request $request)
    {
        Log::info('Début génération service-villes', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            // Validation personnalisée pour service_slug
            $request->validate([
                'service_slug' => 'required|string',
                'city_ids' => 'required|array|min:1',
                'city_ids.*' => 'required|integer|exists:cities,id',
                'ai_prompt' => 'nullable|string|max:5000',
                'batch_size' => 'nullable|integer|min:1|max:50'
            ]);

            // Vérifier que le service existe dans les settings
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $serviceSlug = $request->input('service_slug');
            $serviceExists = collect($services)->contains('slug', $serviceSlug);
            
            if (!$serviceExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le service sélectionné n\'existe pas'
                ], 422);
            }

            $validated = $request->all();

            $serviceSlug = $validated['service_slug'];
            $cityIds = $validated['city_ids'];
            $aiPrompt = $validated['ai_prompt'] ?? '';
            $batchSize = $validated['batch_size'] ?? 20;

            Log::info('Validation réussie', [
                'service_slug' => $serviceSlug,
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

            // Traitement immédiat au lieu d'utiliser les queues
            $this->processServiceCitiesGeneration($serviceSlug, $cityIds, $aiPrompt, $batchSize);

            return response()->json([
                'success' => true,
                'message' => 'Génération terminée avec succès',
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation service-villes', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur génération service-villes: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génération d'annonces par mot-clé et villes
     */
    public function generateByKeywordCities(Request $request)
    {
        Log::info('Début génération mot-clé-villes', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            $validated = $request->validate([
                'keyword' => 'required|string|max:255',
                'city_ids' => 'required|array|min:1',
                'city_ids.*' => 'required|integer|exists:cities,id',
                'ai_prompt' => 'nullable|string|max:5000',
                'batch_size' => 'nullable|integer|min:1|max:50'
            ]);

            $keyword = $validated['keyword'];
            $cityIds = $validated['city_ids'];
            $aiPrompt = $validated['ai_prompt'] ?? '';
            $batchSize = $validated['batch_size'] ?? 20;

            Log::info('Validation réussie', [
                'keyword' => $keyword,
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

            // Traitement immédiat au lieu d'utiliser les queues
            $this->processKeywordCitiesGeneration($keyword, $cityIds, $aiPrompt, $batchSize);

            return response()->json([
                'success' => true,
                'message' => 'Génération terminée avec succès',
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation mot-clé-villes', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur génération mot-clé-villes: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traitement immédiat de la génération par service et villes
     */
    private function processServiceCitiesGeneration($serviceSlug, $cityIds, $aiPrompt, $batchSize)
    {
        Log::info('Début traitement immédiat service-villes', [
            'service_slug' => $serviceSlug,
            'cities_count' => count($cityIds),
            'batch_size' => $batchSize
        ]);

        try {
            // Récupérer le service depuis les settings
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $service = collect($services)->firstWhere('slug', $serviceSlug);
            
            if (!$service) {
                throw new \Exception('Service non trouvé');
            }

            // Récupérer les villes
            $cities = City::whereIn('id', $cityIds)->get();
            
            $createdAds = 0;
            $errors = [];

            foreach ($cities as $city) {
                try {
                    // Vérifier si une annonce existe déjà pour cette combinaison
                    $existingAd = Ad::where('keyword', $service['name'])
                        ->where('city_id', $city->id)
                        ->first();

                    if ($existingAd) {
                        Log::info('Annonce déjà existante', [
                            'service' => $service['name'],
                            'city' => $city->name
                        ]);
                        continue;
                    }

                    // Créer l'annonce
                    $ad = Ad::create([
                        'title' => $service['name'] . ' à ' . $city->name,
                        'keyword' => $service['name'],
                        'city_id' => $city->id,
                        'slug' => Str::slug($service['name'] . ' ' . $city->name),
                        'status' => 'draft',
                        'meta_title' => $service['name'] . ' à ' . $city->name . ' | Devis Gratuit',
                        'meta_description' => 'Service professionnel de ' . $service['name'] . ' à ' . $city->name . '. Devis gratuit et intervention rapide.',
                        'content_html' => $this->generateAdContent($service, $city, $aiPrompt),
                        'content_json' => json_encode([
                            'service' => $service,
                            'city' => $city->toArray(),
                            'ai_prompt' => $aiPrompt
                        ])
                    ]);

                    $createdAds++;
                    Log::info('Annonce créée', [
                        'ad_id' => $ad->id,
                        'service' => $service['name'],
                        'city' => $city->name
                    ]);

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

            Log::info('Traitement terminé', [
                'created_ads' => $createdAds,
                'errors_count' => count($errors),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur traitement service-villes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Traitement immédiat de la génération par mot-clé et villes
     */
    private function processKeywordCitiesGeneration($keyword, $cityIds, $aiPrompt, $batchSize)
    {
        Log::info('Début traitement immédiat mot-clé-villes', [
            'keyword' => $keyword,
            'cities_count' => count($cityIds),
            'batch_size' => $batchSize
        ]);

        try {
            // Récupérer les villes
            $cities = City::whereIn('id', $cityIds)->get();
            
            $createdAds = 0;
            $errors = [];

            foreach ($cities as $city) {
                try {
                    // Vérifier si une annonce existe déjà pour cette combinaison
                    $existingAd = Ad::where('keyword', $keyword)
                        ->where('city_id', $city->id)
                        ->first();

                    if ($existingAd) {
                        Log::info('Annonce déjà existante', [
                            'keyword' => $keyword,
                            'city' => $city->name
                        ]);
                        continue;
                    }

                    // Créer l'annonce
                    $ad = Ad::create([
                        'title' => $keyword . ' à ' . $city->name,
                        'keyword' => $keyword,
                        'city_id' => $city->id,
                        'slug' => Str::slug($keyword . ' ' . $city->name),
                        'status' => 'draft',
                        'meta_title' => $keyword . ' à ' . $city->name . ' | Devis Gratuit',
                        'meta_description' => 'Service professionnel de ' . $keyword . ' à ' . $city->name . '. Devis gratuit et intervention rapide.',
                        'content_html' => $this->generateKeywordAdContent($keyword, $city, $aiPrompt),
                        'content_json' => json_encode([
                            'keyword' => $keyword,
                            'city' => $city->toArray(),
                            'ai_prompt' => $aiPrompt
                        ])
                    ]);

                    $createdAds++;
                    Log::info('Annonce créée', [
                        'ad_id' => $ad->id,
                        'keyword' => $keyword,
                        'city' => $city->name
                    ]);

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

            Log::info('Traitement terminé', [
                'created_ads' => $createdAds,
                'errors_count' => count($errors),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur traitement mot-clé-villes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer le contenu d'une annonce
     */
    private function generateAdContent($service, $city, $aiPrompt)
    {
        $apiKey = setting('chatgpt_api_key');
        
        if (!$apiKey) {
            Log::error('Clé API manquante pour génération annonce');
            return $this->generateFallbackAdContent($service, $city);
        }
        
        try {
            // Récupérer les informations de l'entreprise
            $companyInfo = [
                'company_name' => setting('company_name', 'Notre Entreprise'),
                'company_city' => setting('company_city', ''),
                'company_region' => setting('company_region', ''),
                'company_phone' => setting('company_phone', ''),
                'company_email' => setting('company_email', ''),
            ];
            
            // Prompt avec structure spécifique pour les annonces
            $prompt = "Crée un contenu HTML professionnel pour une annonce de service de rénovation.

INFORMATIONS:
- Entreprise: {$companyInfo['company_name']}
- Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
- Service: {$service['name']}
- Ville spécifique: {$city->name}";
            
            if ($city->postal_code) {
                $prompt .= " ({$city->postal_code})";
            }
            
            if ($aiPrompt) {
                $prompt .= "\n\nINSTRUCTIONS PERSONNALISÉES:\n{$aiPrompt}";
            }

            $prompt .= "\n\nSTRUCTURE HTML OBLIGATOIRE - EXACTEMENT COMME CET EXEMPLE:
<div class=\"grid md:grid-cols-2 gap-8\">
  <div class=\"space-y-6\">
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">[Introduction personnalisée pour {$service['name']} à {$city->name}]</p>
      <p class=\"text-lg leading-relaxed\">[Expertise spécifique au service {$service['name']} à {$city->name}]</p>
      <p class=\"text-lg leading-relaxed\">[Approche personnalisée et satisfaction client à {$city->name}]</p>
    </div>
    
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité</h3>
      <p class=\"leading-relaxed mb-3\">Chez {$companyInfo['company_name']}, nous garantissons la satisfaction totale.</p>
      <p class=\"leading-relaxed\">[Matériaux et techniques spécifiques à {$service['name']} à {$city->name}]</p>
    </div>
    
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$service['name']}</h3>
    <ul class=\"space-y-3\">
      <li class=\"flex items-start\"><span><strong>[Prestation 1 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 2 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 3 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 4 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 5 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 6 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 7 spécifique à {$service['name']}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 8 spécifique à {$service['name']}]</strong></span></li>
    </ul>
    
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir Notre Entreprise</h3>
      <p class=\"leading-relaxed\">[Réputation locale pour {$service['name']} à {$city->name}]</p>
    </div>
  </div>
  
  <div class=\"space-y-6\">
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">[Expertise locale pour {$service['name']} à {$city->name}]</p>
    
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis ?</h4>
      <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour vos {$service['name']} à {$city->name}.</p>
      <a href=\"https://www.jd-renovation-service.fr/form/propertyType\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
    </div>
    
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\"><span>[Info pratique 1 pour {$service['name']} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 2 pour {$service['name']} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 3 pour {$service['name']} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 4 pour {$service['name']} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 5 pour {$service['name']} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 6 pour {$service['name']} à {$city->name}]</span></li>
      </ul>
    </div>
  </div>
</div>

INSTRUCTIONS DÉTAILLÉES:
1. ADAPTE complètement le contenu au service spécifique: {$service['name']}
2. ÉCRIS du contenu PERSONNALISÉ selon le type de service (toiture, façade, isolation, gouttières, etc.)
3. UTILISE les informations de l'entreprise: {$companyInfo['company_name']}
4. INTÉGRE la localisation spécifique: {$city->name}";
            
            if ($city->postal_code) {
                $prompt .= " ({$city->postal_code})";
            }
            
            $prompt .= "
5. GARDE la structure HTML exacte de l'exemple ci-dessus
6. PERSONNALISE les prestations selon le service (pas de contenu générique)
7. ÉCRIS du contenu UNIQUE et SPÉCIFIQUE au service et à la ville
8. ADAPTE le vocabulaire et les formulations selon le service
9. INCLUE des informations sur le financement, les garanties, les délais
10. VARIE le contenu pour éviter les répétitions
11. ADAPTE tout le contenu à la ville spécifique: {$city->name}

IMPORTANT:
- SUIVEZ EXACTEMENT la structure HTML de l'exemple
- ÉCRIVEZ du contenu PERSONNALISÉ pour le service {$service['name']} à {$city->name}
- ADAPTEZ les prestations selon le type de service (toiture, façade, isolation, etc.)
- GARDEZ les classes CSS et la structure
- UTILISEZ les informations de l'entreprise et de la localisation
- Le contenu doit être professionnel et engageant
- ÉVITEZ la répétition de phrases identiques
- Variez le vocabulaire et les formulations
- INCLUEZ des informations sur le financement et les garanties
- ADAPTEZ le contenu selon le service spécifique et la ville

Réponds UNIQUEMENT avec le HTML complet, sans JSON, sans texte avant ou après.";

            Log::info('Génération IA pour annonce', [
                'service_name' => $service['name'],
                'city_name' => $city->name,
                'prompt_length' => strlen($prompt)
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.8
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                Log::info('Réponse IA pour annonce reçue', [
                    'service_name' => $service['name'],
                    'city_name' => $city->name,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 300)
                ]);
                
                if (!empty(trim($content))) {
                    return $content;
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération IA annonce: ' . $e->getMessage());
        }
        
        // Fallback en cas d'échec
        return $this->generateFallbackAdContent($service, $city);
    }

    /**
     * Contenu de fallback pour les annonces
     */
    private function generateFallbackAdContent($service, $city)
    {
        $companyName = setting('company_name', 'Notre Entreprise');
        
        $content = '<div class="grid md:grid-cols-2 gap-8">';
        $content .= '<div class="space-y-6">';
        
        $content .= '<div class="space-y-4">';
        $content .= '<p class="text-lg leading-relaxed">Service professionnel de ' . $service['name'] . ' à ' . $city->name . '. ' . $companyName . ' vous propose ses services avec expertise et qualité.</p>';
        $content .= '<p class="text-lg leading-relaxed">Découvrez notre expertise professionnelle en ' . $service['name'] . ' à ' . $city->name . '. Nous assurons un service de qualité avec des matériaux de qualité et des techniques éprouvées.</p>';
        $content .= '<p class="text-lg leading-relaxed">Chaque projet bénéficie d\'une attention personnalisée et d\'un accompagnement complet pour garantir la satisfaction de nos clients.</p>';
        $content .= '</div>';
        
        $content .= '<div class="bg-blue-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>';
        $content .= '<p class="leading-relaxed mb-3">Chez ' . $companyName . ', nous garantissons la satisfaction totale.</p>';
        $content .= '<p class="leading-relaxed">Nous utilisons des matériaux de haute qualité et des techniques modernes pour un service professionnel.</p>';
        $content .= '</div>';
        
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $service['name'] . '</h3>';
        $content .= '<ul class="space-y-3">';
        $content .= '<li class="flex items-start"><span><strong>Service professionnel de qualité</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Matériaux de haute qualité</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Techniques éprouvées</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Garantie de satisfaction</strong></span></li>';
        $content .= '</ul>';
        
        $content .= '<div class="bg-green-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>';
        $content .= '<p class="leading-relaxed">Notre réputation à ' . $city->name . ' repose sur notre engagement qualité et notre transparence tarifaire.</p>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        $content .= '<div class="space-y-6">';
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>';
        $content .= '<p class="leading-relaxed">Forts de notre expérience, nous connaissons parfaitement les spécificités de ' . $city->name . ' pour un service adapté et de qualité.</p>';
        
        $content .= '<div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">';
        $content .= '<h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>';
        $content .= '<p class="mb-4">Contactez-nous pour un devis gratuit pour vos ' . $service['name'] . ' à ' . $city->name . '.</p>';
        $content .= '<a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>';
        $content .= '</div>';
        
        $content .= '<div class="bg-gray-50 p-6 rounded-lg">';
        $content .= '<h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>';
        $content .= '<ul class="space-y-2 text-sm">';
        $content .= '<li class="flex items-center"><span>Intervention rapide et efficace à ' . $city->name . '</span></li>';
        $content .= '<li class="flex items-center"><span>Disponibilité 7j/7 pour répondre à vos besoins</span></li>';
        $content .= '<li class="flex items-center"><span>Garantie de satisfaction pour un service impeccable</span></li>';
        $content .= '</ul>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        
        return $content;
    }

    /**
     * Générer le contenu d'une annonce par mot-clé
     */
    private function generateKeywordAdContent($keyword, $city, $aiPrompt)
    {
        $apiKey = setting('chatgpt_api_key');
        
        if (!$apiKey) {
            Log::error('Clé API manquante pour génération annonce mot-clé');
            return $this->generateFallbackKeywordAdContent($keyword, $city);
        }
        
        try {
            // Récupérer les informations de l'entreprise
            $companyInfo = [
                'company_name' => setting('company_name', 'Notre Entreprise'),
                'company_city' => setting('company_city', ''),
                'company_region' => setting('company_region', ''),
                'company_phone' => setting('company_phone', ''),
                'company_email' => setting('company_email', ''),
            ];
            
            // Prompt avec structure spécifique pour les annonces par mot-clé
            $prompt = "Crée un contenu HTML professionnel pour une annonce de service de rénovation.

INFORMATIONS:
- Entreprise: {$companyInfo['company_name']}
- Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
- Mot-clé: {$keyword}
- Ville spécifique: {$city->name}";
            
            if ($city->postal_code) {
                $prompt .= " ({$city->postal_code})";
            }
            
            if ($aiPrompt) {
                $prompt .= "\n\nINSTRUCTIONS PERSONNALISÉES:\n{$aiPrompt}";
            }

            $prompt .= "\n\nSTRUCTURE HTML OBLIGATOIRE - EXACTEMENT COMME CET EXEMPLE:
<div class=\"grid md:grid-cols-2 gap-8\">
  <div class=\"space-y-6\">
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">[Introduction personnalisée pour {$keyword} à {$city->name}]</p>
      <p class=\"text-lg leading-relaxed\">[Expertise spécifique au service {$keyword} à {$city->name}]</p>
      <p class=\"text-lg leading-relaxed\">[Approche personnalisée et satisfaction client à {$city->name}]</p>
    </div>
    
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité</h3>
      <p class=\"leading-relaxed mb-3\">Chez {$companyInfo['company_name']}, nous garantissons la satisfaction totale.</p>
      <p class=\"leading-relaxed\">[Matériaux et techniques spécifiques à {$keyword} à {$city->name}]</p>
    </div>
    
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$keyword}</h3>
    <ul class=\"space-y-3\">
      <li class=\"flex items-start\"><span><strong>[Prestation 1 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 2 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 3 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 4 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 5 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 6 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 7 spécifique à {$keyword}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 8 spécifique à {$keyword}]</strong></span></li>
    </ul>
    
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir Notre Entreprise</h3>
      <p class=\"leading-relaxed\">[Réputation locale pour {$keyword} à {$city->name}]</p>
    </div>
  </div>
  
  <div class=\"space-y-6\">
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">[Expertise locale pour {$keyword} à {$city->name}]</p>
    
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis ?</h4>
      <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour vos {$keyword} à {$city->name}.</p>
      <a href=\"https://www.jd-renovation-service.fr/form/propertyType\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
    </div>
    
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\"><span>[Info pratique 1 pour {$keyword} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 2 pour {$keyword} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 3 pour {$keyword} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 4 pour {$keyword} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 5 pour {$keyword} à {$city->name}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 6 pour {$keyword} à {$city->name}]</span></li>
      </ul>
    </div>
  </div>
</div>

INSTRUCTIONS DÉTAILLÉES:
1. ADAPTE complètement le contenu au mot-clé spécifique: {$keyword}
2. ÉCRIS du contenu PERSONNALISÉ selon le type de service (toiture, façade, isolation, gouttières, etc.)
3. UTILISE les informations de l'entreprise: {$companyInfo['company_name']}
4. INTÉGRE la localisation spécifique: {$city->name}";
            
            if ($city->postal_code) {
                $prompt .= " ({$city->postal_code})";
            }
            
            $prompt .= "
5. GARDE la structure HTML exacte de l'exemple ci-dessus
6. PERSONNALISE les prestations selon le mot-clé (pas de contenu générique)
7. ÉCRIS du contenu UNIQUE et SPÉCIFIQUE au mot-clé et à la ville
8. ADAPTE le vocabulaire et les formulations selon le mot-clé
9. INCLUE des informations sur le financement, les garanties, les délais
10. VARIE le contenu pour éviter les répétitions
11. ADAPTE tout le contenu à la ville spécifique: {$city->name}

IMPORTANT:
- SUIVEZ EXACTEMENT la structure HTML de l'exemple
- ÉCRIVEZ du contenu PERSONNALISÉ pour le mot-clé {$keyword} à {$city->name}
- ADAPTEZ les prestations selon le type de service (toiture, façade, isolation, etc.)
- GARDEZ les classes CSS et la structure
- UTILISEZ les informations de l'entreprise et de la localisation
- Le contenu doit être professionnel et engageant
- ÉVITEZ la répétition de phrases identiques
- Variez le vocabulaire et les formulations
- INCLUEZ des informations sur le financement et les garanties
- ADAPTEZ le contenu selon le mot-clé spécifique et la ville

Réponds UNIQUEMENT avec le HTML complet, sans JSON, sans texte avant ou après.";

            Log::info('Génération IA pour annonce mot-clé', [
                'keyword' => $keyword,
                'city_name' => $city->name,
                'prompt_length' => strlen($prompt)
            ]);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 4000,
                'temperature' => 0.8
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                Log::info('Réponse IA pour annonce mot-clé reçue', [
                    'keyword' => $keyword,
                    'city_name' => $city->name,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 300)
                ]);
                
                if (!empty(trim($content))) {
                    return $content;
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération IA annonce mot-clé: ' . $e->getMessage());
        }
        
        // Fallback en cas d'échec
        return $this->generateFallbackKeywordAdContent($keyword, $city);
    }

    /**
     * Contenu de fallback pour les annonces par mot-clé
     */
    private function generateFallbackKeywordAdContent($keyword, $city)
    {
        $companyName = setting('company_name', 'Notre Entreprise');
        
        $content = '<div class="grid md:grid-cols-2 gap-8">';
        $content .= '<div class="space-y-6">';
        
        $content .= '<div class="space-y-4">';
        $content .= '<p class="text-lg leading-relaxed">Service professionnel de ' . $keyword . ' à ' . $city->name . '. ' . $companyName . ' vous propose ses services avec expertise et qualité.</p>';
        $content .= '<p class="text-lg leading-relaxed">Découvrez notre expertise professionnelle en ' . $keyword . ' à ' . $city->name . '. Nous assurons un service de qualité avec des matériaux de qualité et des techniques éprouvées.</p>';
        $content .= '<p class="text-lg leading-relaxed">Chaque projet bénéficie d\'une attention personnalisée et d\'un accompagnement complet pour garantir la satisfaction de nos clients.</p>';
        $content .= '</div>';
        
        $content .= '<div class="bg-blue-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>';
        $content .= '<p class="leading-relaxed mb-3">Chez ' . $companyName . ', nous garantissons la satisfaction totale.</p>';
        $content .= '<p class="leading-relaxed">Nous utilisons des matériaux de haute qualité et des techniques modernes pour un service professionnel.</p>';
        $content .= '</div>';
        
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $keyword . '</h3>';
        $content .= '<ul class="space-y-3">';
        $content .= '<li class="flex items-start"><span><strong>Service professionnel de qualité</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Matériaux de haute qualité</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Techniques éprouvées</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Garantie de satisfaction</strong></span></li>';
        $content .= '</ul>';
        
        $content .= '<div class="bg-green-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>';
        $content .= '<p class="leading-relaxed">Notre réputation à ' . $city->name . ' repose sur notre engagement qualité et notre transparence tarifaire.</p>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        $content .= '<div class="space-y-6">';
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>';
        $content .= '<p class="leading-relaxed">Forts de notre expérience, nous connaissons parfaitement les spécificités de ' . $city->name . ' pour un service adapté et de qualité.</p>';
        
        $content .= '<div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">';
        $content .= '<h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>';
        $content .= '<p class="mb-4">Contactez-nous pour un devis gratuit pour vos ' . $keyword . ' à ' . $city->name . '.</p>';
        $content .= '<a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>';
        $content .= '</div>';
        
        $content .= '<div class="bg-gray-50 p-6 rounded-lg">';
        $content .= '<h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>';
        $content .= '<ul class="space-y-2 text-sm">';
        $content .= '<li class="flex items-center"><span>Intervention rapide et efficace à ' . $city->name . '</span></li>';
        $content .= '<li class="flex items-center"><span>Disponibilité 7j/7 pour répondre à vos besoins</span></li>';
        $content .= '<li class="flex items-center"><span>Garantie de satisfaction pour un service impeccable</span></li>';
        $content .= '</ul>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        
        return $content;
    }

    /**
     * Statut d'un job de génération
     */
    public function jobStatus(GenerationJob $job)
    {
        return response()->json([
            'id' => $job->id,
            'status' => $job->status,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
            'payload' => json_decode($job->payload_json, true)
        ]);
    }
}