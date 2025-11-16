<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Review;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConfigController extends Controller
{
    /**
     * Show initial setup wizard
     */
    public function showSetup()
    {
        // Redirect to admin config if setup is already completed
        if (Setting::isSetupCompleted()) {
            return redirect()->route('config.index');
        }
        
        return view('config.setup');
    }

    /**
     * Process initial setup
     */
    public function processSetup(Request $request)
    {
        $validated = $request->validate([
            // Company Info
            'company_name' => 'required|string|max:255',
            'company_legal_name' => 'nullable|string|max:255',
            'company_slogan' => 'nullable|string|max:255',
            'company_description' => 'nullable|string',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'company_address' => 'nullable|string',
            'company_city' => 'nullable|string|max:100',
            'company_postal_code' => 'nullable|string|max:10',
            'company_country' => 'nullable|string|max:100',
            
            // Admin Credentials
            'admin_username' => 'required|string|min:4|max:50',
            'admin_password' => 'required|string|min:6',
            
            // Email Settings
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string',
            
            // Logo
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
        ]);

        // Save company settings
        Setting::set('company_name', $validated['company_name'], 'string', 'company');
        Setting::set('company_legal_name', $validated['company_legal_name'] ?? '', 'string', 'company');
        Setting::set('company_slogan', $validated['company_slogan'] ?? '', 'string', 'company');
        Setting::set('company_description', $validated['company_description'] ?? '', 'text', 'company');
        Setting::set('company_phone', $validated['company_phone'], 'string', 'company');
        Setting::set('company_phone_raw', preg_replace('/[^0-9]/', '', $validated['company_phone']), 'string', 'company');
        Setting::set('company_email', $validated['company_email'], 'string', 'company');
        Setting::set('company_address', $validated['company_address'] ?? '', 'string', 'company');
        Setting::set('company_city', $validated['company_city'] ?? '', 'string', 'company');
        Setting::set('company_postal_code', $validated['company_postal_code'] ?? '', 'string', 'company');
        Setting::set('company_country', $validated['company_country'] ?? 'France', 'string', 'company');

        // Save admin credentials
        Setting::set('admin_username', $validated['admin_username'], 'string', 'admin');
        Setting::set('admin_password', bcrypt($validated['admin_password']), 'string', 'admin');

        // Save email settings
        Setting::set('mail_from_address', $validated['mail_from_address'] ?? $validated['company_email'], 'string', 'email');
        Setting::set('mail_from_name', $validated['mail_from_name'] ?? $validated['company_name'], 'string', 'email');

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('logo'), $logoName);
            Setting::set('company_logo', 'logo/' . $logoName, 'file', 'branding');
        }

        // Mark setup as completed
        Setting::markSetupCompleted();

        // Clear cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return redirect()->route('home')->with('success', 'Configuration initiale terminée avec succès !');
    }

    /**
     * Show config dashboard
     */
    public function index()
    {
        try {
            if (!Setting::isSetupCompleted()) {
                return redirect()->route('config.setup');
            }

            // Gérer le cas où la base de données n'est pas encore configurée
            try {
                $allSettings = Setting::all();
            } catch (\Exception $e) {
                Log::error('Erreur lors de la récupération des settings: ' . $e->getMessage());
                // Si la table n'existe pas, rediriger vers le setup
                return redirect()->route('config.setup')->with('error', 'La base de données n\'est pas encore configurée. Veuillez exécuter les migrations.');
            }
            
            // Grouper par 'group' et s'assurer que tous les groupes existent
            $settings = [
                'company' => $allSettings->where('group', 'company'),
                'branding' => $allSettings->where('group', 'branding'),
                'email' => $allSettings->where('group', 'email'),
                'social' => $allSettings->where('group', 'social'),
                'seo' => $allSettings->where('group', 'seo'),
                'reviews' => $allSettings->where('group', 'reviews'),
                'general' => $allSettings->where('group', 'general'),
            ];
            
            // Gérer le cas où la table reviews n'existe pas
            try {
                $reviews = Review::orderBy('display_order')->get();
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la récupération des reviews: ' . $e->getMessage());
                $reviews = collect([]); // Utiliser une collection vide si la table n'existe pas
            }
            
            return view('config.index', compact('settings', 'reviews'));
        } catch (\Exception $e) {
            Log::error('Erreur dans ConfigController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Si c'est une erreur de base de données, rediriger vers le setup
            if (strpos($e->getMessage(), 'SQLSTATE') !== false || strpos($e->getMessage(), 'table') !== false) {
                return redirect()->route('config.setup')->with('error', 'Erreur de base de données. Veuillez vérifier votre configuration et exécuter les migrations.');
            }
            
            // Sinon, afficher une erreur générique
            return response()->view('errors.500', [
                'message' => 'Une erreur est survenue lors du chargement de la page de configuration. Veuillez vérifier les logs pour plus de détails.'
            ], 500);
        }
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            $type = is_bool($value) ? 'boolean' : 'string';
            Setting::set($key, $value, $type, 'general');
        }

        Setting::clearCache();

        return back()->with('success', 'Paramètres généraux mis à jour avec succès !');
    }

    /**
     * Update company settings
     */
    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_legal_name' => 'nullable|string|max:255',
            'company_slogan' => 'nullable|string|max:255',
            'company_description' => 'nullable|string',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'company_address' => 'nullable|string',
            'company_city' => 'nullable|string|max:100',
            'google_maps_api_key' => 'nullable|string|max:255',
            'company_postal_code' => 'nullable|string|max:10',
            'company_country' => 'nullable|string|max:100',
            'company_siret' => 'nullable|string|max:20',
            'company_vat' => 'nullable|string|max:20',
            'company_certifications' => 'nullable|string',
            'company_hours' => 'nullable|string',
        ]);

        Setting::set('company_name', $validated['company_name'], 'string', 'company');
        Setting::set('company_legal_name', $validated['company_legal_name'] ?? '', 'string', 'company');
        Setting::set('company_slogan', $validated['company_slogan'] ?? '', 'string', 'company');
        Setting::set('company_description', $validated['company_description'] ?? '', 'text', 'company');
        Setting::set('company_phone', $validated['company_phone'], 'string', 'company');
        Setting::set('company_phone_raw', preg_replace('/[^0-9]/', '', $validated['company_phone']), 'string', 'company');
        Setting::set('company_email', $validated['company_email'], 'string', 'company');
        Setting::set('company_address', $validated['company_address'] ?? '', 'string', 'company');
        Setting::set('company_city', $validated['company_city'] ?? '', 'string', 'company');
        Setting::set('company_postal_code', $validated['company_postal_code'] ?? '', 'string', 'company');
        Setting::set('company_country', $validated['company_country'] ?? 'France', 'string', 'company');
        
        if (isset($validated['google_maps_api_key'])) {
            Setting::set('google_maps_api_key', $validated['google_maps_api_key'], 'string', 'company');
        }
        
        Setting::set('company_siret', $validated['company_siret'] ?? '', 'string', 'company');
        Setting::set('company_vat', $validated['company_vat'] ?? '', 'string', 'company');
        Setting::set('company_certifications', $validated['company_certifications'] ?? '', 'string', 'company');
        Setting::set('company_hours', $validated['company_hours'] ?? '', 'string', 'company');

        Setting::clearCache();

        return back()->with('success', 'Informations de l\'entreprise mises à jour avec succès !');
    }

    /**
     * Update branding settings
     */
    public function updateBranding(Request $request)
    {
        $validated = $request->validate([
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'favicon' => 'nullable|image|mimes:ico,png,jpg|max:512',
            'contact_hero_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'simulator_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
            'primary_font' => 'nullable|string|max:50',
            'font_size' => 'nullable|string|max:10',
        ]);

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $logo = $request->file('company_logo');
            $logoName = 'company-logo-' . time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads'), $logoName);
            Setting::set('company_logo', 'uploads/' . $logoName, 'file', 'branding');
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            
            // Supprimer l'ancien favicon s'il existe
            $oldFavicon = Setting::get('site_favicon');
            if ($oldFavicon && file_exists(public_path($oldFavicon))) {
                @unlink(public_path($oldFavicon));
            }
            
            // Supprimer aussi l'ancien favicon.ico si présent
            $oldFaviconIco = public_path('favicon.ico');
            if (file_exists($oldFaviconIco)) {
                @unlink($oldFaviconIco);
            }
            
            $extension = $favicon->getClientOriginalExtension();
            $faviconName = 'favicon-' . time() . '.' . $extension;
            $favicon->move(public_path(), $faviconName);
            Setting::set('site_favicon', $faviconName, 'file', 'branding');
            
            // Créer aussi un favicon.ico pour Google (copie ou conversion)
            try {
                $sourcePath = public_path($faviconName);
                $icoPath = public_path('favicon.ico');
                
                // Supprimer l'ancien favicon.ico s'il existe
                if (file_exists($icoPath)) {
                    @unlink($icoPath);
                }
                
                // Copier le fichier vers favicon.ico
                // (les navigateurs modernes acceptent PNG comme favicon.ico)
                if (copy($sourcePath, $icoPath)) {
                    \Log::info('favicon.ico créé avec succès à partir de ' . $faviconName);
                } else {
                    \Log::warning('Impossible de copier le favicon vers favicon.ico');
                }
            } catch (\Exception $e) {
                \Log::warning('Impossible de créer favicon.ico: ' . $e->getMessage());
            }
        }

        // Handle contact hero image upload
        if ($request->hasFile('contact_hero_image')) {
            $heroImage = $request->file('contact_hero_image');
            $heroImageName = 'contact-hero-' . time() . '.' . $heroImage->getClientOriginalExtension();
            
            // Créer le dossier uploads s'il n'existe pas
            $uploadsDir = public_path('uploads');
            if (!file_exists($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            // Supprimer l'ancienne image si elle existe
            $oldImage = Setting::get('contact_hero_image');
            if ($oldImage && file_exists(public_path($oldImage))) {
                @unlink(public_path($oldImage));
            }
            
            $heroImage->move($uploadsDir, $heroImageName);
            Setting::set('contact_hero_image', 'uploads/' . $heroImageName, 'file', 'branding');
        }

        // Handle simulator image upload
        if ($request->hasFile('simulator_image')) {
            $simulatorImage = $request->file('simulator_image');
            $simulatorImageName = 'simulator-' . time() . '.' . $simulatorImage->getClientOriginalExtension();
            
            // Créer le dossier uploads/images s'il n'existe pas
            $imagesDir = public_path('uploads/images');
            if (!file_exists($imagesDir)) {
                mkdir($imagesDir, 0755, true);
            }
            
            // Supprimer l'ancienne image si elle existe
            $oldSimulatorImage = Setting::get('simulator_image');
            if ($oldSimulatorImage && file_exists(public_path($oldSimulatorImage))) {
                @unlink(public_path($oldSimulatorImage));
            }
            
            $simulatorImage->move($imagesDir, $simulatorImageName);
            Setting::set('simulator_image', 'uploads/images/' . $simulatorImageName, 'file', 'branding');
        }

        // Colors and typography
        if (isset($validated['primary_color'])) {
            Setting::set('primary_color', $validated['primary_color'], 'string', 'branding');
        }
        if (isset($validated['secondary_color'])) {
            Setting::set('secondary_color', $validated['secondary_color'], 'string', 'branding');
        }
        if (isset($validated['accent_color'])) {
            Setting::set('accent_color', $validated['accent_color'], 'string', 'branding');
        }
        if (isset($validated['primary_font'])) {
            Setting::set('primary_font', $validated['primary_font'], 'string', 'branding');
        }
        if (isset($validated['font_size'])) {
            Setting::set('font_size', $validated['font_size'], 'string', 'branding');
        }

        Setting::clearCache();
        
        // Vider aussi le cache Laravel pour forcer le rechargement du favicon
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');

        return back()->with('success', 'Paramètres de branding mis à jour avec succès !');
    }

    /**
     * Update portfolio settings
     */
    public function updatePortfolio(Request $request)
    {
        $validated = $request->validate([
            'portfolio_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'project_title' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|in:roof,facade,isolation,mixed',
            'project_description' => 'nullable|string|max:1000',
            'portfolio_per_page' => 'nullable|integer|min:3|max:20',
            'portfolio_order' => 'nullable|string|in:newest,oldest,random',
        ]);

        // Handle portfolio images upload
        if ($request->hasFile('portfolio_images')) {
            $images = $request->file('portfolio_images');
            $uploadedImages = [];
            
            foreach ($images as $image) {
                $imageName = 'portfolio-' . time() . '-' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/portfolio'), $imageName);
                $uploadedImages[] = 'uploads/portfolio/' . $imageName;
            }
            
            // Save portfolio data
            $portfolioData = [
                'title' => $validated['project_title'] ?? 'Réalisation',
                'work_type' => $validated['work_type'] ?? 'mixed',
                'description' => $validated['project_description'] ?? '',
                'images' => $uploadedImages,
                'created_at' => now()->toISOString(),
            ];
            
            // Get existing portfolio
            $existingPortfolio = json_decode(Setting::get('portfolio_items', '[]'), true);
            $existingPortfolio[] = $portfolioData;
            
            Setting::set('portfolio_items', json_encode($existingPortfolio), 'json', 'portfolio');
        }

        // Save display settings
        if (isset($validated['portfolio_per_page'])) {
            Setting::set('portfolio_per_page', $validated['portfolio_per_page'], 'integer', 'portfolio');
        }
        if (isset($validated['portfolio_order'])) {
            Setting::set('portfolio_order', $validated['portfolio_order'], 'string', 'portfolio');
        }

        Setting::clearCache();

        return back()->with('success', 'Portfolio mis à jour avec succès !');
    }

    /**
     * Portfolio management index
     */
    public function portfolioIndex()
    {
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        // S'assurer que $portfolioItems est toujours un tableau
        if (!is_array($portfolioItems)) {
            $portfolioItems = [];
        }
        
        // Ajouter un ID aux éléments qui n'en ont pas et nettoyer les images manquantes
        foreach ($portfolioItems as $index => &$item) {
            if (!isset($item['id'])) {
                $item['id'] = time() . rand(1000, 9999) . '_' . $index;
            }
            
            // Nettoyer les images manquantes
            if (isset($item['images']) && is_array($item['images'])) {
                $validImages = [];
                foreach ($item['images'] as $imagePath) {
                    if (file_exists(public_path($imagePath))) {
                        $validImages[] = $imagePath;
                    } else {
                        \Log::warning('Image manquante supprimée du portfolio', ['image' => $imagePath, 'item' => $item['title'] ?? 'Sans titre']);
                    }
                }
                $item['images'] = $validImages;
            }
        }
        
        // Filtrer seulement les éléments visibles pour le public
        $visibleItems = array_filter($portfolioItems, function($item) {
            return isset($item['is_visible']) && $item['is_visible'] === true;
        });
        
        // Sauvegarder les données nettoyées
        Setting::set('portfolio_items', json_encode($portfolioItems), 'json', 'portfolio');
        Setting::clearCache();
        
        \Log::info('Portfolio index loaded', [
            'portfolio_items' => $portfolioItems,
            'visible_items' => $visibleItems,
            'count' => count($portfolioItems),
            'visible_count' => count($visibleItems)
        ]);
        
        // Détecter si c'est un accès admin ou public
        if (request()->is('admin/portfolio*')) {
            // Accès admin - vue complète avec gestion
            return view('admin.portfolio', compact('portfolioItems'));
        } else {
            // Accès public - vue simple avec seulement les éléments visibles
            return view('portfolio.public', compact('visibleItems'));
        }
    }

    /**
     * Get portfolio data (AJAX)
     */
    public function getPortfolioData()
    {
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        \Log::info('Portfolio data requested', [
            'raw_data' => $portfolioData,
            'decoded_items' => $portfolioItems,
            'count' => count($portfolioItems)
        ]);
        
        return response()->json(['items' => $portfolioItems]);
    }

    /**
     * Edit portfolio item page
     */
    public function editPortfolioItem($id)
    {
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        // Ajouter un ID aux éléments qui n'en ont pas
        foreach ($portfolioItems as $index => &$item) {
            if (!isset($item['id'])) {
                $item['id'] = time() . rand(1000, 9999) . '_' . $index;
            }
        }
        
        // Trouver l'élément à modifier
        $item = null;
        foreach ($portfolioItems as $portfolioItem) {
            if (isset($portfolioItem['id']) && $portfolioItem['id'] == $id) {
                $item = $portfolioItem;
                break;
            }
        }
        
        if (!$item) {
            return redirect()->route('portfolio.admin.index')->with('error', 'Élément non trouvé');
        }
        
        return view('admin.portfolio.edit', compact('item'));
    }

    /**
     * Add new portfolio item
     */
    public function addPortfolioItem(Request $request)
    {
        \Log::info('Portfolio upload attempt', [
            'method' => $request->method(),
            'url' => $request->url(),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'title' => $request->input('title'),
            'work_type' => $request->input('work_type'),
            'all_input' => $request->all()
        ]);
        
        // Ultra-simple validation
        if (!$request->hasFile('images')) {
            return response()->json(['success' => false, 'message' => 'Aucune image sélectionnée']);
        }
        
        if (!$request->input('title')) {
            return response()->json(['success' => false, 'message' => 'Le titre est requis']);
        }
        
        if (!$request->input('work_type')) {
            return response()->json(['success' => false, 'message' => 'Le type de travaux est requis']);
        }

        // Handle image uploads
        $uploadedImages = [];
        foreach ($request->file('images') as $image) {
            $extension = strtolower($image->getClientOriginalExtension());
            $imageName = 'portfolio-' . time() . '-' . rand(1000, 9999) . '.' . $extension;
            $image->move(public_path('uploads/portfolio'), $imageName);
            $uploadedImages[] = 'uploads/portfolio/' . $imageName;
        }

        // Generate SEO data for the portfolio item
        $companyName = setting('company_name', 'Votre Entreprise');
        $companyCity = setting('company_city', '');
        $workTypeLabels = [
            'roof' => 'Toiture',
            'facade' => 'Façade', 
            'isolation' => 'Isolation',
            'mixed' => 'Travaux mixtes'
        ];
        $workTypeLabel = $workTypeLabels[$request->input('work_type')] ?? 'Travaux';
        
        // Generate SEO title
        $seoTitle = $request->input('title') . ' - ' . $workTypeLabel;
        if ($companyCity) {
            $seoTitle .= ' à ' . $companyCity;
        }
        $seoTitle .= ' | ' . $companyName;
        
        // Generate SEO description
        $seoDescription = 'Découvrez notre réalisation ' . $request->input('title') . ' - ' . $workTypeLabel;
        if ($companyCity) {
            $seoDescription .= ' à ' . $companyCity;
        }
        $seoDescription .= '. ' . ($request->input('description') ? Str::limit($request->input('description'), 100) : 'Réalisation professionnelle par ' . $companyName);
        
        // Generate keywords
        $keywords = [
            $workTypeLabel,
            'réalisation',
            'travaux',
            'rénovation',
            $companyName
        ];
        if ($companyCity) {
            $keywords[] = $companyCity;
        }
        if ($request->input('work_type') === 'roof') {
            $keywords = array_merge($keywords, ['toiture', 'couverture', 'charpente']);
        } elseif ($request->input('work_type') === 'facade') {
            $keywords = array_merge($keywords, ['façade', 'enduit', 'ravalement']);
        } elseif ($request->input('work_type') === 'isolation') {
            $keywords = array_merge($keywords, ['isolation', 'thermique', 'énergie']);
        }
        $seoKeywords = implode(', ', array_unique($keywords));

        // Create portfolio item with SEO
        $portfolioItem = [
            'id' => time() . rand(1000, 9999),
            'title' => $request->input('title'),
            'work_type' => $request->input('work_type'),
            'service_type' => $workTypeLabel, // For compatibility
            'description' => $request->input('description', ''),
            'images' => $uploadedImages,
            'is_visible' => $request->has('is_visible'),
            // SEO data
            'meta_title' => $seoTitle,
            'meta_description' => $seoDescription,
            'meta_keywords' => $seoKeywords,
            'og_title' => $seoTitle,
            'og_description' => $seoDescription,
            'og_image' => !empty($uploadedImages) ? $uploadedImages[0] : '',
            'og_type' => 'article',
            'og_url' => url('/nos-realisations/' . (time() . rand(1000, 9999))),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $seoTitle,
            'twitter_description' => $seoDescription,
            'twitter_image' => !empty($uploadedImages) ? $uploadedImages[0] : '',
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Get existing portfolio
        $portfolioData = Setting::get('portfolio_items', '[]');
        $existingPortfolio = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        $existingPortfolio[] = $portfolioItem;

        Setting::set('portfolio_items', json_encode($existingPortfolio), 'json', 'portfolio');
        Setting::clearCache();

        return response()->json(['success' => true, 'message' => 'Réalisation ajoutée avec succès !']);
    }
    
    /**
     * Test ultra-simple pour l'upload
     */
    public function testUpload(Request $request)
    {
        \Log::info('Test upload - Request received', [
            'method' => $request->method(),
            'has_files' => $request->hasFile('images'),
            'files_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
            'all_data' => $request->all()
        ]);
        
        return response()->json(['success' => true, 'message' => 'Test upload OK - ' . date('H:i:s')]);
    }



    /**
     * Update portfolio item
     */
    public function updatePortfolioItem(Request $request, $id)
    {
        $validated = $request->validate([
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'title' => 'required|string|max:255',
            'work_type' => 'required|string|in:roof,facade,isolation,mixed',
            'description' => 'nullable|string|max:1000',
            'is_visible' => 'nullable|in:on,1,true,false,0',
        ]);

        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        $itemIndex = array_search($id, array_column($portfolioItems, 'id'));

        if ($itemIndex === false) {
            return response()->json(['success' => false, 'message' => 'Réalisation non trouvée'], 404);
        }

        // Handle removal of existing images
        $existingImages = $portfolioItems[$itemIndex]['images'] ?? [];
        if ($request->has('remove_images')) {
            $imagesToRemove = $request->input('remove_images');
            foreach ($imagesToRemove as $index) {
                if (isset($existingImages[$index])) {
                    $imagePath = public_path($existingImages[$index]);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    unset($existingImages[$index]);
                }
            }
            // Réindexer le tableau
            $existingImages = array_values($existingImages);
        }

        // Handle new image uploads
        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = 'portfolio-' . time() . '-' . rand(1000, 9999) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/portfolio'), $imageName);
                $newImages[] = 'uploads/portfolio/' . $imageName;
            }
        }

        // Update item
        $portfolioItems[$itemIndex]['title'] = $validated['title'];
        $portfolioItems[$itemIndex]['work_type'] = $validated['work_type'];
        $portfolioItems[$itemIndex]['description'] = $validated['description'] ?? '';
        
        // Traitement correct du champ is_visible
        $isVisible = $request->input('is_visible');
        if ($isVisible === 'on' || $isVisible === '1' || $isVisible === 'true' || $isVisible === true) {
            $portfolioItems[$itemIndex]['is_visible'] = true;
        } else {
            $portfolioItems[$itemIndex]['is_visible'] = false;
        }
        
        $portfolioItems[$itemIndex]['updated_at'] = now()->toISOString();

        // Combine existing and new images
        $portfolioItems[$itemIndex]['images'] = array_merge($existingImages, $newImages);

        Setting::set('portfolio_items', json_encode($portfolioItems), 'json', 'portfolio');
        Setting::clearCache();

        return response()->json(['success' => true, 'message' => 'Réalisation mise à jour avec succès !']);
    }

    /**
     * Delete portfolio item
     */
    public function deletePortfolioItem($id)
    {
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        $itemIndex = array_search($id, array_column($portfolioItems, 'id'));

        if ($itemIndex === false) {
            return response()->json(['success' => false, 'message' => 'Réalisation non trouvée'], 404);
        }

        // Delete associated images
        $item = $portfolioItems[$itemIndex];
        foreach ($item['images'] as $imagePath) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Remove item from array
        unset($portfolioItems[$itemIndex]);
        $portfolioItems = array_values($portfolioItems); // Re-index array

        Setting::set('portfolio_items', json_encode($portfolioItems), 'json', 'portfolio');
        Setting::clearCache();

        return response()->json(['success' => true, 'message' => 'Réalisation supprimée avec succès !']);
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            // SMTP Settings
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer',
            'mail_encryption' => 'required|in:tls,ssl',
            'mail_username' => 'required|email',
            'mail_password' => 'required|string',
            
            // From Settings
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string|max:255',
            
            // Notification
            'admin_notification_email' => 'nullable|email',
            'email_enabled' => 'nullable|boolean',
        ]);

        // Save SMTP settings
        Setting::set('mail_host', $validated['mail_host'], 'string', 'email');
        Setting::set('mail_port', $validated['mail_port'], 'integer', 'email');
        Setting::set('mail_encryption', $validated['mail_encryption'], 'string', 'email');
        Setting::set('mail_username', $validated['mail_username'], 'string', 'email');
        Setting::set('mail_password', $validated['mail_password'], 'string', 'email');
        
        // Save From settings
        Setting::set('mail_from_address', $validated['mail_from_address'], 'string', 'email');
        Setting::set('mail_from_name', $validated['mail_from_name'], 'string', 'email');
        
        // Save notification settings
        Setting::set('admin_notification_email', $validated['admin_notification_email'] ?? '', 'string', 'email');
        Setting::set('email_enabled', $request->has('email_enabled'), 'boolean', 'email');

        // Update .env file or config dynamically
        config([
            'mail.mailers.smtp.host' => $validated['mail_host'],
            'mail.mailers.smtp.port' => $validated['mail_port'],
            'mail.mailers.smtp.encryption' => $validated['mail_encryption'],
            'mail.mailers.smtp.username' => $validated['mail_username'],
            'mail.mailers.smtp.password' => $validated['mail_password'],
            'mail.from.address' => $validated['mail_from_address'],
            'mail.from.name' => $validated['mail_from_name'],
        ]);

        Setting::clearCache();

        return back()->with('success', 'Paramètres email SMTP mis à jour avec succès ! Vous pouvez maintenant tester l\'envoi.');
    }

    /**
     * Update security settings (reCAPTCHA)
     */
    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'recaptcha_site_key' => 'nullable|string|max:255',
            'recaptcha_secret_key' => 'nullable|string|max:255',
            'block_non_france' => 'nullable|boolean',
        ]);

        if (isset($validated['recaptcha_site_key'])) {
            Setting::set('recaptcha_site_key', $validated['recaptcha_site_key'], 'string', 'security');
        }
        if (isset($validated['recaptcha_secret_key'])) {
            Setting::set('recaptcha_secret_key', $validated['recaptcha_secret_key'], 'string', 'security');
        }
        
        // Blocage géographique
        $blockNonFrance = $request->boolean('block_non_france', false);
        Setting::set('block_non_france', $blockNonFrance, 'boolean', 'security');

        Setting::clearCache();

        return back()->with('success', 'Paramètres de sécurité mis à jour avec succès !');
    }

    /**
     * Update Analytics settings
     */
    public function updateAnalytics(Request $request)
    {
        $validated = $request->validate([
            'analytics_view_id' => 'nullable|string|max:255',
            'analytics_credentials' => 'nullable|file|mimes:json|max:1024',
        ]);

        // Sauvegarder le View ID
        if (isset($validated['analytics_view_id'])) {
            Setting::set('analytics_view_id', $validated['analytics_view_id'], 'string', 'analytics');
            
            // Mettre à jour aussi le .env si possible
            $envFile = base_path('.env');
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                
                // Remplacer ou ajouter ANALYTICS_VIEW_ID
                if (preg_match('/^ANALYTICS_VIEW_ID=.*$/m', $envContent)) {
                    $envContent = preg_replace('/^ANALYTICS_VIEW_ID=.*$/m', 'ANALYTICS_VIEW_ID=' . $validated['analytics_view_id'], $envContent);
                } else {
                    $envContent .= "\nANALYTICS_VIEW_ID=" . $validated['analytics_view_id'];
                }
                
                file_put_contents($envFile, $envContent);
            }
        }

        // Gérer le fichier JSON des credentials
        if ($request->hasFile('analytics_credentials')) {
            $file = $request->file('analytics_credentials');
            
            // Créer le dossier s'il n'existe pas
            $analyticsDir = storage_path('app/analytics');
            if (!file_exists($analyticsDir)) {
                mkdir($analyticsDir, 0755, true);
            }
            
            // Déplacer le fichier
            $file->move($analyticsDir, 'service-account-credentials.json');
            
            \Log::info('Fichier credentials Analytics téléchargé avec succès');
        }

        Setting::clearCache();
        Artisan::call('config:clear');

        return back()->with('success', 'Configuration Analytics mise à jour avec succès !');
    }

    /**
     * Update social media settings
     */
    public function updateSocial(Request $request)
    {
        $validated = $request->validate([
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'youtube_url' => 'nullable|url',
            'tiktok_url' => 'nullable|url',
            'pinterest_url' => 'nullable|url',
            'snapchat_url' => 'nullable|url',
            'whatsapp_url' => 'nullable|url',
            'telegram_url' => 'nullable|url',
            'google_business_url' => 'nullable|url',
            'google_place_id' => 'nullable|string|max:255',
            'google_api_key' => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value ?? '', 'string', 'social');
        }

        Setting::clearCache();

        return back()->with('success', 'Réseaux sociaux mis à jour avec succès !');
    }


    /**
     * Manage reviews
     */
    public function updateReviews(Request $request)
    {
        $validated = $request->validate([
            'reviews' => 'required|array',
            'reviews.*.id' => 'nullable|exists:reviews,id',
            'reviews.*.author_name' => 'required|string|max:255',
            'reviews.*.author_location' => 'nullable|string|max:255',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
            'reviews.*.review_text' => 'required|string',
            'reviews.*.is_active' => 'nullable|boolean',
            'reviews.*.display_order' => 'nullable|integer',
        ]);

        foreach ($validated['reviews'] as $index => $reviewData) {
            if (isset($reviewData['id'])) {
                // Update existing review
                $review = Review::find($reviewData['id']);
                $review->update([
                    'author_name' => $reviewData['author_name'],
                    'author_location' => $reviewData['author_location'] ?? '',
                    'rating' => $reviewData['rating'],
                    'review_text' => $reviewData['review_text'],
                    'is_active' => $reviewData['is_active'] ?? true,
                    'display_order' => $reviewData['display_order'] ?? $index,
                ]);
            } else {
                // Create new review
                Review::create([
                    'author_name' => $reviewData['author_name'],
                    'author_location' => $reviewData['author_location'] ?? '',
                    'rating' => $reviewData['rating'],
                    'review_text' => $reviewData['review_text'],
                    'is_active' => $reviewData['is_active'] ?? true,
                    'is_verified' => false,
                    'display_order' => $reviewData['display_order'] ?? $index,
                    'review_date' => now(),
                ]);
            }
        }

        return back()->with('success', 'Avis mis à jour avec succès !');
    }


    /**
     * Fetch Google Reviews automatically
     * Importe TOUS les avis Google disponibles
     */
    public function fetchGoogleReviews()
    {
        try {
            $placeId = Setting::get('google_place_id');
            $apiKey = Setting::get('google_api_key');

            if (!$placeId || !$apiKey) {
                return back()->with('error', 'Veuillez configurer votre Google Place ID et votre clé API Google dans les paramètres de réseaux sociaux.');
            }

            // Appel à l'API Google Places pour récupérer TOUS les avis
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'reviews,rating,user_ratings_total',
                'key' => $apiKey,
                'language' => 'fr', // Forcer la langue française
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Erreur lors de la récupération des avis Google : ' . $response->status());
            }

            $data = $response->json();

            if (isset($data['error_message'])) {
                return back()->with('error', 'Erreur Google API : ' . $data['error_message']);
            }

            if (!isset($data['result']['reviews'])) {
                return back()->with('warning', 'Aucun avis trouvé pour ce Google Place ID.');
            }

            $reviews = $data['result']['reviews'];
            $imported = 0;
            $updated = 0;

            // Importer TOUS les avis disponibles
            foreach ($reviews as $index => $googleReview) {
                $reviewDate = date('Y-m-d H:i:s', $googleReview['time']);
                
                // Vérifier si l'avis existe déjà avec un identifiant unique
                $googleReviewId = md5($googleReview['author_name'] . $googleReview['time'] . $googleReview['text']);
                
                $existingReview = Review::where('google_review_id', $googleReviewId)
                    ->orWhere(function($query) use ($googleReview, $reviewDate) {
                        $query->where('author_name', $googleReview['author_name'])
                              ->where('source', 'google')
                              ->whereDate('review_date', '=', date('Y-m-d', $googleReview['time']));
                    })
                    ->first();

                if (!$existingReview) {
                    // Créer un nouvel avis
                    Review::create([
                        'google_review_id' => $googleReviewId,
                        'author_name' => $googleReview['author_name'],
                        'author_location' => 'Google', // Lieu générique au lieu du lien
                        'author_photo_url' => $googleReview['profile_photo_url'] ?? null,
                        'rating' => $googleReview['rating'],
                        'review_text' => $googleReview['text'] ?? '',
                        'is_active' => true,
                        'is_verified' => true,
                        'source' => 'google',
                        'display_order' => $index,
                        'review_date' => $reviewDate,
                    ]);
                    $imported++;
                } else {
                    // Mettre à jour l'avis existant si nécessaire
                    $existingReview->update([
                        'google_review_id' => $googleReviewId,
                        'author_location' => 'Google', // Corriger le lieu aussi
                        'rating' => $googleReview['rating'],
                        'review_text' => $googleReview['text'] ?? '',
                        'author_photo_url' => $googleReview['profile_photo_url'] ?? $existingReview->author_photo_url,
                    ]);
                    $updated++;
                }
            }

            $totalReviews = Review::where('source', 'google')->count();
            $message = [];
            if ($imported > 0) $message[] = "$imported nouveaux avis importés";
            if ($updated > 0) $message[] = "$updated avis mis à jour";
            $message[] = "Total: $totalReviews avis Google dans la base";

            return back()->with('success', implode(', ', $message) . ' !');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Update email template
     */
    public function updateEmailTemplate(Request $request)
    {
        $templateType = $request->input('template_type');
        $subject = $request->input('subject');
        $htmlContent = $request->input('html_content');
        $fromName = $request->input('from_name');
        $fromEmail = $request->input('from_email');
        $adminEmail = $request->input('admin_email');

        try {
            if ($templateType === 'client') {
                Setting::set('email_client_subject', $subject, 'string', 'email');
                Setting::set('email_client_template', $htmlContent, 'text', 'email');
                Setting::set('email_client_from_name', $fromName, 'string', 'email');
                Setting::set('email_client_from_email', $fromEmail, 'string', 'email');
            } elseif ($templateType === 'admin') {
                Setting::set('email_admin_subject', $subject, 'string', 'email');
                Setting::set('email_admin_template', $htmlContent, 'text', 'email');
                Setting::set('email_admin_recipient', $adminEmail, 'string', 'email');
            }

            Setting::clearCache();

            return response()->json(['success' => true, 'message' => 'Template email mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
        }
    }

    /**
     * Test email template
     */
    public function testEmailTemplate(Request $request)
    {
        $email = $request->input('test_email');
        $templateType = $request->input('template_type');
        
        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Email requis']);
        }
        
        try {
            $emailService = new \App\Services\EmailService();
            
            if ($templateType === 'client') {
                $emailService->sendTestEmailTemplate($email, 'client');
            } elseif ($templateType === 'admin') {
                $emailService->sendTestEmailTemplate($email, 'admin');
            }
            
            return response()->json(['success' => true, 'message' => 'Email de test envoyé']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Load SMTP config from database
            $mailHost = Setting::get('mail_host');
            $mailPort = Setting::get('mail_port');
            $mailEncryption = Setting::get('mail_encryption');
            $mailUsername = Setting::get('mail_username');
            $mailPassword = Setting::get('mail_password');
            $mailFromAddress = Setting::get('mail_from_address');
            $mailFromName = Setting::get('mail_from_name');

            if (!$mailHost || !$mailUsername || !$mailPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration SMTP incomplète. Veuillez remplir tous les champs SMTP.'
                ]);
            }

            // Configure mail settings dynamically
            config([
                'mail.mailers.smtp.host' => $mailHost,
                'mail.mailers.smtp.port' => $mailPort,
                'mail.mailers.smtp.encryption' => $mailEncryption,
                'mail.mailers.smtp.username' => $mailUsername,
                'mail.mailers.smtp.password' => $mailPassword,
                'mail.from.address' => $mailFromAddress,
                'mail.from.name' => $mailFromName,
            ]);

            // Send test email
            \Mail::raw('✅ Félicitations ! Votre configuration SMTP fonctionne correctement.\n\nCet email de test a été envoyé depuis votre simulateur Laravel.\n\nServeur SMTP : ' . $mailHost . ':' . $mailPort . '\nEncryption : ' . strtoupper($mailEncryption), function ($message) use ($validated, $mailFromName) {
                $message->to($validated['test_email'])
                    ->subject('✅ Test Email SMTP - ' . Setting::get('company_name', $mailFromName));
            });

            return response()->json([
                'success' => true,
                'message' => 'Email de test envoyé avec succès à ' . $validated['test_email']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur SMTP : ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Show reset configuration confirmation page
     */
    public function showReset()
    {
        return view('config.reset');
    }
    
    /**
     * Reset all configuration
     */
    public function resetConfiguration(Request $request)
    {
        $validated = $request->validate([
            'confirm' => 'required|in:RESET',
        ]);
        
        // Delete all settings
        Setting::truncate();
        
        // Clear cache
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        
        // Log out admin
        session()->forget('admin_logged_in');
        
        return redirect()->route('config.setup')->with('success', 'Configuration réinitialisée ! Veuillez reconfigurer le site.');
    }

    /**
     * Update AI configuration
     */
    public function updateAI(Request $request)
    {
        $validated = $request->validate([
            'chatgpt_enabled' => 'nullable|boolean',
            'chatgpt_api_key' => 'nullable|string',
            'chatgpt_model' => 'required|string|in:gpt-3.5-turbo,gpt-4,gpt-4-turbo,gpt-4o',
            'groq_api_key' => 'nullable|string',
            'groq_model' => 'nullable|string|in:llama-3.1-8b-instant,llama-3.1-70b-versatile,mixtral-8x7b-32768',
            'default_ai_provider' => 'nullable|string|in:chatgpt,groq',
            'ai_temperature' => 'nullable|numeric|min:0|max:1',
            'ai_max_tokens' => 'nullable|integer|min:100|max:4000',
            'ai_prompt_template' => 'nullable|string|max:2000',
        ]);

        // Sauvegarder les paramètres IA
        // Pour une checkbox, si elle n'est pas présente dans la requête, elle n'est pas cochée (false)
        // Si elle est présente, elle est cochée (true)
        $chatgptEnabled = $request->has('chatgpt_enabled') ? $request->boolean('chatgpt_enabled') : false;
        Setting::set('chatgpt_enabled', $chatgptEnabled, 'boolean', 'ai');
        
        // Sauvegarder la clé ChatGPT telle quelle (pas de nettoyage, pas de validation)
        if (isset($validated['chatgpt_api_key'])) {
            Setting::set('chatgpt_api_key', $validated['chatgpt_api_key'], 'string', 'ai');
        }
        
        Setting::set('chatgpt_model', $validated['chatgpt_model'], 'string', 'ai');
        
        // Sauvegarder la clé Groq telle quelle (pas de nettoyage, pas de validation)
        if (isset($validated['groq_api_key'])) {
            Setting::set('groq_api_key', $validated['groq_api_key'], 'string', 'ai');
        }
        
        Setting::set('groq_model', $validated['groq_model'] ?? 'llama-3.1-8b-instant', 'string', 'ai');
        Setting::set('default_ai_provider', $validated['default_ai_provider'] ?? 'chatgpt', 'string', 'ai');
        
        if (isset($validated['ai_temperature'])) {
        Setting::set('ai_temperature', $validated['ai_temperature'], 'float', 'ai');
        }
        if (isset($validated['ai_max_tokens'])) {
        Setting::set('ai_max_tokens', $validated['ai_max_tokens'], 'integer', 'ai');
        }
        if (isset($validated['ai_prompt_template'])) {
        Setting::set('ai_prompt_template', $validated['ai_prompt_template'], 'string', 'ai');
        }
        
        Setting::clearCache();

        return redirect()->back()->with('success', 'Configuration IA mise à jour avec succès !');
    }

    /**
     * Test ChatGPT API connection
     * Version ultra-simple : pas de nettoyage, pas de validation, test direct
     */
    public function testChatGPT(Request $request)
    {
        try {
            $apiKey = $request->input('api_key');
            
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API manquante'
                ]);
            }

            // Log pour debug
            \Log::info('testChatGPT: Début test', [
                'api_key_length' => strlen($apiKey),
                'api_key_start' => substr($apiKey, 0, 7)
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => 'OK']
                ],
                'max_tokens' => 5,
                'temperature' => 0.1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Setting::set('chatgpt_api_key', $apiKey, 'string', 'ai');
                Setting::clearCache();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connexion ChatGPT réussie !',
                    'usage' => $data['usage'] ?? null
                ]);
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Erreur API inconnue';
            
            \Log::error('testChatGPT: Erreur API', [
                'status' => $response->status(),
                'error_message' => $errorMessage,
                'error_data' => $errorData
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'status' => $response->status()
            ]);
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('testChatGPT: Erreur de connexion', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            \Log::error('testChatGPT: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test Groq API connection
     * Version ultra-simple : pas de nettoyage, pas de validation, test direct
     */
    public function testGroq(Request $request)
    {
        try {
            $apiKey = $request->input('api_key');
            
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API manquante'
                ]);
            }

            // Log pour debug
            \Log::info('testGroq: Début test', [
                'api_key_length' => strlen($apiKey),
                'api_key_start' => substr($apiKey, 0, 7)
            ]);

            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'user', 'content' => 'OK']
                    ],
                    'max_tokens' => 5,
                    'temperature' => 0.1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                Setting::set('groq_api_key', $apiKey, 'string', 'ai');
                Setting::clearCache();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Connexion Groq réussie !',
                    'usage' => $data['usage'] ?? null
                ]);
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? 'Erreur API inconnue';
            
            \Log::error('testGroq: Erreur API', [
                'status' => $response->status(),
                'error_message' => $errorMessage,
                'error_data' => $errorData
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'status' => $response->status()
            ]);
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('testGroq: Erreur de connexion', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            \Log::error('testGroq: Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test ChatGPT avec génération de contenu complet
     * Version ultra-simple : pas de nettoyage, pas de validation
     */
    public function testChatGPTGenerate(Request $request)
    {
        $apiKey = $request->input('api_key') ?: setting('chatgpt_api_key');
        $prompt = $request->input('prompt', 'Créez un contenu web complet pour un service de "Rénovation de façade". Le contenu doit inclure une description détaillée, 3 prestations spécifiques, et une section FAQ avec 2 questions.');
        
        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Clé API ChatGPT manquante. Veuillez la configurer d\'abord.'
            ]);
        }

        try {
            $model = setting('chatgpt_model', 'gpt-4o');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un expert en rédaction web pour les services de rénovation. Crée du contenu professionnel, engageant et optimisé SEO.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.8
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Génération ChatGPT réussie !',
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null,
                    'model' => $model,
                    'prompt' => $prompt
                ]);
            }
            
            $errorBody = $response->json();
            return response()->json([
                'success' => false,
                'message' => $errorBody['error']['message'] ?? 'Erreur API inconnue',
                'status' => $response->status()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test Groq avec génération de contenu complet
     * Version ultra-simple : pas de nettoyage, pas de validation
     */
    public function testGroqGenerate(Request $request)
    {
        $apiKey = $request->input('api_key') ?: setting('groq_api_key');
        $prompt = $request->input('prompt', 'Créez un contenu web complet pour un service de "Rénovation de façade". Le contenu doit inclure une description détaillée, 3 prestations spécifiques, et une section FAQ avec 2 questions.');
        
        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Clé API Groq manquante. Veuillez la configurer d\'abord.'
            ]);
        }

        try {
            $model = setting('groq_model', 'llama-3.1-8b-instant');
            
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu es un expert en rédaction web pour les services de rénovation. Crée du contenu professionnel, engageant et optimisé SEO.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.8
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => 'Génération Groq réussie !',
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null,
                    'model' => $model,
                    'prompt' => $prompt
                ]);
            }
            
            $errorBody = $response->json();
            return response()->json([
                'success' => false,
                'message' => $errorBody['error']['message'] ?? 'Erreur API inconnue',
                'status' => $response->status()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Afficher la page d'édition de la page d'accueil
     */
    public function editHomepage()
    {
        $config = Setting::get('homepage_config', null);
        
        if ($config && is_string($config)) {
            $config = json_decode($config, true);
        }
        
        // Default configuration
        if (!$config) {
            $config = [
                'hero' => [
                    'title' => Setting::get('company_name', 'Votre Entreprise'),
                    'subtitle' => 'Expert en ' . (Setting::get('company_specialization', 'Travaux de Rénovation')),
                    'cta_text' => 'Demander un Devis Gratuit',
                    'show_phone' => true,
                    'background_image' => null,
                ],
                'trust_badges' => [
                    'garantie_decennale' => true,
                    'certifie_rge' => true,
                    'show_rating' => true,
                ],
                'about' => [
                    'enabled' => false,
                    'title' => 'Qui Sommes-Nous ?',
                    'content' => '',
                    'image' => null,
                ],
                'ecology' => [
                    'enabled' => false,
                    'title' => 'Notre Engagement Écologique',
                    'content' => '',
                ],
                'financing' => [
                    'enabled' => false,
                    'title' => 'Aides et Financements Disponibles',
                    'content' => '',
                ],
                'footer' => [
                    'intervention_zone' => 'Nous intervenons dans toute la région ' . Setting::get('company_region', 'Île-de-France') . ' et ses environs.',
                    'about' => Setting::get('company_description', ''),
                    'show_cities' => false,
                ],
                'sections' => [
                    'services' => ['enabled' => true, 'title' => 'Nos Services', 'limit' => 6],
                    'portfolio' => ['enabled' => true, 'title' => 'Nos Réalisations', 'limit' => 6],
                    'reviews' => ['enabled' => true, 'title' => 'Avis de Nos Clients', 'limit' => 6],
                    'why_choose_us' => ['enabled' => true, 'title' => 'Pourquoi Nous Choisir?'],
                    'cta' => ['enabled' => true, 'title' => 'Prêt à Démarrer Votre Projet?'],
                ],
                'stats' => [
                    ['label' => 'Projets Réalisés', 'value' => '500+', 'icon' => 'fa-check-circle'],
                    ['label' => 'Clients Satisfaits', 'value' => '98%', 'icon' => 'fa-smile'],
                    ['label' => 'Années d\'Expérience', 'value' => '15+', 'icon' => 'fa-award'],
                    ['label' => 'Garantie', 'value' => '10 ans', 'icon' => 'fa-shield-alt'],
                ],
                'partners' => [
                    'enabled' => false,
                    'title' => 'Nos Partenaires',
                    'logos' => [],
                ],
            ];
        }
        
        return view('admin.homepage.edit', compact('config'));
    }

    /**
     * Mettre à jour la configuration de la page d'accueil
     */
    public function updateHomepage(Request $request)
    {
        $request->validate([
            'hero.title' => 'required|string|max:255',
            'hero.subtitle' => 'required|string|max:500',
            'hero.cta_text' => 'required|string|max:100',
            'hero_background' => 'nullable|image|max:5120', // 5MB max
            'about_image' => 'nullable|image|max:5120', // 5MB max
            'partner_logos.*' => 'nullable|image|max:2048', // 2MB max per logo
            'partner_names.*' => 'nullable|string|max:255',
            'partner_urls.*' => 'nullable|url|max:500',
        ]);

        // Get current config
        $currentConfig = Setting::get('homepage_config', null);
        if ($currentConfig && is_string($currentConfig)) {
            $currentConfig = json_decode($currentConfig, true);
        }

        // Handle hero background image upload
        $backgroundImage = $currentConfig['hero']['background_image'] ?? null;
        
        if ($request->hasFile('hero_background')) {
            $file = $request->file('hero_background');
            $filename = 'hero-bg-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/homepage'), $filename);
            $backgroundImage = '/uploads/homepage/' . $filename;
            
            // Delete old image if exists
            if (!empty($currentConfig['hero']['background_image'])) {
                $oldPath = public_path($currentConfig['hero']['background_image']);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        } elseif ($request->has('remove_hero_background') && $request->remove_hero_background) {
            // Remove background image
            if (!empty($currentConfig['hero']['background_image'])) {
                $oldPath = public_path($currentConfig['hero']['background_image']);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $backgroundImage = null;
        }

        // Handle about section image upload
        $aboutImage = $currentConfig['about']['image'] ?? null;
        
        if ($request->hasFile('about_image')) {
            $file = $request->file('about_image');
            $filename = 'about-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/homepage'), $filename);
            $aboutImage = '/uploads/homepage/' . $filename;
            
            // Delete old image if exists
            if (!empty($currentConfig['about']['image'])) {
                $oldPath = public_path($currentConfig['about']['image']);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        } elseif ($request->has('remove_about_image') && $request->remove_about_image) {
            // Remove about image
            if (!empty($currentConfig['about']['image'])) {
                $oldPath = public_path($currentConfig['about']['image']);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $aboutImage = null;
        }

        $config = [
            'hero' => [
                'title' => $request->input('hero.title'),
                'subtitle' => $request->input('hero.subtitle'),
                'cta_text' => $request->input('hero.cta_text'),
                'show_phone' => $request->boolean('hero.show_phone'),
                'background_image' => $backgroundImage,
            ],
            'trust_badges' => [
                'garantie_decennale' => $request->boolean('trust_badges.garantie_decennale'),
                'certifie_rge' => $request->boolean('trust_badges.certifie_rge'),
                'show_rating' => $request->boolean('trust_badges.show_rating'),
            ],
            'about' => [
                'enabled' => $request->boolean('about.enabled'),
                'title' => $request->input('about.title', 'Qui Sommes-Nous ?'),
                'content' => $request->input('about.content', ''),
                'image' => $aboutImage,
            ],
            'ecology' => [
                'enabled' => $request->boolean('ecology.enabled'),
                'title' => $request->input('ecology.title', 'Notre Engagement Écologique'),
                'content' => $request->input('ecology.content', ''),
            ],
            'financing' => [
                'enabled' => $request->boolean('financing.enabled'),
                'title' => $request->input('financing.title', 'Aides et Financements Disponibles'),
                'content' => $request->input('financing.content', ''),
            ],
            'footer' => [
                'intervention_zone' => $request->input('footer.intervention_zone', ''),
                'about' => $request->input('footer.about', ''),
                'show_cities' => $request->boolean('footer.show_cities'),
            ],
            'sections' => [
                'services' => [
                    'enabled' => $request->boolean('sections.services.enabled'),
                    'title' => $request->input('sections.services.title', 'Nos Services'),
                    'limit' => (int)$request->input('sections.services.limit', 6),
                ],
                'portfolio' => [
                    'enabled' => $request->boolean('sections.portfolio.enabled'),
                    'title' => $request->input('sections.portfolio.title', 'Nos Réalisations'),
                    'limit' => (int)$request->input('sections.portfolio.limit', 6),
                ],
                'reviews' => [
                    'enabled' => $request->boolean('sections.reviews.enabled'),
                    'title' => $request->input('sections.reviews.title', 'Avis de Nos Clients'),
                    'limit' => (int)$request->input('sections.reviews.limit', 6),
                ],
                'why_choose_us' => [
                    'enabled' => $request->boolean('sections.why_choose_us.enabled'),
                    'title' => $request->input('sections.why_choose_us.title', 'Pourquoi Nous Choisir?'),
                ],
                'cta' => [
                    'enabled' => $request->boolean('sections.cta.enabled'),
                    'title' => $request->input('sections.cta.title', 'Prêt à Démarrer Votre Projet?'),
                ],
            ],
            'stats' => json_decode($request->input('stats_json', '[]'), true) ?: [
                ['label' => 'Projets Réalisés', 'value' => '500+', 'icon' => 'fa-check-circle'],
                ['label' => 'Clients Satisfaits', 'value' => '98%', 'icon' => 'fa-smile'],
                ['label' => 'Années d\'Expérience', 'value' => '15+', 'icon' => 'fa-award'],
                ['label' => 'Garantie', 'value' => '10 ans', 'icon' => 'fa-shield-alt'],
            ],
            'partners' => [
                'enabled' => $request->boolean('partners.enabled'),
                'title' => $request->input('partners.title', 'Nos Partenaires'),
                'logos' => $this->handlePartnerLogos($request, $currentConfig),
            ],
        ];

        
        Setting::set('homepage_config', json_encode($config), 'json', 'homepage');
        Setting::clearCache();

        return redirect()->route('admin.homepage.edit')->with('success', 'Page d\'accueil mise à jour avec succès !');
    }

    /**
     * Gérer l'upload et la sauvegarde des logos de partenaires
     */
    private function handlePartnerLogos(Request $request, $currentConfig)
    {
        $partners = [];
        $existingPartners = $currentConfig['partners']['logos'] ?? [];
        
        // Créer le dossier s'il n'existe pas
        $partnersDir = public_path('uploads/partners');
        if (!file_exists($partnersDir)) {
            mkdir($partnersDir, 0755, true);
        }
        
        $names = $request->input('partner_names', []);
        $urls = $request->input('partner_urls', []);
        $logos = $request->hasFile('partner_logos') ? $request->file('partner_logos') : [];
        
        // Parcourir tous les partenaires (basé sur les noms fournis)
        $maxIndex = max(count($names), count($urls), count($logos), count($existingPartners));
        
        for ($index = 0; $index < $maxIndex; $index++) {
            $logo = $logos[$index] ?? null;
            $name = $names[$index] ?? '';
            $url = $urls[$index] ?? '';
            
            // Si un nouveau logo est uploadé
            if ($logo && $logo->isValid()) {
                $filename = 'partner-' . time() . '-' . $index . '.' . $logo->getClientOriginalExtension();
                $logo->move($partnersDir, $filename);
                
                $partners[] = [
                    'logo' => 'uploads/partners/' . $filename,
                    'name' => $name,
                    'url' => $url,
                ];
            } else {
                // Conserver le logo existant s'il existe
                $existingPartner = $existingPartners[$index] ?? null;
                if ($existingPartner && !empty($existingPartner['logo'])) {
                    $partners[] = [
                        'logo' => $existingPartner['logo'],
                        'name' => $name ?: ($existingPartner['name'] ?? ''),
                        'url' => $url ?: ($existingPartner['url'] ?? ''),
                    ];
                } elseif ($name || $url) {
                    // Si seulement le nom ou l'URL est fourni sans logo, on garde quand même
                    $partners[] = [
                        'logo' => $existingPartner['logo'] ?? '',
                        'name' => $name,
                        'url' => $url,
                    ];
                }
            }
        }
        
        return $partners;
    }

    /**
     * Générer le contenu de la page d'accueil avec l'IA
     */
    public function generateHomepageContentAI(Request $request)
    {
        try {
            $apiKey = Setting::get('chatgpt_api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'error' => 'Clé API ChatGPT non configurée'
                ], 400);
            }

            $companyName = Setting::get('company_name', 'Votre Entreprise');
            $companyCity = Setting::get('company_city', 'Paris');
            $companyRegion = Setting::get('company_region', 'Île-de-France');
            $companySpecialization = Setting::get('company_specialization', 'Travaux de Rénovation');

            $prompt = "Tu es un expert en rédaction web pour une entreprise de {$companySpecialization} appelée {$companyName}, située à {$companyCity} en {$companyRegion}.

Génère un contenu optimisé SEO et attractif pour la page d'accueil du site web. Le contenu doit être en français, professionnel et convaincant.

Réponds UNIQUEMENT avec un JSON valide contenant:
{
  \"hero_title\": \"Titre principal accrocheur (max 60 caractères)\",
  \"hero_subtitle\": \"Sous-titre explicatif avec localisation (max 150 caractères)\",
  \"hero_cta_text\": \"Texte du bouton d'appel à l'action (max 30 caractères)\",
  \"about_why_us_points\": [
    {\"title\": \"Point fort 1\", \"description\": \"Description courte\"},
    {\"title\": \"Point fort 2\", \"description\": \"Description courte\"},
    {\"title\": \"Point fort 3\", \"description\": \"Description courte\"}
  ]
}";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un expert en rédaction web et SEO pour le secteur du bâtiment et de la rénovation.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur API: ' . $response->body()
                ], 500);
            }

            $aiResponse = $response->json();
            $content = $aiResponse['choices'][0]['message']['content'] ?? '';
            
            // Extract JSON from response
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }
            
            $generatedContent = json_decode($content, true);

            if (!$generatedContent) {
                return response()->json([
                    'success' => false,
                    'error' => 'Format de réponse invalide'
                ], 500);
            }

            // Update AI usage counter
            $currentCount = (int)Setting::get('ai_generations_count', 0);
            Setting::set('ai_generations_count', $currentCount + 1);
            Setting::set('ai_last_used', now()->toDateTimeString());

            return response()->json([
                'success' => true,
                'content' => $generatedContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer TOUS les textes de la page d'accueil avec l'IA simultanément
     */
    public function generateAllHomepageContentAI(Request $request)
    {
        try {
            $apiKey = Setting::get('chatgpt_api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'error' => 'Clé API ChatGPT non configurée'
                ], 400);
            }

            $companyName = Setting::get('company_name', 'Mon Entreprise');
            $companySpecialization = Setting::get('company_specialization', 'Travaux de Rénovation');
            $companyRegion = Setting::get('company_region', 'Île-de-France');
            $companyDescription = Setting::get('company_description', '');
            
            $prompt = "Tu es un expert en marketing digital pour une entreprise de {$companySpecialization} basée en {$companyRegion}. 
            Génère TOUT le contenu professionnel pour la page d'accueil de {$companyName} :
            
            Informations entreprise :
            - Nom : {$companyName}
            - Spécialisation : {$companySpecialization}
            - Région : {$companyRegion}
            - Description : {$companyDescription}
            
            Génère le contenu pour :
            1. Hero section (titre, sous-titre, CTA)
            2. Section À propos (titre + contenu détaillé sur l'entreprise, histoire, valeurs)
            3. Section Écologie (titre + contenu sur engagement environnemental, matériaux écologiques)
            4. Section Aides et Financements (titre + contenu sur MaPrimeRénov', éco-PTZ, CEE, aides locales)
            5. Footer À propos (mini description entreprise)
            6. Zone d'intervention (description géographique)
            
            IMPORTANT : 
            - Le contenu doit être professionnel et adapté au secteur {$companySpecialization}
            - Inclure des informations sur les aides financières disponibles
            - Mentionner l'engagement écologique et les matériaux durables
            - Adapter le contenu à la région {$companyRegion}
            
            Réponds UNIQUEMENT au format JSON :
            {
                \"hero\": {
                    \"title\": \"titre principal accrocheur\",
                    \"subtitle\": \"sous-titre descriptif\",
                    \"cta_text\": \"texte bouton CTA\"
                },
                \"about\": {
                    \"title\": \"Qui Sommes-Nous ?\",
                    \"content\": \"contenu détaillé sur l'entreprise, son histoire, ses valeurs, son expertise\"
                },
                \"ecology\": {
                    \"title\": \"Notre Engagement Écologique\",
                    \"content\": \"contenu sur l'engagement environnemental, matériaux écologiques, pratiques durables\"
                },
                \"financing\": {
                    \"title\": \"Aides et Financements Disponibles\",
                    \"content\": \"contenu sur MaPrimeRénov', éco-PTZ, CEE, aides locales, financements\"
                },
                \"footer\": {
                    \"about\": \"mini description entreprise pour le footer\",
                    \"intervention_zone\": \"description de la zone d'intervention géographique\"
                }
            }";

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'max_tokens' => 2000,
                    'temperature' => 0.7
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $content = $data['choices'][0]['message']['content'];
            
            // Nettoyer le contenu JSON
            $content = trim($content);
            if (strpos($content, '```json') !== false) {
                $content = str_replace(['```json', '```'], '', $content);
            }
            
            $generatedContent = json_decode($content, true);
            
            if (!$generatedContent) {
                throw new \Exception('Erreur lors du parsing JSON');
            }

            // Incrémenter le compteur d'utilisation IA
            $currentCount = Setting::get('ai_generations_count', 0);
            Setting::set('ai_generations_count', $currentCount + 1);
            Setting::set('ai_last_used', now()->toISOString());

            return response()->json([
                'success' => true,
                'content' => $generatedContent
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update FAQ settings
     */
    public function updateConversion(Request $request)
    {
        $validated = $request->validate([
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'nullable|string|max:255',
            'faqs.*.answer' => 'nullable|string',
        ]);

        // FAQ settings
        if (isset($validated['faqs'])) {
            // Filtrer les FAQ vides
            $faqs = array_filter($validated['faqs'], function($faq) {
                return !empty($faq['question']) && !empty($faq['answer']);
            });
            // Réindexer le tableau
            $faqs = array_values($faqs);
            Setting::set('faqs', json_encode($faqs), 'json', 'conversion');
        }

        Setting::clearCache();
        Artisan::call('config:clear');

        return back()->with('success', 'Configuration FAQ mise à jour avec succès !');
    }

    /**
     * Générer 5 questions fréquentes avec l'IA
     */
    public function generateFaqsWithAI(Request $request)
    {
        try {
            $companyDescription = Setting::get('company_description', '');
            $companyName = Setting::get('company_name', 'Votre Entreprise');
            
            if (empty($companyDescription)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord remplir la description de l\'entreprise dans les paramètres.'
                ], 400);
            }
            
            $prompt = "Génère exactement 5 questions fréquentes (FAQ) pertinentes pour une entreprise de rénovation et de construction.

Informations sur l'entreprise:
- Nom: {$companyName}
- Description: {$companyDescription}

Instructions:
1. Génère exactement 5 questions fréquentes que les clients pourraient se poser
2. Chaque question doit être spécifique et pertinente pour cette entreprise
3. Fournis une réponse détaillée et professionnelle pour chaque question (2-3 phrases minimum)
4. Les questions doivent couvrir différents aspects: services, tarifs, délais, garanties, etc.

Format de réponse JSON strict (sans texte avant ou après):
{
  \"faqs\": [
    {
      \"question\": \"Question 1?\",
      \"answer\": \"Réponse détaillée à la question 1.\"
    },
    {
      \"question\": \"Question 2?\",
      \"answer\": \"Réponse détaillée à la question 2.\"
    }
  ]
}

Réponds UNIQUEMENT avec le JSON valide, sans texte avant ou après.";

            $result = AiService::callAI($prompt, 'Tu es un expert en rédaction web pour le secteur du bâtiment et de la rénovation. Tu génères des FAQ pertinentes et professionnelles.', [
                'max_tokens' => 2000,
                'temperature' => 0.8
            ]);

            if ($result && isset($result['content'])) {
                $content = $result['content'];
                
                // Parser le JSON
                $jsonStart = strpos($content, '{');
                $jsonEnd = strrpos($content, '}');
                
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                    $aiData = json_decode($jsonContent, true);
                    
                    if ($aiData && isset($aiData['faqs']) && is_array($aiData['faqs'])) {
                        // Limiter à 5 FAQ
                        $faqs = array_slice($aiData['faqs'], 0, 5);
                        
                        return response()->json([
                            'success' => true,
                            'faqs' => $faqs,
                            'message' => count($faqs) . ' questions fréquentes générées avec succès !'
                        ]);
                    }
                }
                
                // Si le parsing JSON échoue, essayer d'extraire manuellement
                Log::warning('Parsing JSON FAQ échoué, tentative extraction manuelle', [
                    'content_preview' => substr($content, 0, 500)
                ]);
                
                // Fallback: générer des FAQ basiques
                $fallbackFaqs = $this->generateFallbackFaqs($companyName, $companyDescription);
                
                return response()->json([
                    'success' => true,
                    'faqs' => $fallbackFaqs,
                    'message' => '5 questions fréquentes générées (mode fallback)'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des FAQ. Veuillez réessayer.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération FAQ avec IA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Générer des FAQ de fallback si l'IA échoue
     */
    private function generateFallbackFaqs($companyName, $description)
    {
        return [
            [
                'question' => 'Quels sont vos domaines d\'intervention ?',
                'answer' => "Nous intervenons dans tous les domaines de la rénovation et de la construction. " . substr($description, 0, 100) . "..."
            ],
            [
                'question' => 'Quels sont vos délais d\'intervention ?',
                'answer' => 'Nos délais d\'intervention varient selon la nature et l\'ampleur des travaux. Nous nous efforçons de respecter les délais convenus et vous tenons informé de l\'avancement de votre projet.'
            ],
            [
                'question' => 'Proposez-vous un devis gratuit ?',
                'answer' => 'Oui, nous proposons un devis gratuit et sans engagement pour tous vos projets de rénovation. Contactez-nous pour planifier une visite et obtenir une estimation précise.'
            ],
            [
                'question' => 'Quelles garanties offrez-vous ?',
                'answer' => 'Nous garantissons tous nos travaux et respectons les normes en vigueur. Tous nos artisans sont qualifiés et assurés pour votre tranquillité d\'esprit.'
            ],
            [
                'question' => 'Intervenez-vous dans toute la région ?',
                'answer' => 'Nous intervenons dans toute la région Bretagne. N\'hésitez pas à nous contacter pour vérifier notre zone d\'intervention et planifier une intervention.'
            ]
        ];
    }

}










