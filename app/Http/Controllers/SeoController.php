<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
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
            'robots_index' => true,
            'robots_follow' => true,
            'robots_archive' => true,
            'robots_snippet' => true,
            'robots_imageindex' => true,
            'favicon' => '',
            'apple_touch_icon' => '',
            'manifest' => '',
            'sitemap_enabled' => true,
            'sitemap_priority' => 0.8,
            'sitemap_changefreq' => 'weekly',
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
            'favicon' => 'nullable|image|max:512',
            'apple_touch_icon' => 'nullable|image|max:512',
            'manifest' => 'nullable|file|mimes:json|max:1024',
            'google_analytics' => 'nullable|string|max:50',
            'google_search_console' => 'nullable|string|max:100',
            'facebook_pixel' => 'nullable|string|max:50',
            'google_ads' => 'nullable|string|max:50',
            'bing_webmaster' => 'nullable|string|max:100',
            'schema_markup' => 'nullable|string',
            'sitemap_priority' => 'nullable|numeric|min:0|max:1',
            'sitemap_changefreq' => 'nullable|string|in:always,hourly,daily,weekly,monthly,yearly,never'
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
            'robots_index' => $request->boolean('robots_index'),
            'robots_follow' => $request->boolean('robots_follow'),
            'robots_archive' => $request->boolean('robots_archive'),
            'robots_snippet' => $request->boolean('robots_snippet'),
            'robots_imageindex' => $request->boolean('robots_imageindex'),
            'sitemap_enabled' => $request->boolean('sitemap_enabled'),
            'sitemap_priority' => $request->input('sitemap_priority', 0.8),
            'sitemap_changefreq' => $request->input('sitemap_changefreq', 'weekly'),
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
            $filename = 'favicon-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/seo'), $filename);
            $config['favicon'] = 'uploads/seo/' . $filename;
        }

        if ($request->hasFile('apple_touch_icon')) {
            $file = $request->file('apple_touch_icon');
            $filename = 'apple-touch-icon-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/seo'), $filename);
            $config['apple_touch_icon'] = 'uploads/seo/' . $filename;
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
            
            return redirect()->route('admin.seo.index')->with('success', 'Configuration SEO sauvegardée avec succès !');
            
        } catch (\Exception $e) {
            \Log::error('SEO Config save error: ' . $e->getMessage());
            return redirect()->route('admin.seo.index')->with('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }

    /**
     * Afficher la configuration SEO par page
     */
    public function pages()
    {
        $pages = ['home', 'services', 'portfolio', 'blog', 'ads', 'reviews', 'contact', 'about'];
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
        $pages = ['home', 'services', 'portfolio', 'blog', 'ads', 'reviews', 'contact', 'about'];
        
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
        $seoConfigData = Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        if (!($seoConfig['sitemap_enabled'] ?? true)) {
            return response('Sitemap désactivé', 404);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Page d'accueil
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . url('/') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>' . ($seoConfig['sitemap_changefreq'] ?? 'weekly') . '</changefreq>' . "\n";
        $xml .= '    <priority>' . ($seoConfig['sitemap_priority'] ?? 0.8) . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";

        // Page nos réalisations
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . url('/nos-realisations') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.7</priority>' . "\n";
        $xml .= '  </url>' . "\n";

        // Services
        $services = Setting::get('services', []);
        if (is_string($services)) {
            $services = json_decode($services, true) ?? [];
        }
        
        foreach ($services as $service) {
            if (isset($service['slug']) && ($service['is_visible'] ?? true)) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . url('/services/' . $service['slug']) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.6</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }

        // Portfolio items
        $portfolioItems = Setting::get('portfolio_items', []);
        if (is_string($portfolioItems)) {
            $portfolioItems = json_decode($portfolioItems, true) ?? [];
        }
        foreach ($portfolioItems as $item) {
            if (isset($item['slug']) && ($item['is_visible'] ?? true)) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . url('/nos-realisations/' . $item['slug']) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.5</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }

        // Ads (published only)
        $ads = \App\Models\Ad::where('status', 'published')->orderByDesc('updated_at')->limit(5000)->get(['slug','updated_at']);
        foreach ($ads as $ad) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url('/annonces/' . $ad->slug) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . optional($ad->updated_at)->format('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    /**
     * Générer le robots.txt
     */
    public function generateRobots()
    {
        $seoConfig = Setting::get('seo_config', []);
        
        $robots = "User-agent: *\n";
        
        if (!($seoConfig['robots_index'] ?? true)) {
            $robots .= "Disallow: /\n";
        } else {
            $robots .= "Allow: /\n";
        }
        
        if (!($seoConfig['robots_follow'] ?? true)) {
            $robots .= "Disallow: /*\n";
        }
        
        $robots .= "\nSitemap: " . url('/sitemap.xml') . "\n";
        
        // Debug temporaire
        \Log::info('Robots.txt generated:', ['content' => $robots]);
        
        return response($robots, 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Générer le manifest.json
     */
    public function generateManifest()
    {
        $seoConfig = Setting::get('seo_config', []);
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

        if (!empty($seoConfig['favicon'])) {
            $manifest['icons'][] = [
                'src' => asset($seoConfig['favicon']),
                'sizes' => '192x192',
                'type' => 'image/png'
            ];
        }

        return response()->json($manifest);
    }

    /**
     * Tester la configuration SEO
     */
    public function testSeo()
    {
        $seoConfig = Setting::get('seo_config', []);
        
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
                'status' => !empty($seoConfig['og_image']),
                'message' => !empty($seoConfig['og_image']) ? 'Image OG définie' : 'Image OG manquante',
                'recommendation' => 'Image recommandée : 1200x630px'
            ],
            'favicon' => [
                'status' => !empty($seoConfig['favicon']),
                'message' => !empty($seoConfig['favicon']) ? 'Favicon défini' : 'Favicon manquant',
                'recommendation' => 'Format recommandé : ICO ou PNG 32x32px'
            ],
            'sitemap' => [
                'status' => $seoConfig['sitemap_enabled'] ?? true,
                'message' => ($seoConfig['sitemap_enabled'] ?? true) ? 'Sitemap activé' : 'Sitemap désactivé',
                'recommendation' => 'Le sitemap aide les moteurs de recherche'
            ]
        ];

        return response()->json($tests);
    }
}








