<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SitemapService;
use App\Services\AiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeoController extends Controller
{
    /**
     * Afficher la page de gestion SEO
     */
    public function index()
    {
        $seoConfigData = Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // Valeurs par défaut si pas de données
        $defaults = [
            'meta_title' => '',
            'meta_description' => '',
            'meta_keywords' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'twitter_card' => 'summary_large_image',
            'twitter_site' => '',
            'twitter_creator' => '',
            'canonical_url' => '',
            'favicon' => '',
            'apple_touch_icon' => '',
            'manifest' => '',
            'google_analytics' => '',
            'google_search_console' => '',
            'facebook_pixel' => '',
            'google_ads' => '',
            'bing_webmaster' => '',
            'schema_markup' => '',
            'structured_data' => []
        ];
        
        $seoConfig = array_merge($defaults, $seoConfig);
        
        // Debug: Log the SEO config
        \Log::info('SEO Config loaded:', ['seoConfig' => $seoConfig]);

        try {
            return view('admin.seo.index', compact('seoConfig'));
        } catch (\Exception $e) {
            \Log::error('SEO View Error: ' . $e->getMessage());
            return view('admin.seo.simple', compact('seoConfig'));
        }
    }

    /**
     * Sauvegarder la configuration SEO
     */
    public function update(Request $request)
    {
        $request->validate([
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|image|max:5120',
            'twitter_site' => 'nullable|string|max:50',
            'twitter_creator' => 'nullable|string|max:50',
            'canonical_url' => 'nullable|url',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048',
            'favicon_svg' => 'nullable|file|mimes:svg|max:512',
            'bimi_logo' => 'nullable|file|mimes:svg|max:512',
            'apple_touch_icon' => 'nullable|image|mimes:png,jpg,jpeg|max:512',
            'manifest' => 'nullable|file|mimes:json|max:1024',
            'google_analytics' => 'nullable|string|max:50',
            'google_search_console' => 'nullable|string|max:500',
            'facebook_pixel' => 'nullable|string|max:50',
            'google_ads' => 'nullable|string|max:50',
            'bing_webmaster' => 'nullable|string|max:500',
            'schema_markup' => 'nullable|string'
        ]);

        // Récupérer la configuration existante
        $existingConfig = Setting::get('seo_config', '[]');
        $existingConfig = is_string($existingConfig) ? json_decode($existingConfig, true) : ($existingConfig ?? []);
        
        $config = [
            'meta_title' => $request->input('meta_title', ''),
            'meta_description' => $request->input('meta_description', ''),
            'meta_keywords' => $request->input('meta_keywords', ''),
            'og_title' => $request->input('og_title', ''),
            'og_description' => $request->input('og_description', ''),
            'og_image' => $existingConfig['og_image'] ?? '', // Préserver l'image existante
            'twitter_card' => $request->input('twitter_card', 'summary_large_image'),
            'twitter_site' => $request->input('twitter_site', ''),
            'twitter_creator' => $request->input('twitter_creator', ''),
            'canonical_url' => $request->input('canonical_url', ''),
            'google_analytics' => $request->input('google_analytics', ''),
            'google_search_console' => $request->input('google_search_console', ''),
            'facebook_pixel' => $request->input('facebook_pixel', ''),
            'google_ads' => $request->input('google_ads', ''),
            'bing_webmaster' => $request->input('bing_webmaster', ''),
            'schema_markup' => $request->input('schema_markup', ''),
            'structured_data' => $request->input('structured_data', []),
            'favicon' => $existingConfig['favicon'] ?? '', // Préserver l'image existante
            'apple_touch_icon' => $existingConfig['apple_touch_icon'] ?? '', // Préserver l'image existante
            'manifest' => $existingConfig['manifest'] ?? '' // Préserver le manifest existant
        ];

        // Gestion des uploads d'images
        if ($request->hasFile('og_image')) {
            $file = $request->file('og_image');
            $filename = 'og-image-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/seo'), $filename);
            $config['og_image'] = 'uploads/seo/' . $filename;
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'favicon-source-' . time() . '.' . $file->getClientOriginalExtension();
            $sourcePath = public_path('uploads/seo/' . $filename);
            $file->move(public_path('uploads/seo'), $filename);
            $config['favicon'] = 'uploads/seo/' . $filename;
            
            // Générer toutes les tailles de favicon
            try {
                $faviconService = new \App\Services\FaviconService();
                $results = $faviconService->generateFavicons($sourcePath);
                
                if ($results['success']) {
                    \Log::info('Favicons générés avec succès', ['files' => $results['files']]);
                    // Sauvegarder les chemins des favicons générés
                    $config['favicon_16x16'] = 'favicons/favicon-16x16.png';
                    $config['favicon_32x32'] = 'favicons/favicon-32x32.png';
                    $config['favicon_48x48'] = 'favicons/favicon-48x48.png';
                    $config['favicon_96x96'] = 'favicons/favicon-96x96.png';
                    $config['favicon_192x192'] = 'favicons/favicon-192x192.png';
                    $config['favicon_512x512'] = 'favicons/favicon-512x512.png';
                    $config['apple_touch_icon'] = 'favicons/apple-touch-icon.png';
                } else {
                    \Log::warning('Erreurs lors de la génération des favicons', ['errors' => $results['errors']]);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur génération favicons: ' . $e->getMessage());
            }
        }
        
        // Gestion du SVG favicon
        if ($request->hasFile('favicon_svg')) {
            $file = $request->file('favicon_svg');
            $filename = 'favicon-' . time() . '.svg';
            $file->move(public_path('favicons'), $filename);
            $config['favicon_svg'] = 'favicons/' . $filename;
        }

        // Gestion du logo BIMI
        $bimiUploadError = null;
        $bimiUploadSuccess = false;
        
        if ($request->hasFile('bimi_logo')) {
            try {
                $file = $request->file('bimi_logo');
                
                \Log::info('Tentative upload logo BIMI', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError(),
                ]);
                
                // Vérifier que le fichier est valide
                if (!$file->isValid()) {
                    $bimiUploadError = 'Fichier invalide. Erreur : ' . $file->getError();
                    \Log::error('Logo BIMI invalide', ['error' => $file->getError()]);
                } else {
                    // Vérifier le type MIME
                    $mimeType = $file->getMimeType();
                    $allowedMimes = ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml'];
                    
                    if (!in_array($mimeType, $allowedMimes)) {
                        $bimiUploadError = 'Format de fichier invalide. Format reçu : ' . $mimeType . '. Format attendu : SVG (image/svg+xml)';
                        \Log::error('Logo BIMI format invalide', ['mime_type' => $mimeType]);
                    } else {
                        // Créer le dossier logo s'il n'existe pas
                        $logoDir = public_path('logo');
                        if (!file_exists($logoDir)) {
                            if (!mkdir($logoDir, 0755, true)) {
                                $bimiUploadError = 'Impossible de créer le dossier logo. Vérifiez les permissions.';
                                \Log::error('Impossible de créer le dossier logo', ['path' => $logoDir]);
                            }
                        }
                        
                        if (!$bimiUploadError) {
                            // Vérifier que le dossier est accessible en écriture
                            if (!is_writable($logoDir)) {
                                $bimiUploadError = 'Le dossier logo n\'est pas accessible en écriture. Permissions : ' . substr(sprintf('%o', fileperms($logoDir)), -4);
                                \Log::error('Dossier logo non accessible en écriture', ['path' => $logoDir, 'perms' => substr(sprintf('%o', fileperms($logoDir)), -4)]);
                            } else {
                                // Sauvegarder comme logo.svg (écraser l'ancien si existe)
                                $filename = 'logo.svg';
                                $targetPath = $logoDir . '/' . $filename;
                                
                                // Supprimer l'ancien fichier s'il existe
                                if (file_exists($targetPath)) {
                                    if (!unlink($targetPath)) {
                                        $bimiUploadError = 'Impossible de supprimer l\'ancien logo. Vérifiez les permissions.';
                                        \Log::error('Impossible de supprimer l\'ancien logo', ['path' => $targetPath]);
                                    }
                                }
                                
                                if (!$bimiUploadError) {
                                    // Déplacer le nouveau fichier
                                    try {
                                        $file->move($logoDir, $filename);
                                        
                                        // Vérifier que le fichier a bien été créé
                                        if (file_exists($targetPath)) {
                                            // Définir les permissions correctes (644 = rw-r--r--)
                                            chmod($targetPath, 0644);
                                            $config['bimi_logo'] = 'logo/' . $filename;
                                            $bimiUploadSuccess = true;
                                            \Log::info('Logo BIMI uploadé avec succès', [
                                                'path' => 'logo/' . $filename,
                                                'size' => filesize($targetPath),
                                                'url' => asset('logo/' . $filename),
                                                'permissions' => substr(sprintf('%o', fileperms($targetPath)), -4)
                                            ]);
                                        } else {
                                            $bimiUploadError = 'Le fichier n\'a pas été créé après le déplacement. Vérifiez les permissions du dossier.';
                                            \Log::error('Logo BIMI non créé après déplacement', ['target_path' => $targetPath]);
                                        }
                                    } catch (\Exception $e) {
                                        $bimiUploadError = 'Erreur lors du déplacement du fichier : ' . $e->getMessage();
                                        \Log::error('Erreur déplacement logo BIMI', [
                                            'error' => $e->getMessage(),
                                            'trace' => $e->getTraceAsString()
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $bimiUploadError = 'Erreur inattendue : ' . $e->getMessage();
                \Log::error('Exception lors de l\'upload logo BIMI', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Log::info('Aucun fichier logo BIMI dans la requête', [
                'has_file' => $request->hasFile('bimi_logo'),
                'all_files' => array_keys($request->allFiles())
            ]);
        }

        // Apple Touch Icon est maintenant généré automatiquement depuis le favicon
        // Mais on garde la possibilité d'en uploader un manuellement
        if ($request->hasFile('apple_touch_icon')) {
            $file = $request->file('apple_touch_icon');
            $filename = 'apple-touch-icon-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('favicons'), $filename);
            $config['apple_touch_icon'] = 'favicons/' . $filename;
        }

        if ($request->hasFile('manifest')) {
            $file = $request->file('manifest');
            $filename = 'manifest-' . time() . '.json';
            $file->move(public_path('uploads/seo'), $filename);
            $config['manifest'] = 'uploads/seo/' . $filename;
        }

        // Debug: Log the config before saving
        \Log::info('SEO Config to save:', ['config' => $config]);
        
        try {
        // Sauvegarder en JSON (pour la compatibilité avec l'interface admin)
        Setting::set('seo_config', json_encode($config), 'json', 'seo');
        
        // Sauvegarder aussi les paramètres individuels (pour la compatibilité avec le layout)
        Setting::set('meta_title', $config['meta_title'], 'string', 'seo');
        Setting::set('meta_description', $config['meta_description'], 'string', 'seo');
        Setting::set('meta_keywords', $config['meta_keywords'], 'string', 'seo');
        Setting::set('og_title', $config['og_title'], 'string', 'seo');
        Setting::set('og_description', $config['og_description'], 'string', 'seo');
            
            \Log::info('SEO Config saved successfully');
            
            // Préparer le message de succès avec info sur le logo BIMI
            $successMessage = 'Configuration SEO sauvegardée avec succès !';
            if ($bimiUploadSuccess) {
                $successMessage .= ' Logo BIMI uploadé avec succès.';
            } elseif ($bimiUploadError) {
                $successMessage .= ' ⚠️ Erreur logo BIMI : ' . $bimiUploadError;
            }
            
            $redirect = redirect()->route('admin.seo.index')->with('success', $successMessage);
            
            // Ajouter un message d'erreur séparé si nécessaire
            if ($bimiUploadError) {
                $redirect->with('bimi_error', $bimiUploadError);
            }
            
            return $redirect;
            
        } catch (\Exception $e) {
            \Log::error('SEO Config save error: ' . $e->getMessage());
            $errorMessage = 'Erreur lors de la sauvegarde : ' . $e->getMessage();
            if ($bimiUploadError) {
                $errorMessage .= ' | Erreur logo BIMI : ' . $bimiUploadError;
            }
            return redirect()->route('admin.seo.index')->with('error', $errorMessage);
        }
    }

    /**
     * Afficher la configuration SEO par page
     */
    public function pages()
    {
        $pages = ['home', 'services', 'portfolio', 'blog', 'ads', 'reviews', 'contact', 'mentions-legales', 'politique-confidentialite', 'cgv'];
        $seoPages = [];
        
        foreach ($pages as $page) {
            $seoPages[$page] = [
                'meta_title' => Setting::get("seo_page_{$page}_meta_title", ''),
                'meta_description' => Setting::get("seo_page_{$page}_meta_description", ''),
                'og_title' => Setting::get("seo_page_{$page}_og_title", ''),
                'og_description' => Setting::get("seo_page_{$page}_og_description", ''),
                'og_image' => Setting::get("seo_page_{$page}_og_image", ''),
            ];
        }
        
        return view('admin.seo.pages', compact('seoPages'));
    }

    /**
     * Mettre à jour les métadonnées de toutes les pages
     */
    public function updatePages(Request $request)
    {
        $pages = ['home', 'services', 'portfolio', 'blog', 'ads', 'reviews', 'contact', 'mentions-legales', 'politique-confidentialite', 'cgv'];
        
        foreach ($pages as $page) {
            // Sauvegarder les métadonnées de base
            if ($request->has("{$page}_meta_title")) {
                Setting::set("seo_page_{$page}_meta_title", $request->input("{$page}_meta_title", ''));
            }
            if ($request->has("{$page}_meta_description")) {
                Setting::set("seo_page_{$page}_meta_description", $request->input("{$page}_meta_description", ''));
            }
            if ($request->has("{$page}_og_title")) {
                Setting::set("seo_page_{$page}_og_title", $request->input("{$page}_og_title", ''));
            }
            if ($request->has("{$page}_og_description")) {
                Setting::set("seo_page_{$page}_og_description", $request->input("{$page}_og_description", ''));
            }
            
            // Gérer l'upload d'image Open Graph
            if ($request->hasFile("{$page}_og_image")) {
                $file = $request->file("{$page}_og_image");
                $filename = "og-{$page}-" . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/seo'), $filename);
                Setting::set("seo_page_{$page}_og_image", "uploads/seo/{$filename}");
            }
        }
        
        Setting::clearCache();
        
        return redirect()->route('admin.seo.pages')->with('success', 'Configuration SEO des pages sauvegardée avec succès !');
    }

    /**
     * Générer le sitemap XML
     */
    public function generateSitemap()
    {
        // NE PAS régénérer à chaque requête - juste servir le fichier existant
        // La régénération doit être faite manuellement via sitemap:reset ou l'interface admin
        $sitemapPath = public_path('sitemap.xml');
        
        if (file_exists($sitemapPath)) {
            $content = file_get_contents($sitemapPath);
            return response($content, 200)->header('Content-Type', 'application/xml');
        }
        
        // Si le fichier n'existe pas, le générer une seule fois
        try {
            $sitemapService = new SitemapService();
            $result = $sitemapService->generateSitemap();
            
            if ($result['success'] && file_exists($sitemapPath)) {
                $content = file_get_contents($sitemapPath);
                return response($content, 200)->header('Content-Type', 'application/xml');
            } else {
                return response('Erreur lors de la génération du sitemap', 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur génération sitemap: ' . $e->getMessage());
            return response('Erreur lors de la génération du sitemap', 500);
        }
    }

    /**
     * Mettre à jour le sitemap via AJAX
     */
    public function updateSitemap(Request $request)
    {
        try {
            // Utiliser le service de sitemap pour générer le sitemap
            $sitemapService = new SitemapService();
            $sitemapService->generateSitemap();
            
            // Vérifier que le fichier a été créé
            $sitemapPath = public_path('sitemap.xml');
            if (file_exists($sitemapPath)) {
                $fileSize = filesize($sitemapPath);
                $lastModified = date('d/m/Y H:i:s', filemtime($sitemapPath));
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sitemap mis à jour avec succès',
                    'status' => "Mis à jour le {$lastModified} ({$fileSize} octets)",
                    'file_size' => $fileSize,
                    'last_modified' => $lastModified
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: Le fichier sitemap n\'a pas été créé'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour sitemap: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du sitemap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer le robots.txt
     */
    public function generateRobots()
    {
        $seoConfig = Setting::get('seo_config', []);
        
        $robots = "User-agent: *\n";
        
        // Par défaut, permettre l'indexation de tout le site
        $robots .= "Allow: /\n";
        
        // Autoriser explicitement le favicon et les icônes (important pour Google)
        $robots .= "Allow: /favicon.ico\n";
        $robots .= "Allow: /favicon*\n";
        $robots .= "Allow: /manifest.json\n";
        
        // Bloquer seulement les dossiers sensibles
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /storage/\n";
        $robots .= "Disallow: /bootstrap/\n";
        $robots .= "Disallow: /vendor/\n";
        $robots .= "Disallow: /config/\n";
        $robots .= "Disallow: /database/\n";
        $robots .= "Disallow: /resources/\n";
        $robots .= "Disallow: /routes/\n";
        $robots .= "Disallow: /app/\n";
        $robots .= "Disallow: /artisan\n";
        $robots .= "Disallow: /composer.json\n";
        $robots .= "Disallow: /composer.lock\n";
        $robots .= "Disallow: /package.json\n";
        $robots .= "Disallow: /phpunit.xml\n";
        $robots .= "Disallow: /vite.config.js\n";
        $robots .= "Disallow: /.env\n";
        $robots .= "Disallow: /.git/\n";
        $robots .= "Disallow: /.gitignore\n";
        $robots .= "Disallow: /README.md\n";
        
        // Ajouter tous les sitemaps trouvés
        $robots .= "\n";
        
        // Sitemap index principal (sitemap.xml liste tous les autres sitemaps)
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
        
        // Optionnel : Ajouter aussi sitemap_index.xml si il existe
        if (file_exists(public_path('sitemap_index.xml'))) {
            $robots .= "Sitemap: " . url('/sitemap_index.xml') . "\n";
        }
        
        // Trouver tous les sitemaps numérotés (sitemap1.xml, sitemap2.xml, etc.)
        $sitemapFiles = glob(public_path('sitemap*.xml'));
        foreach ($sitemapFiles as $sitemapFile) {
            $filename = basename($sitemapFile);
            // Ignorer sitemap.xml (c'est l'index) et sitemap_index.xml
            if ($filename !== 'sitemap.xml' && $filename !== 'sitemap_index.xml') {
                $robots .= "Sitemap: " . url('/' . $filename) . "\n";
            }
        }
        
        // Debug temporaire
        \Log::info('Robots.txt generated:', ['content' => $robots]);
        
        return response($robots, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Générer le manifest.json
     */
    public function generateManifest()
    {
        $seoConfigData = Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        $companyName = Setting::get('company_name', 'Votre Entreprise');
        
        $manifest = [
            'name' => $companyName,
            'short_name' => Str::limit($companyName, 12),
            'description' => $seoConfig['meta_description'] ?? 'Site web professionnel',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => Setting::get('primary_color', '#3b82f6'),
            'icons' => []
        ];

        // Récupérer le favicon depuis site_favicon ou seo_config
        $faviconPath = Setting::get('site_favicon');
        if (!$faviconPath && !empty($seoConfig['favicon'])) {
            $faviconPath = $seoConfig['favicon'];
        }
        
        // Utiliser les favicons générés si disponibles
        $faviconService = new \App\Services\FaviconService();
        $manifestIcons = $faviconService->generateManifestIcons();
        
        if (!empty($manifestIcons)) {
            $manifest['icons'] = $manifestIcons;
        } else {
            // Fallback: utiliser le favicon source
            $faviconPath = Setting::get('site_favicon');
            if (!$faviconPath && !empty($seoConfig['favicon'])) {
                $faviconPath = $seoConfig['favicon'];
            }
            
            if ($faviconPath) {
                $faviconUrl = asset($faviconPath);
                $extension = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
                $mimeType = 'image/png';
                
                if ($extension === 'ico') {
                    $mimeType = 'image/x-icon';
                } elseif ($extension === 'jpg' || $extension === 'jpeg') {
                    $mimeType = 'image/jpeg';
                } elseif ($extension === 'svg') {
                    $mimeType = 'image/svg+xml';
                }
                
                // Ajouter les tailles requises pour le manifest
                $sizes = ['192x192', '512x512'];
                foreach ($sizes as $size) {
                    $manifest['icons'][] = [
                        'src' => $faviconUrl,
                        'sizes' => $size,
                        'type' => $mimeType,
                        'purpose' => 'any maskable'
                    ];
                }
            }
        }

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }

    /**
     * Tester la configuration SEO
     */
    public function testSeo()
    {
        $seoConfig = Setting::get('seo_config', []);
        $seoConfig = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
        
        // Utiliser le service de validation
        $validationService = new \App\Services\SeoValidationService();
        $faviconValidation = $validationService->validateFavicon();
        $ogImageValidation = $validationService->validateOgImage();
        
        $tests = [
            'meta_title' => [
                'status' => !empty($seoConfig['meta_title']),
                'message' => !empty($seoConfig['meta_title']) ? 'Titre meta défini' : 'Titre meta manquant',
                'recommendation' => 'Le titre doit faire entre 50-60 caractères'
            ],
            'meta_description' => [
                'status' => !empty($seoConfig['meta_description']),
                'message' => !empty($seoConfig['meta_description']) ? 'Description meta définie' : 'Description meta manquante',
                'recommendation' => 'La description doit faire entre 150-160 caractères'
            ],
            'og_image' => [
                'status' => $ogImageValidation['valid'],
                'message' => $ogImageValidation['valid'] ? 'Image OG valide' : 'Image OG invalide',
                'recommendation' => 'Image recommandée : 1200x630px, accessible en HTTPS',
                'errors' => $ogImageValidation['errors'] ?? [],
                'warnings' => $ogImageValidation['warnings'] ?? [],
                'info' => $ogImageValidation['info'] ?? [],
                'image_url' => $ogImageValidation['image_url'] ?? null
            ],
            'favicon' => [
                'status' => $faviconValidation['valid'],
                'message' => $faviconValidation['valid'] ? 'Favicon valide' : 'Favicon invalide',
                'recommendation' => 'Format recommandé : PNG/ICO 48-512px, accessible en HTTPS',
                'errors' => $faviconValidation['errors'] ?? [],
                'warnings' => $faviconValidation['warnings'] ?? [],
                'info' => $faviconValidation['info'] ?? [],
                'favicon_url' => $faviconValidation['favicon_url'] ?? null
            ],
            'sitemap' => [
                'status' => $seoConfig['sitemap_enabled'] ?? true,
                'message' => ($seoConfig['sitemap_enabled'] ?? true) ? 'Sitemap activé' : 'Sitemap désactivé',
                'recommendation' => 'Le sitemap aide les moteurs de recherche'
            ]
        ];

        return response()->json($tests);
    }
    
    /**
     * Valider complètement le SEO pour Google
     */
    public function validateSeoForGoogle(Request $request)
    {
        try {
            $validationService = new \App\Services\SeoValidationService();
            $pageUrl = $request->input('url', url('/'));
            
            $results = $validationService->validateMetaTags($pageUrl);
            
            return response()->json([
                'success' => true,
                'validation' => $results,
                'recommendations' => $this->getRecommendations($results)
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur validation SEO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtenir les recommandations basées sur la validation
     */
    private function getRecommendations($validation)
    {
        $recommendations = [];
        
        // Favicon
        if (!$validation['favicon']['valid']) {
            $recommendations[] = [
                'type' => 'error',
                'title' => 'Favicon non conforme',
                'message' => 'Votre favicon ne respecte pas les critères Google. ' . implode(' ', $validation['favicon']['errors'])
            ];
        }
        
        // Image OG
        if (!$validation['og_image']['valid']) {
            $recommendations[] = [
                'type' => 'error',
                'title' => 'Image Open Graph non conforme',
                'message' => 'Votre image OG ne respecte pas les critères Google. ' . implode(' ', $validation['og_image']['errors'])
            ];
        }
        
        return $recommendations;
    }

    /**
     * Générer le contenu SEO avec l'IA basé sur la description de l'entreprise
     */
    public function generateSeoWithAI(Request $request)
    {
        try {
            // Récupérer les informations de l'entreprise
            $companyName = Setting::get('company_name', 'Votre Entreprise');
            $companyDescription = Setting::get('company_description', '');
            $companySpecialization = Setting::get('company_specialization', 'Travaux de Rénovation');
            $companyCity = Setting::get('company_city', '');
            $companyRegion = Setting::get('company_region', 'Bretagne');
            
            if (empty($companyDescription)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord configurer la description de votre entreprise dans les paramètres.'
                ], 400);
            }
            
            // Construire le prompt pour l'IA
            $prompt = "Tu es un expert en SEO et rédaction web. Génère un contenu SEO optimisé pour une entreprise de rénovation.

INFORMATIONS DE L'ENTREPRISE:
- Nom: {$companyName}
- Description: {$companyDescription}
- Spécialisation: {$companySpecialization}
" . (!empty($companyCity) ? "- Localisation: {$companyCity}, {$companyRegion}\n" : "");

            $prompt .= "
GÉNÈRE UN CONTENU SEO COMPLET AU FORMAT JSON STRICT avec les champs suivants:
{
  \"meta_title\": \"Titre SEO optimisé (max 60 caractères, incluant le nom de l'entreprise et la spécialisation)\",
  \"meta_description\": \"Description SEO optimisée (max 160 caractères, accrocheuse et incluant des mots-clés pertinents)\",
  \"meta_keywords\": \"Mots-clés pertinents séparés par des virgules (max 10-15 mots-clés)\",
  \"og_title\": \"Titre optimisé pour les réseaux sociaux (max 60 caractères)\",
  \"og_description\": \"Description optimisée pour les réseaux sociaux (max 160 caractères, engageante)\"
}

IMPORTANT:
- Le titre meta doit être accrocheur et inclure le nom de l'entreprise
- La description doit être persuasive et inclure un appel à l'action
- Les mots-clés doivent être pertinents pour le secteur de la rénovation
- Le contenu doit être en français
- Réponds UNIQUEMENT avec le JSON, sans texte avant ou après";

            // Appeler l'IA
            $result = AiService::callAI($prompt, 'Tu es un expert en SEO et rédaction web pour le secteur du bâtiment et de la rénovation.', [
                'max_tokens' => 1000,
                'temperature' => 0.7
            ]);

            if (!$result || !isset($result['content'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération avec l\'IA. Vérifiez votre configuration API.'
                ], 500);
            }

            $content = $result['content'];
            
            // Extraire le JSON de la réponse
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }
            
            // Nettoyer le contenu
            $content = trim($content);
            if (strpos($content, '{') !== 0) {
                $content = substr($content, strpos($content, '{'));
            }
            if (strrpos($content, '}') !== false) {
                $content = substr($content, 0, strrpos($content, '}') + 1);
            }
            
            $generatedContent = json_decode($content, true);

            if (!$generatedContent || json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erreur parsing JSON SEO généré', [
                    'content' => $content,
                    'json_error' => json_last_error_msg()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du parsing de la réponse de l\'IA. Réessayez.'
                ], 500);
            }

            // Valider et nettoyer les champs - Ne pas tronquer, laisser Google gérer l'affichage
            // Google peut afficher jusqu'à 60 caractères pour les titres (mais accepte plus)
            // et jusqu'à 320 caractères pour les descriptions (anciennement 160)
            $seoContent = [
                'meta_title' => trim($generatedContent['meta_title'] ?? ''),
                'meta_description' => trim($generatedContent['meta_description'] ?? ''),
                'meta_keywords' => Str::limit($generatedContent['meta_keywords'] ?? '', 255, ''),
                'og_title' => trim($generatedContent['og_title'] ?? $generatedContent['meta_title'] ?? ''),
                'og_description' => trim($generatedContent['og_description'] ?? $generatedContent['meta_description'] ?? '')
            ];

            return response()->json([
                'success' => true,
                'content' => $seoContent
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur génération SEO avec IA: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer le contenu SEO d'une page spécifique avec l'IA
     */
    public function generatePageSeoWithAI(Request $request)
    {
        try {
            $pageName = $request->input('page');
            if (empty($pageName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Page non spécifiée'
                ], 400);
            }

            // Mapping des noms de pages vers des descriptions
            $pageDescriptions = [
                'home' => 'Page d\'accueil présentant l\'entreprise et ses services de rénovation',
                'services' => 'Page présentant tous les services de rénovation proposés',
                'portfolio' => 'Page présentant les réalisations et projets de l\'entreprise',
                'blog' => 'Page du blog avec articles sur la rénovation et conseils',
                'ads' => 'Page des annonces de services de rénovation par ville',
                'reviews' => 'Page des avis clients et témoignages',
                'contact' => 'Page de contact pour demander un devis ou des informations',
                'mentions-legales' => 'Page des mentions légales',
                'politique-confidentialite' => 'Page de la politique de confidentialité',
                'cgv' => 'Page des conditions générales de vente'
            ];

            $pageDescription = $pageDescriptions[$pageName] ?? 'Page du site';

            // Récupérer les informations de l'entreprise
            $companyName = Setting::get('company_name', 'Votre Entreprise');
            $companyDescription = Setting::get('company_description', '');
            $companySpecialization = Setting::get('company_specialization', 'Travaux de Rénovation');
            $companyCity = Setting::get('company_city', '');
            $companyRegion = Setting::get('company_region', 'Bretagne');
            
            if (empty($companyDescription)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez d\'abord configurer la description de votre entreprise dans les paramètres.'
                ], 400);
            }
            
            // Construire le prompt pour l'IA
            $prompt = "Tu es un expert en SEO et rédaction web. Génère un contenu SEO optimisé pour une page spécifique d'un site web d'entreprise de rénovation.

INFORMATIONS DE L'ENTREPRISE:
- Nom: {$companyName}
- Description: {$companyDescription}
- Spécialisation: {$companySpecialization}
" . (!empty($companyCity) ? "- Localisation: {$companyCity}, {$companyRegion}\n" : "");

            $prompt .= "
PAGE À OPTIMISER: {$pageDescription}

GÉNÈRE UN CONTENU SEO COMPLET AU FORMAT JSON STRICT avec les champs suivants:
{
  \"meta_title\": \"Titre SEO optimisé (max 60 caractères, incluant le nom de l'entreprise et adapté à la page)\",
  \"meta_description\": \"Description SEO optimisée (max 160 caractères, accrocheuse et incluant des mots-clés pertinents pour cette page)\",
  \"og_title\": \"Titre optimisé pour les réseaux sociaux (max 60 caractères, adapté à la page)\",
  \"og_description\": \"Description optimisée pour les réseaux sociaux (max 160 caractères, engageante et adaptée à la page)\"
}

IMPORTANT:
- Le titre meta doit être accrocheur et inclure le nom de l'entreprise
- La description doit être persuasive et inclure un appel à l'action adapté à la page
- Le contenu doit être en français
- Adapte le contenu au type de page ({$pageDescription})
- Réponds UNIQUEMENT avec le JSON, sans texte avant ou après";

            // Appeler l'IA
            $result = AiService::callAI($prompt, 'Tu es un expert en SEO et rédaction web pour le secteur du bâtiment et de la rénovation.', [
                'max_tokens' => 1000,
                'temperature' => 0.7
            ]);

            if (!$result || !isset($result['content'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération avec l\'IA. Vérifiez votre configuration API.'
                ], 500);
            }

            $content = $result['content'];
            
            // Extraire le JSON de la réponse
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            } elseif (preg_match('/```\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }
            
            // Nettoyer le contenu
            $content = trim($content);
            if (strpos($content, '{') !== 0) {
                $content = substr($content, strpos($content, '{'));
            }
            if (strrpos($content, '}') !== false) {
                $content = substr($content, 0, strrpos($content, '}') + 1);
            }
            
            $generatedContent = json_decode($content, true);

            if (!$generatedContent || json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erreur parsing JSON SEO généré pour page', [
                    'page' => $pageName,
                    'content' => $content,
                    'json_error' => json_last_error_msg()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du parsing de la réponse de l\'IA. Réessayez.'
                ], 500);
            }

            // Valider et nettoyer les champs - Ne pas tronquer, laisser Google gérer l'affichage
            // Google peut afficher jusqu'à 60 caractères pour les titres (mais accepte plus)
            // et jusqu'à 320 caractères pour les descriptions (anciennement 160)
            $seoContent = [
                'meta_title' => trim($generatedContent['meta_title'] ?? ''),
                'meta_description' => trim($generatedContent['meta_description'] ?? ''),
                'og_title' => trim($generatedContent['og_title'] ?? $generatedContent['meta_title'] ?? ''),
                'og_description' => trim($generatedContent['og_description'] ?? $generatedContent['meta_description'] ?? '')
            ];

            return response()->json([
                'success' => true,
                'seo_content' => $seoContent
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur génération SEO IA pour page: ' . $e->getMessage(), [
                'page' => $request->input('page'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }
}








