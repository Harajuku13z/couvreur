<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        
        // Récupérer les villes favorites
        $favoriteCities = Setting::get('favorite_cities', []);
        
        // Si pas de villes favorites configurées, utiliser les 10 premières villes
        if (empty($favoriteCities)) {
            $favoriteCities = $cities->take(10)->pluck('id')->toArray();
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
                $favoriteCityIds = Setting::get('favorite_cities', []);
                $cities = City::whereIn('id', $favoriteCityIds)->orderBy('name')->get();
            }

            $batchSize = $request->input('batch_size', 10);
            $aiPrompt = $request->input('ai_prompt');
            
            Log::info('Début génération en masse', [
                'service' => $service['name'],
                'cities_count' => $cities->count(),
                'batch_size' => $batchSize,
                'include_all_cities' => $request->boolean('include_all_cities')
            ]);

            // Générer le template de base pour ce service
            $template = $this->generateServiceTemplate($service, $aiPrompt);
            
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

                        // Générer le contenu personnalisé pour cette ville
                        $content = $this->customizeTemplateForCity($template, $service, $city);
                        
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
     * Générer un template de base pour le service
     */
    private function generateServiceTemplate($service, $aiPrompt = null)
    {
        // Récupérer les informations de l'entreprise
        $companyInfo = [
            'company_name' => setting('company_name', 'Notre Entreprise'),
            'company_city' => setting('company_city', ''),
            'company_region' => setting('company_region', ''),
            'company_phone' => setting('company_phone', ''),
            'company_email' => setting('company_email', ''),
        ];

        // Template HTML de base avec variables
        $template = '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">Service professionnel de {{SERVICE_NAME}} à {{CITY_NAME}}, une expertise reconnue dans {{REGION_NAME}}. Notre entreprise spécialisée intervient sur tous types de bâtiments pour des travaux de {{SERVICE_NAME}} durables et esthétiques, adaptés aux spécificités climatiques locales.</p>
      <p class="text-lg leading-relaxed">Spécialistes en travaux de {{SERVICE_NAME}} pour une rénovation de qualité supérieure. Nous maîtrisons les techniques modernes de pose, de réparation et de rénovation, garantissant des résultats durables et performants pour votre habitation.</p>
      <p class="text-lg leading-relaxed">Approche personnalisée pour chaque projet de {{SERVICE_NAME}}, satisfaction garantie. De l\'audit initial à la finition, notre équipe d\'artisans qualifiés assure un suivi rigoureux et respecte les délais d\'exécution convenus avec nos clients.</p>
    </div>
    
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
      <p class="leading-relaxed mb-3">Chez {{COMPANY_NAME}}, nous garantissons la satisfaction totale de nos clients à {{CITY_NAME}} et dans toute la région de {{REGION_NAME}}. Chaque intervention de {{SERVICE_NAME}} est réalisée selon les normes professionnelles les plus strictes et les réglementations en vigueur.</p>
      <p class="leading-relaxed">Utilisation de matériaux durables et techniques modernes pour votre {{SERVICE_NAME}}. Nous privilégions les produits écologiques et performants, garantissant une longévité exceptionnelle et une esthétique soignée pour votre habitation, tout en respectant l\'environnement.</p>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations {{SERVICE_NAME}}</h3>
    <ul class="space-y-3">
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réparation et maintenance</strong> - Diagnostic précis et traitement adapté pour restaurer l\'intégrité de votre {{SERVICE_NAME}}, avec intervention rapide et efficace</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Rénovation complète</strong> - Remplacement intégral avec matériaux de qualité et techniques modernes, garantissant une performance optimale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation professionnelle</strong> - Pose selon les normes en vigueur, avec choix de matériaux adaptés à votre région</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réfection et rénovation</strong> - Renforcement et réparation des structures, assurant la solidité et la sécurité</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation de systèmes</strong> - Pose et réparation de systèmes complémentaires, optimisant la performance globale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Isolation et étanchéité</strong> - Amélioration de la performance énergétique avec des matériaux isolants performants et durables</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Traitement et protection</strong> - Pose de protections et traitement d\'étanchéité pour une protection optimale contre les intempéries</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Urgences et dépannage</strong> - Intervention rapide 24h/24 pour les réparations d\'urgence, minimisant les dégâts et les risques</span>
      </li>
    </ul>
    
    <div class="bg-green-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>
      <p class="leading-relaxed">Réputation locale solide pour les travaux de {{SERVICE_NAME}} à {{CITY_NAME}} et dans {{REGION_NAME}}. Forts de plus de 15 ans d\'expérience, nous avons réalisé des centaines de projets de {{SERVICE_NAME}} avec succès. Notre connaissance approfondie des spécificités climatiques locales nous permet d\'adapter nos techniques et matériaux pour garantir des résultats durables et esthétiques, tout en respectant votre budget et vos délais.</p>
    </div>
  </div>
  
  <div class="space-y-6">
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
    <p class="leading-relaxed">Une connaissance approfondie des exigences climatiques locales pour chaque projet de {{SERVICE_NAME}} à {{CITY_NAME}}. {{REGION_NAME}} présente des défis spécifiques : humidité, variations de température, pollution urbaine. Notre équipe maîtrise parfaitement ces contraintes et adapte ses interventions en conséquence. Nous utilisons des matériaux testés et approuvés pour résister aux conditions climatiques locales, garantissant ainsi la longévité de vos travaux de {{SERVICE_NAME}} et votre tranquillité d\'esprit.</p>
    
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit et personnalisé pour vos travaux de {{SERVICE_NAME}}. Notre expert se déplace à {{CITY_NAME}} pour évaluer votre projet et vous proposer la solution la plus adaptée à vos besoins et à votre budget, avec des conseils personnalisés.</p>
      <a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Financement possible pour les travaux de {{SERVICE_NAME}} avec nos partenaires bancaires</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Garantie de 10 ans sur nos interventions de {{SERVICE_NAME}} et matériaux utilisés</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Délais d\'exécution rapides et respectés pour votre tranquillité d\'esprit</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Conseils personnalisés pour l\'entretien et la durabilité de votre {{SERVICE_NAME}}</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Équipe qualifiée et professionnelle à votre service pour toute demande</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Respect des normes environnementales et réglementations en vigueur</span>
        </li>
      </ul>
    </div>
  </div>
</div>';

        return $template;
    }

    /**
     * Personnaliser le template pour une ville spécifique
     */
    private function customizeTemplateForCity($template, $service, $city)
    {
        // Remplacer les variables dans le template
        $content = str_replace([
            '{{SERVICE_NAME}}',
            '{{CITY_NAME}}',
            '{{REGION_NAME}}',
            '{{COMPANY_NAME}}'
        ], [
            $service['name'],
            $city->name,
            $city->region ?? setting('company_region', ''),
            setting('company_name', 'Notre Entreprise')
        ], $template);

        return $content;
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
                $favoriteCityIds = Setting::get('favorite_cities', []);
                
                // Si pas de villes favorites configurées, utiliser les 10 premières villes
                if (empty($favoriteCityIds)) {
                    $cities = City::orderBy('name')->take(10)->get();
                    Log::info('Aucune ville favorite configurée, utilisation des 10 premières villes');
                } else {
                    $cities = City::whereIn('id', $favoriteCityIds)->orderBy('name')->get();
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

            // Générer le template de base pour ce mot-clé
            $template = $this->generateKeywordTemplate($keyword, $aiPrompt);
            
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

                        // Générer le contenu personnalisé pour cette ville
                        $content = $this->customizeTemplateForCityByKeyword($template, $keyword, $city);
                        
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

    /**
     * Générer un template de base pour un mot-clé
     */
    private function generateKeywordTemplate($keyword, $aiPrompt = null)
    {
        // Récupérer les informations de l'entreprise
        $companyInfo = [
            'company_name' => setting('company_name', 'Notre Entreprise'),
            'company_city' => setting('company_city', ''),
            'company_region' => setting('company_region', ''),
            'company_phone' => setting('company_phone', ''),
            'company_email' => setting('company_email', ''),
        ];

        // Template HTML de base avec variables pour mot-clé
        $template = '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">Service professionnel de {{KEYWORD}} à {{CITY_NAME}}, une expertise reconnue dans {{REGION_NAME}}. Notre entreprise spécialisée intervient sur tous types de bâtiments pour des travaux de {{KEYWORD}} durables et esthétiques, adaptés aux spécificités climatiques locales.</p>
      <p class="text-lg leading-relaxed">Spécialistes en travaux de {{KEYWORD}} pour une rénovation de qualité supérieure. Nous maîtrisons les techniques modernes de pose, de réparation et de rénovation, garantissant des résultats durables et performants pour votre habitation.</p>
      <p class="text-lg leading-relaxed">Approche personnalisée pour chaque projet de {{KEYWORD}}, satisfaction garantie. De l\'audit initial à la finition, notre équipe d\'artisans qualifiés assure un suivi rigoureux et respecte les délais d\'exécution convenus avec nos clients.</p>
    </div>
    
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
      <p class="leading-relaxed mb-3">Chez {{COMPANY_NAME}}, nous garantissons la satisfaction totale de nos clients à {{CITY_NAME}} et dans toute la région de {{REGION_NAME}}. Chaque intervention de {{KEYWORD}} est réalisée selon les normes professionnelles les plus strictes et les réglementations en vigueur.</p>
      <p class="leading-relaxed">Utilisation de matériaux durables et techniques modernes pour votre {{KEYWORD}}. Nous privilégions les produits écologiques et performants, garantissant une longévité exceptionnelle et une esthétique soignée pour votre habitation, tout en respectant l\'environnement.</p>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations {{KEYWORD}}</h3>
    <ul class="space-y-3">
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réparation et maintenance</strong> - Diagnostic précis et traitement adapté pour restaurer l\'intégrité de votre {{KEYWORD}}, avec intervention rapide et efficace</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Rénovation complète</strong> - Remplacement intégral avec matériaux de qualité et techniques modernes, garantissant une performance optimale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation professionnelle</strong> - Pose selon les normes en vigueur, avec choix de matériaux adaptés à votre région</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réfection et rénovation</strong> - Renforcement et réparation des structures, assurant la solidité et la sécurité</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation de systèmes</strong> - Pose et réparation de systèmes complémentaires, optimisant la performance globale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Isolation et étanchéité</strong> - Amélioration de la performance énergétique avec des matériaux isolants performants et durables</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Traitement et protection</strong> - Pose de protections et traitement d\'étanchéité pour une protection optimale contre les intempéries</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Urgences et dépannage</strong> - Intervention rapide 24h/24 pour les réparations d\'urgence, minimisant les dégâts et les risques</span>
      </li>
    </ul>
    
    <div class="bg-green-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>
      <p class="leading-relaxed">Réputation locale solide pour les travaux de {{KEYWORD}} à {{CITY_NAME}} et dans {{REGION_NAME}}. Forts de plus de 15 ans d\'expérience, nous avons réalisé des centaines de projets de {{KEYWORD}} avec succès. Notre connaissance approfondie des spécificités climatiques locales nous permet d\'adapter nos techniques et matériaux pour garantir des résultats durables et esthétiques, tout en respectant votre budget et vos délais.</p>
    </div>
  </div>
  
  <div class="space-y-6">
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
    <p class="leading-relaxed">Une connaissance approfondie des exigences climatiques locales pour chaque projet de {{KEYWORD}} à {{CITY_NAME}}. {{REGION_NAME}} présente des défis spécifiques : humidité, variations de température, pollution urbaine. Notre équipe maîtrise parfaitement ces contraintes et adapte ses interventions en conséquence. Nous utilisons des matériaux testés et approuvés pour résister aux conditions climatiques locales, garantissant ainsi la longévité de vos travaux de {{KEYWORD}} et votre tranquillité d\'esprit.</p>
    
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit et personnalisé pour vos travaux de {{KEYWORD}}. Notre expert se déplace à {{CITY_NAME}} pour évaluer votre projet et vous proposer la solution la plus adaptée à vos besoins et à votre budget, avec des conseils personnalisés.</p>
      <a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Financement possible pour les travaux de {{KEYWORD}} avec nos partenaires bancaires</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Garantie de 10 ans sur nos interventions de {{KEYWORD}} et matériaux utilisés</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Délais d\'exécution rapides et respectés pour votre tranquillité d\'esprit</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Conseils personnalisés pour l\'entretien et la durabilité de votre {{KEYWORD}}</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Équipe qualifiée et professionnelle à votre service pour toute demande</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Respect des normes environnementales et réglementations en vigueur</span>
        </li>
      </ul>
    </div>
  </div>
</div>';

        return $template;
    }

    /**
     * Personnaliser le template pour une ville spécifique (version mot-clé)
     */
    private function customizeTemplateForCityByKeyword($template, $keyword, $city)
    {
        // Remplacer les variables dans le template
        $content = str_replace([
            '{{KEYWORD}}',
            '{{CITY_NAME}}',
            '{{REGION_NAME}}',
            '{{COMPANY_NAME}}'
        ], [
            ucfirst($keyword),
            $city->name,
            $city->region ?? setting('company_region', ''),
            setting('company_name', 'Notre Entreprise')
        ], $template);

        return $content;
    }
}
