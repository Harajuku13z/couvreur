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
use App\Services\AiService;

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
     * Nouveau workflow : créer d'abord un template, puis générer les annonces
     */
    public function generateBulkAds(Request $request)
    {
        // Log pour déboguer
        Log::info('=== DÉBUT generateBulkAds ===', [
            'all_input' => $request->all(),
            'service_slug' => $request->input('service_slug'),
            'service_slug_get' => $request->get('service_slug'),
            'service_slug_post' => $request->post('service_slug'),
            'city_ids' => $request->input('city_ids'),
            'city_ids_array' => $request->input('city_ids', []),
            'ai_prompt' => $request->input('ai_prompt'),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'is_json' => $request->isJson(),
        ]);

        // Essayer plusieurs façons de récupérer service_slug
        $serviceSlug = $request->input('service_slug') 
                    ?? $request->get('service_slug')
                    ?? $request->post('service_slug')
                    ?? ($request->isJson() && $request->json() ? $request->json()->get('service_slug') : null)
                    ?? null;

        // Vérifier manuellement avant validation
        if (empty($serviceSlug)) {
            Log::error('service_slug manquant dans la requête', [
                'all_input' => $request->all(),
                'all_request_keys' => array_keys($request->all()),
                'has_service_slug' => $request->has('service_slug'),
                'service_slug_input' => $request->input('service_slug'),
                'service_slug_get' => $request->get('service_slug'),
                'service_slug_post' => $request->post('service_slug'),
                'content_type' => $request->header('Content-Type'),
                'raw_content' => substr($request->getContent(), 0, 500),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Le champ service_slug est requis. Vérifiez que vous avez sélectionné un service.',
                'debug' => [
                    'has_service_slug' => $request->has('service_slug'),
                    'service_slug_input' => $request->input('service_slug'),
                    'service_slug_get' => $request->get('service_slug'),
                    'all_keys' => array_keys($request->all()),
                    'content_type' => $request->header('Content-Type'),
                ]
            ], 422);
        }
        
        // Utiliser la valeur récupérée
        $request->merge(['service_slug' => $serviceSlug]);

        $request->validate([
            'service_slug' => 'required|string',
            'ai_prompt' => 'nullable|string|max:5000',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => 'required|integer|exists:cities,id',
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

            // ÉTAPE 1 : Créer ou récupérer le template pour ce service
            $template = \App\Models\AdTemplate::where('service_slug', $request->service_slug)->first();
            
            if (!$template) {
                // Créer le template directement avec la même logique que AdTemplateController
                $templateController = app(\App\Http\Controllers\Admin\AdTemplateController::class);
                
                // Créer un nouveau Request avec les données nécessaires
                $templateRequest = Request::create(
                    '/admin/ads/templates/create-from-service',
                    'POST',
                    [
                        'service_slug' => $request->service_slug,
                        'ai_prompt' => $request->input('ai_prompt'),
                        'force_create' => false
                    ]
                );
                $templateRequest->headers->set('Content-Type', 'application/json');
                $templateRequest->headers->set('Accept', 'application/json');
                
                // Appeler la méthode du contrôleur
                $templateResponse = $templateController->createFromService($templateRequest);
                $templateData = json_decode($templateResponse->getContent(), true);
                
                if (!$templateData || !isset($templateData['success']) || !$templateData['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de la création du template : ' . ($templateData['message'] ?? 'Erreur inconnue')
                    ], 500);
                }
                
                $template = \App\Models\AdTemplate::find($templateData['template_id']);
            }

            // ÉTAPE 2 : Générer les annonces depuis le template avec les villes sélectionnées
            $cities = City::whereIn('id', $request->input('city_ids'))->get();
            
            $createdAds = 0;
            $skippedAds = 0;
            $errors = [];

            foreach ($cities as $city) {
                    try {
                    // Vérifier si une annonce existe déjà pour cette combinaison
                    $existingAd = Ad::where('template_id', $template->id)
                            ->where('city_id', $city->id)
                            ->first();

                        if ($existingAd) {
                            $skippedAds++;
                            continue;
                        }

                    // Mode rapide : pas de personnalisation IA par ville
                    $contentForCity = $template->getContentForCity($city, false);
                    $metaForCity = $template->getMetaForCity($city, false);
                        
                        // Créer l'annonce
                        $ad = Ad::create([
                        'title' => $template->service_name . ' à ' . $city->name,
                        'keyword' => $template->service_name,
                            'city_id' => $city->id,
                        'template_id' => $template->id,
                        // slug avec fallback si Str::slug() retourne vide
                        'slug' => (function() use ($template, $city) {
                            $base = Str::slug($template->service_name . '-' . $city->name);
                            if (empty($base)) {
                                $base = 'ad-' . $template->id . '-' . $city->id . '-' . time();
                            }
                            return $this->generateUniqueSlug($base);
                        })(),
                            'status' => 'published',
                        'published_at' => now(),
                        'meta_title' => $metaForCity['meta_title'],
                        'meta_description' => $metaForCity['meta_description'],
                        'meta_keywords' => $metaForCity['meta_keywords'],
                        'content_html' => $contentForCity,
                            'content_json' => [
                                'template_id' => $template->id,
                                'city' => $city->toArray(),
                                'generated_at' => now()->toIso8601String(),
                                'bulk_generated' => true
                            ]
                        ]);

                        $createdAds++;
                    $template->incrementUsage();

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

            return response()->json([
                'success' => true,
                'message' => "Génération terminée : {$createdAds} annonces créées, {$skippedAds} ignorées",
                'data' => [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
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
     * Générer un slug unique pour les annonces
     */
    private function generateUniqueSlug($baseSlug)
    {
        $slug = $baseSlug;
        $counter = 1;
        
        while (Ad::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Générer du contenu personnalisé via l'IA pour un service et une ville
     */
    private function generateAdContentWithAI($service, $city, $aiPrompt = null)
    {
        try {
            Log::info('Début génération IA pour annonce', [
                    'service' => $service['name'],
                    'city' => $city->name
            ]);
            
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
            
            Log::info('Prompt construit', [
                'service' => $service['name'],
                'city' => $city->name,
                'prompt_length' => strlen($prompt)
            ]);
            
            // Appel à l'API IA avec fallback
            $result = AiService::callAI($prompt, null, [
                'max_tokens' => 4000,
                'temperature' => 0.7
            ]);

            if ($result && isset($result['content'])) {
                $aiContent = $result['content'];
                
                Log::info('Réponse IA reçue', [
                    'service' => $service['name'],
                    'city' => $city->name,
                    'content_length' => strlen($aiContent),
                    'content_preview' => substr($aiContent, 0, 200)
                ]);
                
                // Nettoyer et valider le contenu
                $content = $this->validateAndCleanAIData($aiContent, $service['name'], $city);
                
                Log::info('Contenu validé', [
                    'service' => $service['name'],
                    'city' => $city->name,
                    'final_content_length' => strlen($content),
                    'is_fallback' => strpos($content, 'Service professionnel de') !== false
                ]);
                
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
                    'status' => $response->status(),
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
        $basePrompt = "Crée une page web pour le service {$serviceName} à {$city->name}.

SERVICE: {$serviceName}
VILLE: {$city->name}
RÉGION: " . ($city->region ?? '') . "

GÉNÈRE UN JSON AVEC CES CHAMPS:

{
  \"description\": \"<div class='grid md:grid-cols-2 gap-8'><div class='space-y-6'><div class='space-y-4'><p class='text-lg leading-relaxed'>Service professionnel de {$serviceName} à {$city->name}, une expertise reconnue dans " . ($city->region ?? '') . ".</p><p class='text-lg leading-relaxed'>Spécialistes en travaux de {$serviceName} pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p></div><div class='bg-blue-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Notre Engagement Qualité</h3><p class='leading-relaxed mb-3'>Nous garantissons la satisfaction totale de nos clients à {$city->name} et dans toute la région de " . ($city->region ?? '') . ".</p><p class='leading-relaxed'>Chaque intervention de {$serviceName} est réalisée selon les normes professionnelles les plus strictes.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Nos Prestations {$serviceName}</h3><ul class='space-y-3'><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Diagnostic et évaluation</strong> - Analyse complète de vos besoins en {$serviceName}</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Intervention d'urgence</strong> - Service rapide 24h/7j pour {$serviceName}</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Maintenance préventive</strong> - Entretien régulier pour éviter les problèmes</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Réparation spécialisée</strong> - Correction des dysfonctionnements</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Installation complète</strong> - Pose selon les normes en vigueur</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Rénovation totale</strong> - Remplacement intégral avec matériaux de qualité</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Conseil personnalisé</strong> - Recommandations adaptées à votre situation</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Suivi post-intervention</strong> - Accompagnement après travaux</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Formation utilisateur</strong> - Apprentissage des bonnes pratiques</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Garantie étendue</strong> - Protection supplémentaire sur nos interventions</span></li></ul><div class='bg-gray-50 p-6 rounded-lg mt-6'><h4 class='text-xl font-bold text-gray-900 mb-3'>FAQ</h4><div class='space-y-2'><p><strong>Q1: Combien coûte un service de {$serviceName} à {$city->name}?</strong></p><p>A: Le prix dépend de la complexité et de l'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p><p><strong>Q2: Quel est le délai d'intervention pour {$serviceName}?</strong></p><p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l'urgence de votre demande.</p><p><strong>Q3: Proposez-vous une garantie sur vos services de {$serviceName}?</strong></p><p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p></div></div></div><div class='space-y-6'><div class='bg-green-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Pourquoi choisir ce service</h3><p class='leading-relaxed'>Notre expertise locale à {$city->name} nous permet de comprendre les spécificités de votre région et d'adapter nos services en conséquence.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Notre Expertise Locale</h3><p class='leading-relaxed'>Depuis plusieurs années, nous intervenons sur {$city->name} et sa région, développant une connaissance approfondie des besoins locaux en {$serviceName}.</p><div class='bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Financement et aides</h4><p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de {$serviceName}.</p></div><div class='bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Besoin d'un devis?</h4><p class='mb-4'>Contactez-nous pour un devis gratuit pour {$serviceName} à {$city->name}.</p><a href='[FORM_URL]' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300'>Demande de devis</a></div><div class='bg-gray-50 p-6 rounded-lg'><h4 class='text-lg font-bold text-gray-900 mb-3'>Informations Pratiques</h4><ul class='space-y-2 text-sm'><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Devis gratuit et sans engagement</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Intervention rapide sur {$city->name}</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Garantie sur tous nos travaux</span></li></ul></div><div class='mt-8 pt-6 border-t border-gray-200'><div class='text-center'><h4 class='text-lg font-semibold text-gray-800 mb-4'>Partager ce service</h4><div class='flex justify-center items-center space-x-4'><a href='https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]' target='_blank' rel='noopener noreferrer' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-facebook-f text-lg'></i><span class='font-medium'>Facebook</span></a><a href='https://wa.me/?text=[TITRE] - [URL]' target='_blank' rel='noopener noreferrer' class='bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-whatsapp text-lg'></i><span class='font-medium'>WhatsApp</span></a><a href='mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]' class='bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fas fa-envelope text-lg'></i><span class='font-medium'>Email</span></a></div></div></div></div>\",
  \"short_description\": \"Service professionnel de {$serviceName} à {$city->name} - Devis gratuit et intervention rapide\",
  \"long_description\": \"Notre entreprise spécialisée en {$serviceName} intervient sur {$city->name} et dans toute la région de " . ($city->region ?? '') . ". Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.\",
  \"icon\": \"fas fa-tools\",
  \"meta_title\": \"{$serviceName} à {$city->name} - Service professionnel\",
  \"meta_description\": \"Service professionnel de {$serviceName} à {$city->name}. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"og_title\": \"{$serviceName} à {$city->name} - Service professionnel\",
  \"og_description\": \"Service professionnel de {$serviceName} à {$city->name}. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"twitter_title\": \"{$serviceName} à {$city->name} - Service professionnel\",
  \"twitter_description\": \"Service professionnel de {$serviceName} à {$city->name}. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"meta_keywords\": \"{$serviceName}, {$city->name}, " . ($city->region ?? '') . ", service professionnel, devis gratuit\"
}

IMPORTANT: 
- Réponds UNIQUEMENT avec le JSON ci-dessus
- Ne pas ajouter de texte avant ou après
- Ne pas modifier la structure
- Commence directement par { et termine par }
- Copie exactement le JSON fourni";

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
            Log::info('Début validation données IA', [
                'service' => $serviceName,
                'city' => $city->name,
                'raw_content_length' => strlen($aiContent),
                'raw_content_preview' => substr($aiContent, 0, 300)
            ]);
            
            // Nettoyer le contenu pour extraire le JSON
            $cleanContent = $this->cleanHtmlContent($aiContent);
            
            Log::info('Contenu nettoyé', [
                'service' => $serviceName,
                'city' => $city->name,
                'clean_content_length' => strlen($cleanContent),
                'clean_content_preview' => substr($cleanContent, 0, 300)
            ]);
            
            // Extraire le JSON même si l'IA a ajouté du texte avant/après
            $jsonContent = $this->extractJsonFromContent($cleanContent);
            
            if (empty($jsonContent)) {
                Log::warning('Aucun JSON valide trouvé dans le contenu', [
                    'service' => $serviceName,
                    'city' => $city->name,
                    'content_preview' => substr($cleanContent, 0, 500)
                ]);
                return $this->generateFallbackContent($serviceName, $city);
            }
            
            Log::info('JSON extrait', [
                'service' => $serviceName,
                'city' => $city->name,
                'json_length' => strlen($jsonContent),
                'json_preview' => substr($jsonContent, 0, 200)
            ]);
            
            // Parser le JSON
            $aiData = json_decode($jsonContent, true);
            
            if (!$aiData) {
                Log::warning('JSON invalide dans la réponse IA, tentative de correction', [
                    'service' => $serviceName,
                    'city' => $city->name,
                    'json_error' => json_last_error_msg(),
                    'content' => substr($cleanContent, 0, 500)
                ]);
                
                // Tentative de correction du JSON
                $correctedContent = $this->attemptJsonCorrection($jsonContent);
                $aiData = json_decode($correctedContent, true);
                
                if (!$aiData) {
                    Log::error('JSON toujours invalide après correction', [
                        'service' => $serviceName,
                        'city' => $city->name,
                        'json_error' => json_last_error_msg()
                    ]);
                    return $this->generateFallbackContent($serviceName, $city);
                } else {
                    Log::info('JSON corrigé avec succès', [
                        'service' => $serviceName,
                        'city' => $city->name
                    ]);
                }
            }
            
            if (!isset($aiData['description'])) {
                Log::warning('Champ description manquant dans les données IA', [
                    'service' => $serviceName,
                    'city' => $city->name,
                    'available_fields' => array_keys($aiData)
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
                    'city' => $city->name,
                    'description_length' => strlen($validatedData['description']),
                    'description_preview' => substr($validatedData['description'], 0, 200)
                ]);
                return $this->generateFallbackContent($serviceName, $city);
            }
            
            Log::info('Contenu IA validé avec succès', [
                'service' => $serviceName,
                'city' => $city->name,
                'description_length' => strlen($validatedData['description'])
            ]);
            
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
     * Extraire le JSON du contenu même si l'IA a ajouté du texte avant/après
     */
    private function extractJsonFromContent($content)
    {
        // Chercher le premier { et le dernier }
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');
        
        if ($firstBrace === false || $lastBrace === false || $firstBrace >= $lastBrace) {
            return '';
        }
        
        $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
        
        // Vérifier que c'est bien du JSON valide
        if (json_decode($jsonContent, true) !== null) {
            return $jsonContent;
        }
        
        return '';
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
        
        // Corriger les apostrophes non échappées dans le JSON
        // Remplacer les apostrophes simples par des apostrophes échappées dans les chaînes JSON
        $content = preg_replace_callback('/"([^"]*\'[^"]*)"/', function($matches) {
            $string = $matches[1];
            $string = str_replace("'", "\\'", $string);
            return '"' . $string . '"';
        }, $content);
        
        return trim($content);
    }
    
    /**
     * Tenter de corriger un JSON malformé
     */
    private function attemptJsonCorrection($content)
    {
        // Supprimer les caractères de contrôle
        $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content);
        
        // Corriger les apostrophes non échappées dans les chaînes JSON
        $content = preg_replace_callback('/"([^"]*\'[^"]*)"/', function($matches) {
            $string = $matches[1];
            $string = str_replace("'", "\\'", $string);
            return '"' . $string . '"';
        }, $content);
        
        // Corriger les guillemets non échappés dans les chaînes JSON
        $content = preg_replace_callback('/"([^"]*"[^"]*)"/', function($matches) {
            $string = $matches[1];
            $string = str_replace('"', '\\"', $string);
            return '"' . $string . '"';
        }, $content);
        
        // Supprimer les virgules en trop avant les accolades fermantes
        $content = preg_replace('/,(\s*[}\]])/', '$1', $content);
        
        return $content;
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
            // Vérifier la clé API
            $apiKey = setting('openai_api_key') ?: setting('chatgpt_api_key');
            if (!$apiKey) {
                Log::error('Clé API OpenAI manquante pour mot-clé', [
                    'keyword' => $keyword,
                    'city' => $city->name
                ]);
                return $this->generateKeywordFallbackContent($keyword, $city);
            }
            
            Log::info('Début génération IA pour mot-clé', [
                'keyword' => $keyword,
                'city' => $city->name,
                'api_key_length' => strlen($apiKey)
            ]);
            
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
            
            Log::info('Prompt mot-clé construit', [
                'keyword' => $keyword,
                'city' => $city->name,
                'prompt_length' => strlen($prompt)
            ]);
            
            // Appel à l'API OpenAI
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
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
                
                Log::info('Réponse IA mot-clé reçue', [
                    'keyword' => $keyword,
                    'city' => $city->name,
                    'content_length' => strlen($aiContent),
                    'content_preview' => substr($aiContent, 0, 200)
                ]);
                
                // Nettoyer et valider le contenu
                $content = $this->validateAndCleanAIData($aiContent, $keyword, $city);
                
                Log::info('Contenu mot-clé validé', [
                    'keyword' => $keyword,
                    'city' => $city->name,
                    'final_content_length' => strlen($content),
                    'is_fallback' => strpos($content, 'Service professionnel de') !== false
                ]);
                
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
                    'status' => $response->status(),
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
        $basePrompt = "Crée une page web pour le service {$keyword} à {$city->name}.

SERVICE: {$keyword}
VILLE: {$city->name}
RÉGION: " . ($city->region ?? '') . "

GÉNÈRE UN JSON AVEC CES CHAMPS:

{
  \"description\": \"<div class='grid md:grid-cols-2 gap-8'><div class='space-y-6'><div class='space-y-4'><p class='text-lg leading-relaxed'>Service professionnel de {$keyword} à {$city->name}, une expertise reconnue dans " . ($city->region ?? '') . ".</p><p class='text-lg leading-relaxed'>Spécialistes en travaux de {$keyword} pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p></div><div class='bg-blue-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Notre Engagement Qualité</h3><p class='leading-relaxed mb-3'>Nous garantissons la satisfaction totale de nos clients à {$city->name} et dans toute la région de " . ($city->region ?? '') . ".</p><p class='leading-relaxed'>Chaque intervention de {$keyword} est réalisée selon les normes professionnelles les plus strictes.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Nos Prestations {$keyword}</h3><ul class='space-y-3'><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Diagnostic et évaluation</strong> - Analyse complète de vos besoins en {$keyword}</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Intervention d'urgence</strong> - Service rapide 24h/7j pour {$keyword}</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Maintenance préventive</strong> - Entretien régulier pour éviter les problèmes</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Réparation spécialisée</strong> - Correction des dysfonctionnements</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Installation complète</strong> - Pose selon les normes en vigueur</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Rénovation totale</strong> - Remplacement intégral avec matériaux de qualité</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Conseil personnalisé</strong> - Recommandations adaptées à votre situation</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Suivi post-intervention</strong> - Accompagnement après travaux</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Formation utilisateur</strong> - Apprentissage des bonnes pratiques</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Garantie étendue</strong> - Protection supplémentaire sur nos interventions</span></li></ul><div class='bg-gray-50 p-6 rounded-lg mt-6'><h4 class='text-xl font-bold text-gray-900 mb-3'>FAQ</h4><div class='space-y-2'><p><strong>Q1: Combien coûte un service de {$keyword} à {$city->name}?</strong></p><p>A: Le prix dépend de la complexité et de l'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p><p><strong>Q2: Quel est le délai d'intervention pour {$keyword}?</strong></p><p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l'urgence de votre demande.</p><p><strong>Q3: Proposez-vous une garantie sur vos services de {$keyword}?</strong></p><p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p></div></div></div><div class='space-y-6'><div class='bg-green-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Pourquoi choisir ce service</h3><p class='leading-relaxed'>Notre expertise locale à {$city->name} nous permet de comprendre les spécificités de votre région et d'adapter nos services en conséquence.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Notre Expertise Locale</h3><p class='leading-relaxed'>Depuis plusieurs années, nous intervenons sur {$city->name} et sa région, développant une connaissance approfondie des besoins locaux en {$keyword}.</p><div class='bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Financement et aides</h4><p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de {$keyword}.</p></div><div class='bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Besoin d'un devis?</h4><p class='mb-4'>Contactez-nous pour un devis gratuit pour {$keyword} à {$city->name}.</p><a href='[FORM_URL]' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300'>Demande de devis</a></div><div class='bg-gray-50 p-6 rounded-lg'><h4 class='text-lg font-bold text-gray-900 mb-3'>Informations Pratiques</h4><ul class='space-y-2 text-sm'><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Devis gratuit et sans engagement</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Intervention rapide sur {$city->name}</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Garantie sur tous nos travaux</span></li></ul></div><div class='mt-8 pt-6 border-t border-gray-200'><div class='text-center'><h4 class='text-lg font-semibold text-gray-800 mb-4'>Partager ce service</h4><div class='flex justify-center items-center space-x-4'><a href='https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]' target='_blank' rel='noopener noreferrer' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-facebook-f text-lg'></i><span class='font-medium'>Facebook</span></a><a href='https://wa.me/?text=[TITRE] - [URL]' target='_blank' rel='noopener noreferrer' class='bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-whatsapp text-lg'></i><span class='font-medium'>WhatsApp</span></a><a href='mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]' class='bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fas fa-envelope text-lg'></i><span class='font-medium'>Email</span></a></div></div></div></div>\",
  \"short_description\": \"Service professionnel de {$keyword} à {$city->name} - Devis gratuit et intervention rapide\",
  \"long_description\": \"Notre entreprise spécialisée en {$keyword} intervient sur {$city->name} et dans toute la région de " . ($city->region ?? '') . ". Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.\",
  \"icon\": \"fas fa-tools\",
  \"meta_title\": \"{$keyword} à {$city->name} - Service professionnel\",
  \"meta_description\": \"Service professionnel de {$keyword} à {$city->name}. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"og_title\": \"{$keyword} à {$city->name} - Service professionnel\",
  \"og_description\": \"Service professionnel de {$keyword} à {$city->name}. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"twitter_title\": \"{$keyword} à {$city->name} - Service professionnel\",
  \"twitter_description\": \"Service professionnel de {$keyword} à {$city->name}. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"meta_keywords\": \"{$keyword}, {$city->name}, " . ($city->region ?? '') . ", service professionnel, devis gratuit\"
}

IMPORTANT: 
- Réponds UNIQUEMENT avec le JSON ci-dessus
- Ne pas ajouter de texte avant ou après
- Ne pas modifier la structure
- Commence directement par { et termine par }
- Copie exactement le JSON fourni";

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
     * Nouveau workflow : créer d'abord un template, puis générer les annonces
     */
    public function generateBulkAdsByKeyword(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:100',
            'keyword_ai_prompt' => 'nullable|string|max:5000',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => 'required|integer|exists:cities,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $keyword = $request->input('keyword');
            
            // ÉTAPE 1 : Créer ou récupérer le template pour ce mot-clé
            $template = \App\Models\AdTemplate::where('service_slug', Str::slug($keyword))->first();
            
            if (!$template) {
                // Créer le template directement avec la même logique que AdTemplateController
                $templateController = app(\App\Http\Controllers\Admin\AdTemplateController::class);
                
                // Créer un nouveau Request avec les données nécessaires
                $templateRequest = Request::create(
                    '/admin/ads/templates/create-from-keyword',
                    'POST',
                    [
                'keyword' => $keyword,
                        'ai_prompt' => $request->input('keyword_ai_prompt'),
                        'force_create' => false
                    ],
                    [],
                    ['featured_image' => $request->file('featured_image')]
                );
                $templateRequest->headers->set('Content-Type', 'multipart/form-data');
                $templateRequest->headers->set('Accept', 'application/json');
                
                // Appeler la méthode du contrôleur
                $templateResponse = $templateController->createFromKeyword($templateRequest);
                $templateData = json_decode($templateResponse->getContent(), true);
                
                if (!$templateData || !isset($templateData['success']) || !$templateData['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de la création du template : ' . ($templateData['message'] ?? 'Erreur inconnue')
                    ], 500);
                }
                
                $template = \App\Models\AdTemplate::find($templateData['template_id']);
            }

            // ÉTAPE 2 : Générer les annonces depuis le template avec les villes sélectionnées
            $cities = City::whereIn('id', $request->input('city_ids'))->get();
            
            $createdAds = 0;
            $skippedAds = 0;
            $errors = [];

            foreach ($cities as $city) {
                    try {
                    // Vérifier si une annonce existe déjà pour cette combinaison
                    $existingAd = Ad::where('template_id', $template->id)
                            ->where('city_id', $city->id)
                            ->first();

                        if ($existingAd) {
                            $skippedAds++;
                            continue;
                        }

                    // Mode rapide : pas de personnalisation IA par ville
                    $contentForCity = $template->getContentForCity($city, false);
                    $metaForCity = $template->getMetaForCity($city, false);
                        
                        // Créer l'annonce
                        $ad = Ad::create([
                        'title' => $template->service_name . ' à ' . $city->name,
                        'keyword' => $template->service_name,
                            'city_id' => $city->id,
                        'template_id' => $template->id,
                        // slug avec fallback si Str::slug() retourne vide
                        'slug' => (function() use ($template, $city) {
                            $base = Str::slug($template->service_name . '-' . $city->name);
                            if (empty($base)) {
                                $base = 'ad-' . $template->id . '-' . $city->id . '-' . time();
                            }
                            return $this->generateUniqueSlug($base);
                        })(),
                            'status' => 'published',
                        'published_at' => now(),
                        'meta_title' => $metaForCity['meta_title'],
                        'meta_description' => $metaForCity['meta_description'],
                        'meta_keywords' => $metaForCity['meta_keywords'],
                        'content_html' => $contentForCity,
                            'content_json' => [
                                'template_id' => $template->id,
                                'city' => $city->toArray(),
                                'generated_at' => now()->toIso8601String(),
                                'bulk_generated' => true
                            ]
                        ]);

                        $createdAds++;
                    $template->incrementUsage();

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

            return response()->json([
                'success' => true,
                'message' => "Génération terminée : {$createdAds} annonces créées pour le mot-clé '{$keyword}', {$skippedAds} ignorées",
                'data' => [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
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
