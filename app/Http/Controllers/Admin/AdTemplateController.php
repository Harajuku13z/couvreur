<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdTemplate;
use App\Models\City;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\AiService;

class AdTemplateController extends Controller
{
    /**
     * Afficher la liste des templates
     */
    public function index()
    {
        $templates = AdTemplate::withCount('ads')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Récupérer les services depuis les settings
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }

        return view('admin.ads.templates.index', compact('templates', 'services'));
    }

    /**
     * Afficher un template spécifique
     */
    public function show(AdTemplate $template)
    {
        $template->load('ads.city');
        
        return view('admin.ads.templates.show', compact('template'));
    }

    /**
     * Afficher le formulaire d'édition pour personnaliser le template
     */
    public function edit(AdTemplate $template)
    {
        return view('admin.ads.templates.edit', compact('template'));
    }

    /**
     * Mettre à jour le template personnalisé
     */
    public function update(Request $request, AdTemplate $template)
    {
        $validated = $request->validate([
            'content_html' => 'required|string',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string|max:2000',
            'meta_title' => 'required|string|max:160',
            'meta_description' => 'required|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:160',
            'og_description' => 'nullable|string|max:500',
            'twitter_title' => 'nullable|string|max:160',
            'twitter_description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
        ]);

        $template->update($validated);

        return redirect()
            ->route('admin.ads.templates.show', $template->id)
            ->with('success', 'Template personnalisé avec succès ! Vous pouvez maintenant générer des annonces.');
    }

    /**
     * Créer un template manuellement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_name' => 'required|string|max:255',
            'service_slug' => 'required|string|max:255',
            'content_html' => 'required|string',
            'short_description' => 'required|string|max:500',
            'long_description' => 'required|string|max:2000',
            'meta_title' => 'required|string|max:160',
            'meta_description' => 'required|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:160',
            'og_description' => 'nullable|string|max:500',
            'twitter_title' => 'nullable|string|max:160',
            'twitter_description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            // Gérer l'upload de l'image si fournie
            $featuredImagePath = null;
            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $fileName = 'template_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                $uploadPath = public_path('uploads/templates');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                $file->move($uploadPath, $fileName);
                $featuredImagePath = 'uploads/templates/' . $fileName;
            }

            // Créer le template
            $template = AdTemplate::create([
                'name' => $validated['name'],
                'service_name' => $validated['service_name'],
                'service_slug' => $validated['service_slug'],
                'content_html' => $validated['content_html'],
                'short_description' => $validated['short_description'],
                'long_description' => $validated['long_description'],
                'icon' => $validated['icon'] ?? 'fas fa-tools',
                'featured_image' => $featuredImagePath,
                'meta_title' => $validated['meta_title'],
                'meta_description' => $validated['meta_description'],
                'meta_keywords' => $validated['meta_keywords'] ?? '',
                'og_title' => $validated['og_title'] ?? $validated['meta_title'],
                'og_description' => $validated['og_description'] ?? $validated['meta_description'],
                'twitter_title' => $validated['twitter_title'] ?? $validated['meta_title'],
                'twitter_description' => $validated['twitter_description'] ?? $validated['meta_description'],
            ]);

            return redirect()
                ->route('admin.ads.templates.show', $template->id)
                ->with('success', 'Template créé avec succès ! Vous pouvez maintenant générer des annonces.');

        } catch (\Exception $e) {
            Log::error('Erreur création template manuel', [
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du template: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        // Récupérer les services depuis les settings pour le select
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }

        return view('admin.ads.templates.create', compact('services'));
    }

    /**
     * Créer un template à partir d'un service (DÉSACTIVÉ - Utiliser store() à la place)
     * @deprecated
     */
    public function createFromService(Request $request)
    {
        $request->validate([
            'service_slug' => 'required|string',
            'ai_prompt' => 'nullable|string|max:5000',
        ]);

        $serviceSlug = $request->input('service_slug');
        
        // Récupérer les services depuis les settings
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $service = collect($services)->firstWhere('slug', $serviceSlug);
        
        // Log pour vérifier la structure du service récupéré
        if ($service) {
            Log::info('Service récupéré pour création template', [
                'service_name' => $service['name'] ?? 'N/A',
                'service_slug' => $serviceSlug,
                'has_featured_image' => isset($service['featured_image']),
                'featured_image_value' => $service['featured_image'] ?? 'null',
                'has_og_image' => isset($service['og_image']),
                'og_image_value' => $service['og_image'] ?? 'null',
                'service_keys' => array_keys($service)
            ]);
        }
        
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service non trouvé'
            ], 404);
        }

        try {
            // Vérifier si des templates existent déjà pour ce service
            $existingTemplates = AdTemplate::where('service_slug', $serviceSlug)->get();
            
            if ($existingTemplates->count() > 0 && !$request->input('force_create', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Des templates existent déjà pour ce service',
                    'existing_templates' => $existingTemplates->map(function($template) {
                        return [
                            'id' => $template->id,
                            'name' => $template->name,
                            'is_active' => $template->is_active,
                            'ads_count' => $template->ads()->count(),
                            'created_at' => $template->created_at->format('d/m/Y H:i')
                        ];
                    })
                ], 400);
            }

            // Utiliser generateCompleteTemplateContent inspiré de ServicesController
            $companyInfo = $this->getCompanyInfo();
            $aiContent = $this->generateCompleteTemplateContent(
                $service['name'], 
                $service['short_description'] ?? '',
                $companyInfo,
                $request->input('ai_prompt')
            );
            
            // Copier l'image du service vers le template
            $featuredImage = $service['featured_image'] ?? $service['og_image'] ?? null;
            
            // Log pour debugging
            Log::info('Copie image service vers template', [
                'service_name' => $service['name'],
                'featured_image' => $featuredImage,
                'service_keys' => array_keys($service),
                'has_featured_image' => isset($service['featured_image']),
                'has_og_image' => isset($service['og_image'])
            ]);
            
            // Créer le template
            $template = AdTemplate::create([
                'name' => $service['name'],
                'service_name' => $service['name'],
                'service_slug' => $service['slug'],
                'content_html' => $aiContent['description'],
                'short_description' => $aiContent['short_description'],
                'long_description' => $aiContent['long_description'],
                'icon' => $aiContent['icon'],
                'featured_image' => $featuredImage,
                'meta_title' => $aiContent['meta_title'],
                'meta_description' => $aiContent['meta_description'],
                'meta_keywords' => $aiContent['meta_keywords'],
                'og_title' => $aiContent['og_title'],
                'og_description' => $aiContent['og_description'],
                'twitter_title' => $aiContent['twitter_title'],
                'twitter_description' => $aiContent['twitter_description'],
                'ai_prompt_used' => $request->input('ai_prompt'),
                'ai_response_data' => $aiContent,
            ]);

            // Retourner une réponse JSON pour les appels AJAX
            return response()->json([
                'success' => true,
                'message' => 'Template créé avec succès. Vous pouvez maintenant le personnaliser avant de générer les annonces.',
                'template_id' => $template->id,
                'redirect_url' => route('admin.ads.templates.edit', $template->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur création template', [
                'service' => $service['name'],
                'error' => $e->getMessage()
            ]);

            // Essayer de créer un template avec du contenu de fallback
            try {
                $fallbackContent = $this->generateFallbackTemplateContent($service);
                
                // Copier l'image du service même en fallback (vérifier aussi og_image comme fallback)
                $featuredImage = $service['featured_image'] ?? $service['og_image'] ?? null;
                
                Log::info('Copie image service vers template (fallback)', [
                    'service_name' => $service['name'],
                    'featured_image' => $featuredImage,
                    'has_featured_image' => isset($service['featured_image']),
                    'has_og_image' => isset($service['og_image'])
                ]);
                
                $template = AdTemplate::create([
                    'name' => $service['name'],
                    'service_name' => $service['name'],
                    'service_slug' => $service['slug'],
                    'content_html' => $fallbackContent['description'],
                    'short_description' => $fallbackContent['short_description'],
                    'long_description' => $fallbackContent['long_description'],
                    'icon' => $fallbackContent['icon'],
                    'featured_image' => $featuredImage,
                    'meta_title' => $fallbackContent['meta_title'],
                    'meta_description' => $fallbackContent['meta_description'],
                    'meta_keywords' => $fallbackContent['meta_keywords'],
                    'og_title' => $fallbackContent['og_title'],
                    'og_description' => $fallbackContent['og_description'],
                    'twitter_title' => $fallbackContent['twitter_title'],
                    'twitter_description' => $fallbackContent['twitter_description'],
                    'ai_prompt_used' => $request->input('ai_prompt'),
                    'ai_response_data' => ['fallback' => true, 'error' => $e->getMessage()],
                ]);

                // Retourner une réponse JSON même avec fallback
                return response()->json([
                    'success' => true,
                    'message' => 'L\'API IA n\'était pas disponible. Le template a été créé avec du contenu par défaut. Vous pouvez le personnaliser maintenant.',
                    'template_id' => $template->id,
                    'redirect_url' => route('admin.ads.templates.edit', $template->id),
                    'warning' => true
                ]);
                
            } catch (\Exception $fallbackError) {
                Log::error('Erreur création template fallback', [
                    'service' => $service['name'],
                    'error' => $fallbackError->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du template: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Générer des annonces à partir d'un template pour plusieurs villes
     */
    public function generateAdsFromTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:ad_templates,id',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => 'required|integer|exists:cities,id',
        ]);

        $template = AdTemplate::findOrFail($request->input('template_id'));
        $cityIds = $request->input('city_ids');
        $cities = City::whereIn('id', $cityIds)->get();

        $createdAds = 0;
        $skippedAds = 0;
        $errors = [];

        foreach ($cities as $city) {
            try {
                // Vérifier si une annonce existe déjà pour cette combinaison
                $existingAd = \App\Models\Ad::where('template_id', $template->id)
                    ->where('city_id', $city->id)
                    ->first();

                if ($existingAd) {
                    $skippedAds++;
                    continue;
                }

                // Obtenir le contenu et les métadonnées pour cette ville
                $contentForCity = $template->getContentForCity($city);
                $metaForCity = $template->getMetaForCity($city);

                // Créer l'annonce
                $ad = \App\Models\Ad::create([
                    'title' => $template->service_name . ' à ' . $city->name,
                    'keyword' => $template->service_name,
                    'city_id' => $city->id,
                    'template_id' => $template->id,
                    'slug' => $this->generateUniqueSlug(Str::slug($template->service_name . '-' . $city->name)),
                    'status' => 'published',
                    'published_at' => now(),
                    'meta_title' => $metaForCity['meta_title'],
                    'meta_description' => $metaForCity['meta_description'],
                    'meta_keywords' => $metaForCity['meta_keywords'],
                    'content_html' => $contentForCity,
                    'content_json' => json_encode([
                        'template_id' => $template->id,
                        'city' => $city->toArray(),
                        'generated_at' => now()->toISOString()
                    ])
                ]);

                $createdAds++;
                
                // Incrémenter le compteur d'utilisation du template
                $template->incrementUsage();

            } catch (\Exception $e) {
                $errors[] = [
                    'city' => $city->name,
                    'error' => $e->getMessage()
                ];
                Log::error('Erreur création annonce depuis template', [
                    'template_id' => $template->id,
                    'city' => $city->name,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'created' => $createdAds,
            'skipped' => $skippedAds,
            'errors' => $errors,
            'message' => "Génération terminée : {$createdAds} annonces créées, {$skippedAds} ignorées"
        ]);
    }

    /**
     * Générer le contenu du template via IA
     */
    private function generateTemplateContent($service, $aiPrompt = null)
    {
        // Construire le prompt pour le template (sans ville spécifique)
        $prompt = $this->buildTemplatePrompt($service['name'], $aiPrompt);
        
        Log::info('=== DÉBUT GÉNÉRATION TEMPLATE ===', [
            'service_name' => $service['name'],
            'chatgpt_enabled' => setting('chatgpt_enabled', true),
            'chatgpt_api_key_exists' => !empty(setting('chatgpt_api_key')),
            'groq_api_key_exists' => !empty(setting('groq_api_key', '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'))
        ]);
        
        // Message système pour forcer la personnalisation
        $systemMessage = "Tu es un expert technique en {$service['name']} avec une connaissance approfondie du domaine. CRITIQUE ABSOLUE: Chaque contenu DOIT être UNIQUE, TECHNIQUE et SPÉCIFIQUE à {$service['name']}. INTERDIT d'utiliser des prestations génériques ou du contenu copié. Adapte TOUT spécifiquement au service {$service['name']}.";
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 4000,
            'temperature' => 0.9,  // Augmenté pour plus de créativité et personnalisation
            'timeout' => 120
        ]);

        if (!$result || !isset($result['content'])) {
            Log::error('Échec génération template - Aucune réponse de l\'IA', [
                'service_name' => $service['name'],
                'result' => $result
            ]);
            throw new \Exception('Erreur API IA: Impossible de générer le contenu. ChatGPT et Groq ont tous deux échoué.');
        }

        $provider = $result['provider'] ?? 'unknown';
        $aiContent = $result['content'];
        
        Log::info('Réponse IA reçue pour template', [
            'service_name' => $service['name'],
            'provider' => $provider,
            'content_length' => strlen($aiContent),
            'content_preview' => substr($aiContent, 0, 200)
        ]);
        
        // Valider et nettoyer le contenu
        return $this->validateAndCleanAIData($aiContent, $service['name']);
    }

    /**
     * Construire le prompt pour un template (sans ville spécifique)
     */
    private function buildTemplatePrompt($serviceName, $aiPrompt = null)
    {
        $basePrompt = "Tu es un expert technique en {$serviceName} avec une connaissance PROFONDE des prestations, techniques et matériaux spécifiques à ce domaine. Crée un template d'annonce TOTALEMENT personnalisé pour {$serviceName}.

⚠️⚠️⚠️ SERVICE À PERSONNALISER: {$serviceName} ⚠️⚠️⚠️

🚫 INTERDICTIONS ABSOLUES:
- INTERDIT d'utiliser des prestations génériques comme 'Diagnostic', 'Conseil', 'Maintenance générale', 'Installation professionnelle'
- INTERDIT de copier du contenu générique applicable à tous les services
- INTERDIT d'utiliser un vocabulaire vague ou général

✅ OBLIGATIONS ABSOLUES POUR {$serviceName}:
- Chaque prestation DOIT être TECHNIQUE et SPÉCIFIQUE UNIQUEMENT à {$serviceName}
- Utilise le vocabulaire PROFESSIONNEL du métier de {$serviceName}
- Les prestations doivent mentionner des techniques, matériaux ou méthodes PRÉCISES liés à {$serviceName}
- Chaque description doit expliquer QUOI, COMMENT et POURQUOI spécifiquement pour {$serviceName}

EXEMPLES DE PRESTATIONS SPÉCIFIQUES:
- Pour 'Rénovation de toiture': 'Diagnostic et inspection de toiture', 'Nettoyage et démoussage', 'Réparation partielle de toiture', 'Réfection complète de toiture', 'Isolation de toiture', 'Étanchéité et traitement hydrofuge', 'Réparation de zinguerie', 'Pose de charpente', 'Installation de fenêtres de toit', 'Entretien annuel et maintenance préventive'
- Pour 'Plomberie': 'Installation de chauffe-eau', 'Réparation de fuites', 'Débouchage de canalisations', 'Pose de robinetterie', 'Installation de WC', 'Rénovation de salle de bain', 'Détection de fuites', 'Installation de radiateurs', 'Raccordement gaz', 'Maintenance préventive'

GÉNÈRE UN JSON AVEC CES CHAMPS:

{
  \"description\": \"<div class='grid md:grid-cols-2 gap-8'><div class='space-y-6'><div class='space-y-4'><p class='text-lg leading-relaxed'>Service professionnel de {$serviceName} à [VILLE], une expertise reconnue dans [RÉGION].</p><p class='text-lg leading-relaxed'>Spécialistes en travaux de {$serviceName} pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p></div><div class='bg-blue-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Notre Engagement Qualité</h3><p class='leading-relaxed mb-3'>Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION].</p><p class='leading-relaxed'>Chaque intervention de {$serviceName} est réalisée selon les normes professionnelles les plus strictes.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Nos Prestations {$serviceName}</h3><ul class='space-y-3'>[GÉNÈRE 10 PRESTATIONS SPÉCIFIQUES À {$serviceName} AVEC DES DESCRIPTIONS DÉTAILLÉES]</ul><div class='bg-gray-50 p-6 rounded-lg mt-6'><h4 class='text-xl font-bold text-gray-900 mb-3'>FAQ</h4><div class='space-y-2'><p><strong>Q1: Combien coûte un service de {$serviceName} à [VILLE]?</strong></p><p>A: Le prix dépend de la complexité et de l'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p><p><strong>Q2: Quel est le délai d'intervention pour {$serviceName}?</strong></p><p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l'urgence de votre demande.</p><p><strong>Q3: Proposez-vous une garantie sur vos services de {$serviceName}?</strong></p><p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p></div></div></div><div class='space-y-6'><div class='bg-green-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Pourquoi choisir ce service</h3><p class='leading-relaxed'>Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région et d'adapter nos services en conséquence.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Notre Expertise Locale</h3><p class='leading-relaxed'>Depuis plusieurs années, nous intervenons sur [VILLE] et sa région, développant une connaissance approfondie des besoins locaux en {$serviceName}.</p><div class='bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Financement et aides</h4><p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de {$serviceName}.</p></div><div class='bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Besoin d'un devis?</h4><p class='mb-4'>Contactez-nous pour un devis gratuit pour {$serviceName} à [VILLE].</p><a href='[FORM_URL]' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300'>Demande de devis</a></div><div class='bg-gray-50 p-6 rounded-lg'><h4 class='text-lg font-bold text-gray-900 mb-3'>Informations Pratiques</h4><ul class='space-y-2 text-sm'><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Devis gratuit et sans engagement</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Intervention rapide sur [VILLE]</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Garantie sur tous nos travaux</span></li></ul></div><div class='mt-8 pt-6 border-t border-gray-200'><div class='text-center'><h4 class='text-lg font-semibold text-gray-800 mb-4'>Partager ce service</h4><div class='flex justify-center items-center space-x-4'><a href='https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]' target='_blank' rel='noopener noreferrer' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-facebook-f text-lg'></i><span class='font-medium'>Facebook</span></a><a href='https://wa.me/?text=[TITRE] - [URL]' target='_blank' rel='noopener noreferrer' class='bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-whatsapp text-lg'></i><span class='font-medium'>WhatsApp</span></a><a href='mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]' class='bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fas fa-envelope text-lg'></i><span class='font-medium'>Email</span></a></div></div></div></div>\",
  \"short_description\": \"Service professionnel de {$serviceName} à [VILLE] - Devis gratuit et intervention rapide\",
  \"long_description\": \"Notre entreprise spécialisée en {$serviceName} intervient sur [VILLE] et dans toute la région de [RÉGION]. Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.\",
  \"icon\": \"fas fa-tools\",
  \"meta_title\": \"{$serviceName} à [VILLE] - Service professionnel\",
  \"meta_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"og_title\": \"{$serviceName} à [VILLE] - Service professionnel\",
  \"og_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"twitter_title\": \"{$serviceName} à [VILLE] - Service professionnel\",
  \"twitter_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"meta_keywords\": \"{$serviceName}, [VILLE], [RÉGION], service professionnel, devis gratuit\"
}

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - FORMAT JSON ⚠️⚠️⚠️:
- TU DOIS RÉPONDRE UNIQUEMENT AVEC UN JSON VALIDE
- COMMENCE DIRECTEMENT PAR { (accolade ouvrante)
- TERMINE DIRECTEMENT PAR } (accolade fermante)
- PAS de texte avant le JSON
- PAS de texte après le JSON
- PAS de ```json ou ``` autour du JSON
- PAS de commentaires ou explications
- JUSTE le JSON brut

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - CONTENU ⚠️⚠️⚠️:
- REMPLACE TOUT le contenu par du contenu VRAIMENT spécifique à {$serviceName}
- REMPLACE [GÉNÈRE 10 PRESTATIONS SPÉCIFIQUES À {$serviceName}] par 10 prestations TECHNIQUES RÉELLES pour {$serviceName}
- Chaque prestation doit avoir un NOM TECHNIQUE précis et une DESCRIPTION détaillée avec techniques/matériaux pour {$serviceName}
- PERSONNALISE les descriptions, FAQ, et tous les textes pour {$serviceName} spécifiquement
- Utilise [VILLE], [RÉGION], [DÉPARTEMENT] comme placeholders pour les variables dynamiques
- Le contenu HTML doit être COMPLET et PERSONNALISÉ, pas un template copié-collé

EXEMPLES CONCRETS POUR {$serviceName}:
- Si {$serviceName} = 'Désamiantage' → prestations: 'Dépollution amiante', 'Retrait amiante sous confinement', 'Gestion déchets amiante'
- Si {$serviceName} = 'Traitement humidité' → prestations: 'Diagnostic humidité par imagerie thermique', 'Injection résine anti-humidité', 'Installation VMC double flux'
- Si {$serviceName} = 'Rénovation toiture' → prestations: 'Diagnostic toiture par drone', 'Réfection tuiles ardoise', 'Installation écran de sous-toiture'
";

        if ($aiPrompt) {
            $basePrompt .= "\n\nINSTRUCTIONS PERSONNALISÉES SUPPLÉMENTAIRES:\n" . $aiPrompt;
        }

        return $basePrompt;
    }

    /**
     * Valider et nettoyer les données IA
     */
    private function validateAndCleanAIData($aiContent, $serviceName)
    {
        try {
            // Nettoyer le contenu
            $cleanContent = $this->cleanHtmlContent($aiContent);
            
            Log::info('Contenu nettoyé pour validation', [
                'service' => $serviceName,
                'content_length' => strlen($cleanContent),
                'content_preview' => substr($cleanContent, 0, 300)
            ]);
            
            // Extraire le JSON
            $jsonContent = $this->extractJsonFromContent($cleanContent);
            
            // Si jsonContent est null, c'est que c'est du HTML direct
            if ($jsonContent === null) {
                Log::info('Contenu HTML direct détecté, création structure JSON');
                $plainText = strip_tags($cleanContent);
                return [
                    'description' => $cleanContent,
                    'short_description' => Str::limit($plainText, 140),
                    'long_description' => Str::limit($plainText, 500),
                    'icon' => 'fas fa-tools',
                    'meta_title' => $serviceName . ' à [VILLE] - Service professionnel',
                    'meta_description' => Str::limit($plainText, 160),
                    'og_title' => $serviceName . ' à [VILLE] - Service professionnel',
                    'og_description' => Str::limit($plainText, 160),
                    'twitter_title' => $serviceName . ' à [VILLE] - Service professionnel',
                    'twitter_description' => Str::limit($plainText, 160),
                    'meta_keywords' => $serviceName . ', [VILLE], [RÉGION], service professionnel'
                ];
            }
            
            if (empty($jsonContent)) {
                // Dernière tentative : chercher du JSON malformé mais récupérable
                Log::warning('Aucun JSON valide trouvé, tentative extraction manuelle');
                
                // Si le contenu contient du HTML avec des balises, essayer d'extraire
                if (preg_match('/"description"\s*:\s*"([^"]*(?:\\.[^"]*)*)"/s', $cleanContent, $matches)) {
                    Log::info('Extraction description HTML depuis JSON malformé');
                    $htmlContent = str_replace(['\\"', '\\n'], ['"', "\n"], $matches[1]);
                    $plainText = strip_tags($htmlContent);
                    
                    return [
                        'description' => $htmlContent,
                        'short_description' => Str::limit($plainText, 140),
                        'long_description' => Str::limit($plainText, 500),
                        'icon' => 'fas fa-tools',
                        'meta_title' => $serviceName . ' à [VILLE] - Service professionnel',
                        'meta_description' => Str::limit($plainText, 160),
                        'og_title' => $serviceName . ' à [VILLE] - Service professionnel',
                        'og_description' => Str::limit($plainText, 160),
                        'twitter_title' => $serviceName . ' à [VILLE] - Service professionnel',
                        'twitter_description' => Str::limit($plainText, 160),
                        'meta_keywords' => $serviceName . ', [VILLE], [RÉGION], service professionnel'
                    ];
                }
                
                throw new \Exception('Aucun JSON valide trouvé dans le contenu. Contenu reçu: ' . substr($cleanContent, 0, 500));
            }
            
            // Parser le JSON
            $aiData = json_decode($jsonContent, true);
            
            if (!$aiData || !is_array($aiData)) {
                // Tentative de correction
                $correctedContent = $this->attemptJsonCorrection($jsonContent);
                $aiData = json_decode($correctedContent, true);
                
                if (!$aiData || !is_array($aiData)) {
                    Log::error('JSON invalide même après correction', [
                        'json_error' => json_last_error_msg(),
                        'json_preview' => substr($jsonContent, 0, 500)
                    ]);
                    throw new \Exception('JSON invalide après correction: ' . json_last_error_msg());
                }
            }
            
            if (!isset($aiData['description'])) {
                throw new \Exception('Champ description manquant dans les données IA');
            }
            
            // Vérifier que le contenu est personnalisé et non générique
            $description = $aiData['description'] ?? '';
            $isGeneric = $this->isContentGeneric($description, $serviceName);
            
            if ($isGeneric) {
                Log::warning('Contenu template détecté comme générique', [
                    'service' => $serviceName,
                    'description_preview' => substr(strip_tags($description), 0, 200)
                ]);
                // On laisse passer mais on log pour information
            }
            
            Log::info('Données IA template validées avec succès', [
                'service' => $serviceName,
                'has_description' => isset($aiData['description']),
                'description_length' => strlen($aiData['description'] ?? '')
            ]);
            
            return $aiData;
            
        } catch (\Exception $e) {
            Log::error('Erreur validation données IA template', [
                'service' => $serviceName,
                'error' => $e->getMessage(),
                'content_preview' => substr($aiContent ?? '', 0, 500)
            ]);
            throw $e;
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
     * Extraire le JSON du contenu (amélioré pour gérer différents formats)
     */
    private function extractJsonFromContent($content)
    {
        $content = trim($content);
        
        // Si le contenu semble être directement du HTML (pas de JSON)
        if (strpos($content, '<div') !== false && strpos($content, '{') === false) {
            Log::info('Contenu HTML direct détecté dans template, pas de JSON');
            return null; // Retourner null pour indiquer qu'on doit créer une structure JSON
        }
        
        // Pattern 1: JSON dans code block markdown
        $patterns = [
            '/```json\s*(\{[\s\S]*?\})\s*```/s',
            '/```\s*(\{[\s\S]*?\})\s*```/s',
            '/\{[\s\S]*"description"[\s\S]*\}/s',  // JSON avec description
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $jsonString = $matches[1] ?? $matches[0];
                $jsonString = trim($jsonString);
                
                // Essayer de parser
                $data = json_decode($jsonString, true);
                if ($data && is_array($data)) {
                    Log::info('JSON extrait avec succès via pattern');
                    return $jsonString;
                }
            }
        }
        
        // Pattern 2: Chercher directement le JSON brut
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');
        
        if ($firstBrace !== false && $lastBrace !== false && $firstBrace < $lastBrace) {
        $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
        
            // Essayer de parser directement
            $data = json_decode($jsonContent, true);
            if ($data && is_array($data)) {
                Log::info('JSON extrait directement');
            return $jsonContent;
        }
            
            // Essayer après correction
            $corrected = $this->attemptJsonCorrection($jsonContent);
            $data = json_decode($corrected, true);
            if ($data && is_array($data)) {
                Log::info('JSON extrait après correction');
                return $corrected;
            }
        }
        
        Log::warning('Impossible d\'extraire JSON du contenu', [
            'content_preview' => substr($content, 0, 500)
        ]);
        
        return '';
    }

    /**
     * Tenter de corriger un JSON malformé
     */
    private function attemptJsonCorrection($content)
    {
        // Supprimer les caractères de contrôle
        $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content);
        
        // Corriger les apostrophes non échappées
        $content = preg_replace_callback('/"([^"]*\'[^"]*)"/', function($matches) {
            $string = $matches[1];
            $string = str_replace("'", "\\'", $string);
            return '"' . $string . '"';
        }, $content);
        
        // Supprimer les virgules en trop
        $content = preg_replace('/,(\s*[}\]])/', '$1', $content);
        
        return $content;
    }

    /**
     * Vérifier si le contenu est générique
     */
    private function isContentGeneric($description, $serviceName)
    {
        $descriptionLower = mb_strtolower($description);
        $serviceNameLower = mb_strtolower($serviceName);
        
        // Prestations génériques interdites
        $genericTerms = [
            'réparation et maintenance',
            'installation professionnelle',
            'conseils personnalisés',
            'diagnostic précis et traitement adapté',
            'remplacement intégral avec matériaux de qualité',
            'pose selon les normes en vigueur',
            'accompagnement dans vos choix'
        ];
        
        // Vérifier la présence de termes génériques
        $hasGenericTerms = false;
        foreach ($genericTerms as $term) {
            if (stripos($descriptionLower, $term) !== false) {
                $hasGenericTerms = true;
                break;
            }
        }
        
        // Vérifier si le nom du service est présent dans le contenu
        $containsServiceName = stripos($descriptionLower, $serviceNameLower) !== false;
        
        // Vérifier si le contenu est trop court (probablement générique)
        $plainText = strip_tags($description);
        $isTooShort = strlen($plainText) < 1000;
        
        // Le contenu est générique si :
        // - Il contient des termes génériques OU
        // - Le nom du service n'est pas présent ET le contenu est trop court
        return $hasGenericTerms || (!$containsServiceName && $isTooShort);
    }

    /**
     * Générer un contenu de fallback pour un template
     */
    private function generateFallbackTemplateContent($service)
    {
        $serviceName = $service['name'];
        $serviceSlug = $service['slug'];
        
        // Contenu HTML de fallback avec la même structure que l'IA
        $contentHtml = '<div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="space-y-4">
                    <p class="text-lg leading-relaxed">Service professionnel de ' . $serviceName . ' à [VILLE], une expertise reconnue dans [RÉGION].</p>
                    <p class="text-lg leading-relaxed">Spécialistes en travaux de ' . $serviceName . ' pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
                    <p class="leading-relaxed mb-3">Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION].</p>
                    <p class="leading-relaxed">Chaque intervention de ' . $serviceName . ' est réalisée selon les normes professionnelles les plus strictes.</p>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $serviceName . '</h3>
                <ul class="space-y-3">' . $this->generateSpecificPrestations($serviceName) . '</ul>
                <div class="bg-gray-50 p-6 rounded-lg mt-6">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">FAQ</h4>
                    <div class="space-y-2">
                        <p><strong>Q1: Combien coûte un service de ' . $serviceName . ' à [VILLE]?</strong></p>
                        <p>A: Le prix dépend de la complexité et de l\'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p>
                        <p><strong>Q2: Quel est le délai d\'intervention pour ' . $serviceName . '?</strong></p>
                        <p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l\'urgence de votre demande.</p>
                        <p><strong>Q3: Proposez-vous une garantie sur vos services de ' . $serviceName . '?</strong></p>
                        <p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi choisir ce service</h3>
                    <p class="leading-relaxed">Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région et d\'adapter nos services en conséquence.</p>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
                <p class="leading-relaxed">Depuis plusieurs années, nous intervenons sur [VILLE] et sa région, développant une connaissance approfondie des besoins locaux en ' . $serviceName . '.</p>
                <div class="bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Financement et aides</h4>
                    <p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de ' . $serviceName . '.</p>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un devis?</h4>
                    <p class="mb-4">Contactez-nous pour un devis gratuit pour ' . $serviceName . ' à [VILLE].</p>
                    <a href="[FORM_URL]" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Devis gratuit et sans engagement</span></li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Intervention rapide sur [VILLE]</span></li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Garantie sur tous nos travaux</span></li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Partager ce service</h4>
                        <div class="flex justify-center items-center space-x-4">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fab fa-facebook-f text-lg"></i>
                                <span class="font-medium">Facebook</span>
                            </a>
                            <a href="https://wa.me/?text=[TITRE] - [URL]" target="_blank" rel="noopener noreferrer" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fab fa-whatsapp text-lg"></i>
                                <span class="font-medium">WhatsApp</span>
                            </a>
                            <a href="mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fas fa-envelope text-lg"></i>
                                <span class="font-medium">Email</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        return [
            'description' => $contentHtml,
            'short_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE] - Devis gratuit et intervention rapide',
            'long_description' => 'Notre entreprise spécialisée en ' . $serviceName . ' intervient sur [VILLE] et dans toute la région de [RÉGION]. Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d\'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.',
            'icon' => 'fas fa-tools',
            'meta_title' => $serviceName . ' à [VILLE] - Service professionnel',
            'meta_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
            'meta_keywords' => $serviceName . ', [VILLE], [RÉGION], service professionnel, devis gratuit',
            'og_title' => $serviceName . ' à [VILLE] - Service professionnel',
            'og_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
            'twitter_title' => $serviceName . ' à [VILLE] - Service professionnel',
            'twitter_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
        ];
    }

    /**
     * Générer des prestations spécifiques selon le type de service
     */
    private function generateSpecificPrestations($serviceName)
    {
        $prestations = [];
        
        // Détecter le type de service et générer des prestations spécifiques
        $serviceLower = strtolower($serviceName);
        
        if (strpos($serviceLower, 'toiture') !== false || strpos($serviceLower, 'couverture') !== false || strpos($serviceLower, 'rénovation') !== false) {
            $prestations = [
                'Diagnostic et inspection de toiture - Évaluation complète de l\'état de la couverture et détection des fuites',
                'Nettoyage et démoussage - Nettoyage haute pression et application d\'antimousse professionnel',
                'Réparation partielle de toiture - Remplacement d\'ardoises, tuiles et réparation des joints',
                'Réfection complète de toiture - Dépose et pose d\'une nouvelle couverture selon les normes',
                'Isolation de toiture - Pose d\'isolants thermiques sous toiture ou par sarking',
                'Étanchéité et traitement hydrofuge - Protection contre les infiltrations et l\'humidité',
                'Réparation de zinguerie - Pose et entretien des gouttières, chéneaux et descentes d\'eau',
                'Pose de charpente - Réparation ou installation de charpentes en bois traitées',
                'Installation de fenêtres de toit - Pose de Velux et étanchéification des ouvertures',
                'Entretien annuel et maintenance - Inspection périodique et nettoyage saisonnier'
            ];
        } elseif (strpos($serviceLower, 'plomberie') !== false) {
            $prestations = [
                'Installation de chauffe-eau - Pose de chauffe-eau électrique, gaz ou thermodynamique',
                'Réparation de fuites - Détection et réparation de fuites sur canalisations et robinetterie',
                'Débouchage de canalisations - Intervention rapide pour déboucher éviers, WC et canalisations',
                'Pose de robinetterie - Installation de robinets, mitigeurs et accessoires de salle de bain',
                'Installation de WC - Pose, remplacement et raccordement de toilettes et accessoires',
                'Rénovation de salle de bain - Aménagement complet avec carrelage et sanitaires',
                'Détection de fuites - Recherche de fuites cachées avec matériel professionnel',
                'Installation de radiateurs - Pose et raccordement de radiateurs et planchers chauffants',
                'Raccordement gaz - Installation et mise en conformité des installations gaz',
                'Maintenance préventive - Entretien régulier des installations et prévention des pannes'
            ];
        } elseif (strpos($serviceLower, 'électricité') !== false || strpos($serviceLower, 'électrique') !== false) {
            $prestations = [
                'Installation électrique - Mise en conformité et installation de tableaux électriques',
                'Rénovation de tableau électrique - Remplacement et mise aux normes NF C 15-100',
                'Pose de prises et interrupteurs - Installation et remplacement de matériel électrique',
                'Installation d\'éclairage - Pose de spots, lustres et éclairage LED',
                'Installation de volets roulants - Motorisation et automatisation des volets',
                'Installation de climatisation - Pose de climatiseurs et pompes à chaleur',
                'Mise en sécurité - Installation de disjoncteurs différentiels et parafoudres',
                'Installation de domotique - Automatisation et contrôle à distance',
                'Dépannage électrique - Intervention d\'urgence pour pannes et dysfonctionnements',
                'Vérification d\'installation - Contrôle et mise en conformité des installations existantes'
            ];
        } elseif (strpos($serviceLower, 'peinture') !== false) {
            $prestations = [
                'Préparation des surfaces - Ponçage, rebouchage et traitement des murs',
                'Peinture intérieure - Rénovation complète des pièces avec peintures écologiques',
                'Peinture extérieure - Ravalement de façade et protection contre les intempéries',
                'Pose de papier peint - Installation et pose de revêtements muraux',
                'Peinture de plafond - Rénovation et traitement des plafonds',
                'Peinture de menuiseries - Rénovation des portes, fenêtres et volets',
                'Traitement des murs humides - Diagnostic et traitement de l\'humidité',
                'Peinture de cuisine et salle de bain - Revêtements adaptés aux pièces humides',
                'Finitions décoratives - Effets spéciaux, patines et techniques artistiques',
                'Nettoyage et protection - Entretien et protection des surfaces peintes'
            ];
        } elseif (strpos($serviceLower, 'isolation') !== false) {
            $prestations = [
                'Isolation des combles - Pose d\'isolants thermiques sous toiture',
                'Isolation des murs - Isolation intérieure ou extérieure des parois',
                'Isolation des sols - Pose d\'isolants sous plancher et dalle',
                'Isolation phonique - Réduction des bruits et amélioration acoustique',
                'Isolation des fenêtres - Pose de double vitrage et calfeutrage',
                'Isolation des portes - Pose de joints et amélioration de l\'étanchéité',
                'Isolation des tuyaux - Protection des canalisations contre le gel',
                'Isolation des toitures terrasses - Pose de membranes isolantes',
                'Isolation des caves - Traitement de l\'humidité et isolation thermique',
                'Audit énergétique - Diagnostic et recommandations d\'amélioration'
            ];
        } else {
            // Prestations génériques pour les autres services
            $prestations = [
                'Diagnostic et évaluation - Analyse complète de vos besoins en ' . $serviceName,
                'Intervention d\'urgence - Service rapide 24h/7j pour ' . $serviceName,
                'Maintenance préventive - Entretien régulier pour éviter les problèmes',
                'Réparation spécialisée - Correction des dysfonctionnements',
                'Installation complète - Pose selon les normes en vigueur',
                'Rénovation totale - Remplacement intégral avec matériaux de qualité',
                'Conseil personnalisé - Recommandations adaptées à votre situation',
                'Suivi post-intervention - Accompagnement après travaux',
                'Formation utilisateur - Apprentissage des bonnes pratiques',
                'Garantie étendue - Protection supplémentaire sur nos interventions'
            ];
        }
        
        // Générer le HTML des prestations
        $html = '';
        foreach ($prestations as $prestation) {
            $html .= '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>' . $prestation . '</strong></span></li>';
        }
        
        return $html;
    }

    /**
     * Générer du contenu de template pour un mot-clé via IA
     */
    private function generateKeywordTemplateContent($keyword, $aiPrompt = null)
    {
        // Construire le prompt pour le mot-clé
        $prompt = $this->buildKeywordTemplatePrompt($keyword, $aiPrompt);
        
        Log::info('=== DÉBUT GÉNÉRATION TEMPLATE MOT-CLÉ ===', [
            'keyword' => $keyword,
            'chatgpt_enabled' => setting('chatgpt_enabled', true),
            'chatgpt_api_key_exists' => !empty(setting('chatgpt_api_key')),
            'groq_api_key_exists' => !empty(setting('groq_api_key', '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'))
        ]);
        
        // Message système pour forcer la personnalisation
        $systemMessage = "Tu es un expert technique en {$keyword} avec une connaissance approfondie du domaine. CRITIQUE ABSOLUE: Chaque contenu DOIT être UNIQUE, TECHNIQUE et SPÉCIFIQUE à {$keyword}. INTERDIT d'utiliser des prestations génériques ou du contenu copié. Adapte TOUT spécifiquement au mot-clé {$keyword}.";
        
        // Utiliser AiService avec fallback automatique vers Groq
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 4000,
            'temperature' => 0.9,  // Augmenté pour plus de créativité et personnalisation
            'timeout' => 120
        ]);

        if (!$result || !isset($result['content'])) {
            Log::error('Échec génération template mot-clé - Aucune réponse de l\'IA', [
                'keyword' => $keyword,
                'result' => $result
            ]);
            throw new \Exception('Erreur API IA: Impossible de générer le contenu. ChatGPT et Groq ont tous deux échoué.');
        }

        $provider = $result['provider'] ?? 'unknown';
        $aiContent = $result['content'];
        
        Log::info('Réponse IA reçue pour template mot-clé', [
            'keyword' => $keyword,
            'provider' => $provider,
            'content_length' => strlen($aiContent),
            'content_preview' => substr($aiContent, 0, 200)
        ]);
        
        // Valider et nettoyer le contenu
        return $this->validateAndCleanAIData($aiContent, $keyword);
    }

    /**
     * Construire le prompt pour un template de mot-clé
     */
    private function buildKeywordTemplatePrompt($keyword, $aiPrompt = null)
    {
        $basePrompt = "Tu es un expert technique en {$keyword} avec une connaissance PROFONDE des prestations, techniques et matériaux spécifiques à ce domaine. Crée un template d'annonce TOTALEMENT personnalisé pour {$keyword}.

⚠️⚠️⚠️ MOT-CLÉ À PERSONNALISER: {$keyword} ⚠️⚠️⚠️

🚫 INTERDICTIONS ABSOLUES:
- INTERDIT d'utiliser des prestations génériques comme 'Diagnostic', 'Conseil', 'Maintenance générale', 'Installation professionnelle'
- INTERDIT de copier du contenu générique applicable à tous les services
- INTERDIT d'utiliser un vocabulaire vague ou général

✅ OBLIGATIONS ABSOLUES POUR {$keyword}:
- Chaque prestation DOIT être TECHNIQUE et SPÉCIFIQUE UNIQUEMENT à {$keyword}
- Utilise le vocabulaire PROFESSIONNEL du métier de {$keyword}
- Les prestations doivent mentionner des techniques, matériaux ou méthodes PRÉCISES liés à {$keyword}
- Chaque description doit expliquer QUOI, COMMENT et POURQUOI spécifiquement pour {$keyword}

GÉNÈRE UN JSON AVEC CES CHAMPS:

{
  \"description\": \"<div class='grid md:grid-cols-2 gap-8'><div class='space-y-6'><div class='space-y-4'><p class='text-lg leading-relaxed'>Service professionnel de {$keyword} à [VILLE], une expertise reconnue dans [RÉGION].</p><p class='text-lg leading-relaxed'>Spécialistes en travaux de {$keyword} pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p></div><div class='bg-blue-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Notre Engagement Qualité</h3><p class='leading-relaxed mb-3'>Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION].</p><p class='leading-relaxed'>Chaque intervention de {$keyword} est réalisée selon les normes professionnelles les plus strictes.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Nos Prestations {$keyword}</h3><ul class='space-y-3'>[GÉNÈRE 10 PRESTATIONS SPÉCIFIQUES À {$keyword} AVEC DES DESCRIPTIONS DÉTAILLÉES]</ul><div class='bg-gray-50 p-6 rounded-lg mt-6'><h4 class='text-xl font-bold text-gray-900 mb-3'>FAQ</h4><div class='space-y-2'><p><strong>Q1: Combien coûte un service de {$keyword} à [VILLE]?</strong></p><p>A: Le prix dépend de la complexité et de l'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p><p><strong>Q2: Quel est le délai d'intervention pour {$keyword}?</strong></p><p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l'urgence de votre demande.</p><p><strong>Q3: Proposez-vous une garantie sur vos services de {$keyword}?</strong></p><p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p></div></div></div><div class='space-y-6'><div class='bg-green-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Pourquoi choisir ce service</h3><p class='leading-relaxed'>Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région et d'adapter nos services en conséquence.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Notre Expertise Locale</h3><p class='leading-relaxed'>Depuis plusieurs années, nous intervenons sur [VILLE] et sa région, développant une connaissance approfondie des besoins locaux en {$keyword}.</p><div class='bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Financement et aides</h4><p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de {$keyword}.</p></div><div class='bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Besoin d'un devis?</h4><p class='mb-4'>Contactez-nous pour un devis gratuit pour {$keyword} à [VILLE].</p><a href='[FORM_URL]' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300'>Demande de devis</a></div><div class='bg-gray-50 p-6 rounded-lg'><h4 class='text-lg font-bold text-gray-900 mb-3'>Informations Pratiques</h4><ul class='space-y-2 text-sm'><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Devis gratuit et sans engagement</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Intervention rapide sur [VILLE]</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Garantie sur tous nos travaux</span></li></ul></div><div class='mt-8 pt-6 border-t border-gray-200'><div class='text-center'><h4 class='text-lg font-semibold text-gray-800 mb-4'>Partager ce service</h4><div class='flex justify-center items-center space-x-4'><a href='https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]' target='_blank' rel='noopener noreferrer' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-facebook-f text-lg'></i><span class='font-medium'>Facebook</span></a><a href='https://wa.me/?text=[TITRE] - [URL]' target='_blank' rel='noopener noreferrer' class='bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-whatsapp text-lg'></i><span class='font-medium'>WhatsApp</span></a><a href='mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]' class='bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fas fa-envelope text-lg'></i><span class='font-medium'>Email</span></a></div></div></div></div>\",
  \"short_description\": \"Service professionnel de {$keyword} à [VILLE] - Devis gratuit et intervention rapide\",
  \"long_description\": \"Notre entreprise spécialisée en {$keyword} intervient sur [VILLE] et dans toute la région de [RÉGION]. Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.\",
  \"icon\": \"fas fa-tools\",
  \"meta_title\": \"{$keyword} à [VILLE] - Service professionnel\",
  \"meta_description\": \"Service professionnel de {$keyword} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"og_title\": \"{$keyword} à [VILLE] - Service professionnel\",
  \"og_description\": \"Service professionnel de {$keyword} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"twitter_title\": \"{$keyword} à [VILLE] - Service professionnel\",
  \"twitter_description\": \"Service professionnel de {$keyword} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"meta_keywords\": \"{$keyword}, [VILLE], [RÉGION], service professionnel, devis gratuit\"
}

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - FORMAT JSON ⚠️⚠️⚠️:
- TU DOIS RÉPONDRE UNIQUEMENT AVEC UN JSON VALIDE
- COMMENCE DIRECTEMENT PAR { (accolade ouvrante)
- TERMINE DIRECTEMENT PAR } (accolade fermante)
- PAS de texte avant le JSON
- PAS de texte après le JSON
- PAS de ```json ou ``` autour du JSON
- PAS de commentaires ou explications
- JUSTE le JSON brut

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - CONTENU ⚠️⚠️⚠️:
- REMPLACE TOUT le contenu par du contenu VRAIMENT spécifique à {$keyword}
- REMPLACE [GÉNÈRE 10 PRESTATIONS SPÉCIFIQUES À {$keyword}] par 10 prestations TECHNIQUES RÉELLES pour {$keyword}
- Chaque prestation doit avoir un NOM TECHNIQUE précis et une DESCRIPTION détaillée avec techniques/matériaux pour {$keyword}
- PERSONNALISE les descriptions, FAQ, et tous les textes pour {$keyword} spécifiquement
- Utilise [VILLE], [RÉGION], [DÉPARTEMENT] comme placeholders pour les variables dynamiques
- Le contenu HTML doit être COMPLET et PERSONNALISÉ, pas un template copié-collé

EXEMPLES CONCRETS POUR {$keyword}:
- Si {$keyword} = 'Désamiantage' → prestations: 'Dépollution amiante', 'Retrait amiante sous confinement', 'Gestion déchets amiante'
- Si {$keyword} = 'Traitement humidité' → prestations: 'Diagnostic humidité par imagerie thermique', 'Injection résine anti-humidité', 'Installation VMC double flux'
- Si {$keyword} = 'Rénovation toiture' → prestations: 'Diagnostic toiture par drone', 'Réfection tuiles ardoise', 'Installation écran de sous-toiture'
";

        if ($aiPrompt) {
            $basePrompt .= "\n\nINSTRUCTIONS PERSONNALISÉES SUPPLÉMENTAIRES:\n" . $aiPrompt;
        }

        return $basePrompt;
    }

    /**
     * Générer un contenu de fallback pour un template de mot-clé
     */
    private function generateFallbackKeywordTemplateContent($keyword)
    {
        // Contenu HTML de fallback avec la même structure que l'IA
        $contentHtml = '<div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="space-y-4">
                    <p class="text-lg leading-relaxed">Service professionnel de ' . $keyword . ' à [VILLE], une expertise reconnue dans [RÉGION].</p>
                    <p class="text-lg leading-relaxed">Spécialistes en travaux de ' . $keyword . ' pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
                    <p class="leading-relaxed mb-3">Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION].</p>
                    <p class="leading-relaxed">Chaque intervention de ' . $keyword . ' est réalisée selon les normes professionnelles les plus strictes.</p>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $keyword . '</h3>
                <ul class="space-y-3">' . $this->generateSpecificPrestations($keyword) . '</ul>
                <div class="bg-gray-50 p-6 rounded-lg mt-6">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">FAQ</h4>
                    <div class="space-y-2">
                        <p><strong>Q1: Combien coûte un service de ' . $keyword . ' à [VILLE]?</strong></p>
                        <p>A: Le prix dépend de la complexité et de l\'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p>
                        <p><strong>Q2: Quel est le délai d\'intervention pour ' . $keyword . '?</strong></p>
                        <p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l\'urgence de votre demande.</p>
                        <p><strong>Q3: Proposez-vous une garantie sur vos services de ' . $keyword . '?</strong></p>
                        <p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi choisir ce service</h3>
                    <p class="leading-relaxed">Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région et d\'adapter nos services en conséquence.</p>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
                <p class="leading-relaxed">Depuis plusieurs années, nous intervenons sur [VILLE] et sa région, développant une connaissance approfondie des besoins locaux en ' . $keyword . '.</p>
                <div class="bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Financement et aides</h4>
                    <p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de ' . $keyword . '.</p>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un devis?</h4>
                    <p class="mb-4">Contactez-nous pour un devis gratuit pour ' . $keyword . ' à [VILLE].</p>
                    <a href="[FORM_URL]" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Devis gratuit et sans engagement</span></li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Intervention rapide sur [VILLE]</span></li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Garantie sur tous nos travaux</span></li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Partager ce service</h4>
                        <div class="flex justify-center items-center space-x-4">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fab fa-facebook-f text-lg"></i>
                                <span class="font-medium">Facebook</span>
                            </a>
                            <a href="https://wa.me/?text=[TITRE] - [URL]" target="_blank" rel="noopener noreferrer" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fab fa-whatsapp text-lg"></i>
                                <span class="font-medium">WhatsApp</span>
                            </a>
                            <a href="mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fas fa-envelope text-lg"></i>
                                <span class="font-medium">Email</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        return [
            'description' => $contentHtml,
            'short_description' => 'Service professionnel de ' . $keyword . ' à [VILLE] - Devis gratuit et intervention rapide',
            'long_description' => 'Notre entreprise spécialisée en ' . $keyword . ' intervient sur [VILLE] et dans toute la région de [RÉGION]. Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d\'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.',
            'icon' => 'fas fa-tools',
            'meta_title' => $keyword . ' à [VILLE] - Service professionnel',
            'meta_description' => 'Service professionnel de ' . $keyword . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
            'meta_keywords' => $keyword . ', [VILLE], [RÉGION], service professionnel, devis gratuit',
            'og_title' => $keyword . ' à [VILLE] - Service professionnel',
            'og_description' => 'Service professionnel de ' . $keyword . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
            'twitter_title' => $keyword . ' à [VILLE] - Service professionnel',
            'twitter_description' => 'Service professionnel de ' . $keyword . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
        ];
    }

    /**
     * Générer un slug unique pour les annonces
     */
    private function generateUniqueSlug($baseSlug)
    {
        $slug = $baseSlug;
        $counter = 1;
        
        // Vérifier si le slug existe déjà
        while (\App\Models\Ad::where('slug', $slug)->exists()) {
            $suffixes = [
                'devis-gratuit',
                'prix-competitif',
                'service-professionnel',
                'expert-local',
                'qualite-garantie',
                'intervention-rapide',
                'devis-personnalise',
                'travaux-sur-mesure'
            ];
            
            if ($counter <= count($suffixes)) {
                $slug = $baseSlug . '-' . $suffixes[$counter - 1];
            } else {
                $slug = $baseSlug . '-' . $counter;
            }
            
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Obtenir la liste des villes pour la génération d'annonces
     */
    public function getCities()
    {
        $cities = City::select('id', 'name', 'region', 'department')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }

    /**
     * Créer un template à partir d'un mot-clé
     */
    public function createFromKeyword(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:255',
            'ai_prompt' => 'nullable|string|max:5000',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $keyword = $request->input('keyword');
        
        // Gérer l'upload de l'image si fournie
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $fileName = 'template_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('uploads/templates');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $file->move($uploadPath, $fileName);
            $featuredImagePath = 'uploads/templates/' . $fileName;
        }
        
        try {
            // Vérifier si des templates existent déjà pour ce mot-clé
            $existingTemplates = AdTemplate::where('service_slug', Str::slug($keyword))->get();
            
            if ($existingTemplates->count() > 0 && !$request->input('force_create', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Des templates existent déjà pour ce mot-clé',
                    'existing_templates' => $existingTemplates->map(function($template) {
                        return [
                            'id' => $template->id,
                            'name' => $template->name,
                            'is_active' => $template->is_active,
                            'ads_count' => $template->ads()->count(),
                            'created_at' => $template->created_at->format('d/m/Y H:i')
                        ];
                    })
                ], 400);
            }

            // Utiliser generateCompleteTemplateContent pour le mot-clé
            $companyInfo = $this->getCompanyInfo();
            $aiContent = $this->generateCompleteTemplateContent(
                $keyword,
                '',
                $companyInfo,
                $request->input('ai_prompt')
            );
            
            // Créer le template
            $template = AdTemplate::create([
                'name' => $keyword,
                'service_name' => $keyword,
                'service_slug' => Str::slug($keyword),
                'content_html' => $aiContent['description'],
                'short_description' => $aiContent['short_description'],
                'long_description' => $aiContent['long_description'],
                'icon' => $aiContent['icon'],
                'featured_image' => $featuredImagePath,
                'meta_title' => $aiContent['meta_title'],
                'meta_description' => $aiContent['meta_description'],
                'meta_keywords' => $aiContent['meta_keywords'],
                'og_title' => $aiContent['og_title'],
                'og_description' => $aiContent['og_description'],
                'twitter_title' => $aiContent['twitter_title'],
                'twitter_description' => $aiContent['twitter_description'],
                'ai_prompt_used' => $request->input('ai_prompt'),
                'ai_response_data' => $aiContent
            ]);

            // Générer automatiquement une annonce pour une ville aléatoire
            $randomCity = null;
            $adCreated = false;
            
            try {
                // Récupérer une ville au hasard
                $randomCity = City::inRandomOrder()->first();
                
                if ($randomCity) {
                    // Vérifier qu'il n'existe pas déjà une annonce pour cette combinaison
                    $existingAd = Ad::where('template_id', $template->id)
                        ->where('city_id', $randomCity->id)
                        ->first();

                    if (!$existingAd) {
                        // Obtenir le contenu et les métadonnées personnalisées pour cette ville
                        $contentForCity = $template->getContentForCity($randomCity);
                        $metaForCity = $template->getMetaForCity($randomCity);

                        // Créer l'annonce avec personnalisation complète
                        Ad::create([
                            'title' => $template->service_name . ' à ' . $randomCity->name,
                            'keyword' => $template->service_name,
                            'city_id' => $randomCity->id,
                            'template_id' => $template->id,
                            'slug' => $this->generateUniqueSlug(Str::slug($template->service_name . '-' . $randomCity->name)),
                            'status' => 'published',
                            'published_at' => now(),
                            'meta_title' => $metaForCity['meta_title'],
                            'meta_description' => $metaForCity['meta_description'],
                            'meta_keywords' => $metaForCity['meta_keywords'],
                            'content_html' => $contentForCity,
                            'content_json' => json_encode([
                                'template_id' => $template->id,
                                'city' => $randomCity->toArray(),
                                'generated_at' => now()->toISOString(),
                                'auto_generated' => true
                            ])
                        ]);

                        // Incrémenter le compteur d'utilisation du template
                        $template->incrementUsage();
                        $adCreated = true;
                        
                        Log::info('Annonce auto-générée pour template mot-clé', [
                            'template_id' => $template->id,
                            'city' => $randomCity->name,
                            'keyword' => $keyword
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Impossible de créer automatiquement une annonce pour le template', [
                    'template_id' => $template->id,
                    'error' => $e->getMessage()
                ]);
                // On continue même si l'annonce n'a pas pu être créée
            }

            // Message de succès avec information sur la ville
            $message = 'Template créé avec succès pour le mot-clé: ' . $keyword;
            if ($adCreated && $randomCity) {
                $message .= '. Une annonce a été automatiquement générée pour ' . $randomCity->name . '.';
            }

            // Retourner une réponse JSON pour les appels AJAX
            return response()->json([
                'success' => true,
                'message' => $message,
                'template_id' => $template->id,
                'ad_created' => $adCreated,
                'city_name' => $randomCity ? $randomCity->name : null,
                'redirect_url' => route('admin.ads.templates.edit', $template->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur création template mot-clé - Première tentative échouée', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);

            // FORCER une nouvelle tentative avec l'IA - NE JAMAIS utiliser le fallback générique
            try {
                Log::info('Tentative de re-génération IA pour template mot-clé', ['keyword' => $keyword]);
                
                // Essayer plusieurs fois avec des paramètres différents
                $maxRetries = 3;
                $aiContent = null;
                
                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        Log::info("Tentative {$attempt}/{$maxRetries} de génération IA", ['keyword' => $keyword]);
                        
                        // Re-générer avec des paramètres plus agressifs
                        $companyInfo = $this->getCompanyInfo();
                        $aiContent = $this->generateCompleteTemplateContent(
                            $keyword,
                            '',
                            $companyInfo,
                            $request->input('ai_prompt') . ($attempt > 1 ? " (Tentative {$attempt} - CONTENU GÉNÉRIQUE INTERDIT)" : '')
                        );
                        
                        // Si on a réussi à obtenir du contenu, sortir de la boucle
                        if ($aiContent && isset($aiContent['description']) && strlen(strip_tags($aiContent['description'])) > 1500) {
                            Log::info("Génération IA réussie à la tentative {$attempt}", ['keyword' => $keyword]);
                            break;
                        }
                    } catch (\Exception $retryException) {
                        Log::warning("Tentative {$attempt} échouée", [
                            'keyword' => $keyword,
                            'error' => $retryException->getMessage()
                        ]);
                        
                        if ($attempt === $maxRetries) {
                            throw $retryException;
                        }
                    }
                }
                
                if (!$aiContent || !isset($aiContent['description'])) {
                    throw new \Exception("Impossible de générer du contenu via IA après {$maxRetries} tentatives");
                }
                
                // Créer le template avec le contenu IA généré
                $template = AdTemplate::create([
                    'name' => $keyword,
                    'service_name' => $keyword,
                    'service_slug' => Str::slug($keyword),
                    'content_html' => $aiContent['description'],
                    'short_description' => $aiContent['short_description'],
                    'long_description' => $aiContent['long_description'],
                    'icon' => $aiContent['icon'],
                    'featured_image' => $featuredImagePath,
                    'meta_title' => $aiContent['meta_title'],
                    'meta_description' => $aiContent['meta_description'],
                    'meta_keywords' => $aiContent['meta_keywords'],
                    'og_title' => $aiContent['og_title'],
                    'og_description' => $aiContent['og_description'],
                    'twitter_title' => $aiContent['twitter_title'],
                    'twitter_description' => $aiContent['twitter_description'],
                    'ai_prompt_used' => $request->input('ai_prompt'),
                    'ai_response_data' => $aiContent
                ]);
                
                // Retourner la réponse de succès (pas de fallback)
                return response()->json([
                    'success' => true,
                    'message' => 'Template créé avec succès via IA après plusieurs tentatives. Vous pouvez maintenant le personnaliser avant de générer les annonces.',
                    'template_id' => $template->id,
                    'redirect_url' => route('admin.ads.templates.edit', $template->id),
                    'warning' => true
                ]);
                
            } catch (\Exception $fallbackError) {
                Log::error('ÉCHEC COMPLET - Toutes les tentatives IA ont échoué', [
                    'keyword' => $keyword,
                    'error' => $fallbackError->getMessage()
                ]);

                // DERNIER RECOURS: Essayer de créer un template avec du contenu de fallback PERSONNALISÉ (pas générique)
                try {
                    // Utiliser generateFallbackTemplateContent mais avec un service personnalisé
                    $fallbackContent = $this->generateFallbackTemplateContent([
                        'name' => $keyword,
                        'slug' => Str::slug($keyword),
                        'short_description' => "Service professionnel de {$keyword}"
                    ]);
                
                $template = AdTemplate::create([
                    'name' => $keyword,
                    'service_name' => $keyword,
                    'service_slug' => Str::slug($keyword),
                    'content_html' => $fallbackContent['description'],
                    'short_description' => $fallbackContent['short_description'],
                    'long_description' => $fallbackContent['long_description'],
                    'icon' => $fallbackContent['icon'],
                        'featured_image' => $featuredImagePath, // Conserver l'image même en fallback
                    'meta_title' => $fallbackContent['meta_title'],
                    'meta_description' => $fallbackContent['meta_description'],
                    'meta_keywords' => $fallbackContent['meta_keywords'],
                    'og_title' => $fallbackContent['og_title'],
                    'og_description' => $fallbackContent['og_description'],
                    'twitter_title' => $fallbackContent['twitter_title'],
                    'twitter_description' => $fallbackContent['twitter_description'],
                    'ai_prompt_used' => $request->input('ai_prompt'),
                    'ai_response_data' => ['fallback' => true, 'error' => $e->getMessage()],
                ]);

                    // Générer aussi une annonce en fallback pour respecter la personnalisation
                    $randomCity = null;
                    $adCreated = false;
                    
                    try {
                        $randomCity = City::inRandomOrder()->first();
                        
                        if ($randomCity) {
                            $existingAd = Ad::where('template_id', $template->id)
                                ->where('city_id', $randomCity->id)
                                ->first();

                            if (!$existingAd) {
                                $contentForCity = $template->getContentForCity($randomCity);
                                $metaForCity = $template->getMetaForCity($randomCity);

                                Ad::create([
                                    'title' => $template->service_name . ' à ' . $randomCity->name,
                                    'keyword' => $template->service_name,
                                    'city_id' => $randomCity->id,
                                    'template_id' => $template->id,
                                    'slug' => $this->generateUniqueSlug(Str::slug($template->service_name . '-' . $randomCity->name)),
                                    'status' => 'published',
                                    'published_at' => now(),
                                    'meta_title' => $metaForCity['meta_title'],
                                    'meta_description' => $metaForCity['meta_description'],
                                    'meta_keywords' => $metaForCity['meta_keywords'],
                                    'content_html' => $contentForCity,
                                    'content_json' => json_encode([
                                        'template_id' => $template->id,
                                        'city' => $randomCity->toArray(),
                                        'generated_at' => now()->toISOString(),
                                        'auto_generated' => true,
                                        'fallback' => true
                                    ])
                                ]);

                                $template->incrementUsage();
                                $adCreated = true;
                            }
                        }
                    } catch (\Exception $adError) {
                        Log::warning('Impossible de créer automatiquement une annonce en fallback', [
                            'template_id' => $template->id,
                            'error' => $adError->getMessage()
                        ]);
                    }

                    // Message avec information sur la ville
                    $message = 'L\'API IA n\'était pas disponible. Le template a été créé avec du contenu par défaut. Vous pouvez le personnaliser maintenant.';
                    if ($adCreated && $randomCity) {
                        $message .= ' Une annonce a été automatiquement générée pour ' . $randomCity->name . '.';
                    }

                    // Retourner une réponse JSON même avec fallback
                return response()->json([
                    'success' => true,
                        'message' => $message,
                    'template_id' => $template->id,
                        'ad_created' => $adCreated,
                        'city_name' => $randomCity ? $randomCity->name : null,
                        'redirect_url' => route('admin.ads.templates.edit', $template->id),
                        'warning' => true
                    ]);
                    
                } catch (\Exception $finalError) {
                    Log::error('Erreur création template mot-clé fallback final', [
                    'keyword' => $keyword,
                        'error' => $finalError->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du template: ' . $e->getMessage()
                ], 500);
                }
            }
        }
    }

    /**
     * Supprimer un template
     */
    public function destroy(AdTemplate $template)
    {
        try {
            // Vérifier s'il y a des annonces associées
            $adsCount = $template->ads()->count();
            
            if ($adsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer ce template car {$adsCount} annonce(s) y sont associées."
                ], 400);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression template', [
                'template_id' => $template->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du template'
            ], 500);
        }
    }

    /**
     * Basculer le statut d'un template
     */
    public function toggleStatus(Request $request, AdTemplate $template)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $template->update([
            'is_active' => $request->input('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut du template mis à jour'
        ]);
    }

    /**
     * Récupérer les informations de l'entreprise
     */
    private function getCompanyInfo()
    {
        return [
            'company_name' => setting('company_name', 'Notre Entreprise'),
            'company_city' => setting('company_city', ''),
            'company_region' => setting('company_region', ''),
            'company_phone' => setting('company_phone', ''),
            'company_email' => setting('company_email', ''),
            'company_address' => setting('company_address', ''),
        ];
    }

    /**
     * Générer un contenu complet de template via IA (inspiré de generateCompleteServiceContent)
     */
    private function generateCompleteTemplateContent($serviceName, $shortDescription, $companyInfo, $aiPrompt = null)
    {
        try {
            // Construire le prompt pour le template avec placeholders [VILLE], [RÉGION]
            $basePrompt = $this->buildTemplatePromptForService($serviceName, $shortDescription, $companyInfo, $aiPrompt);
            
            Log::info('=== DÉBUT GÉNÉRATION IA TEMPLATE ===', [
                'service_name' => $serviceName,
                'short_description' => $shortDescription,
                'chatgpt_enabled' => setting('chatgpt_enabled', true),
                'chatgpt_api_key_exists' => !empty(setting('chatgpt_api_key')),
                'groq_api_key_exists' => !empty(setting('groq_api_key', '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'))
            ]);
            
            // Ajouter un identifiant unique pour forcer la génération unique
            $uniqueId = uniqid();
            $timestamp = now()->toIso8601String();
            
            $prompt = $basePrompt . "\n\n⚠️ IMPORTANT - GÉNÉRATION UNIQUE REQUISE ⚠️:\n- ID unique: {$uniqueId}\n- Timestamp: {$timestamp}\n- Le contenu DOIT être COMPLÈTEMENT différent de toute génération précédente\n- Personnalise TOUT le contenu spécifiquement pour le service: {$serviceName}\n- Utilise des exemples, techniques, matériaux et prestations UNIQUES à {$serviceName}\n- Ne copie JAMAIS du contenu générique\n- Crée du contenu 100% ORIGINAL et SPÉCIFIQUE à {$serviceName}";
            
            // Utiliser le service AI avec fallback automatique
            $systemMessage = "Tu es un expert technique en {$serviceName} avec une connaissance PROFONDE et SPÉCIFIQUE de ce domaine. Tu crées du contenu professionnel, engageant, technique et optimisé SEO pour des templates d'annonces.

🚨 CRITIQUE ABSOLUE - PERSONNALISATION OBLIGATOIRE:
- Chaque section DOIT contenir du contenu VRAIMENT écrit, pas de placeholders [Paragraphe X] ou [ÉCRIRE ICI]
- Utilise un vocabulaire TECHNIQUE et SPÉCIFIQUE à {$serviceName}: normes (RT 2012, DTU), certifications (RGE, Qualit'ENR, NF), matériaux précis (ex: laine de verre ISOVER, polyuréthane, fibre de bois), techniques professionnelles
- Mentionne des CHIFFRES CONCRETS: résistances thermiques (R en m².K/W), épaisseurs, performances, économies d'énergie, délais d'intervention
- Donne des EXEMPLES CONCRETS de réalisations, processus, garanties spécifiques à {$serviceName}
- INTERDIT absolu: phrases génériques comme \"Nous garantissons la satisfaction\", \"Notre expertise locale\", \"Intervention rapide\" sans détails techniques

✅ ACCEPTE seulement: contenu technique, spécifique, avec chiffres, normes, matériaux, processus précis pour {$serviceName}.

Placeholders autorisés UNIQUEMENT: [VILLE], [RÉGION], [DÉPARTEMENT], [FORM_URL], [URL], [TITRE] pour personnalisation par ville.";
            
            $result = AiService::callAI($prompt, $systemMessage, [
                'max_tokens' => 6000, // Augmenté pour permettre plus de contenu personnalisé
                'temperature' => 0.95 // Augmenté pour plus de créativité et personnalisation
            ]);
            
            // Si l'IA n'a pas répondu, FORCER une nouvelle tentative avec Groq directement
            if (!$result || !isset($result['content'])) {
                Log::warning('Première tentative IA échouée, tentative directe avec Groq', ['service_name' => $serviceName]);
                
                // Essayer directement avec Groq
                $groqApiKey = setting('groq_api_key', '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');
                if ($groqApiKey) {
                    try {
                        $groqResponse = \Illuminate\Support\Facades\Http::withToken($groqApiKey)
                            ->timeout(120)
                            ->post('https://api.groq.com/openai/v1/chat/completions', [
                                'model' => setting('groq_model', 'llama-3.1-8b-instant'),
                                'messages' => [
                                    ['role' => 'system', 'content' => $systemMessage],
                                    ['role' => 'user', 'content' => $prompt]
                                ],
                                'max_tokens' => 6000,
                                'temperature' => 0.95
                            ]);
                        
                        if ($groqResponse->successful() && isset($groqResponse->json()['choices'][0]['message']['content'])) {
                            $result = [
                                'content' => $groqResponse->json()['choices'][0]['message']['content'],
                                'provider' => 'groq-direct'
                            ];
                            Log::info('Génération réussie avec Groq direct', ['service_name' => $serviceName]);
                        }
                    } catch (\Exception $groqException) {
                        Log::error('Échec appel Groq direct', [
                            'service_name' => $serviceName,
                            'error' => $groqException->getMessage()
                        ]);
                    }
                }
                
                // Si toujours pas de résultat, essayer ChatGPT directement
                if (!$result || !isset($result['content'])) {
                    $chatgptApiKey = setting('chatgpt_api_key');
                    $chatgptEnabled = setting('chatgpt_enabled', true);
                    
                    if ($chatgptEnabled && $chatgptApiKey) {
                        Log::warning('Groq échoué, tentative directe avec ChatGPT', ['service_name' => $serviceName]);
                        try {
                            $chatgptResponse = \Illuminate\Support\Facades\Http::withToken($chatgptApiKey)
                                ->timeout(120)
                                ->post('https://api.openai.com/v1/chat/completions', [
                                    'model' => setting('chatgpt_model', 'gpt-4'),
                                    'messages' => [
                                        ['role' => 'system', 'content' => $systemMessage],
                                        ['role' => 'user', 'content' => $prompt]
                                    ],
                                    'max_tokens' => 6000,
                                    'temperature' => 0.95
                                ]);
                            
                            if ($chatgptResponse->successful() && isset($chatgptResponse->json()['choices'][0]['message']['content'])) {
                                $result = [
                                    'content' => $chatgptResponse->json()['choices'][0]['message']['content'],
                                    'provider' => 'chatgpt-direct'
                                ];
                                Log::info('Génération réussie avec ChatGPT direct', ['service_name' => $serviceName]);
                            }
                        } catch (\Exception $chatgptException) {
                            Log::error('Échec appel ChatGPT direct', [
                                'service_name' => $serviceName,
                                'error' => $chatgptException->getMessage()
                            ]);
                        }
                    }
                }
            }
            
            Log::info('Résultat appel AiService pour template', [
                'service_name' => $serviceName,
                'has_result' => !is_null($result),
                'has_content' => isset($result['content']),
                'provider' => $result['provider'] ?? 'none',
                'content_length' => isset($result['content']) ? strlen($result['content']) : 0
            ]);
            
            // FORCER l'utilisation de l'IA - NE JAMAIS retourner de fallback ici
            if (!$result || !isset($result['content'])) {
                Log::error('IMPOSSIBLE de générer du contenu via IA (ni AiService, ni Groq direct, ni ChatGPT direct)', [
                    'service_name' => $serviceName,
                    'chatgpt_enabled' => setting('chatgpt_enabled', true),
                    'chatgpt_api_key_exists' => !empty(setting('chatgpt_api_key')),
                    'groq_api_key_exists' => !empty(setting('groq_api_key'))
                ]);
                throw new \Exception("ERREUR CRITIQUE: Impossible de générer du contenu via IA. Vérifiez vos clés API ChatGPT ou Groq. Le système refuse d'utiliser un contenu générique.");
            }
            
            if ($result && isset($result['content'])) {
                Log::info('Réponse IA reçue pour template', [
                    'service_name' => $serviceName,
                    'provider' => $result['provider'],
                    'content_length' => strlen($result['content']),
                    'content_preview' => substr($result['content'], 0, 500)
                ]);
                
                // Parser le JSON de manière robuste (réutiliser la méthode de ServicesController)
                $aiData = $this->parseAIResponseForTemplate($result['content']);
                
                if ($aiData && is_array($aiData)) {
                    // Vérifier que le contenu n'est pas générique AVANT validation
                    $description = $aiData['description'] ?? '';
                    
                    // Détecter les phrases génériques interdites
                    $genericPhrases = [
                        'Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de',
                        'Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION]',
                        'Chaque intervention de',
                        'est réalisée selon les normes professionnelles les plus strictes',
                        'Spécialistes en travaux de',
                        'pour une qualité supérieure',
                        'Nous maîtrisons les techniques modernes garantissant des résultats durables',
                        'Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région',
                        'une expertise reconnue dans [RÉGION]',
                        'Pourquoi choisir ce service',
                        // Prestations génériques interdites
                        'Diagnostic et évaluation',
                        'Intervention d\'urgence',
                        'Maintenance préventive',
                        'Réparation spécialisée',
                        'Installation complète',
                        'Rénovation totale',
                        'Conseil personnalisé',
                        'Suivi post-intervention',
                        'Formation utilisateur',
                        'Garantie étendue',
                        // Phrases génériques financement interdites
                        'Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de',
                        'Nous vous accompagnons dans vos démarches',
                        'pour bénéficier des aides financières disponibles'
                    ];
                    
                    $hasGenericContent = false;
                    $genericPhraseFound = '';
                    foreach ($genericPhrases as $phrase) {
                        if (stripos($description, $phrase) !== false) {
                            $hasGenericContent = true;
                            $genericPhraseFound = $phrase;
                            break;
                        }
                    }
                    
                    // Vérifier si le contenu contient encore des placeholders "ÉCRIRE ICI"
                    $hasPlaceholders = stripos($description, 'ÉCRIRE ICI') !== false || 
                                      stripos($description, '[Paragraphe') !== false ||
                                      stripos($description, '[MENTIONNER') !== false;
                    
                    // Vérifier si le contenu est trop court ou trop générique
                    $plainText = strip_tags($description);
                    $isTooShort = strlen($plainText) < 1500; // Minimum 1500 caractères de texte réel
                    
                    // Vérifier si le nom du service est présent de manière significative
                    $serviceNameCount = substr_count(strtolower($plainText), strtolower($serviceName));
                    $serviceNamePresent = $serviceNameCount >= 5; // Le service doit être mentionné au moins 5 fois
                    
                    // Vérifier si le contenu de financement est présent (MaPrimeRénov, CEE, etc.)
                    $serviceLower = strtolower($serviceName);
                    $financementInfo = $this->getFinancementInfoForService($serviceName);
                    $hasFinancementContent = false;
                    if (strpos($serviceLower, 'isolation') !== false || 
                        strpos($serviceLower, 'thermique') !== false ||
                        strpos($serviceLower, 'isoler') !== false) {
                        // Pour l'isolation, vérifier la présence de MaPrimeRénov et CEE
                        $hasFinancementContent = (stripos($description, 'MaPrimeRénov') !== false || stripos($description, 'ma prime renov') !== false) &&
                                                  (stripos($description, 'CEE') !== false || stripos($description, 'Certificats d\'Économies') !== false);
                    } else {
                        // Pour les autres services, vérifier au moins MaPrimeRénov ou CEE
                        $hasFinancementContent = (stripos($description, 'MaPrimeRénov') !== false || stripos($description, 'ma prime renov') !== false || 
                                                  stripos($description, 'CEE') !== false || stripos($description, 'Certificats d\'Économies') !== false);
                    }
                    
                    if ($hasGenericContent || $hasPlaceholders || $isTooShort || !$serviceNamePresent || !$hasFinancementContent) {
                        Log::error('Contenu template REJETÉ - générique ou incomplet', [
                            'service_name' => $serviceName,
                            'has_generic_content' => $hasGenericContent,
                            'generic_phrase' => $genericPhraseFound,
                            'has_placeholders' => $hasPlaceholders,
                            'is_too_short' => $isTooShort,
                            'text_length' => strlen($plainText),
                            'service_name_present' => $serviceNamePresent,
                            'service_name_count' => $serviceNameCount,
                            'has_financement_content' => $hasFinancementContent,
                            'description_preview' => substr($plainText, 0, 300)
                        ]);
                        
                        // Essayer une deuxième génération avec un prompt encore plus strict
                        throw new \Exception("Le contenu généré est trop générique. Tentative de re-génération nécessaire.");
                    }
                    
                    Log::info('Contenu template validé - personnalisé et complet', [
                        'service_name' => $serviceName,
                        'text_length' => strlen($plainText),
                        'service_name_count' => $serviceNameCount
                    ]);
                    
                    // Valider et nettoyer les données
                    return $this->validateAndCleanAIDataForTemplate($aiData, $serviceName, $shortDescription);
                } else {
                    Log::warning('Échec parsing JSON de la réponse IA pour template', [
                        'service_name' => $serviceName,
                        'content_preview' => substr($result['content'], 0, 300)
                    ]);
                }
            } else {
                Log::error('Aucune réponse de l\'IA pour template', [
                    'service_name' => $serviceName,
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error('Erreur génération IA template: ' . $errorMessage, [
                'service_name' => $serviceName,
                'error' => $e->getTraceAsString()
            ]);
            
            // Si l'erreur indique un contenu générique, essayer une deuxième tentative
            if (stripos($errorMessage, 'générique') !== false && !isset($retryAttempt)) {
                Log::info('Tentative de re-génération avec prompt renforcé', ['service_name' => $serviceName]);
                
                try {
                    // Re-générer avec un prompt encore plus strict (utiliser basePrompt qui est dans le scope)
                    $retryPrompt = $basePrompt . "\n\n🚨🚨🚨 DEUXIÈME TENTATIVE - CONTENU GÉNÉRIQUE DÉTECTÉ 🚨🚨🚨\n\nL'IA a généré du contenu générique lors de la première tentative. TU DOIS ABSOLUMENT:\n- NE PAS utiliser les phrases suivantes qui ont été détectées comme génériques:\n  * \"Nous vous accompagnons dans vos démarches pour bénéficier des aides financières\"\n  * \"Nous garantissons la satisfaction totale de nos clients\"\n  * \"Chaque intervention est réalisée selon les normes professionnelles les plus strictes\"\n  * \"Spécialistes en travaux de {$serviceName} pour une qualité supérieure\"\n  * \"Notre expertise locale nous permet de comprendre les spécificités de votre région\"\n\n- UTILISER À LA PLACE:\n  * Des phrases UNIQUES et SPÉCIFIQUES à {$serviceName}\n  * Des détails techniques précis (normes, certifications, matériaux)\n  * Des chiffres concrets (performances, économies, délais)\n  * Des processus spécifiques au domaine de {$serviceName}\n\n- REMPLACER TOUS les \"ÉCRIRE ICI\" par du VRAI texte technique et détaillé\n- Le contenu DOIT faire minimum 2000 caractères de texte réel (sans HTML)\n- Le nom du service \"{$serviceName}\" DOIT apparaître au moins 10 fois dans le texte\n\nCRÉE DU CONTENU VRAIMENT ORIGINAL ET TECHNIQUE, PAS DE COPIÉ-COLLÉ GÉNÉRIQUE!";
                    
                    $result = AiService::callAI($retryPrompt, $systemMessage, [
                        'max_tokens' => 8000, // Encore plus pour forcer du contenu détaillé
                        'temperature' => 1.0 // Maximum créativité
                    ]);
                    
                    if ($result && isset($result['content'])) {
                        $aiData = $this->parseAIResponseForTemplate($result['content']);
                        
                        if ($aiData && is_array($aiData)) {
                            $description = $aiData['description'] ?? '';
                            $plainText = strip_tags($description);
                            
                            // Validation encore plus stricte pour la deuxième tentative
                            if (strlen($plainText) >= 2000 && substr_count(strtolower($plainText), strtolower($serviceName)) >= 10) {
                                Log::info('Re-génération réussie avec contenu personnalisé', ['service_name' => $serviceName]);
                                return $this->validateAndCleanAIDataForTemplate($aiData, $serviceName, $shortDescription);
                            }
                        }
                    }
                } catch (\Exception $retryException) {
                    Log::error('Échec re-génération template', [
                        'service_name' => $serviceName,
                        'error' => $retryException->getMessage()
                    ]);
                }
            }
        }
        
        // Fallback amélioré en cas d'échec
        Log::warning('Utilisation du contenu fallback pour template', ['service_name' => $serviceName]);
        return $this->generateFallbackTemplateContent([
            'name' => $serviceName,
            'slug' => Str::slug($serviceName),
            'short_description' => $shortDescription
        ]);
    }

    /**
     * Construire le prompt pour un template de service
     */
    private function buildTemplatePromptForService($serviceName, $shortDescription, $companyInfo, $aiPrompt = null)
    {
        // Déterminer les informations de financement selon le service
        $financementInfo = $this->getFinancementInfoForService($serviceName);
        
        $basePrompt = "Tu es un expert technique en {$serviceName} avec une connaissance PROFONDE des prestations, techniques et matériaux spécifiques à ce domaine. Crée un template d'annonce TOTALEMENT personnalisé pour {$serviceName}.

⚠️⚠️⚠️ SERVICE À PERSONNALISER: {$serviceName} ⚠️⚠️⚠️

IMPORTANT: Ce template sera utilisé pour créer des annonces personnalisées par ville. Utilise les placeholders suivants:
- [VILLE] = sera remplacé par le nom de la ville
- [RÉGION] = sera remplacé par le nom de la région
- [DÉPARTEMENT] = sera remplacé par le nom du département
- [FORM_URL] = URL du formulaire de devis
- [URL] = URL de l'annonce finale
- [TITRE] = Titre de l'annonce avec ville

🚫 INTERDICTIONS ABSOLUES:
- INTERDIT d'utiliser des prestations génériques comme 'Diagnostic', 'Conseil', 'Maintenance générale', 'Installation professionnelle'
- INTERDIT de copier du contenu générique applicable à tous les services
- INTERDIT d'utiliser un vocabulaire vague ou général

✅ OBLIGATIONS ABSOLUES POUR {$serviceName}:
- Chaque prestation DOIT être TECHNIQUE et SPÉCIFIQUE UNIQUEMENT à {$serviceName}
- Utilise le vocabulaire PROFESSIONNEL du métier de {$serviceName}
- Les prestations doivent mentionner des techniques, matériaux ou méthodes PRÉCISES liés à {$serviceName}
- Chaque description doit expliquer QUOI, COMMENT et POURQUOI spécifiquement pour {$serviceName}
- Utilise [VILLE] et [RÉGION] dans le contenu pour personnalisation future

📋 STRUCTURE HTML OBLIGATOIRE DU CHAMP \"description\":

⚠️⚠️⚠️ IMPORTANT: REMPLACEZ TOUS LES PLACEHOLDERS [Paragraphe X] PAR DU VRAI CONTENU PERSONNALISÉ ⚠️⚠️⚠️

Le champ \"description\" DOIT contenir un HTML COMPLET avec cette structure exacte:

<div class=\"grid md:grid-cols-2 gap-8\">
  <div class=\"space-y-6\">
    <!-- SECTION 1: DESCRIPTION LONGUE PERSONNALISÉE -->
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">ÉCRIRE ICI un paragraphe de 5-6 phrases sur {$serviceName} à [VILLE], mentionnant des techniques spécifiques, matériaux, normes (ex: RT 2012, DTU), certifications (ex: RGE, Qualit'ENR), et l'impact pour les clients locaux. NE PAS utiliser de texte générique.</p>
      <p class=\"text-lg leading-relaxed\">ÉCRIRE ICI un deuxième paragraphe avec des détails techniques précis sur {$serviceName}: types de matériaux utilisés (ex: laine de verre, polyuréthane, fibre de bois), méthodes de pose, épaisseurs, résistances thermiques, normes respectées. SOYEZ TRÈS SPÉCIFIQUE.</p>
      <p class=\"text-lg leading-relaxed\">ÉCRIRE ICI un troisième paragraphe expliquant pourquoi notre entreprise est experte en {$serviceName} dans [RÉGION]: nombre d'années d'expérience, nombre de chantiers réalisés, types de projets (particuliers, professionnels, collectivités), certifications obtenues, garanties proposées.</p>
    </div>
    
    <!-- SECTION 2: NOTRE ENGAGEMENT QUALITÉ (PERSONNALISÉ) -->
    <div class=\"bg-blue-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité pour {$serviceName}</h3>
      <p class=\"leading-relaxed mb-3\">ÉCRIRE ICI un paragraphe spécifique: pour {$serviceName}, nous garantissons [MENTIONNER DES GARANTIES SPÉCIFIQUES: ex: garantie décennale, garantie biennale, garantie de résultat d'économie d'énergie, etc.]. Nos équipes sont certifiées [MENTIONNER CERTIFICATIONS SPÉCIFIQUES au domaine {$serviceName}].</p>
      <p class=\"leading-relaxed mb-3\">ÉCRIRE ICI comment nous garantissons la qualité: pour chaque intervention de {$serviceName}, nous réalisons [MENTIONNER DES PROCESSUS SPÉCIFIQUES: ex: diagnostic thermique avant travaux, contrôle qualité en cours de chantier, test d'étanchéité à l'air, mesure de performance, etc.]. Un suivi post-intervention est assuré avec [PRÉCISER: ex: visite de contrôle, mesure de performance, assistance SAV].</p>
      <p class=\"leading-relaxed\">ÉCRIRE ICI notre engagement client: à [VILLE] et dans [RÉGION], nous nous engageons à [MENTIONNER DES ENGAGEMENTS SPÉCIFIQUES: ex: intervenir sous 48h en urgence, devis sous 24h, respect des délais annoncés, nettoyage complet après intervention, etc.].</p>
    </div>
    
    <!-- SECTION 3: 10 PRESTATIONS SPÉCIFIQUES -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$serviceName}</h3>
    <ul class=\"space-y-4\">
      <li class=\"flex items-start\">
        <i class=\"fas fa-check-circle text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <div>
          <strong class=\"text-gray-900 block mb-1\">[NOM PRESTATION 1 TECHNIQUE SPÉCIFIQUE À {$serviceName}]</strong>
          <p class=\"text-gray-700 text-sm\">[Description détaillée avec techniques, matériaux, méthodes précises. Minimum 2-3 phrases.]</p>
        </div>
      </li>
      <!-- RÉPÉTER pour les 10 prestations, chacune avec nom technique et description détaillée -->
    </ul>
    
    <!-- SECTION 4: FAQ PERSONNALISÉE (MINIMUM 6 QUESTIONS) -->
    <div class=\"bg-gray-50 p-6 rounded-lg mt-6\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-4\">Questions Fréquentes sur {$serviceName} à [VILLE]</h4>
      <div class=\"space-y-4\">
        <div>
          <p class=\"font-semibold text-gray-900 mb-2\"><strong>Q1:</strong> [Question spécifique sur {$serviceName} à [VILLE] - coûts, délais, ou processus technique]</p>
          <p class=\"text-gray-700 text-sm\"><strong>R:</strong> [Réponse détaillée et technique, incluant informations spécifiques sur {$serviceName}. Minimum 3-4 phrases.]</p>
        </div>
        <!-- MINIMUM 6 questions au total, toutes spécifiques à {$serviceName} -->
      </div>
    </div>
  </div>
  
  <div class=\"space-y-6\">
    <!-- SECTION 5: POURQUOI CHOISIR CE SERVICE -->
    <div class=\"bg-green-50 p-6 rounded-lg border-l-4 border-green-600\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir {$serviceName} avec Notre Entreprise</h3>
      <p class=\"leading-relaxed mb-3\">ÉCRIRE ICI les avantages concrets de notre approche pour {$serviceName}: [MENTIONNER 3-4 AVANTAGES SPÉCIFIQUES comme: matériaux premium sélectionnés, techniques avancées utilisées, certifications détenues, prix compétitifs, garanties étendues, etc.]. Chaque avantage doit être spécifique à {$serviceName}, pas générique.</p>
      <p class=\"leading-relaxed\">ÉCRIRE ICI notre expertise locale: nous connaissons parfaitement les spécificités climatiques de [RÉGION] (ex: humidité, gel, vent, exposition solaire) et nous adaptons nos solutions de {$serviceName} en conséquence. Notre présence locale à [VILLE] nous permet de [MENTIONNER AVANTAGES LOCAUX: ex: intervenir rapidement, connaître les réglementations locales, travailler avec des artisans locaux, etc.].</p>
    </div>
    
    <!-- SECTION 6: NOTRE EXPERTISE LOCALE -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale en {$serviceName}</h3>
    <p class=\"leading-relaxed mb-4\">ÉCRIRE ICI notre expérience: depuis [NOMBRE] années, nous intervenons sur [VILLE] et dans [RÉGION] pour des projets de {$serviceName}. Nous avons réalisé [MENTIONNER TYPES DE PROJETS: ex: plus de 200 chantiers d'isolation de combles, 150 rénovations de toiture, etc.]. Notre connaissance des spécificités régionales nous permet de proposer des solutions adaptées.</p>
    <p class=\"leading-relaxed\">ÉCRIRE ICI des exemples concrets: nous avons notamment [MENTIONNER 2-3 EXEMPLES CONCRETS de réalisations en {$serviceName} dans [RÉGION], avec détails techniques si possible]. Cette expérience locale nous permet de comprendre les besoins spécifiques des habitants de [VILLE] et de [RÉGION] en matière de {$serviceName}.</p>
    
    <!-- SECTION 7: FINANCEMENT ET AIDES (PERSONNALISÉ SELON SERVICE) -->
    <div class=\"bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Financement et Aides pour {$serviceName}</h4>
      <p class=\"leading-relaxed mb-3\">ÉCRIRE ICI un paragraphe d'introduction personnalisé pour {$serviceName}: mentionner pourquoi les aides sont importantes pour ce type de travaux (économies d'énergie, rénovation, amélioration de l'habitat). SOYEZ SPÉCIFIQUE à {$serviceName}, pas générique.</p>
      
      <!-- INFORMATIONS DE FINANCEMENT SPÉCIFIQUES À {$serviceName} - COPIER CE CONTENU TEL QUEL: -->
      {$financementInfo}
      
      <p class=\"leading-relaxed mt-3\">ÉCRIRE ICI un paragraphe de conclusion CONCRET: expliquer COMMENT notre équipe aide les clients (ex: montage de dossiers MaPrimeRénov, simulation CEE, accompagnement éco-PTZ, etc.). Mentionner des actions concrètes comme \"Nous remplissons votre dossier MaPrimeRénov\", \"Nous calculons votre éligibilité CEE\", etc.</p>
    </div>
    
    <!-- SECTION 8: BESOIN D'UN DEVIS -->
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis pour {$serviceName} à [VILLE]?</h4>
      <p class=\"mb-4 text-gray-700\">Contactez-nous dès aujourd'hui pour obtenir un devis gratuit et personnalisé pour vos travaux de {$serviceName} à [VILLE].</p>
      <a href=\"[FORM_URL]\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">
        <i class=\"fas fa-calculator mr-2\"></i>Demander un Devis Gratuit
      </a>
    </div>
    
    <!-- SECTION 9: INFORMATIONS PRATIQUES -->
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\">
          <i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i>
          <span>Devis gratuit et sans engagement pour {$serviceName}</span>
        </li>
        <li class=\"flex items-center\">
          <i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i>
          <span>Intervention rapide sur [VILLE] et [RÉGION]</span>
        </li>
        <li class=\"flex items-center\">
          <i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i>
          <span>Garantie sur tous nos travaux de {$serviceName}</span>
        </li>
        <li class=\"flex items-center\">
          <i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i>
          <span>Équipe d'experts certifiés en {$serviceName}</span>
        </li>
      </ul>
    </div>
  </div>
</div>

GÉNÈRE UN JSON AVEC CES CHAMPS:

{
  \"description\": \"[HTML complet suivant la structure ci-dessus, avec TOUTES les sections remplies et PERSONNALISÉES pour {$serviceName}]\",
  \"short_description\": \"Service professionnel de {$serviceName} à [VILLE] - Devis gratuit et intervention rapide\",
  \"long_description\": \"[Description longue de 4-5 phrases sur {$serviceName}, expliquant l'expertise, les techniques, les matériaux, et pourquoi choisir notre entreprise pour {$serviceName} à [VILLE] et dans la région de [RÉGION]. SOYEZ SPÉCIFIQUE et DÉTAILLÉ, minimum 400 mots.]\",
  \"icon\": \"fas fa-tools\",
  \"meta_title\": \"{$serviceName} à [VILLE] - Expert professionnel | Devis gratuit\",
  \"meta_description\": \"Service professionnel de {$serviceName} à [VILLE] et dans toute la région de [RÉGION]. Devis gratuit, intervention rapide, équipe experte certifiée. Garantie sur tous nos travaux.\",
  \"og_title\": \"{$serviceName} à [VILLE] - Expert professionnel\",
  \"og_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"twitter_title\": \"{$serviceName} à [VILLE] - Expert professionnel\",
  \"twitter_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"meta_keywords\": \"{$serviceName}, [VILLE], [RÉGION], expert {$serviceName}, devis gratuit {$serviceName}, professionnel {$serviceName}\"
}

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - CONTENU PERSONNALISÉ ⚠️⚠️⚠️:

🔴 INTERDICTIONS STRICTES:
- INTERDIT de copier les placeholders [Paragraphe X] - TU DOIS LES REMPLACER PAR DU VRAI TEXTE
- INTERDIT d'utiliser des phrases génériques comme \"Nous garantissons la satisfaction\", \"Notre expertise locale\", \"Intervention rapide\" sans détails concrets
- INTERDIT d'utiliser \"Spécialistes en travaux de {$serviceName}\" ou phrases similaires génériques
- INTERDIT d'écrire \"Nous vous accompagnons dans vos démarches\" sans expliquer COMMENT concrètement

✅ OBLIGATIONS:
1. REMPLACER TOUS les \"[Paragraphe X]\" et \"ÉCRIRE ICI\" par du VRAI contenu écrit
2. Pour chaque section, écrire 3-4 phrases MINIMUM avec des détails CONCRETS
3. Mentionner des chiffres, techniques, matériaux, normes, certifications SPÉCIFIQUES à {$serviceName}
4. Utiliser un vocabulaire PROFESSIONNEL du métier de {$serviceName}
5. Donner des EXEMPLES CONCRETS (types de projets, techniques utilisées, matériaux)

📝 EXEMPLE POUR \"Isolation thermique\":

❌ MAUVAIS (GÉNÉRIQUE):
\"Nous garantissons la satisfaction totale de nos clients. Nous réalisons des travaux d'isolation selon les normes.\"

✅ BON (PERSONNALISÉ):
\"Pour l'isolation thermique, nous garantissons une performance énergétique conforme aux exigences RT 2012, avec une résistance thermique minimale R = 7 m².K/W pour les combles perdus. Nos équipes certifiées RGE Qualit'ENR utilisent exclusivement des isolants certifiés ACERMI (laine de verre, laine de roche, ouate de cellulose) adaptés aux spécificités climatiques de [RÉGION]. Chaque chantier fait l'objet d'un diagnostic thermique complet avant travaux, puis d'un contrôle qualité avec mesure de l'étanchéité à l'air selon la norme NF EN 13829, garantissant jusqu'à 30% d'économies d'énergie.\"

📋 INSTRUCTIONS TECHNIQUES JSON:
- TU DOIS RÉPONDRE UNIQUEMENT AVEC UN JSON VALIDE
- COMMENCE DIRECTEMENT PAR { (accolade ouvrante)
- TERMINE DIRECTEMENT PAR } (accolade fermante)
- PAS de texte avant le JSON
- PAS de texte après le JSON
- PAS de ```json ou ``` autour du JSON
- GÉNÈRE EXACTEMENT 10 PRESTATIONS TECHNIQUES SPÉCIFIQUES avec descriptions détaillées (minimum 2 phrases par prestation)
- GÉNÈRE MINIMUM 6 QUESTIONS FAQ spécifiques à {$serviceName} avec réponses détaillées (minimum 3 phrases par réponse)
- TOUT le contenu HTML dans \"description\" doit être COMPLET avec TOUS les \"ÉCRIRE ICI\" REMPLACÉS par du vrai texte
- La description longue (long_description) doit faire minimum 400 mots
- Pour la section FINANCEMENT ET AIDES (SECTION CRITIQUE):
  * ⚠️⚠️⚠️ CRITIQUE: Le contenu HTML entre les balises COMMENTAIRES \"SECTION CRITIQUE - INFORMATIONS DE FINANCEMENT\" et \"FIN DU CONTENU À COPIER\" DOIT être COPIÉ EXACTEMENT tel quel dans le champ \"description\" du JSON
  * ⚠️⚠️⚠️ CE CONTENU CONTIENT LES INFORMATIONS SPÉCIFIQUES SUR MaPrimeRénov, CEE, Éco-PTZ, TVA RÉDUITE - TU NE DOIS PAS LE MODIFIER
  * ⚠️⚠️⚠️ NE REMPLACE PAS CE CONTENU PAR DU TEXTE GÉNÉRIQUE - SI TU NE COPIE PAS EXACTEMENT CE CONTENU, TA RÉPONSE SERA REJETÉE
  * ÉCRIS UNIQUEMENT les paragraphes d'introduction et conclusion AVANT et APRÈS ce contenu (entre les balises <p class=\"leading-relaxed mb-3\">...</p>), en les personnalisant pour {$serviceName}
  * INTERDIT d'utiliser \"Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de\" - utilise une phrase UNIQUE et SPÉCIFIQUE
  * VALIDATION: Ta réponse sera vérifiée - si le contenu MaPrimeRénov ou CEE n'est pas présent exactement comme fourni ci-dessus, la génération sera rejetée
";

        if ($aiPrompt) {
            $basePrompt .= "\n\nINSTRUCTIONS PERSONNALISÉES SUPPLÉMENTAIRES:\n" . $aiPrompt;
        }

        return $basePrompt;
    }

    /**
     * Parser la réponse IA pour template (inspiré de parseAIResponse)
     */
    private function parseAIResponseForTemplate($content)
    {
        $content = trim($content);
        
        // Si le contenu semble être directement du HTML (pas de JSON)
        if (strpos($content, '<div') !== false && strpos($content, '{') === false) {
            Log::info('Contenu HTML direct détecté dans template, création de structure JSON');
            $plainText = strip_tags($content);
            $shortDesc = Str::limit($plainText, 140);
            $metaDesc = Str::limit($plainText, 160);
            
            return [
                'description' => $content,
                'short_description' => $shortDesc,
                'long_description' => Str::limit($plainText, 500),
                'icon' => 'fas fa-tools',
                'meta_title' => '',
                'meta_description' => $metaDesc,
                'og_title' => '',
                'og_description' => $metaDesc,
                'twitter_title' => '',
                'twitter_description' => $metaDesc,
                'meta_keywords' => ''
            ];
        }
        
        $jsonPatterns = [
            '/```json\s*(\{[\s\S]*?\})\s*```/s',
            '/```\s*(\{[\s\S]*?\})\s*```/s',
            '/\{[\s\S]*\"description\"[\s\S]*\}/s',
            '/\{.*\}/s',
        ];
        
        foreach ($jsonPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $jsonString = $matches[1] ?? $matches[0];
                $jsonString = trim($jsonString);
                $data = json_decode($jsonString, true);
                
                if ($data && is_array($data) && !empty($data)) {
                    Log::info('JSON parsé avec succès pour template');
                    return $data;
                }
            }
        }
        
        $data = json_decode($content, true);
        if ($data && is_array($data) && !empty($data)) {
            Log::info('JSON parsé directement pour template');
            return $data;
        }
        
        Log::warning('Impossible de parser la réponse IA pour template', [
            'content_preview' => substr($content, 0, 500)
        ]);
        
        return null;
    }

    /**
     * Valider et nettoyer les données IA pour template
     */
    private function validateAndCleanAIDataForTemplate($aiData, $serviceName, $shortDescription)
    {
        $description = $aiData['description'] ?? '';
        $cleanText = function($text, $maxLength = null) {
            $text = strip_tags($text);
            $text = trim($text);
            return $maxLength ? Str::limit($text, $maxLength) : $text;
        };
        
        return [
            'description' => $description,
            'short_description' => $cleanText($aiData['short_description'] ?? $shortDescription, 140),
            'long_description' => $cleanText($aiData['long_description'] ?? strip_tags($description), 500),
            'icon' => $aiData['icon'] ?? 'fas fa-tools',
            'meta_title' => $cleanText($aiData['meta_title'] ?? ($serviceName . ' à [VILLE] - Service professionnel'), 160),
            'meta_description' => $cleanText($aiData['meta_description'] ?? 'Service professionnel à [VILLE]', 500),
            'meta_keywords' => $aiData['meta_keywords'] ?? ($serviceName . ', [VILLE], [RÉGION], service professionnel'),
            'og_title' => $cleanText($aiData['og_title'] ?? ($serviceName . ' à [VILLE] - Service professionnel'), 160),
            'og_description' => $cleanText($aiData['og_description'] ?? 'Service professionnel à [VILLE]', 500),
            'twitter_title' => $cleanText($aiData['twitter_title'] ?? ($serviceName . ' à [VILLE] - Service professionnel'), 160),
            'twitter_description' => $cleanText($aiData['twitter_description'] ?? 'Service professionnel à [VILLE]', 500),
        ];
    }

    /**
     * Obtenir les informations de financement selon le type de service
     */
    private function getFinancementInfoForService($serviceName)
    {
        $serviceLower = strtolower($serviceName);
        
        // Isolation thermique - MaPrimeRénov, CEE, etc.
        if (strpos($serviceLower, 'isolation') !== false || 
            strpos($serviceLower, 'thermique') !== false ||
            strpos($serviceLower, 'isoler') !== false) {
            return "
      <p class=\"leading-relaxed mb-3\"><strong>MaPrimeRénov :</strong> Pour vos travaux d'isolation thermique, MaPrimeRénov est l'aide principale de l'État. Elle peut couvrir jusqu'à 90% du montant de vos travaux selon vos revenus (jusqu'à 75€/m² pour l'isolation des combles perdus, 100€/m² pour l'isolation des murs, 120€/m² pour l'isolation des planchers bas). Cette aide est versée directement à l'entreprise RGE et est cumulable avec les primes CEE et l'éco-PTZ.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Primes CEE (Certificats d'Économies d'Énergie) :</strong> Les primes CEE pour l'isolation peuvent atteindre 50€/m² pour les combles, 25€/m² pour les murs, selon le niveau d'isolation et votre fournisseur d'énergie (EDF, Engie, Total Direct Énergie, etc.). Ces primes sont versées directement par les fournisseurs et sont cumulables avec MaPrimeRénov, permettant de réduire significativement le coût de vos travaux d'isolation.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Éco-PTZ (Éco-prêt à taux zéro) :</strong> L'éco-prêt à taux zéro permet de financer jusqu'à 30 000€ de travaux d'isolation sans intérêts, sur 20 ans maximum. Accessible sans conditions de ressources, il finance jusqu'à 75€/m² d'isolation de combles ou 100€/m² d'isolation de murs. Il est cumulable avec MaPrimeRénov et les primes CEE.</p>
      <p class=\"leading-relaxed\"><strong>TVA réduite à 5,5% :</strong> Tous les travaux d'isolation thermique bénéficient de la TVA réduite à 5,5% au lieu de 20% pour les logements de plus de 2 ans. Cette réduction s'applique à la fois sur la main d'œuvre et les matériaux, représentant une économie importante sur votre facture globale d'isolation.</p>";
        }
        
        // Rénovation énergétique
        if (strpos($serviceLower, 'rénovation') !== false && 
            (strpos($serviceLower, 'énergét') !== false || strpos($serviceLower, 'energet') !== false)) {
            return "<p class=\"leading-relaxed mb-3\"><strong>MaPrimeRénov :</strong> Aide principale pour la rénovation énergétique, pouvant atteindre jusqu'à 90% du montant des travaux selon vos revenus.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Prime CEE :</strong> Complémentaire à MaPrimeRénov, les Certificats d'Économies d'Énergie offrent des primes additionnelles pour améliorer l'efficacité énergétique.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Éco-PTZ :</strong> Prêt à taux zéro jusqu'à 50 000€ pour financer votre rénovation énergétique globale.</p>
      <p class=\"leading-relaxed\"><strong>Chèque Énergie :</strong> Pour les ménages modestes, le chèque énergie peut être utilisé pour financer une partie des travaux.</p>";
        }
        
        // Chauffage / PAC
        if (strpos($serviceLower, 'chauffage') !== false || 
            strpos($serviceLower, 'pompe à chaleur') !== false ||
            strpos($serviceLower, 'pac') !== false) {
            return "<p class=\"leading-relaxed mb-3\"><strong>MaPrimeRénov :</strong> Installation de pompes à chaleur éligible à MaPrimeRénov, jusqu'à 11 000€ selon vos revenus.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Prime CEE :</strong> Prime additionnelle pour l'installation de systèmes de chauffage performants, cumulable avec MaPrimeRénov.</p>
      <p class=\"leading-relaxed mb-3\"><strong>TVA réduite :</strong> TVA à 5,5% pour l'installation d'équipements de chauffage dans les logements anciens.</p>
      <p class=\"leading-relaxed\"><strong>Éco-PTZ :</strong> Financement jusqu'à 30 000€ sans intérêts pour remplacer votre système de chauffage.</p>";
        }
        
        // Toiture / Couverture
        if (strpos($serviceLower, 'toiture') !== false || 
            strpos($serviceLower, 'couverture') !== false ||
            strpos($serviceLower, 'charpente') !== false) {
            return "<p class=\"leading-relaxed mb-3\"><strong>MaPrimeRénov :</strong> Rénovation de toiture éligible si couplée avec des travaux d'isolation, jusqu'à 75€/m² pour l'isolation des combles.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Prime CEE :</strong> Aide disponible pour l'isolation de toiture lors d'une rénovation complète.</p>
      <p class=\"leading-relaxed mb-3\"><strong>TVA réduite :</strong> TVA à 5,5% pour les travaux de rénovation de toiture dans les logements de plus de 2 ans.</p>
      <p class=\"leading-relaxed\"><strong>Crédit d'impôt :</strong> Certains travaux de toiture peuvent bénéficier d'un crédit d'impôt sous certaines conditions.</p>";
        }
        
        // Fenêtres / Menuiserie
        if (strpos($serviceLower, 'fenêtre') !== false || 
            strpos($serviceLower, 'menuiserie') !== false ||
            strpos($serviceLower, 'vitrage') !== false) {
            return "<p class=\"leading-relaxed mb-3\"><strong>MaPrimeRénov :</strong> Remplacement de fenêtres par des modèles performants éligible à MaPrimeRénov, jusqu'à 75€ par menuiserie.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Prime CEE :</strong> Aide supplémentaire pour l'installation de fenêtres double vitrage performantes.</p>
      <p class=\"leading-relaxed mb-3\"><strong>TVA réduite :</strong> TVA à 5,5% pour le remplacement de menuiseries dans les logements anciens.</p>
      <p class=\"leading-relaxed\"><strong>Crédit d'impôt :</strong> Possibilité de crédit d'impôt pour l'installation de fenêtres à haute performance énergétique.</p>";
        }
        
        // Par défaut - financement général (mais toujours avec détails)
        return "
      <p class=\"leading-relaxed mb-3\"><strong>MaPrimeRénov :</strong> Selon votre projet de {$serviceName} et vos revenus, vous pouvez bénéficier de MaPrimeRénov. Cette aide de l'État peut couvrir une partie significative de vos travaux. Notre équipe vous aide à monter votre dossier et vérifier votre éligibilité selon le type de travaux de {$serviceName} que vous souhaitez réaliser.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Primes CEE (Certificats d'Économies d'Énergie) :</strong> Les primes CEE sont versées par les fournisseurs d'énergie pour inciter à la rénovation énergétique. Pour vos travaux de {$serviceName}, nous vérifions votre éligibilité et calculons le montant de la prime que vous pouvez obtenir. Ces primes sont cumulables avec MaPrimeRénov.</p>
      <p class=\"leading-relaxed mb-3\"><strong>Éco-PTZ (Éco-prêt à taux zéro) :</strong> Pour financer vos travaux de {$serviceName}, l'éco-prêt à taux zéro permet d'emprunter jusqu'à 50 000€ sans intérêts sur 20 ans. Accessible sans conditions de ressources, il peut couvrir une partie importante de votre projet.</p>
      <p class=\"leading-relaxed\"><strong>TVA réduite à 5,5% :</strong> Pour vos travaux de {$serviceName} dans un logement de plus de 2 ans, vous bénéficiez de la TVA réduite à 5,5% au lieu de 20%, ce qui représente une économie significative sur l'ensemble de votre projet.</p>";
    }
}