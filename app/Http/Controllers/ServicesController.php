<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    /**
     * 
     * Afficher la liste des services
     */
    public function index()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
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
        
        // S'assurer que $services est toujours un tableau
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
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string|max:500',
            'twitter_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Générer le slug
        $slug = Str::slug($validated['name']);
        
        // Récupérer les informations de l'entreprise pour l'IA
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyAddress = setting('company_address', '');
        $companyCity = setting('company_city', '');
        $companyPostalCode = setting('company_postal_code', '');
        $companyRegion = setting('company_region', '');
        
        // Récupérer le département à partir du code postal
        $department = $this->getDepartmentFromPostalCode($companyPostalCode);
        if ($department) {
            $companyRegion = $department;
        }
        
        // Générer automatiquement le contenu avec l'IA
        $aiContent = $this->generatePureAIContent(
            $validated['name'], 
            $validated['short_description'],
            [
                'company_name' => $companyName,
                'company_phone' => $companyPhone,
                'company_email' => $companyEmail,
                'company_address' => $companyAddress,
                'company_city' => $companyCity,
                'company_region' => $companyRegion,
            ]
        );
        
        // Gérer l'upload d'image de mise en avant
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'service_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('storage/uploads/services');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $featuredImagePath = 'storage/uploads/services/' . $filename;
        }
        
        // Gérer l'upload de l'image Open Graph
        $ogImagePath = null;
        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $filename = 'og_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('storage/uploads/services');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $ogImagePath = 'storage/uploads/services/' . $filename;
        }
        
        // Gérer l'upload de l'image Twitter
        $twitterImagePath = null;
        if ($request->hasFile('twitter_image')) {
            $file = $request->file('twitter_image');
            $filename = 'twitter_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('storage/uploads/services');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $twitterImagePath = 'storage/uploads/services/' . $filename;
        }

        // Récupérer les services existants
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
        if (!is_array($services)) {
            $services = [];
        }
        
        // Créer le nouveau service avec contenu généré par IA
        $service = [
            'id' => time() . rand(1000, 9999),
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $aiContent['description'],
            'short_description' => $aiContent['short_description'], // ✅ Description courte générée par l'IA
            'icon' => $aiContent['icon'],
            'featured_image' => $featuredImagePath,
            'is_featured' => $request->has('is_featured'),
            'is_menu' => $request->has('is_menu'),
            'meta_title' => $aiContent['meta_title'],
            'meta_description' => $aiContent['meta_description'],
            // ✅ Métadonnées Open Graph générées automatiquement
            'og_title' => $validated['og_title'] ?? $aiContent['og_title'] ?? $aiContent['meta_title'],
            'og_description' => $validated['og_description'] ?? $aiContent['og_description'] ?? $aiContent['meta_description'],
            'og_image' => $ogImagePath ?: $featuredImagePath, // Utilise l'image OG ou l'image featured
            'og_type' => 'website',
            'og_url' => url('/services/' . $slug),
            // ✅ Balises Twitter générées automatiquement
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $validated['twitter_title'] ?? $validated['og_title'] ?? $aiContent['og_title'] ?? $aiContent['meta_title'],
            'twitter_description' => $validated['twitter_description'] ?? $validated['og_description'] ?? $aiContent['og_description'] ?? $aiContent['meta_description'],
            'twitter_image' => $twitterImagePath ?: $ogImagePath ?: $featuredImagePath,
            // ✅ Mots-clés SEO générés automatiquement
            'meta_keywords' => $aiContent['meta_keywords'] ?? $this->generateKeywords($validated['name'], $companyName, $companyCity),
            'ai_generated' => true,
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Ajouter le service
        $services[] = $service;
        
        // Sauvegarder
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();

        return redirect()->route('services.admin.index')->with('success', 'Service créé avec succès par l\'IA !');
    }

    /**
     * Afficher le formulaire d'édition de service
     */
    public function edit($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
        if (!is_array($services)) {
            $services = [];
        }
        
        // Si l'ID est numérique et correspond à un index, récupérer par index
        if (is_numeric($id) && isset($services[$id])) {
            $service = $services[$id];
        } else {
            // Sinon, essayer de récupérer par ID si le service en a un
            $service = collect($services)->firstWhere('id', $id);
        }
        
        if (!$service) {
            return redirect()->route('services.admin.index')->with('error', 'Service non trouvé');
        }
        
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Mettre à jour un service
     */
    public function update(Request $request, $id)
    {
        // Debug: Log the request data
        \Log::info('Update request data:', $request->all());
        \Log::info('Has featured_image file:', ['has_file' => $request->hasFile('featured_image')]);
        \Log::info('Service ID to update:', ['id' => $id, 'id_type' => gettype($id)]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'short_description' => 'required|string|max:500',
                'icon' => 'nullable|string|max:100',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'is_featured' => 'nullable|boolean',
                'is_menu' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'og_title' => 'nullable|string|max:255',
                'og_description' => 'nullable|string|max:500',
                'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'twitter_title' => 'nullable|string|max:255',
                'twitter_description' => 'nullable|string|max:500',
                'twitter_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation errors:', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
        if (!is_array($services)) {
            $services = [];
        }
        
        // Si l'ID est numérique et correspond à un index, utiliser cet index
        if (is_numeric($id) && isset($services[$id])) {
            $serviceIndex = $id;
        } else {
            // Sinon, essayer de trouver par ID si le service en a un
            $serviceIndex = collect($services)->search(function ($service) use ($id) {
                return isset($service['id']) && $service['id'] == $id;
            });
        }
        
        if ($serviceIndex === false) {
            return redirect()->route('services.admin.index')->with('error', 'Service non trouvé');
        }
        
        // Gérer l'upload de l'image si fournie
        $featuredImagePath = $services[$serviceIndex]['featured_image'] ?? null;
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = 'service_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('storage/uploads/services');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $featuredImagePath = 'storage/uploads/services/' . $filename;
        }
        
        // Gérer l'upload de l'image Open Graph
        $ogImagePath = $services[$serviceIndex]['og_image'] ?? null;
        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $filename = 'og_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('storage/uploads/services');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $ogImagePath = 'storage/uploads/services/' . $filename;
        }
        
        // Gérer l'upload de l'image Twitter
        $twitterImagePath = $services[$serviceIndex]['twitter_image'] ?? null;
        if ($request->hasFile('twitter_image')) {
            $file = $request->file('twitter_image');
            $filename = 'twitter_' . time() . '_' . Str::slug($validated['name']) . '.' . $file->getClientOriginalExtension();
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('storage/uploads/services');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $file->move($uploadPath, $filename);
            $twitterImagePath = 'storage/uploads/services/' . $filename;
        }

        // Mettre à jour le service
        $services[$serviceIndex]['name'] = $validated['name'];
        $services[$serviceIndex]['slug'] = Str::slug($validated['name']);
        $services[$serviceIndex]['description'] = $validated['description'];
        $services[$serviceIndex]['short_description'] = $validated['short_description'];
        $services[$serviceIndex]['icon'] = $validated['icon'] ?? 'fas fa-tools';
        $services[$serviceIndex]['featured_image'] = $featuredImagePath;
        $services[$serviceIndex]['is_featured'] = $request->has('is_featured');
        $services[$serviceIndex]['is_menu'] = $request->has('is_menu');
        $services[$serviceIndex]['meta_title'] = $validated['meta_title'] ?? $validated['name'];
        $services[$serviceIndex]['meta_description'] = $validated['meta_description'] ?? $validated['short_description'];
        
        // Log pour debug des images
        \Log::info('Image paths debug:', [
            'featured_image_path' => $featuredImagePath,
            'og_image_path' => $ogImagePath,
            'twitter_image_path' => $twitterImagePath,
            'current_featured' => $services[$serviceIndex]['featured_image'] ?? 'N/A',
            'current_og' => $services[$serviceIndex]['og_image'] ?? 'N/A',
            'current_twitter' => $services[$serviceIndex]['twitter_image'] ?? 'N/A'
        ]);
        
        // Mettre à jour les métadonnées Open Graph
        $services[$serviceIndex]['og_title'] = $validated['og_title'] ?? $validated['meta_title'] ?? $validated['name'];
        $services[$serviceIndex]['og_description'] = $validated['og_description'] ?? $validated['meta_description'] ?? $validated['short_description'];
        $services[$serviceIndex]['og_image'] = $ogImagePath ?: $featuredImagePath ?: ($services[$serviceIndex]['og_image'] ?? null);
        $services[$serviceIndex]['og_type'] = 'website';
        $services[$serviceIndex]['og_url'] = url('/services/' . Str::slug($validated['name']));
        
        // Mettre à jour les métadonnées Twitter
        $services[$serviceIndex]['twitter_card'] = 'summary_large_image';
        $services[$serviceIndex]['twitter_title'] = $validated['twitter_title'] ?? $validated['og_title'] ?? $validated['meta_title'] ?? $validated['name'];
        $services[$serviceIndex]['twitter_description'] = $validated['twitter_description'] ?? $validated['og_description'] ?? $validated['meta_description'] ?? $validated['short_description'];
        $services[$serviceIndex]['twitter_image'] = $twitterImagePath ?: $ogImagePath ?: $featuredImagePath ?: ($services[$serviceIndex]['twitter_image'] ?? null);
        
        $services[$serviceIndex]['updated_at'] = now()->toISOString();
        
        // Sauvegarder
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();

        return redirect()->route('services.admin.index')->with('success', 'Service mis à jour avec succès !');
    }

    /**
     * Régénérer le contenu d'un service avec l'IA
     */
    public function regenerate($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
        if (!is_array($services)) {
            $services = [];
        }
        
        // Si l'ID est numérique et correspond à un index, utiliser cet index
        if (is_numeric($id) && isset($services[$id])) {
            $serviceIndex = $id;
        } else {
            // Sinon, essayer de trouver par ID si le service en a un
            $serviceIndex = collect($services)->search(function ($service) use ($id) {
                return isset($service['id']) && $service['id'] == $id;
            });
        }
        
        if ($serviceIndex === false) {
            return redirect()->route('services.admin.index')->with('error', 'Service non trouvé');
        }
        
        $service = $services[$serviceIndex];
        
        // Récupérer les informations de l'entreprise pour l'IA
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyAddress = setting('company_address', '');
        $companyCity = setting('company_city', '');
        $companyPostalCode = setting('company_postal_code', '');
        $companyRegion = setting('company_region', '');
        
        // Récupérer le département à partir du code postal
        $department = $this->getDepartmentFromPostalCode($companyPostalCode);
        if ($department) {
            $companyRegion = $department;
        }
        
        // Régénérer le contenu avec l'IA (force la régénération complète)
        $aiContent = $this->generatePureAIContent(
            $service['name'], 
            $service['short_description'] ?? 'Service professionnel de ' . $service['name'],
            [
                'company_name' => $companyName,
                'company_phone' => $companyPhone,
                'company_email' => $companyEmail,
                'company_address' => $companyAddress,
                'company_city' => $companyCity,
                'company_region' => $companyRegion,
            ]
        );
        
        // Log pour debug
        \Log::info('Régénération service:', [
            'service_name' => $service['name'],
            'ai_content' => $aiContent,
            'description_length' => strlen($aiContent['description'] ?? ''),
            'description_preview' => substr($aiContent['description'] ?? '', 0, 200)
        ]);
        
        // Mettre à jour avec le nouveau contenu IA (nettoyé)
        $services[$serviceIndex]['description'] = $this->cleanHtmlContent($aiContent['description']);
        $services[$serviceIndex]['short_description'] = $aiContent['short_description']; // ✅ Description courte générée
        $services[$serviceIndex]['icon'] = $aiContent['icon'];
        $services[$serviceIndex]['meta_title'] = $aiContent['meta_title'];
        $services[$serviceIndex]['meta_description'] = $aiContent['meta_description'];
        $services[$serviceIndex]['ai_generated'] = true;
        $services[$serviceIndex]['updated_at'] = now()->toISOString();
        
        // Sauvegarder
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();

        return redirect()->route('services.admin.index')->with('success', 'Service régénéré avec l\'IA avec succès !');
    }
    
    /**
     * Régénérer tous les services avec le nouveau contenu personnalisé
     */
    public function regenerateAll()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyAddress = setting('company_address', '');
        $companyCity = setting('company_city', '');
        $companyPostalCode = setting('company_postal_code', '');
        $companyRegion = setting('company_region', '');
        
        // Récupérer le département à partir du code postal
        $department = $this->getDepartmentFromPostalCode($companyPostalCode);
        if ($department) {
            $companyRegion = $department;
        }
        
        $companyInfo = [
            'company_name' => $companyName,
            'company_phone' => $companyPhone,
            'company_email' => $companyEmail,
            'company_address' => $companyAddress,
            'company_city' => $companyCity,
            'company_region' => $companyRegion,
        ];
        
        $regenerated = 0;
        foreach ($services as $index => $service) {
            try {
                $aiContent = $this->generatePureAIContent(
                    $service['name'], 
                    $service['short_description'] ?? 'Service professionnel de ' . $service['name'],
                    $companyInfo
                );
                
                // Utiliser le contenu de l'IA même s'il est court
                \Log::info('Utilisation du contenu IA pour service: ' . $service['name'], [
                    'description_length' => strlen($aiContent['description'])
                ]);
                
                $services[$index]['description'] = $this->cleanHtmlContent($aiContent['description']);
                $services[$index]['short_description'] = $aiContent['short_description'];
                $services[$index]['icon'] = $aiContent['icon'];
                $services[$index]['meta_title'] = $aiContent['meta_title'];
                $services[$index]['meta_description'] = $aiContent['meta_description'];
                $services[$index]['ai_generated'] = true;
                $services[$index]['updated_at'] = now()->toISOString();
                
                $regenerated++;
            } catch (\Exception $e) {
                \Log::error('Erreur régénération service ' . $service['name'] . ': ' . $e->getMessage());
            }
        }
        
        // Sauvegarder tous les services
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();
        
        return redirect()->route('services.admin.index')->with('success', "Contenu régénéré pour {$regenerated} services !");
    }
    
    /**
     * Debug: Afficher le contenu d'un service
     */
    public function debug($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $service = collect($services)->firstWhere('slug', $slug);
        
        if (!$service) {
            return response()->json(['error' => 'Service not found']);
        }
        
        return response()->json([
            'service_name' => $service['name'],
            'description_length' => strlen($service['description'] ?? ''),
            'description_preview' => substr($service['description'] ?? '', 0, 500),
            'short_description' => $service['short_description'] ?? '',
            'ai_generated' => $service['ai_generated'] ?? false,
            'updated_at' => $service['updated_at'] ?? null,
            'full_description' => $service['description'] ?? ''
        ]);
    }
    
    /**
     * Forcer la régénération d'un service avec contenu plus long
     */
    public function forceRegenerate($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $serviceIndex = null;
        foreach ($services as $index => $service) {
            if ($service['slug'] === $slug) {
                $serviceIndex = $index;
                break;
            }
        }
        
        if ($serviceIndex === null) {
            return response()->json(['error' => 'Service not found']);
        }
        
        $service = $services[$serviceIndex];
        
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyAddress = setting('company_address', '');
        $companyCity = setting('company_city', '');
        $companyPostalCode = setting('company_postal_code', '');
        $companyRegion = setting('company_region', '');
        
        // Récupérer le département à partir du code postal
        $department = $this->getDepartmentFromPostalCode($companyPostalCode);
        if ($department) {
            $companyRegion = $department;
        }
        
        $companyInfo = [
            'company_name' => $companyName,
            'company_phone' => $companyPhone,
            'company_email' => $companyEmail,
            'company_address' => $companyAddress,
            'company_city' => $companyCity,
            'company_region' => $companyRegion,
        ];
        
        // Forcer la régénération avec un contenu plus long
        $aiContent = $this->generatePureAIContent(
            $service['name'], 
            'Service professionnel de ' . $service['name'] . ' avec expertise complète et solutions personnalisées',
            $companyInfo
        );
        
        // Utiliser le contenu de l'IA même s'il est court
        \Log::info('Utilisation du contenu IA pour service: ' . $service['name'], [
            'description_length' => strlen($aiContent['description'])
        ]);
        
        $services[$serviceIndex]['description'] = $this->cleanHtmlContent($aiContent['description']);
        $services[$serviceIndex]['short_description'] = $aiContent['short_description'];
        $services[$serviceIndex]['icon'] = $aiContent['icon'];
        $services[$serviceIndex]['meta_title'] = $aiContent['meta_title'];
        $services[$serviceIndex]['meta_description'] = $aiContent['meta_description'];
        $services[$serviceIndex]['ai_generated'] = true;
        $services[$serviceIndex]['updated_at'] = now()->toISOString();
        
        // Sauvegarder
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();
        
        return response()->json([
            'success' => true,
            'service_name' => $service['name'],
            'description_length' => strlen($aiContent['description']),
            'message' => 'Service régénéré avec succès'
        ]);
    }
    
    /**
     * Forcer la mise à jour des images d'un service
     */
    public function fixImages($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $serviceIndex = null;
        foreach ($services as $index => $service) {
            if ($service['slug'] === $slug) {
                $serviceIndex = $index;
                break;
            }
        }
        
        if ($serviceIndex === null) {
            return response()->json(['error' => 'Service not found']);
        }
        
        $service = $services[$serviceIndex];
        
        // Log des images actuelles
        \Log::info('Images actuelles pour service: ' . $service['name'], [
            'featured_image' => $service['featured_image'] ?? 'N/A',
            'og_image' => $service['og_image'] ?? 'N/A',
            'twitter_image' => $service['twitter_image'] ?? 'N/A'
        ]);
        
        // Corriger les images manquantes
        $featuredImage = $service['featured_image'] ?? null;
        $ogImage = $service['og_image'] ?? null;
        $twitterImage = $service['twitter_image'] ?? null;
        
        // Si featured_image existe mais og_image ou twitter_image sont vides, les corriger
        if ($featuredImage && !$ogImage) {
            $services[$serviceIndex]['og_image'] = $featuredImage;
            \Log::info('Correction og_image avec featured_image');
        }
        
        if ($featuredImage && !$twitterImage) {
            $services[$serviceIndex]['twitter_image'] = $featuredImage;
            \Log::info('Correction twitter_image avec featured_image');
        }
        
        // Si og_image existe mais twitter_image est vide, utiliser og_image
        if ($ogImage && !$twitterImage) {
            $services[$serviceIndex]['twitter_image'] = $ogImage;
            \Log::info('Correction twitter_image avec og_image');
        }
        
        // Sauvegarder
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();
        
        return response()->json([
            'success' => true,
            'service_name' => $service['name'],
            'featured_image' => $services[$serviceIndex]['featured_image'] ?? 'N/A',
            'og_image' => $services[$serviceIndex]['og_image'] ?? 'N/A',
            'twitter_image' => $services[$serviceIndex]['twitter_image'] ?? 'N/A',
            'message' => 'Images corrigées avec succès'
        ]);
    }

    /**
     * Supprimer un service
     */
    public function destroy($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
        if (!is_array($services)) {
            $services = [];
        }
        
        // Si l'ID est numérique et correspond à un index, supprimer par index
        if (is_numeric($id) && isset($services[$id])) {
            unset($services[$id]);
            $services = array_values($services); // Réindexer le tableau
        } else {
            // Sinon, essayer de supprimer par ID si le service en a un
            $services = collect($services)->reject(function ($service) use ($id) {
                return isset($service['id']) && $service['id'] == $id;
            })->values()->toArray();
        }
        
        // Sauvegarder
        Setting::set('services', json_encode($services), 'json', 'services');
        Setting::clearCache();

        return redirect()->route('services.admin.index')->with('success', 'Service supprimé avec succès !');
    }

    /**
     * Afficher une page de service publique
     */
    public function show($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // S'assurer que $services est toujours un tableau
        if (!is_array($services)) {
            $services = [];
        }
        
        $service = collect($services)->firstWhere('slug', $slug);
        
        if (!$service) {
            abort(404);
        }
        
        // Log pour debug
        \Log::info('Affichage service:', [
            'service_name' => $service['name'],
            'description_length' => strlen($service['description'] ?? ''),
            'description_preview' => substr($service['description'] ?? '', 0, 200),
            'short_description' => $service['short_description'] ?? '',
            'ai_generated' => $service['ai_generated'] ?? false,
            'updated_at' => $service['updated_at'] ?? null
        ]);
        
        // Nettoyer le contenu HTML pour éviter les erreurs d'affichage et les répétitions
        if (!empty($service['description'])) {
            $service['description'] = $this->cleanHtmlContent($service['description']);
            
            // Vérifier s'il y a encore des répétitions et les nettoyer
            $service['description'] = $this->removeDuplicateContent($service['description']);
        }
        
        // Récupérer les images du portfolio liées à ce service
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        // Ensure we have a valid array and filter out any invalid items
        if (!is_array($portfolioItems)) {
            $portfolioItems = [];
        }
        
        // Filtrer les images par type de service
        $serviceImages = collect($portfolioItems)
            ->filter(function ($item) use ($service) {
                return is_array($item) && 
                       isset($item['work_type']) && 
                       isset($item['title']) &&
                       $this->isServiceRelated($item['work_type'], $service['name']);
            })
            ->values()
            ->toArray();
        
        return view('services.show', compact('service', 'serviceImages'));
    }

    /**
     * Générer automatiquement le contenu d'une page de service avec IA
     */
    public function generateContent(Request $request)
    {
        $serviceName = $request->input('service_name');
        $description = $request->input('description');
        
        // Récupérer les informations de l'entreprise
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyAddress = setting('company_address', '');
        
        // Récupérer la configuration IA
        $apiKey = setting('chatgpt_api_key');
        $model = setting('chatgpt_model', 'gpt-3.5-turbo');
        $temperature = setting('ai_temperature', 0.7);
        $maxTokens = setting('ai_max_tokens', 1000);
        $promptTemplate = setting('ai_prompt_template', 'Créez une page web professionnelle pour le service "{service_name}" avec la description "{description}". Incluez une section hero, description détaillée, avantages, et section contact. Utilisez un style moderne et professionnel.');
        
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Clé API ChatGPT non configurée. Veuillez configurer l\'IA dans les paramètres.'
            ]);
        }
        
        try {
            // Générer le contenu avec la vraie API ChatGPT
            $generatedContent = $this->generateRealAIContent($serviceName, $description, [
                'company_name' => $companyName,
                'company_phone' => $companyPhone,
                'company_email' => $companyEmail,
                'company_address' => $companyAddress,
            ], $apiKey, $model, $temperature, $maxTokens, $promptTemplate);
            
            // Mettre à jour les statistiques
            $this->updateAIStats();
            
            return response()->json([
                'success' => true,
                'content' => $generatedContent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération IA: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifier si un type de travail est lié à un service
     */
    private function isServiceRelated($workType, $serviceName)
    {
        // Nouvelle logique: comparer directement le work_type (slug) avec le slug du service
        // ou utiliser les anciennes correspondances pour compatibilité
        $serviceMappings = [
            'couverture' => ['roof', 'couverture'],
            'toiture' => ['roof', 'toiture'],
            'façade' => ['facade', 'facade'],
            'facade' => ['facade'],
            'isolation' => ['isolation'],
            'hydrofuge' => ['hydrofuge', 'facade'],
            'rénovation' => ['roof', 'facade', 'isolation', 'mixed', 'renovation'],
            'renovation' => ['roof', 'facade', 'isolation', 'mixed'],
        ];
        
        $serviceKey = strtolower($serviceName);
        $mappedTypes = $serviceMappings[$serviceKey] ?? [$serviceKey];
        
        return in_array(strtolower($workType), array_map('strtolower', $mappedTypes)) || strtolower($workType) === $serviceKey;
    }

    /**
     * Générer le contenu avec la vraie API ChatGPT
     */
    private function generateRealAIContent($serviceName, $description, $companyInfo, $apiKey, $model, $temperature, $maxTokens, $promptTemplate)
    {
        // Préparer le prompt avec les variables
        $prompt = str_replace([
            '{service_name}',
            '{description}',
            '{company_name}',
            '{company_phone}',
            '{company_email}',
            '{company_address}'
        ], [
            $serviceName,
            $description,
            $companyInfo['company_name'],
            $companyInfo['company_phone'],
            $companyInfo['company_email'],
            $companyInfo['company_address']
        ], $promptTemplate);
        
        // Appel à l'API OpenAI
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Erreur API OpenAI: ' . ($response->json()['error']['message'] ?? 'Erreur inconnue'));
        }
        
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        // Mettre à jour les statistiques d'utilisation
        $this->updateAIUsageStats($data['usage'] ?? []);
        
        return $content;
    }
    
    /**
     * Mettre à jour les statistiques d'utilisation IA
     */
    private function updateAIUsageStats($usage)
    {
        $currentCount = setting('ai_generations_count', 0);
        $currentTokens = setting('ai_tokens_used', 0);
        
        Setting::set('ai_generations_count', $currentCount + 1, 'integer', 'ai');
        Setting::set('ai_tokens_used', $currentTokens + ($usage['total_tokens'] ?? 0), 'integer', 'ai');
        Setting::set('ai_last_used', now()->format('d/m/Y H:i'), 'string', 'ai');
        Setting::clearCache();
    }
    
    /**
     * Mettre à jour les statistiques générales IA
     */
    private function updateAIStats()
    {
        $currentCount = setting('ai_generations_count', 0);
        Setting::set('ai_generations_count', $currentCount + 1, 'integer', 'ai');
        Setting::set('ai_last_used', now()->format('d/m/Y H:i'), 'string', 'ai');
        Setting::clearCache();
    }
    
    /**
     * Générer le contenu avec IA (simulation - fallback)
     */
    private function generateAIContent($serviceName, $description, $companyInfo)
    {
        // Simulation de génération IA (fallback si pas d'API)
        $content = "
        <div class='service-hero bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16'>
            <div class='container mx-auto px-4 text-center'>
                <h1 class='text-4xl font-bold mb-4'>Service {$serviceName}</h1>
                <p class='text-xl mb-8'>{$description}</p>
                <a href='#contact' class='bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition'>
                    Demander un Devis
                </a>
            </div>
        </div>
        
        <div class='service-content py-16'>
            <div class='container mx-auto px-4'>
                <div class='grid md:grid-cols-2 gap-12'>
                    <div>
                        <h2 class='text-3xl font-bold mb-6'>Notre Expertise en {$serviceName}</h2>
                        <p class='text-lg text-gray-700 mb-6'>{$description}</p>
                        
                        <h3 class='text-2xl font-semibold mb-4'>Pourquoi Choisir {$companyInfo['company_name']} ?</h3>
                        <ul class='space-y-3'>
                            <li class='flex items-center'>
                                <i class='fas fa-check text-green-500 mr-3'></i>
                                <span>Plus de 10 ans d'expérience</span>
                            </li>
                            <li class='flex items-center'>
                                <i class='fas fa-check text-green-500 mr-3'></i>
                                <span>Matériaux de qualité premium</span>
                            </li>
                            <li class='flex items-center'>
                                <i class='fas fa-check text-green-500 mr-3'></i>
                                <span>Équipe d'artisans qualifiés</span>
                            </li>
                            <li class='flex items-center'>
                                <i class='fas fa-check text-green-500 mr-3'></i>
                                <span>Garantie sur tous nos travaux</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class='bg-gray-50 p-8 rounded-lg'>
                        <h3 class='text-2xl font-semibold mb-6'>Contactez-nous</h3>
                        <div class='space-y-4'>
                            <div class='flex items-center'>
                                <i class='fas fa-phone text-blue-600 mr-3'></i>
                                <span>{$companyInfo['company_phone']}</span>
                            </div>
                            <div class='flex items-center'>
                                <i class='fas fa-envelope text-blue-600 mr-3'></i>
                                <span>{$companyInfo['company_email']}</span>
                            </div>
                            <div class='flex items-center'>
                                <i class='fas fa-map-marker-alt text-blue-600 mr-3'></i>
                                <span>{$companyInfo['company_address']}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        ";
        
        return $content;
    }

    /**
     * NOUVELLE MÉTHODE - Génération pure avec IA sans préconfiguration
     */
    private function generatePureAIContent($serviceName, $shortDescription, $companyInfo)
    {
        $apiKey = setting('chatgpt_api_key');
        
        if (!$apiKey) {
            \Log::error('Clé API manquante pour génération IA pure');
            return $this->generatePersonalizedDefaultContent($serviceName, $shortDescription, $companyInfo);
        }
        
        try {
            // Prompt ultra-simple avec liberté totale
            $prompt = "Tu es un expert en rédaction web pour le secteur de la rénovation.

CONTEXTE:
- Entreprise: {$companyInfo['company_name']}
- Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
- Service: {$serviceName}
- Description: {$shortDescription}

MISSION:
Crée un contenu HTML professionnel et attractif pour ce service. Tu as une liberté totale pour:
- Structurer le contenu comme tu veux
- Choisir les sections pertinentes
- Adapter le style et le formatage
- Personnaliser selon le type de service
- Créer du contenu unique et engageant

CONTRAINTES UNIQUES:
- Utilise Tailwind CSS pour le styling
- Inclus des prestations spécifiques au service
- Ajoute des informations pratiques
- Inclus un appel à l'action pour le devis
- Adapte tout au service {$serviceName}

FORMAT JSON:
{
  \"description\": \"[HTML complet avec ton propre style et structure]\",
  \"short_description\": \"[Description courte et accrocheuse]\",
  \"icon\": \"fas fa-[icône appropriée au service]\",
  \"meta_title\": \"[Titre SEO optimisé]\",
  \"meta_description\": \"[Description SEO engageante]\",
  \"og_title\": \"[Titre pour réseaux sociaux]\",
  \"og_description\": \"[Description pour réseaux sociaux]\",
  \"meta_keywords\": \"[Mots-clés pertinents]\"
}

Tu as carte blanche pour créer le meilleur contenu possible. Sois créatif et professionnel.

Réponds UNIQUEMENT avec le JSON valide.";

            \Log::info('Génération IA pure pour service', [
                'service_name' => $serviceName,
                'prompt_length' => strlen($prompt)
            ]);
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-3.5-turbo'),
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
                
                \Log::info('Réponse IA pure reçue', [
                    'service_name' => $serviceName,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 300)
                ]);
                
                // Parser directement le JSON
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
                            'meta_keywords' => $aiData['meta_keywords'] ?? $serviceName . ', ' . $companyInfo['company_city']
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erreur génération IA pure: ' . $e->getMessage());
        }
        
        // Fallback minimal
        return $this->generatePersonalizedDefaultContent($serviceName, $shortDescription, $companyInfo);
    }

    /**
     * Générer tout le contenu du service avec l'IA - ANCIENNE MÉTHODE
     */
    private function generateServiceContentWithAI($serviceName, $shortDescription, $companyInfo, $customPrompt = null)
    {
        $apiKey = setting('chatgpt_api_key');
        
        if (!$apiKey) {
            // Utiliser un contenu par défaut personnalisé au lieu du fallback générique
            \Log::warning('Clé API manquante, utilisation du contenu par défaut personnalisé', [
                'service_name' => $serviceName
            ]);
            return $this->generatePersonalizedDefaultContent($serviceName, $shortDescription, $companyInfo);
        }
        
        try {
            // Prompt complet pour générer tout le contenu
            $prompt = $this->buildCompletePrompt($serviceName, $shortDescription, $companyInfo, $customPrompt);
            
            \Log::info('Génération IA pour service', [
                'service_name' => $serviceName,
                'prompt_length' => strlen($prompt)
            ]);
            
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-3.5-turbo'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => setting('ai_max_tokens', 4000),
                'temperature' => setting('ai_temperature', 0.7)
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                \Log::info('Réponse IA reçue', [
                    'service_name' => $serviceName,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 200)
                ]);
                
                // Incrémenter les statistiques d'utilisation de l'IA
                $currentCount = (int) setting('ai_generations_count', 0);
                Setting::set('ai_generations_count', $currentCount + 1);
                Setting::set('ai_last_used', now()->format('d/m/Y H:i'));
                Setting::clearCache();
                
                // Parser le contenu JSON retourné par l'IA
                return $this->parseAIResponse($content, $serviceName, $shortDescription, $companyInfo);
            } else {
                \Log::error('Erreur API OpenAI', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur génération IA: ' . $e->getMessage());
        }
        
        // Fallback en cas d'erreur
        return $this->generateFallbackContent($serviceName, $shortDescription, $companyInfo);
    }

    /**
     * Générer automatiquement les mots-clés SEO pour un service
     */
    private function generateKeywords($serviceName, $companyName, $city)
    {
        $baseKeywords = [
            strtolower($serviceName),
            'devis gratuit',
            'professionnel',
            'qualité',
            'expert'
        ];
        
        if ($city) {
            $baseKeywords[] = strtolower($city);
            $baseKeywords[] = $serviceName . ' ' . strtolower($city);
        }
        
        // Ajouter des mots-clés spécifiques selon le type de service
        $serviceKeywords = [
            'couverture' => ['toiture', 'tuiles', 'charpente', 'zinc', 'ardoise'],
            'façade' => ['enduit', 'peinture', 'isolation', 'rénovation'],
            'isolation' => ['thermique', 'phonique', 'combles', 'murs'],
            'plomberie' => ['sanitaire', 'chauffage', 'salle de bain'],
            'électricité' => ['éclairage', 'tableau', 'prises', 'domotique']
        ];
        
        $serviceType = strtolower($serviceName);
        foreach ($serviceKeywords as $key => $keywords) {
            if (str_contains($serviceType, $key)) {
                $baseKeywords = array_merge($baseKeywords, $keywords);
                break;
            }
        }
        
        return implode(', ', array_unique($baseKeywords));
    }
    
    /**
     * Construire le prompt complet pour l'IA
     */
    private function buildCompletePrompt($serviceName, $shortDescription, $companyInfo, $customPrompt = null)
    {
        $basePrompt = $customPrompt ?? "Tu es un expert en rédaction web et en marketing pour le secteur de la rénovation et du bâtiment.

CONTEXTE:
Entreprise: {$companyInfo['company_name']}
Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
Service: {$serviceName}
Description courte: {$shortDescription}

MISSION:
Créez un contenu HTML professionnel, attractif et optimisé SEO pour une page de service de rénovation.

STRUCTURE HTML OBLIGATOIRE - EXACTEMENT COMME CET EXEMPLE:
<div class=\"grid md:grid-cols-2 gap-8\">
  <!-- Colonne gauche : description + engagement + prestations -->
  <div class=\"space-y-6\">
    <!-- Introduction générale -->
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">
        [ÉCRIVEZ UNE INTRODUCTION PERSONNALISÉE pour le service {$serviceName} à {$companyInfo['company_city']}, {$companyInfo['company_region']}. Adaptez le contenu selon le type de service : toiture, façade, isolation, etc.]
      </p>
      <p class=\"text-lg leading-relaxed\">
        [ÉCRIVEZ UN DEUXIÈME PARAGRAPHE sur l'expertise spécifique au service {$serviceName}]
      </p>
      <p class=\"text-lg leading-relaxed\">
        [ÉCRIVEZ UN TROISIÈME PARAGRAPHE sur l'approche personnalisée et la satisfaction client]
      </p>
    </div>

    <!-- Engagement qualité -->
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité</h3>
      <p class=\"leading-relaxed mb-3\">
        Chez <strong>{$companyInfo['company_name']}</strong>, nous mettons un point d'honneur à garantir la satisfaction totale de nos clients. Chaque projet est unique et mérite une attention particulière.
      </p>
      <p class=\"leading-relaxed\">
        [ÉCRIVEZ UN PARAGRAPHE sur la qualité des matériaux et techniques spécifiques au service {$serviceName}]
      </p>
    </div>

    <!-- Prestations -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$serviceName}</h3>
    <ul class=\"space-y-3\">
      [LISTEZ 6-8 PRESTATIONS SPÉCIFIQUES au service {$serviceName}. Adaptez selon le type : toiture, façade, isolation, gouttières, etc.]
    </ul>

    <!-- Pourquoi choisir notre entreprise -->
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir Notre Entreprise</h3>
      <p class=\"leading-relaxed\">
        [ÉCRIVEZ UN PARAGRAPHE sur la réputation et l'expertise locale pour le service {$serviceName} à {$companyInfo['company_city']}, {$companyInfo['company_region']}]
      </p>
    </div>
  </div>

  <!-- Colonne droite : expertise locale + devis + infos pratiques -->
  <div class=\"space-y-6\">
    <!-- Expertise locale -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
    <p class=\"leading-relaxed\">
      [ÉCRIVEZ UN PARAGRAPHE sur l'expertise locale spécifique au service {$serviceName} dans la région {$companyInfo['company_region']}]
    </p>

    <!-- Devis -->
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis ?</h4>
      <p class=\"mb-4\">
        Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour vos {$serviceName}.
      </p>
      <a href=\"https://www.jd-renovation-service.fr/form/propertyType\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">
        Demande de devis
      </a>
    </div>

    <!-- Informations pratiques -->
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        [LISTEZ 4-6 INFORMATIONS PRATIQUES spécifiques au service {$serviceName} : délais, garanties, financement, etc.]
      </ul>
    </div>
  </div>
</div>

INSTRUCTIONS DÉTAILLÉES:
1. ADAPTEZ COMPLÈTEMENT le contenu au service spécifique: {$serviceName}
2. ÉCRIVEZ du contenu PERSONNALISÉ selon le type de service (toiture, façade, isolation, gouttières, etc.)
3. UTILISEZ les informations de l'entreprise: {$companyInfo['company_name']}
4. INTÉGREZ la localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
5. GARDEZ la structure HTML exacte de l'exemple ci-dessus
6. PERSONNALISEZ les prestations selon le service (pas de contenu générique)
7. ÉCRIVEZ du contenu UNIQUE et SPÉCIFIQUE au service
8. ADAPTEZ le vocabulaire et les formulations selon le service
9. INCLUEZ des informations sur le financement, les garanties, les délais
10. VARIEZ le contenu pour éviter les répétitions

FORMAT DE RÉPONSE (JSON):
{
  \"description\": \"<div class=\\\"grid md:grid-cols-2 gap-8\\\">...CONTENU HTML COMPLET EN 2 COLONNES...</div>\",
  \"short_description\": \"[Description courte et accrocheuse pour la page d'accueil - 200-300 caractères]\",
  \"icon\": \"fas fa-[icône appropriée au service]\",
  \"meta_title\": \"[Titre SEO optimisé avec ville/région - max 60 caractères]\",
  \"meta_description\": \"[Description SEO engageante avec localisation et CTA - 150-160 caractères]\",
  \"og_title\": \"[Titre optimisé pour Facebook/LinkedIn - max 60 caractères]\",
  \"og_description\": \"[Description engageante pour les réseaux sociaux - 150-160 caractères]\",
  \"meta_keywords\": \"[Mots-clés SEO séparés par virgules - max 255 caractères]\"
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

Répondez UNIQUEMENT avec le JSON valide, sans texte avant ou après.";
    }
    
    /**
     * Parser la réponse de l'IA
     */
    private function parseAIResponse($content, $serviceName, $shortDescription, $companyInfo)
    {
        try {
            // Nettoyer le contenu pour extraire le JSON
            $jsonStart = strpos($content, '{');
            $jsonEnd = strrpos($content, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $data = json_decode($jsonContent, true);
                
                if ($data) {
                    $description = $data['description'] ?? $shortDescription;
                    $shortDesc = $data['short_description'] ?? $shortDescription;
                    
                    // Utiliser le contenu de l'IA même s'il est court
                    \Log::info('Utilisation du contenu IA généré', [
                        'service_name' => $serviceName,
                        'description_length' => strlen($description),
                        'short_description_length' => strlen($shortDesc)
                    ]);
                    
                    return [
                        'description' => $this->cleanHtmlContent($description),
                        'short_description' => $shortDesc,
                        'icon' => $data['icon'] ?? 'fas fa-tools',
                        'meta_title' => $data['meta_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'],
                        'meta_description' => $data['meta_description'] ?? $shortDesc,
                        'og_title' => $data['og_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'],
                        'og_description' => $data['og_description'] ?? $shortDesc,
                        'meta_keywords' => $data['meta_keywords'] ?? $serviceName . ', ' . $companyInfo['company_city'] . ', ' . $companyInfo['company_region']
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erreur parsing IA: ' . $e->getMessage());
        }
        
        // Fallback si parsing échoue
        return $this->generateFallbackContent($serviceName, $shortDescription, $companyInfo);
    }
    
    /**
     * Contenu par défaut personnalisé selon le service
     */
    private function generatePersonalizedDefaultContent($serviceName, $shortDescription, $companyInfo)
    {
        $serviceType = $this->detectServiceType($serviceName);
        $serviceContent = $this->getServiceSpecificContent($serviceType);
        
        // Générer du contenu personnalisé selon le service
        $description = $this->buildCleanServiceContent($shortDescription);
        
        return [
            'description' => $description,
            'short_description' => $shortDescription,
            'icon' => $this->getServiceIcon($serviceName),
            'meta_title' => $serviceName . ' à ' . $companyInfo['company_city'] . ' - ' . $companyInfo['company_name'],
            'meta_description' => "Service " . $serviceName . " professionnel à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ". Devis gratuit, équipe qualifiée.",
            'og_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'og_description' => "Service " . $serviceName . " professionnel à " . $companyInfo['company_city'] . ". Devis gratuit.",
            'meta_keywords' => $serviceName . ', ' . $companyInfo['company_city'] . ', ' . $companyInfo['company_region'] . ', devis gratuit'
        ];
    }
    
    /**
     * Obtenir l'icône appropriée pour un service
     */
    private function getServiceIcon($serviceName)
    {
        $serviceKey = strtolower($serviceName);
        
        if (strpos($serviceKey, 'toiture') !== false || strpos($serviceKey, 'couverture') !== false) {
            return 'fas fa-home';
        } elseif (strpos($serviceKey, 'façade') !== false || strpos($serviceKey, 'ravalement') !== false) {
            return 'fas fa-building';
        } elseif (strpos($serviceKey, 'isolation') !== false) {
            return 'fas fa-thermometer-half';
        } elseif (strpos($serviceKey, 'gouttière') !== false) {
            return 'fas fa-tint';
        } elseif (strpos($serviceKey, 'maçonnerie') !== false) {
            return 'fas fa-hammer';
        } else {
            return 'fas fa-tools';
        }
    }

    /**
     * Contenu de fallback si IA indisponible
     */
    private function generateFallbackContent($serviceName, $shortDescription, $companyInfo)
    {
        $icons = [
            'couverture' => 'fas fa-home',
            'toiture' => 'fas fa-home',
            'façade' => 'fas fa-building',
            'facade' => 'fas fa-building',
            'isolation' => 'fas fa-thermometer-half',
            'hydrofuge' => 'fas fa-tint',
            'rénovation' => 'fas fa-tools',
            'renovation' => 'fas fa-tools',
            'peinture' => 'fas fa-paint-brush',
            'plomberie' => 'fas fa-wrench',
            'électricité' => 'fas fa-bolt',
            'electricite' => 'fas fa-bolt',
            'gouttières' => 'fas fa-tint',
            'gouttiere' => 'fas fa-tint',
            'évacuations' => 'fas fa-tint',
            'evacuations' => 'fas fa-tint',
            'ouvertures' => 'fas fa-door-open',
            'confort' => 'fas fa-home',
            'fenêtres' => 'fas fa-window-maximize',
            'fenetres' => 'fas fa-window-maximize',
            'combles' => 'fas fa-home',
            'étanchéité' => 'fas fa-shield-alt',
            'etancheite' => 'fas fa-shield-alt'
        ];
        
        $serviceKey = strtolower($serviceName);
        $icon = $icons[$serviceKey] ?? 'fas fa-tools';
        
        // Générer du contenu personnalisé selon le type de service
        $serviceContent = $this->getPersonalizedServiceContent($serviceName, $companyInfo);
        
        $htmlDescription = "<div class=\"grid md:grid-cols-2 gap-8\">
            <div class=\"space-y-6\">
                <div class=\"space-y-4\">
                    <p class=\"text-lg leading-relaxed\">
                        Découvrez notre <strong class=\"text-blue-600\">expertise professionnelle en " . $serviceName . "</strong> à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ". 
                        " . $shortDescription . "
                    </p>
                    <p class=\"text-lg leading-relaxed\">
                        " . $serviceContent['intro'] . "
                    </p>
                    <p class=\"text-lg leading-relaxed\">
                        " . $serviceContent['expertise'] . "
                    </p>
                </div>
                
                <div class=\"bg-blue-50 p-6 rounded-lg\">
                    <h3 class=\"text-xl font-bold text-gray-900 mb-3\"><i class=\"fas fa-star text-yellow-500 mr-2\"></i>Notre Engagement Qualité</h3>
                    <p class=\"leading-relaxed mb-3\">
                        Chez <strong>" . $companyInfo['company_name'] . "</strong>, nous mettons un point d'honneur à garantir la satisfaction totale de nos clients. 
                        Chaque projet est unique et mérite une attention particulière et personnalisée.
                    </p>
                    <p class=\"leading-relaxed\">
                        Nous sélectionnons rigoureusement nos matériaux et appliquons les techniques les plus avancées pour vous offrir 
                        un service de qualité professionnelle qui dépasse vos attentes.
                    </p>
                </div>
                
                <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations " . $serviceName . "</h3>
                <ul class=\"space-y-3\">";
        
        foreach ($serviceContent['prestations'] as $prestation) {
            $htmlDescription .= "
                    <li class=\"flex items-start\">
                        <i class=\"fas fa-check-circle text-green-500 mr-3 mt-1\"></i>
                        <span><strong>" . $prestation . "</strong></span>
                    </li>";
        }
        
        $htmlDescription .= "
                </ul>
                
                <div class=\"bg-green-50 p-6 rounded-lg\">
                    <h3 class=\"text-xl font-bold text-gray-900 mb-3\"><i class=\"fas fa-trophy text-green-600 mr-2\"></i>Pourquoi Choisir Notre Entreprise</h3>
                    <p class=\"leading-relaxed\">
                        Notre réputation dans la région de " . $companyInfo['company_region'] . " repose sur notre engagement qualité, 
                        notre transparence tarifaire et notre capacité à livrer des projets dans les temps. 
                        Nous sommes fiers de compter parmi nos clients de nombreuses familles et entreprises satisfaites.
                    </p>
                </div>
            </div>
            
            <div class=\"space-y-6\">
                <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
                <p class=\"leading-relaxed\">Forts de notre expérience dans le domaine, nous connaissons parfaitement les spécificités de la région pour un service adapté et de qualité.</p>
                
                <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
                    <h4 class=\"text-xl font-bold text-gray-900 mb-3\"><i class=\"fas fa-phone text-blue-600 mr-2\"></i>Besoin d'un Devis ?</h4>
                    <p class=\"mb-4\">Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour votre " . $serviceName . ".</p>
                </div>
                
                <div class=\"bg-gray-50 p-6 rounded-lg\">
                    <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
                    <ul class=\"space-y-2 text-sm\">
                        <li class=\"flex items-center\"><i class=\"fas fa-clock text-blue-600 mr-2\"></i><span>Intervention rapide et efficace dans la région</span></li>
                        <li class=\"flex items-center\"><i class=\"fas fa-calendar text-green-600 mr-2\"></i><span>Disponibilité 7j/7 pour répondre à vos besoins</span></li>
                        <li class=\"flex items-center\"><i class=\"fas fa-shield-alt text-purple-600 mr-2\"></i><span>Garantie de satisfaction pour une toiture impeccable</span></li>
                    </ul>
                </div>
            </div>
        </div>";
        
        // Générer une description courte accrocheuse
        $shortDescGenerated = "Service professionnel de " . $serviceName . " à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ". " . $shortDescription;
        if (strlen($shortDescGenerated) > 300) {
            $shortDescGenerated = substr($shortDescGenerated, 0, 297) . '...';
        }
        
        return [
            'description' => $this->cleanHtmlContent($htmlDescription),
            'short_description' => $shortDescGenerated,
            'icon' => $icon,
            'meta_title' => $serviceName . " à " . $companyInfo['company_city'] . " - " . $companyInfo['company_name'],
            'meta_description' => "Service " . $serviceName . " professionnel à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ". Devis gratuit, équipe qualifiée. Contactez " . $companyInfo['company_name'] . "."
        ];
    }
    
    /**
     * Générer du contenu personnalisé selon le type de service
     */
    private function getPersonalizedServiceContent($serviceName, $companyInfo)
    {
        $serviceKey = strtolower($serviceName);
        
        // Contenu spécifique par type de service
        $serviceContents = [
            'ouvertures' => [
                'intro' => 'Spécialistes des ouvertures de toit, nous transformons vos combles en espaces habitables confortables. Nos solutions d\'ouvertures de toit apportent lumière naturelle et ventilation optimale à votre habitation.',
                'prestations' => [
                    'Pose de fenêtres de toit VELUX et autres marques',
                    'Installation de puits de lumière pour combles',
                    'Ouvertures de toit sur mesure',
                    'Amélioration de l\'éclairage naturel',
                    'Ventilation et aération des combles',
                    'Isolation thermique et phonique',
                    'Étanchéité parfaite garantie',
                    'Rénovation d\'ouvertures existantes'
                ],
                'expertise' => 'Notre expertise en ouvertures de toit nous permet de conseiller la solution la plus adaptée à votre type de toiture et à vos besoins d\'éclairage et de ventilation.'
            ],
            'confort' => [
                'intro' => 'Améliorez le confort de votre habitation avec nos solutions d\'isolation et d\'étanchéité. Nous optimisons l\'efficacité énergétique de votre toiture pour un confort optimal toute l\'année.',
                'prestations' => [
                    'Isolation des combles perdus et aménagés',
                    'Pose d\'écrans de sous-toiture',
                    'Amélioration de l\'étanchéité à l\'air',
                    'Traitement des ponts thermiques',
                    'Ventilation des combles',
                    'Isolation phonique',
                    'Régulation de l\'humidité',
                    'Audit énergétique gratuit'
                ],
                'expertise' => 'Notre approche globale du confort intérieur combine isolation thermique, gestion de l\'humidité et ventilation pour un habitat sain et économe en énergie.'
            ],
            'gouttières' => [
                'intro' => 'Protégez votre habitation avec nos solutions d\'évacuation des eaux pluviales. Nos systèmes de gouttières et d\'évacuation garantissent une protection optimale contre les infiltrations.',
                'prestations' => [
                    'Installation de gouttières PVC et aluminium',
                    'Pose de descentes d\'eau pluviale',
                    'Raccordement aux réseaux d\'évacuation',
                    'Protection contre les débordements',
                    'Nettoyage et entretien des gouttières',
                    'Réparation et remplacement',
                    'Systèmes de récupération d\'eau',
                    'Drainage périphérique'
                ],
                'expertise' => 'Nous maîtrisons tous les systèmes d\'évacuation des eaux pluviales pour protéger efficacement votre bâtiment contre les infiltrations et l\'humidité.'
            ],
            'toiture' => [
                'intro' => 'Experts en couverture, nous assurons la protection et l\'étanchéité de votre toiture. De la réparation à la rénovation complète, nous garantissons la durabilité de votre couverture.',
                'prestations' => [
                    'Réparation de toiture en urgence',
                    'Rénovation complète de couverture',
                    'Pose de tuiles et ardoises',
                    'Étanchéité de toiture plate',
                    'Traitement anti-mousse',
                    'Réparation de fuites',
                    'Ventilation de toiture',
                    'Isolation sous toiture'
                ],
                'expertise' => 'Notre savoir-faire en couverture nous permet d\'intervenir sur tous types de toitures avec des matériaux de qualité et des techniques éprouvées.'
            ],
            'isolation' => [
                'intro' => 'Optimisez les performances énergétiques de votre habitation avec nos solutions d\'isolation. Nous réduisons vos factures de chauffage tout en améliorant votre confort.',
                'prestations' => [
                    'Isolation des combles perdus',
                    'Isolation des murs par l\'intérieur',
                    'Isolation des murs par l\'extérieur',
                    'Isolation des sols',
                    'Traitement des ponts thermiques',
                    'Isolation phonique',
                    'Étanchéité à l\'air',
                    'Ventilation mécanique contrôlée'
                ],
                'expertise' => 'Notre expertise en isolation thermique et phonique vous garantit des économies d\'énergie significatives et un confort de vie optimal.'
            ]
        ];
        
        // Trouver le contenu le plus approprié
        foreach ($serviceContents as $key => $content) {
            if (strpos($serviceKey, $key) !== false) {
                return $content;
            }
        }
        
        // Contenu générique si aucun match
        return [
            'intro' => 'Spécialistes de la rénovation, nous vous accompagnons dans l\'amélioration de votre habitation. Notre expertise technique et notre savoir-faire artisanal garantissent des résultats de qualité.',
            'prestations' => [
                'Diagnostic complet et gratuit',
                'Matériaux de qualité supérieure',
                'Équipe d\'artisans qualifiés',
                'Respect des délais et du budget',
                'Garantie décennale',
                'Suivi personnalisé',
                'Nettoyage et remise en état',
                'Conseils d\'entretien'
            ],
            'expertise' => 'Notre expérience dans le domaine de la rénovation nous permet de vous proposer des solutions adaptées à vos besoins spécifiques.'
        ];
    }
    
    /**
     * Nettoyer le contenu HTML pour éviter les problèmes d'affichage
     */
    private function cleanHtmlContent($html)
    {
        // Nettoyer les caractères problématiques
        $html = str_replace(['&amp;', '&lt;', '&gt;'], ['&', '<', '>'], $html);
        
        // Échapper les caractères spéciaux qui peuvent causer des problèmes
        $html = htmlspecialchars_decode($html, ENT_QUOTES);
        
        // Nettoyer les entités HTML malformées
        $html = preg_replace('/&(?![a-zA-Z0-9#]+;)/', '&amp;', $html);
        
        // Supprimer les balises vides ou malformées
        $html = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/', '', $html);
        
        // Nettoyer les espaces multiples
        $html = preg_replace('/\s+/', ' ', $html);
        
        // Supprimer les répétitions de phrases
        $html = $this->removeRepeatedPhrases($html);
        
        // S'assurer que les balises sont bien fermées
        $html = $this->fixUnclosedTags($html);
        
        return trim($html);
    }
    
    /**
     * Supprimer les phrases répétées dans le contenu
     */
    private function removeRepeatedPhrases($html)
    {
        // Extraire le texte sans les balises HTML pour détecter les répétitions
        $text = strip_tags($html);
        
        // Détecter les phrases répétées (plus de 3 mots identiques consécutifs)
        $sentences = preg_split('/[.!?]+/', $text);
        $cleanedSentences = [];
        $seenSentences = [];
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            // Normaliser la phrase (supprimer espaces multiples, minuscules)
            $normalized = strtolower(preg_replace('/\s+/', ' ', $sentence));
            
            // Vérifier si cette phrase a déjà été vue
            if (!in_array($normalized, $seenSentences)) {
                $cleanedSentences[] = $sentence;
                $seenSentences[] = $normalized;
            }
        }
        
        // Si on a nettoyé des répétitions, reconstruire le HTML
        if (count($cleanedSentences) < count($sentences)) {
            $cleanedText = implode('. ', $cleanedSentences);
            
            // Reconstruire le HTML en gardant la structure
            $html = $this->reconstructHtmlFromText($html, $cleanedText);
        }
        
        return $html;
    }
    
    /**
     * Reconstruire le HTML à partir du texte nettoyé
     */
    private function reconstructHtmlFromText($originalHtml, $cleanedText)
    {
        // Utiliser la méthode buildCleanServiceContent pour reconstruire avec la bonne structure
        return $this->buildCleanServiceContent($cleanedText);
    }
    
    /**
     * Supprimer le contenu dupliqué spécifiquement pour les répétitions de phrases
     */
    private function removeDuplicateContent($html)
    {
        // D'abord, essayer de nettoyer les répétitions simples dans le HTML
        $cleanedHtml = $this->removeSimpleDuplicates($html);
        
        // Si le HTML a été modifié, le retourner
        if ($cleanedHtml !== $html) {
            return $cleanedHtml;
        }
        
        // Sinon, extraire le texte et nettoyer les répétitions
        $text = strip_tags($html);
        
        // Détecter les répétitions de phrases complètes
        $sentences = preg_split('/[.!?]+/', $text);
        $uniqueSentences = [];
        $seenPhrases = [];
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence)) continue;
            
            // Normaliser la phrase
            $normalized = strtolower(preg_replace('/\s+/', ' ', $sentence));
            
            // Vérifier si cette phrase est déjà apparue
            if (!in_array($normalized, $seenPhrases)) {
                $uniqueSentences[] = $sentence;
                $seenPhrases[] = $normalized;
            }
        }
        
        // Si on a trouvé des doublons, reconstruire le contenu
        if (count($uniqueSentences) < count($sentences)) {
            $cleanedText = implode('. ', $uniqueSentences);
            
            // Reconstruire avec une structure HTML propre
            return $this->buildCleanServiceContent($cleanedText);
        }
        
        // Si le contenu semble déjà mal formaté, le reconstruire complètement
        if (strpos($html, '<div class="service-content">') !== false && strpos($html, '<p>') !== false) {
            return $this->buildCleanServiceContent($text);
        }
        
        return $html;
    }
    
    /**
     * Supprimer les répétitions simples dans le HTML
     */
    private function removeSimpleDuplicates($html)
    {
        // Détecter les répétitions de phrases dans le HTML
        $patterns = [
            // Répétitions de "Service professionnel de..."
            '/(Service professionnel de[^<]+)(\s*\1)+/i',
            // Répétitions de phrases complètes
            '/([^.!?]+[.!?])(\s*\1)+/',
            // Répétitions de mots isolés
            '/(\b\w+\b)(\s+\1){2,}/i'
        ];
        
        $cleanedHtml = $html;
        
        foreach ($patterns as $pattern) {
            $cleanedHtml = preg_replace($pattern, '$1', $cleanedHtml);
        }
        
        // Nettoyer les espaces multiples
        $cleanedHtml = preg_replace('/\s+/', ' ', $cleanedHtml);
        
        return $cleanedHtml;
    }
    
    /**
     * Construire un contenu de service propre sans répétitions
     */
    private function buildCleanServiceContent($text)
    {
        $companyName = setting('company_name', 'Notre Entreprise');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companyCity = setting('company_city', '');
        $companyRegion = setting('company_region', '');
        
        // Détecter le type de service à partir du texte
        $serviceType = $this->detectServiceType($text);
        $serviceContent = $this->getServiceSpecificContent($serviceType);
        
        $html = '<div class="grid md:grid-cols-2 gap-8">';
        
        // Colonne gauche : description + engagement + prestations
        $html .= '<div class="space-y-6">';
        
        // Introduction générale
        $html .= '<div class="space-y-4">';
        $html .= '<p class="text-lg leading-relaxed">';
        $html .= 'Découvrez notre <strong class="text-blue-600">expertise professionnelle en ' . $serviceContent['title'] . '</strong> à ' . $companyCity . ', ' . $companyRegion . '. Nous assurons la protection et l\'étanchéité de votre toiture, de la réparation à la rénovation complète, avec des matériaux de qualité et des techniques éprouvées.';
        $html .= '</p>';
        $html .= '<p class="text-lg leading-relaxed">';
        $html .= 'Experts en couverture, nous garantissons la durabilité de votre toiture et intervenons sur tous types de toitures : tuiles, ardoises, toitures plates, etc.';
        $html .= '</p>';
        $html .= '<p class="text-lg leading-relaxed">';
        $html .= 'Chaque projet bénéficie d\'une attention personnalisée et d\'un accompagnement complet pour garantir la satisfaction de nos clients.';
        $html .= '</p>';
        $html .= '</div>';
        
        // Engagement qualité
        $html .= '<div class="bg-blue-50 p-6 rounded-lg">';
        $html .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>';
        $html .= '<p class="leading-relaxed mb-3">';
        $html .= 'Chez <strong>' . $companyName . '</strong>, nous mettons un point d\'honneur à garantir la satisfaction totale de nos clients. Chaque projet est unique et mérite une attention particulière.';
        $html .= '</p>';
        $html .= '<p class="leading-relaxed">';
        $html .= 'Nous sélectionnons rigoureusement nos matériaux et appliquons les techniques les plus avancées pour vous offrir un service professionnel de qualité, respectueux des normes et de l\'environnement.';
        $html .= '</p>';
        $html .= '</div>';
        
        // Prestations
        $html .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $serviceContent['title'] . '</h3>';
        $html .= '<ul class="space-y-3">';
        foreach ($serviceContent['prestations'] as $prestation) {
            $html .= '<li class="flex items-start"><span><strong>' . $prestation . '</strong></span></li>';
        }
        $html .= '</ul>';
        
        // Pourquoi choisir notre entreprise
        $html .= '<div class="bg-green-50 p-6 rounded-lg">';
        $html .= '<h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>';
        $html .= '<p class="leading-relaxed">';
        $html .= 'Notre réputation à ' . $companyCity . ' et en ' . $companyRegion . ' repose sur notre engagement qualité, notre transparence tarifaire et notre capacité à livrer les projets dans les délais. Nous avons déjà satisfait de nombreuses familles et entreprises.';
        $html .= '</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Colonne droite : expertise locale + devis + infos pratiques
        $html .= '<div class="space-y-6">';
        
        // Expertise locale
        $html .= '<h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>';
        $html .= '<p class="leading-relaxed">';
        $html .= 'Forts de notre expérience, nous connaissons parfaitement les spécificités de la région pour un service adapté et de qualité.';
        $html .= '</p>';
        
        // Devis
        $html .= '<div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">';
        $html .= '<h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>';
        $html .= '<p class="mb-4">';
        $html .= 'Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour vos ' . $serviceContent['title'] . '.';
        $html .= '</p>';
        $html .= '<a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">';
        $html .= 'Demande de devis';
        $html .= '</a>';
        $html .= '</div>';
        
        // Informations pratiques
        $html .= '<div class="bg-gray-50 p-6 rounded-lg">';
        $html .= '<h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>';
        $html .= '<ul class="space-y-2 text-sm">';
        $html .= '<li class="flex items-center"><span>Intervention rapide et efficace dans la région</span></li>';
        $html .= '<li class="flex items-center"><span>Disponibilité 7j/7 pour répondre à vos besoins</span></li>';
        $html .= '<li class="flex items-center"><span>Garantie de satisfaction pour une toiture impeccable</span></li>';
        $html .= '<li class="flex items-center"><span>Devis gratuit et sans engagement</span></li>';
        $html .= '<li class="flex items-center"><span>Paiement échelonné possible</span></li>';
        $html .= '<li class="flex items-center"><span>Aides financières disponibles</span></li>';
        $html .= '<li class="flex items-center"><span>Crédit d\'impôt éligible</span></li>';
        $html .= '</ul>';
        $html .= '</div>';
        
        // Garanties et assurances
        $html .= '<div class="bg-yellow-50 p-6 rounded-lg">';
        $html .= '<h4 class="text-lg font-bold text-gray-900 mb-3">Nos Garanties</h4>';
        $html .= '<ul class="space-y-2 text-sm">';
        $html .= '<li class="flex items-center"><span>Garantie décennale sur les travaux</span></li>';
        $html .= '<li class="flex items-center"><span>Assurance responsabilité civile</span></li>';
        $html .= '<li class="flex items-center"><span>Garantie de satisfaction client</span></li>';
        $html .= '<li class="flex items-center"><span>Suivi post-travaux inclus</span></li>';
        $html .= '</ul>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Détecter le type de service à partir du texte
     */
    private function detectServiceType($text)
    {
        $text = strtolower($text);
        
        // Mots-clés pour chaque type de service
        $keywords = [
            'toiture' => ['toiture', 'toit', 'couverture', 'tuile', 'ardoise', 'charpente', 'étanchéité', 'réparation toiture', 'rénovation toiture'],
            'facade' => ['façade', 'facade', 'ravalement', 'enduit', 'crépi', 'peinture extérieure', 'nettoyage façade', 'rénovation façade'],
            'gouttiere' => ['gouttière', 'gouttiere', 'évacuation', 'eau pluviale', 'descente', 'drainage', 'installation gouttière'],
            'isolation' => ['isolation', 'isolant', 'thermique', 'phonique', 'combles', 'pont thermique', 'isolation toiture', 'isolation façade'],
            'maconnerie' => ['maçonnerie', 'maconnerie', 'petit', 'enduit', 'joint', 'réparation mur'],
            'ramonage' => ['ramonage', 'cheminée', 'cheminée', 'conduit', 'nettoyage cheminée'],
            'ouverture' => ['ouverture', 'fenêtre', 'velux', 'puits de lumière', 'éclairage naturel']
        ];
        
        $scores = [];
        foreach ($keywords as $type => $words) {
            $score = 0;
            foreach ($words as $word) {
                if (strpos($text, $word) !== false) {
                    $score++;
                }
            }
            $scores[$type] = $score;
        }
        
        // Retourner le type avec le score le plus élevé
        $bestType = array_keys($scores, max($scores))[0];
        
        return $scores[$bestType] > 0 ? $bestType : 'toiture';
    }
    
    /**
     * Obtenir le contenu spécifique selon le type de service
     */
    private function getServiceSpecificContent($serviceType)
    {
        $contents = [
            'toiture' => [
                'title' => 'Travaux de toiture',
                'prestations' => [
                    'Réparation de toiture en urgence',
                    'Rénovation complète de couverture',
                    'Pose de tuiles et ardoises',
                    'Étanchéité de toiture plate',
                    'Traitement anti-mousse',
                    'Réparation de fuites',
                    'Ventilation de toiture',
                    'Isolation sous toiture'
                ]
            ],
            'facade' => [
                'title' => 'Ravalement & façades',
                'prestations' => [
                    'Ravalement de façade complet',
                    'Nettoyage haute pression',
                    'Traitement anti-mousse',
                    'Réparation des enduits',
                    'Peinture extérieure',
                    'Isolation thermique par l\'extérieur',
                    'Rénovation des joints',
                    'Protection hydrofuge'
                ]
            ],
            'gouttiere' => [
                'title' => 'Gouttières & évacuations',
                'prestations' => [
                    'Installation de gouttières',
                    'Réparation et remplacement',
                    'Nettoyage et entretien',
                    'Évacuation des eaux pluviales',
                    'Protection contre les débordements',
                    'Installation de descentes',
                    'Raccordement au réseau',
                    'Maintenance préventive'
                ]
            ],
            'maconnerie' => [
                'title' => 'Maçonnerie & petits travaux',
                'prestations' => [
                    'Réparation de murs',
                    'Enduits et crépis',
                    'Petits travaux de maçonnerie',
                    'Réparation de joints',
                    'Restauration de pierres',
                    'Réparation de fissures',
                    'Travaux de finition',
                    'Rénovation de façades'
                ]
            ],
            'ramonage' => [
                'title' => 'Ramonage & cheminées',
                'prestations' => [
                    'Ramonage de cheminées',
                    'Nettoyage des conduits',
                    'Inspection vidéo',
                    'Réparation de conduits',
                    'Installation de chapeaux',
                    'Débouchage d\'urgence',
                    'Contrôle de tirage',
                    'Entretien préventif'
                ]
            ],
            'ouverture' => [
                'title' => 'Ouvertures & confort',
                'prestations' => [
                    'Installation de fenêtres',
                    'Pose de portes',
                    'Isolation thermique',
                    'Ventilation naturelle',
                    'Étanchéité à l\'air',
                    'Rénovation d\'ouvertures',
                    'Amélioration du confort',
                    'Optimisation énergétique'
                ]
            ],
            'general' => [
                'title' => 'Services',
                'prestations' => [
                    'Diagnostic complet et gratuit',
                    'Matériaux de qualité supérieure',
                    'Équipe d\'artisans qualifiés',
                    'Respect des délais et du budget',
                    'Garantie décennale',
                    'Suivi personnalisé',
                    'Nettoyage et remise en état',
                    'Conseils d\'entretien'
                ]
            ]
        ];
        
        return $contents[$serviceType] ?? $contents['general'];
    }
    
    /**
     * Corriger les balises HTML non fermées
     */
    private function fixUnclosedTags($html)
    {
        // Liste des balises auto-fermantes
        $selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link'];
        
        // Balises qui doivent être fermées
        $tagsToClose = ['div', 'p', 'span', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li'];
        
        foreach ($tagsToClose as $tag) {
            // Compter les balises ouvrantes et fermantes
            $openCount = substr_count($html, "<$tag");
            $closeCount = substr_count($html, "</$tag>");
            
            // Ajouter les balises fermantes manquantes
            if ($openCount > $closeCount) {
                $missing = $openCount - $closeCount;
                for ($i = 0; $i < $missing; $i++) {
                    $html .= "</$tag>";
                }
            }
        }
        
        return $html;
    }
    
    /**
     * Générer un contenu de fallback étendu avec plus de détails
     */
    private function generateExtendedFallbackContent($serviceName, $shortDescription, $companyInfo)
    {
        $icons = [
            'couverture' => 'fas fa-home',
            'toiture' => 'fas fa-home',
            'façade' => 'fas fa-building',
            'facade' => 'fas fa-building',
            'isolation' => 'fas fa-thermometer-half',
            'hydrofuge' => 'fas fa-tint',
            'rénovation' => 'fas fa-tools',
            'renovation' => 'fas fa-tools',
            'gouttières' => 'fas fa-tint',
            'gouttiere' => 'fas fa-tint',
            'évacuations' => 'fas fa-tint',
            'evacuations' => 'fas fa-tint',
            'ouvertures' => 'fas fa-door-open',
            'confort' => 'fas fa-home',
            'fenêtres' => 'fas fa-window-maximize',
            'fenetres' => 'fas fa-window-maximize',
            'combles' => 'fas fa-home',
            'étanchéité' => 'fas fa-shield-alt',
            'etancheite' => 'fas fa-shield-alt'
        ];

        $serviceKey = strtolower($serviceName);
        $icon = $icons[$serviceKey] ?? 'fas fa-tools';

        // Générer du contenu personnalisé selon le type de service
        $serviceContent = $this->getPersonalizedServiceContent($serviceName, $companyInfo);

        $htmlDescription = "<div class=\"space-y-8\">
            <!-- Section principale -->
            <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-8 rounded-2xl border-l-4 border-blue-600\">
                <h2 class=\"text-3xl font-bold text-gray-900 mb-6\">
                    <i class=\"" . $icon . " text-blue-600 mr-3\"></i>
                    Expertise Professionnelle en " . $serviceName . "
                </h2>
                <p class=\"text-xl leading-relaxed text-gray-700 mb-6\">
                    Découvrez notre <strong class=\"text-blue-600\">expertise professionnelle en " . $serviceName . "</strong> à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ".
                    " . $shortDescription . "
                </p>
                <p class=\"text-lg leading-relaxed text-gray-600\">
                    " . $serviceContent['intro'] . "
                </p>
            </div>

            <!-- Section détaillée -->
            <div class=\"grid md:grid-cols-2 gap-8\">
                <div class=\"space-y-6\">
                    <div class=\"bg-white p-6 rounded-xl shadow-lg\">
                        <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">
                            <i class=\"fas fa-star text-yellow-500 mr-2\"></i>
                            Notre Expertise
                        </h3>
                        <p class=\"text-lg leading-relaxed text-gray-700\">
                            " . $serviceContent['expertise'] . "
                        </p>
                    </div>

                    <div class=\"bg-blue-50 p-6 rounded-xl\">
                        <h3 class=\"text-xl font-bold text-gray-900 mb-3\">
                            <i class=\"fas fa-trophy text-blue-600 mr-2\"></i>
                            Notre Engagement Qualité
                        </h3>
                        <p class=\"leading-relaxed mb-3\">
                            Chez <strong>" . $companyInfo['company_name'] . "</strong>, nous mettons un point d'honneur à garantir la satisfaction totale de nos clients.
                            Chaque projet est unique et mérite une attention particulière et personnalisée.
                        </p>
                        <p class=\"leading-relaxed\">
                            Nous sélectionnons rigoureusement nos matériaux et appliquons les techniques les plus avancées pour vous offrir
                            un service de qualité professionnelle qui dépasse vos attentes.
                        </p>
                    </div>
                </div>

                <div class=\"space-y-6\">
                    <div class=\"bg-white p-6 rounded-xl shadow-lg\">
                        <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">
                            <i class=\"fas fa-list-check text-green-600 mr-2\"></i>
                            Nos Prestations " . $serviceName . "
                        </h3>
                        <ul class=\"space-y-3\">";

        foreach ($serviceContent['prestations'] as $prestation) {
            $htmlDescription .= "
                            <li class=\"flex items-start\">
                                <i class=\"fas fa-check-circle text-green-500 mr-3 mt-1\"></i>
                                <span class=\"text-gray-700\"><strong>" . $prestation . "</strong></span>
                            </li>";
        }

        $htmlDescription .= "
                        </ul>
                    </div>

                    <div class=\"bg-green-50 p-6 rounded-xl\">
                        <h3 class=\"text-xl font-bold text-gray-900 mb-3\">
                            <i class=\"fas fa-heart text-green-600 mr-2\"></i>
                            Pourquoi Choisir Notre Entreprise
                        </h3>
                        <p class=\"leading-relaxed text-gray-700\">
                            Notre réputation dans la région de " . $companyInfo['company_region'] . " repose sur notre engagement qualité,
                            notre transparence tarifaire et notre capacité à livrer des projets dans les temps.
                            Nous sommes fiers de compter parmi nos clients de nombreuses familles et entreprises satisfaites.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section expertise locale -->
            <div class=\"bg-gradient-to-r from-gray-50 to-blue-50 p-8 rounded-2xl\">
                <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">
                    <i class=\"fas fa-map-marker-alt text-blue-600 mr-2\"></i>
                    Notre Expertise Locale
                </h3>
                <p class=\"text-lg leading-relaxed text-gray-700 mb-6\">
                    Forts de notre expérience dans le domaine, nous connaissons parfaitement les spécificités de la région pour un service adapté et de qualité.
                </p>
                
                <div class=\"grid md:grid-cols-2 gap-6\">
                    <div class=\"bg-white p-6 rounded-xl shadow-md\">
                        <h4 class=\"text-xl font-bold text-gray-900 mb-3\">
                            <i class=\"fas fa-phone text-blue-600 mr-2\"></i>
                            Besoin d'un Devis ?
                        </h4>
                        <p class=\"mb-4 text-gray-700\">Contactez-nous dès maintenant pour un devis personnalisé et gratuit pour votre " . $serviceName . ".</p>
                    </div>
                    
                    <div class=\"bg-white p-6 rounded-xl shadow-md\">
                        <h4 class=\"text-lg font-bold text-gray-900 mb-3\">
                            <i class=\"fas fa-info-circle text-green-600 mr-2\"></i>
                            Informations Pratiques
                        </h4>
                        <ul class=\"space-y-2 text-sm text-gray-600\">
                            <li class=\"flex items-center\">
                                <i class=\"fas fa-clock text-blue-600 mr-2\"></i>
                                <span>Intervention rapide et efficace dans la région</span>
                            </li>
                            <li class=\"flex items-center\">
                                <i class=\"fas fa-calendar text-green-600 mr-2\"></i>
                                <span>Disponibilité 7j/7 pour répondre à vos besoins</span>
                            </li>
                            <li class=\"flex items-center\">
                                <i class=\"fas fa-shield-alt text-purple-600 mr-2\"></i>
                                <span>Garantie de satisfaction pour un résultat impeccable</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>";
        
        // Générer une description courte accrocheuse
        $shortDescGenerated = "Service professionnel de " . $serviceName . " à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ". " . $shortDescription;
        if (strlen($shortDescGenerated) > 300) {
            $shortDescGenerated = substr($shortDescGenerated, 0, 297) . '...';
        }
        
        return [
            'description' => $this->cleanHtmlContent($htmlDescription),
            'short_description' => $shortDescGenerated,
            'icon' => $icon,
            'meta_title' => $serviceName . " à " . $companyInfo['company_city'] . " - " . $companyInfo['company_name'],
            'meta_description' => "Service " . $serviceName . " professionnel à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ". Devis gratuit, équipe qualifiée. Contactez " . $companyInfo['company_name'] . "."
        ];
    }
    
    /**
     * Récupérer le département à partir du code postal
     */
    private function getDepartmentFromPostalCode($postalCode)
    {
        if (empty($postalCode)) {
            return null;
        }
        
        try {
            $city = \App\Models\City::where('postal_code', $postalCode)->first();
            return $city ? $city->department : null;
        } catch (\Exception $e) {
            \Log::error('Erreur récupération département: ' . $e->getMessage());
            return null;
        }
    }
}








