<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServicesController extends Controller
{
    /**
     * Afficher la liste des services (admin)
     */
    public function index()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        return view('admin.services.index', compact('services'));
    }

    /**
     * Afficher tous les services (page publique)
     */
    public function publicIndex()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        // Filtrer les services visibles
        $visibleServices = collect($services)->filter(function($service) {
            return isset($service['is_visible']) ? $service['is_visible'] : true;
        });
        
        return view('services.index', compact('visibleServices'));
    }

    /**
     * Afficher le formulaire de création de service
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Enregistrer un nouveau service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'nullable|boolean',
            'is_menu' => 'nullable|boolean',
            'ai_prompt' => 'nullable|string|max:2000',
        ]);

        // Générer le slug
        $slug = Str::slug($validated['name']);
        
        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Générer automatiquement TOUT le contenu avec l'IA
        $aiContent = $this->generateCompleteServiceContent(
            $validated['name'], 
            $validated['short_description'],
            $companyInfo,
            $validated['ai_prompt'] ?? null
        );

        // Gérer l'upload d'image de mise en avant
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'), 'featured');
        }

        // Créer le service avec TOUT le contenu généré par l'IA
        $service = [
            'id' => time() . '_' . rand(1000, 9999),
            'name' => $validated['name'],
            'slug' => $slug,
            'short_description' => $aiContent['short_description'],
            'description' => $aiContent['description'],
            'icon' => $aiContent['icon'],
            'featured_image' => $featuredImagePath,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_menu' => $validated['is_menu'] ?? true,
            'is_visible' => true,
            'meta_title' => $aiContent['meta_title'],
            'meta_description' => $aiContent['meta_description'],
            'meta_keywords' => $aiContent['meta_keywords'],
            'og_title' => $aiContent['og_title'],
            'og_description' => $aiContent['og_description'],
            'og_image' => $featuredImagePath, // Utilise la même image par défaut
            'twitter_title' => $aiContent['twitter_title'],
            'twitter_description' => $aiContent['twitter_description'],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Sauvegarder dans les settings
        $this->saveService($service);

        return redirect()->route('services.admin.index')
            ->with('success', 'Service créé avec succès avec contenu généré par l\'IA');
    }

    /**
     * Afficher le formulaire d'édition d'un service
     */
    public function edit($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('id', $id);
        
        if (!$service) {
            return redirect()->route('services.admin.index')
                ->with('error', 'Service non trouvé');
        }
        
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Mettre à jour un service
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'nullable|boolean',
            'is_menu' => 'nullable|boolean',
            'ai_prompt' => 'nullable|string|max:2000',
        ]);

        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Générer automatiquement TOUT le contenu avec l'IA
        $aiContent = $this->generateCompleteServiceContent(
            $validated['name'], 
            $validated['short_description'],
            $companyInfo,
            $validated['ai_prompt'] ?? null
        );

        // Gérer l'upload d'image de mise en avant
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'), 'featured');
        }

        // Mettre à jour le service
        $this->updateService($id, [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'short_description' => $aiContent['short_description'],
            'description' => $aiContent['description'],
            'icon' => $aiContent['icon'],
            'featured_image' => $featuredImagePath ?: $this->getServiceImage($id, 'featured_image'),
            'is_featured' => $validated['is_featured'] ?? false,
            'is_menu' => $validated['is_menu'] ?? true,
            'meta_title' => $aiContent['meta_title'],
            'meta_description' => $aiContent['meta_description'],
            'meta_keywords' => $aiContent['meta_keywords'],
            'og_title' => $aiContent['og_title'],
            'og_description' => $aiContent['og_description'],
            'og_image' => $featuredImagePath ?: $this->getServiceImage($id, 'og_image'),
            'twitter_title' => $aiContent['twitter_title'],
            'twitter_description' => $aiContent['twitter_description'],
            'updated_at' => now()->toISOString(),
        ]);

        return redirect()->route('services.admin.index')
            ->with('success', 'Service mis à jour avec succès avec contenu généré par l\'IA');
    }

    /**
     * Afficher un service (page publique)
     */
    public function show($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('slug', $slug);
        
        if (!$service) {
            abort(404);
        }
        
        // Récupérer les services liés (autres services)
        $relatedServices = collect($services)->filter(function($s) use ($service) {
            return $s['id'] !== $service['id'] && (isset($s['is_visible']) ? $s['is_visible'] : true);
        })->take(3);
        
        return view('services.show', compact('service', 'relatedServices'));
    }

    /**
     * Supprimer un service
     */
    public function destroy($id)
    {
        $this->deleteService($id);
        
        return redirect()->route('services.admin.index')
            ->with('success', 'Service supprimé avec succès');
    }

    /**
     * Régénérer le contenu d'un service avec l'IA
     */
    public function regenerate(Request $request, $id)
    {
        try {
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            $service = collect($services)->firstWhere('id', $id);
            
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service non trouvé'
                ], 404);
            }

            // Récupérer les informations de l'entreprise
            $companyInfo = $this->getCompanyInfo();
            
            // Générer automatiquement TOUT le contenu avec l'IA
            $aiContent = $this->generateCompleteServiceContent(
                $service['name'], 
                $service['short_description'],
                $companyInfo,
                $request->input('ai_prompt')
            );

            // Mettre à jour le service avec le nouveau contenu
            $this->updateService($id, [
                'short_description' => $aiContent['short_description'],
                'description' => $aiContent['description'],
                'icon' => $aiContent['icon'],
                'meta_title' => $aiContent['meta_title'],
                'meta_description' => $aiContent['meta_description'],
                'meta_keywords' => $aiContent['meta_keywords'],
                'og_title' => $aiContent['og_title'],
                'og_description' => $aiContent['og_description'],
                'twitter_title' => $aiContent['twitter_title'],
                'twitter_description' => $aiContent['twitter_description'],
                'updated_at' => now()->toISOString(),
            ]);

            // Si c'est une requête AJAX, retourner du JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contenu régénéré avec succès par l\'IA',
                    'content' => $aiContent
                ]);
            }
            
            // Sinon, rediriger avec un message de succès
            return redirect()->route('services.admin.index')
                ->with('success', 'Contenu régénéré avec succès par l\'IA');

        } catch (\Exception $e) {
            Log::error('Erreur régénération service: ' . $e->getMessage());
            
            // Si c'est une requête AJAX, retourner du JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la régénération: ' . $e->getMessage()
                ], 500);
            }
            
            // Sinon, rediriger avec un message d'erreur
            return redirect()->route('services.admin.index')
                ->with('error', 'Erreur lors de la régénération: ' . $e->getMessage());
        }
    }

    /**
     * Générer TOUT le contenu du service avec l'IA
     */
    private function generateCompleteServiceContent($serviceName, $shortDescription, $companyInfo, $customPrompt = null)
    {
        // Récupérer la clé API depuis la base de données
        $apiKey = setting('chatgpt_api_key');
        
        // Si pas trouvée, essayer directement en base
        if (!$apiKey) {
            $setting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
            $apiKey = $setting ? $setting->value : null;
        }
        
        if (!$apiKey) {
            Log::error('Clé API manquante pour génération service');
            return $this->generateFallbackContent($serviceName, $shortDescription, $companyInfo);
        }
        
        try {
            // Prompt avec structure spécifique demandée
            $prompt = "Crée un contenu HTML professionnel pour ce service de rénovation.

INFORMATIONS:
- Entreprise: {$companyInfo['company_name']}
- Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
- Service: {$serviceName}
- Description: {$shortDescription}";

            if ($customPrompt) {
                $prompt .= "\n\nINSTRUCTIONS PERSONNALISÉES:\n{$customPrompt}";
            }

            $prompt .= "\n\nSTRUCTURE HTML OBLIGATOIRE - EXACTEMENT COMME CET EXEMPLE:
<div class=\"grid md:grid-cols-2 gap-8\">
  <div class=\"space-y-6\">
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">[Introduction personnalisée pour {$serviceName} à {$companyInfo['company_city']}, {$companyInfo['company_region']}]</p>
      <p class=\"text-lg leading-relaxed\">[Expertise spécifique au service {$serviceName}]</p>
      <p class=\"text-lg leading-relaxed\">[Approche personnalisée et satisfaction client]</p>
    </div>
    
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité</h3>
      <p class=\"leading-relaxed mb-3\">Chez {$companyInfo['company_name']}, nous garantissons la satisfaction totale.</p>
      <p class=\"leading-relaxed\">[Matériaux et techniques spécifiques à {$serviceName}]</p>
    </div>
    
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$serviceName}</h3>
    <ul class=\"space-y-3\">
      <li class=\"flex items-start\"><span><strong>[Prestation 1 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 2 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 3 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 4 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 5 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 6 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 7 spécifique à {$serviceName}]</strong></span></li>
      <li class=\"flex items-start\"><span><strong>[Prestation 8 spécifique à {$serviceName}]</strong></span></li>
    </ul>
    
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir Notre Entreprise</h3>
      <p class=\"leading-relaxed\">[Réputation locale pour {$serviceName} à {$companyInfo['company_city']}]</p>
    </div>
  </div>
  
  <div class=\"space-y-6\">
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">[Expertise locale pour {$serviceName} en {$companyInfo['company_region']}]</p>
    
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis ?</h4>
      <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour vos {$serviceName}.</p>
      <a href=\"https://www.jd-renovation-service.fr/form/propertyType\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
    </div>
    
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\"><span>[Info pratique 1 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 2 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 3 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 4 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 5 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><span>[Info pratique 6 pour {$serviceName}]</span></li>
      </ul>
    </div>
  </div>
</div>

INSTRUCTIONS DÉTAILLÉES:
1. ADAPTE complètement le contenu au service spécifique: {$serviceName}
2. ÉCRIS du contenu PERSONNALISÉ selon le type de service (toiture, façade, isolation, gouttières, etc.)
3. UTILISE les informations de l'entreprise: {$companyInfo['company_name']}
4. INTÉGRE la localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
5. GARDE la structure HTML exacte de l'exemple ci-dessus
6. PERSONNALISE les prestations selon le service (pas de contenu générique)
7. ÉCRIS du contenu UNIQUE et SPÉCIFIQUE au service
8. ADAPTE le vocabulaire et les formulations selon le service
9. INCLUE des informations sur le financement, les garanties, les délais
10. VARIE le contenu pour éviter les répétitions

FORMAT JSON:
{
  \"description\": \"[HTML complet avec la structure exacte ci-dessus]\",
  \"short_description\": \"[Description courte et accrocheuse - 140 caractères max]\",
  \"icon\": \"fas fa-[icône appropriée au service]\",
  \"meta_title\": \"[Titre SEO optimisé - 60 caractères max]\",
  \"meta_description\": \"[Description SEO engageante - 160 caractères max]\",
  \"og_title\": \"[Titre pour réseaux sociaux - 60 caractères max]\",
  \"og_description\": \"[Description pour réseaux sociaux - 160 caractères max]\",
  \"twitter_title\": \"[Titre Twitter - 60 caractères max]\",
  \"twitter_description\": \"[Description Twitter - 160 caractères max]\",
  \"meta_keywords\": \"[Mots-clés pertinents séparés par virgules]\"
}

IMPORTANT:
- SUIVEZ EXACTEMENT la structure HTML de l'exemple
- ÉCRIVEZ du contenu PERSONNALISÉ pour le service {$serviceName}
- ADAPTEZ les prestations selon le type de service (toiture, façade, isolation, etc.)
- GARDEZ les classes CSS et la structure
- UTILISEZ les informations de l'entreprise et de la localisation
- Le contenu doit être professionnel et engageant
- ÉVITEZ la répétition de phrases identiques
- Variez le vocabulaire et les formulations
- INCLUEZ des informations sur le financement et les garanties
- ADAPTEZ le contenu selon le service spécifique

Réponds UNIQUEMENT avec le JSON valide, sans texte avant ou après.";

            Log::info('Génération IA complète pour service', [
                'service_name' => $serviceName,
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
                
                Log::info('Réponse IA complète reçue', [
                    'service_name' => $serviceName,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 300)
                ]);
                
                // Parser le JSON
                $jsonStart = strpos($content, '{');
                $jsonEnd = strrpos($content, '}');
                
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                    $aiData = json_decode($jsonContent, true);
                    
                    if ($aiData) {
                        return [
                            'description' => $aiData['description'] ?? $shortDescription,
                            'short_description' => $aiData['short_description'] ?? $shortDescription,
                            'icon' => $aiData['icon'] ?? 'fas fa-tools',
                            'meta_title' => $aiData['meta_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'],
                            'meta_description' => $aiData['meta_description'] ?? $shortDescription,
                            'og_title' => $aiData['og_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'],
                            'og_description' => $aiData['og_description'] ?? $shortDescription,
                            'twitter_title' => $aiData['twitter_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'],
                            'twitter_description' => $aiData['twitter_description'] ?? $shortDescription,
                            'meta_keywords' => $aiData['meta_keywords'] ?? $serviceName . ', ' . $companyInfo['company_city']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération IA complète: ' . $e->getMessage());
        }
        
        // Fallback en cas d'échec
        return $this->generateFallbackContent($serviceName, $shortDescription, $companyInfo);
    }

    /**
     * Contenu de fallback en cas d'échec de l'IA
     */
    private function generateFallbackContent($serviceName, $shortDescription, $companyInfo)
    {
        return [
            'description' => '<div class="space-y-6"><p class="text-lg">' . $shortDescription . '</p><p>Service professionnel de ' . $serviceName . ' par ' . $companyInfo['company_name'] . '.</p></div>',
            'short_description' => $shortDescription,
            'icon' => 'fas fa-tools',
            'meta_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'meta_description' => $shortDescription,
            'og_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'og_description' => $shortDescription,
            'twitter_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'twitter_description' => $shortDescription,
            'meta_keywords' => $serviceName . ', ' . $companyInfo['company_city'] . ', rénovation'
        ];
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
     * Gérer l'upload d'image
     */
    private function handleImageUpload($file, $type)
    {
        $filename = 'service_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Créer le dossier s'il n'existe pas
        $uploadPath = public_path('storage/uploads/services');
        
        // Create directory structure step by step with better error handling
        $directories = [
            public_path('storage'),
            public_path('storage/uploads'),
            public_path('storage/uploads/services')
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                // Check if there's a file with the same name that conflicts
                if (file_exists($dir)) {
                    \Log::warning("File exists at directory path: {$dir}. Removing conflicting file.");
                    if (!unlink($dir)) {
                        throw new \Exception("Cannot remove conflicting file at: {$dir}. Please check server permissions.");
                    }
                }
                
                if (!mkdir($dir, 0755, true)) {
                    // Log the error for debugging
                    \Log::error("Failed to create directory: {$dir}. Parent exists: " . (is_dir(dirname($dir)) ? 'yes' : 'no'));
                    throw new \Exception("Failed to create upload directory: {$dir}. Please check server permissions.");
                }
            }
        }
        
        // Verify the final directory exists and is writable
        if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
            throw new \Exception("Upload directory is not writable: {$uploadPath}");
        }
        
        $file->move($uploadPath, $filename);
        return 'storage/uploads/services/' . $filename;
    }

    /**
     * Sauvegarder un service
     */
    private function saveService($service)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $services[] = $service;
        Setting::set('services', json_encode($services));
    }

    /**
     * Mettre à jour un service
     */
    private function updateService($id, $data)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        foreach ($services as $index => $service) {
            if ($service['id'] == $id) {
                $services[$index] = array_merge($service, $data);
                break;
            }
        }
        
        Setting::set('services', json_encode($services));
    }

    /**
     * Supprimer un service
     */
    private function deleteService($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $services = array_filter($services, function($service) use ($id) {
            return $service['id'] != $id;
        });
        
        Setting::set('services', json_encode(array_values($services)));
    }

    /**
     * Récupérer l'image d'un service
     */
    private function getServiceImage($id, $type)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('id', $id);
        return $service[$type] ?? null;
    }
}