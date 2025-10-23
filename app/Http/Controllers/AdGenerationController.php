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
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyCity = setting('company_city', '');
        $companyRegion = setting('company_region', '');
        
        $content = '<div class="ad-content">';
        $content .= '<h1>' . $service['name'] . ' à ' . $city->name . '</h1>';
        $content .= '<p>Service professionnel de ' . $service['name'] . ' à ' . $city->name . '. ' . $companyName . ' vous propose ses services avec expertise et qualité.</p>';
        
        // Contenu spécifique à la ville
        $content .= '<div class="grid md:grid-cols-2 gap-8">';
        $content .= '<div class="space-y-6">';
        
        // Introduction générale adaptée à la ville
        $content .= '<div class="space-y-4">';
        $content .= '<p class="text-lg leading-relaxed">';
        $content .= 'Découvrez notre <strong class="text-blue-600">expertise professionnelle en ' . $service['name'] . '</strong> à ' . $city->name;
        if ($city->postal_code) {
            $content .= ' (' . $city->postal_code . ')';
        }
        $content .= '. Nous assurons la protection et l\'étanchéité de votre toiture, de la réparation à la rénovation complète, avec des matériaux de qualité et des techniques éprouvées.';
        $content .= '</p>';
        $content .= '<p class="text-lg leading-relaxed">';
        $content .= 'Experts en couverture, nous garantissons la durabilité de votre toiture et intervenons sur tous types de toitures : tuiles, ardoises, toitures plates, etc.';
        $content .= '</p>';
        $content .= '<p class="text-lg leading-relaxed">';
        $content .= 'Chaque projet bénéficie d\'une attention personnalisée et d\'un accompagnement complet pour garantir la satisfaction de nos clients.';
        $content .= '</p>';
        $content .= '</div>';
        
        // Engagement qualité
        $content .= '<div class="bg-blue-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>';
        $content .= '<p class="leading-relaxed mb-3">';
        $content .= 'Chez <strong>' . $companyName . '</strong>, nous mettons un point d\'honneur à garantir la satisfaction totale de nos clients. Chaque projet est unique et mérite une attention particulière.';
        $content .= '</p>';
        $content .= '<p class="leading-relaxed">';
        $content .= 'Nous sélectionnons rigoureusement nos matériaux et appliquons les techniques les plus avancées pour vous offrir un service professionnel de qualité, respectueux des normes et de l\'environnement.';
        $content .= '</p>';
        $content .= '</div>';
        
        // Prestations
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $service['name'] . '</h3>';
        $content .= '<ul class="space-y-3">';
        $content .= '<li class="flex items-start"><span><strong>Réparation de toiture en urgence</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Rénovation complète de couverture</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Pose de tuiles et ardoises</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Étanchéité de toiture plate</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Traitement anti-mousse</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Réparation de fuites</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Ventilation de toiture</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Isolation sous toiture</strong></span></li>';
        $content .= '</ul>';
        
        // Pourquoi choisir notre entreprise - adapté à la ville
        $content .= '<div class="bg-green-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>';
        $content .= '<p class="leading-relaxed">';
        $content .= 'Notre réputation à ' . $city->name . ' et dans la région repose sur notre engagement qualité, notre transparence tarifaire et notre capacité à livrer les projets dans les délais. Nous avons déjà satisfait de nombreuses familles et entreprises.';
        $content .= '</p>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        // Colonne droite
        $content .= '<div class="space-y-6">';
        
        // Expertise locale
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>';
        $content .= '<p class="leading-relaxed">';
        $content .= 'Forts de notre expérience, nous connaissons parfaitement les spécificités de ' . $city->name . ' et de sa région pour un service adapté et de qualité.';
        $content .= '</p>';
        
        // Devis
        $content .= '<div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">';
        $content .= '<h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>';
        $content .= '<p class="mb-4">';
        $content .= 'Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour vos ' . $service['name'] . ' à ' . $city->name . '.';
        $content .= '</p>';
        $content .= '<a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">';
        $content .= 'Demande de devis';
        $content .= '</a>';
        $content .= '</div>';
        
        // Informations pratiques
        $content .= '<div class="bg-gray-50 p-6 rounded-lg">';
        $content .= '<h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>';
        $content .= '<ul class="space-y-2 text-sm">';
        $content .= '<li class="flex items-center"><span>Intervention rapide et efficace à ' . $city->name . '</span></li>';
        $content .= '<li class="flex items-center"><span>Disponibilité 7j/7 pour répondre à vos besoins</span></li>';
        $content .= '<li class="flex items-center"><span>Garantie de satisfaction pour une toiture impeccable</span></li>';
        $content .= '</ul>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        
        // Informations de contact
        if ($companyPhone) {
            $content .= '<p><strong>Téléphone :</strong> ' . $companyPhone . '</p>';
        }
        
        if ($companyEmail) {
            $content .= '<p><strong>Email :</strong> ' . $companyEmail . '</p>';
        }
        
        $content .= '</div>';
        
        return $content;
    }

    /**
     * Générer le contenu d'une annonce par mot-clé
     */
    private function generateKeywordAdContent($keyword, $city, $aiPrompt)
    {
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        
        $content = '<div class="ad-content">';
        $content .= '<h1>' . $keyword . ' à ' . $city->name . '</h1>';
        $content .= '<p>Service professionnel de ' . $keyword . ' à ' . $city->name . '. ' . $companyName . ' vous propose ses services avec expertise et qualité.</p>';
        
        // Contenu spécifique à la ville
        $content .= '<div class="grid md:grid-cols-2 gap-8">';
        $content .= '<div class="space-y-6">';
        
        // Introduction générale adaptée à la ville
        $content .= '<div class="space-y-4">';
        $content .= '<p class="text-lg leading-relaxed">';
        $content .= 'Découvrez notre <strong class="text-blue-600">expertise professionnelle en ' . $keyword . '</strong> à ' . $city->name;
        if ($city->postal_code) {
            $content .= ' (' . $city->postal_code . ')';
        }
        $content .= '. Nous assurons la protection et l\'étanchéité de votre toiture, de la réparation à la rénovation complète, avec des matériaux de qualité et des techniques éprouvées.';
        $content .= '</p>';
        $content .= '<p class="text-lg leading-relaxed">';
        $content .= 'Experts en couverture, nous garantissons la durabilité de votre toiture et intervenons sur tous types de toitures : tuiles, ardoises, toitures plates, etc.';
        $content .= '</p>';
        $content .= '<p class="text-lg leading-relaxed">';
        $content .= 'Chaque projet bénéficie d\'une attention personnalisée et d\'un accompagnement complet pour garantir la satisfaction de nos clients.';
        $content .= '</p>';
        $content .= '</div>';
        
        // Engagement qualité
        $content .= '<div class="bg-blue-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>';
        $content .= '<p class="leading-relaxed mb-3">';
        $content .= 'Chez <strong>' . $companyName . '</strong>, nous mettons un point d\'honneur à garantir la satisfaction totale de nos clients. Chaque projet est unique et mérite une attention particulière.';
        $content .= '</p>';
        $content .= '<p class="leading-relaxed">';
        $content .= 'Nous sélectionnons rigoureusement nos matériaux et appliquons les techniques les plus avancées pour vous offrir un service professionnel de qualité, respectueux des normes et de l\'environnement.';
        $content .= '</p>';
        $content .= '</div>';
        
        // Prestations
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $keyword . '</h3>';
        $content .= '<ul class="space-y-3">';
        $content .= '<li class="flex items-start"><span><strong>Réparation de toiture en urgence</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Rénovation complète de couverture</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Pose de tuiles et ardoises</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Étanchéité de toiture plate</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Traitement anti-mousse</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Réparation de fuites</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Ventilation de toiture</strong></span></li>';
        $content .= '<li class="flex items-start"><span><strong>Isolation sous toiture</strong></span></li>';
        $content .= '</ul>';
        
        // Pourquoi choisir notre entreprise - adapté à la ville
        $content .= '<div class="bg-green-50 p-6 rounded-lg">';
        $content .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>';
        $content .= '<p class="leading-relaxed">';
        $content .= 'Notre réputation à ' . $city->name . ' et dans la région repose sur notre engagement qualité, notre transparence tarifaire et notre capacité à livrer les projets dans les délais. Nous avons déjà satisfait de nombreuses familles et entreprises.';
        $content .= '</p>';
        $content .= '</div>';
        
        $content .= '</div>';
        
        // Colonne droite
        $content .= '<div class="space-y-6">';
        
        // Expertise locale
        $content .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>';
        $content .= '<p class="leading-relaxed">';
        $content .= 'Forts de notre expérience, nous connaissons parfaitement les spécificités de ' . $city->name . ' et de sa région pour un service adapté et de qualité.';
        $content .= '</p>';
        
        // Devis
        $content .= '<div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">';
        $content .= '<h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>';
        $content .= '<p class="mb-4">';
        $content .= 'Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour vos ' . $keyword . ' à ' . $city->name . '.';
        $content .= '</p>';
        $content .= '<a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">';
        $content .= 'Demande de devis';
        $content .= '</a>';
        $content .= '</div>';
        
        // Informations pratiques
        $content .= '<div class="bg-gray-50 p-6 rounded-lg">';
        $content .= '<h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>';
        $content .= '<ul class="space-y-2 text-sm">';
        $content .= '<li class="flex items-center"><span>Intervention rapide et efficace à ' . $city->name . '</span></li>';
        $content .= '<li class="flex items-center"><span>Disponibilité 7j/7 pour répondre à vos besoins</span></li>';
        $content .= '<li class="flex items-center"><span>Garantie de satisfaction pour une toiture impeccable</span></li>';
        $content .= '</ul>';
        $content .= '</div>';
        
        $content .= '</div>';
        $content .= '</div>';
        
        // Informations de contact
        if ($companyPhone) {
            $content .= '<p><strong>Téléphone :</strong> ' . $companyPhone . '</p>';
        }
        
        if ($companyEmail) {
            $content .= '<p><strong>Email :</strong> ' . $companyEmail . '</p>';
        }
        
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