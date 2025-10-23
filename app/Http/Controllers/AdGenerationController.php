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
        
        $content = '<div class="ad-content">';
        $content .= '<h1>' . $service['name'] . ' à ' . $city->name . '</h1>';
        $content .= '<p>Service professionnel de ' . $service['name'] . ' à ' . $city->name . '. ' . $companyName . ' vous propose ses services avec expertise et qualité.</p>';
        
        if (!empty($service['description'])) {
            $content .= '<div class="service-description">' . $service['description'] . '</div>';
        }
        
        $content .= '<h2>Pourquoi choisir ' . $companyName . ' ?</h2>';
        $content .= '<ul>';
        $content .= '<li>Expertise professionnelle</li>';
        $content .= '<li>Devis gratuit et sans engagement</li>';
        $content .= '<li>Intervention rapide</li>';
        $content .= '<li>Qualité garantie</li>';
        $content .= '</ul>';
        
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
        
        $content .= '<h2>Pourquoi choisir ' . $companyName . ' ?</h2>';
        $content .= '<ul>';
        $content .= '<li>Expertise professionnelle</li>';
        $content .= '<li>Devis gratuit et sans engagement</li>';
        $content .= '<li>Intervention rapide</li>';
        $content .= '<li>Qualité garantie</li>';
        $content .= '</ul>';
        
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