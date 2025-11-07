<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\AiService;

class ServicesController extends Controller
{
    /**
     * Liste publique des services
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
            return is_array($service) && ($service['is_visible'] ?? true);
        });
        
        // Définir la page courante pour le SEO
        $currentPage = 'services';
        
        return view('services.index', compact('services', 'visibleServices', 'currentPage'));
    }

    /**
     * Afficher un service public
     */
    public function show($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $service = collect($services)->firstWhere('slug', $slug);
        
        if (!$service) {
            abort(404, 'Service non trouvé');
        }
        
        // Préparer les métadonnées SEO
        $pageTitle = $service['meta_title'] ?? ($service['name'] . ' - Expert professionnel');
        $pageDescription = $service['meta_description'] ?? ($service['short_description'] ?? '');
        $pageKeywords = $service['meta_keywords'] ?? '';
        
        // Passer au layout
        $currentPage = 'services';
        $ogTitle = $service['og_title'] ?? $pageTitle;
        $ogDescription = $service['og_description'] ?? $pageDescription;
        $twitterTitle = $service['twitter_title'] ?? $ogTitle;
        $twitterDescription = $service['twitter_description'] ?? $ogDescription;
        
        // Image Open Graph : utiliser l'image du service si disponible, sinon image par défaut
        $pageImage = null;
        if (!empty($service['featured_image'])) {
            $pageImage = asset($service['featured_image']);
        } else {
            // Utiliser l'image par défaut pour les services
            $defaultServiceImage = 'images/og-services.jpg';
            if (file_exists(public_path($defaultServiceImage))) {
                $pageImage = asset($defaultServiceImage);
            } else {
                // Fallback sur le logo
                $companyLogo = setting('company_logo');
                if ($companyLogo) {
                    $pageImage = asset($companyLogo);
                }
            }
        }
        
        return view('services.show', compact('service', 'pageTitle', 'pageDescription', 'pageKeywords', 'currentPage', 'ogTitle', 'ogDescription', 'twitterTitle', 'twitterDescription', 'pageImage'));
    }

    /**
     * Liste admin des services
     */
    public function index()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        // Statistiques
        $totalServices = count($services);
        $servicesWithContent = count(array_filter($services, function($s) {
            return !empty($s['description'] ?? '');
        }));
        
        return view('admin.services.index', compact('services', 'totalServices', 'servicesWithContent'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Créer un nouveau service
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

        try {
            $slug = Str::slug($validated['name']);
            
            // Vérifier si le service existe déjà
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            foreach ($services as $existingService) {
                if (isset($existingService['slug']) && $existingService['slug'] === $slug) {
                    return back()->with('error', 'Un service avec ce nom existe déjà.')->withInput();
                }
            }
            
            // Générer le contenu via IA
            $companyInfo = $this->getCompanyInfo();
            $aiContent = $this->generateCompleteServiceContent(
                $validated['name'], 
                $validated['short_description'],
                $companyInfo,
                $validated['ai_prompt'] ?? null
            );
            
            if (isset($aiContent['error']) && $aiContent['error']) {
                return back()->with('error', 'Erreur lors de la génération par l\'IA: ' . ($aiContent['error_message'] ?? 'Erreur inconnue'))->withInput();
            }
            
            // Gérer l'upload d'image
            $featuredImagePath = null;
            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $this->handleImageUpload($request->file('featured_image'), 'featured');
            }
            
            // Créer le service
            $newService = [
                'id' => uniqid(),
                'name' => $validated['name'],
                'slug' => $slug,
                'short_description' => $aiContent['short_description'] ?? $validated['short_description'],
                'description' => $aiContent['description'] ?? '',
                'icon' => $aiContent['icon'] ?? 'fas fa-tools',
                'featured_image' => $featuredImagePath,
                'is_featured' => $request->has('is_featured') && $request->input('is_featured') == '1',
                'is_menu' => $request->has('is_menu') && $request->input('is_menu') == '1',
                'meta_title' => $aiContent['meta_title'] ?? '',
                'meta_description' => $aiContent['meta_description'] ?? '',
                'meta_keywords' => $aiContent['meta_keywords'] ?? '',
                'og_title' => $aiContent['og_title'] ?? $aiContent['meta_title'] ?? '',
                'og_description' => $aiContent['og_description'] ?? $aiContent['meta_description'] ?? '',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];
            
            $services[] = $newService;
            Setting::set('services', json_encode($services), 'json', 'services');
            Setting::clearCache();
            
            return redirect()->route('services.admin.index')->with('success', 'Service créé avec succès !');
            
        } catch (\Exception $e) {
            Log::error('Erreur création service: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la création: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $service = collect($services)->firstWhere('id', $id);
        
        if (!$service) {
            abort(404, 'Service non trouvé');
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
            'description' => 'nullable|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'nullable|boolean',
            'is_menu' => 'nullable|boolean',
            'ai_prompt' => 'nullable|string|max:2000',
            'regenerate' => 'nullable|boolean',
        ]);

        try {
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $serviceIndex = null;
            foreach ($services as $index => $service) {
                if (isset($service['id']) && $service['id'] == $id) {
                    $serviceIndex = $index;
                    break;
                }
            }
            
            if ($serviceIndex === null) {
                return back()->with('error', 'Service non trouvé')->withInput();
            }
            
            // Régénérer le contenu si demandé
            if ($request->has('regenerate') && $request->input('regenerate') == '1') {
                $companyInfo = $this->getCompanyInfo();
                $aiContent = $this->generateCompleteServiceContent(
                    $validated['name'], 
                    $validated['short_description'],
                    $companyInfo,
                    $validated['ai_prompt'] ?? null
                );
                
                if (isset($aiContent['error']) && $aiContent['error']) {
                    return back()->with('error', 'Erreur lors de la régénération par l\'IA: ' . ($aiContent['error_message'] ?? 'Erreur inconnue'))->withInput();
                }
                
                $validated['description'] = $aiContent['description'] ?? $validated['description'] ?? '';
                $validated['meta_title'] = $aiContent['meta_title'] ?? $services[$serviceIndex]['meta_title'] ?? '';
                $validated['meta_description'] = $aiContent['meta_description'] ?? $services[$serviceIndex]['meta_description'] ?? '';
                $validated['meta_keywords'] = $aiContent['meta_keywords'] ?? $services[$serviceIndex]['meta_keywords'] ?? '';
                $validated['og_title'] = $aiContent['og_title'] ?? $validated['meta_title'];
                $validated['og_description'] = $aiContent['og_description'] ?? $validated['meta_description'];
            }
            
            // Gérer l'upload d'image
            if ($request->hasFile('featured_image')) {
                // Supprimer l'ancienne image si elle existe
                if (!empty($services[$serviceIndex]['featured_image'])) {
                    $oldImage = $services[$serviceIndex]['featured_image'];
                    if (file_exists(public_path($oldImage))) {
                        unlink(public_path($oldImage));
                    }
                }
                $validated['featured_image'] = $this->handleImageUpload($request->file('featured_image'), 'featured');
            } else {
                $validated['featured_image'] = $services[$serviceIndex]['featured_image'] ?? null;
            }
            
            // Mettre à jour le service
            $services[$serviceIndex] = array_merge($services[$serviceIndex], [
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'short_description' => $validated['short_description'],
                'description' => $validated['description'] ?? $services[$serviceIndex]['description'] ?? '',
                'featured_image' => $validated['featured_image'],
                'is_featured' => $request->has('is_featured') && $request->input('is_featured') == '1',
                'is_menu' => $request->has('is_menu') && $request->input('is_menu') == '1',
                'meta_title' => $validated['meta_title'] ?? $services[$serviceIndex]['meta_title'] ?? '',
                'meta_description' => $validated['meta_description'] ?? $services[$serviceIndex]['meta_description'] ?? '',
                'meta_keywords' => $validated['meta_keywords'] ?? $services[$serviceIndex]['meta_keywords'] ?? '',
                'og_title' => $validated['og_title'] ?? $services[$serviceIndex]['og_title'] ?? '',
                'og_description' => $validated['og_description'] ?? $services[$serviceIndex]['og_description'] ?? '',
                'updated_at' => now()->toISOString(),
            ]);
            
            Setting::set('services', json_encode($services), 'json', 'services');
            Setting::clearCache();
            
            return redirect()->route('services.admin.index')->with('success', 'Service mis à jour avec succès !');
            
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour service: ' . $e->getMessage(), [
                'service_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Supprimer un service
     */
    public function destroy($id)
    {
        try {
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $serviceIndex = null;
            $service = null;
            foreach ($services as $index => $s) {
                if (isset($s['id']) && $s['id'] == $id) {
                    $serviceIndex = $index;
                    $service = $s;
                    break;
                }
            }
            
            if ($serviceIndex === null) {
                return back()->with('error', 'Service non trouvé');
            }
            
            // Supprimer l'image si elle existe
            if (!empty($service['featured_image'])) {
                $imagePath = public_path($service['featured_image']);
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Supprimer le service
            unset($services[$serviceIndex]);
            $services = array_values($services); // Réindexer
            
            Setting::set('services', json_encode($services), 'json', 'services');
            Setting::clearCache();
            
            return back()->with('success', 'Service supprimé avec succès !');
            
        } catch (\Exception $e) {
            Log::error('Erreur suppression service: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Régénérer le contenu d'un service via IA
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

            $companyInfo = $this->getCompanyInfo();
            $aiContent = $this->generateCompleteServiceContent(
                $service['name'], 
                $service['short_description'] ?? '',
                $companyInfo,
                $request->input('ai_prompt')
            );
            
            if (isset($aiContent['error']) && $aiContent['error']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la régénération: ' . ($aiContent['error_message'] ?? 'Erreur inconnue')
                ], 500);
            }

            // Mettre à jour le service
            $serviceIndex = null;
            foreach ($services as $index => $s) {
                if (isset($s['id']) && $s['id'] == $id) {
                    $serviceIndex = $index;
                    break;
                }
            }
            
            if ($serviceIndex !== null) {
                $services[$serviceIndex] = array_merge($services[$serviceIndex], [
                    'description' => $aiContent['description'],
                    'meta_title' => $aiContent['meta_title'],
                    'meta_description' => $aiContent['meta_description'],
                    'meta_keywords' => $aiContent['meta_keywords'],
                    'og_title' => $aiContent['og_title'],
                    'og_description' => $aiContent['og_description'],
                    'updated_at' => now()->toISOString(),
                ]);
                
                Setting::set('services', json_encode($services), 'json', 'services');
                Setting::clearCache();
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contenu régénéré avec succès',
                    'content' => $aiContent
                ]);
            }
            
            return back()->with('success', 'Contenu régénéré avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur régénération service: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
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
        ];
    }

    /**
     * Générer le contenu complet via IA (utilise ServiceAiController)
     */
    private function generateCompleteServiceContent($serviceName, $shortDescription, $companyInfo, $aiPrompt = null)
    {
        $serviceAiController = new ServiceAiController();
        return $serviceAiController->generateCompleteServiceContent($serviceName, $shortDescription, $companyInfo);
    }

    /**
     * Gérer l'upload d'image
     */
    private function handleImageUpload($file, $prefix = 'service')
    {
        $fileName = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $uploadPath = public_path('uploads/services');
        
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $file->move($uploadPath, $fileName);
        
        return 'uploads/services/' . $fileName;
    }
}
