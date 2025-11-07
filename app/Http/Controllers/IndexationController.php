<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SitemapService;

class IndexationController extends Controller
{
    /**
     * Afficher la page de gestion de l'indexation
     */
    public function index()
    {
        $seoConfigData = Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // Valeurs par défaut pour l'indexation
        $defaults = [
            'robots_index' => true,
            'robots_follow' => true,
            'robots_archive' => true,
            'robots_snippet' => true,
            'robots_imageindex' => true,
            'sitemap_enabled' => true,
            'sitemap_priority' => 0.8,
            'sitemap_changefreq' => 'weekly',
        ];
        
        $indexationConfig = array_merge($defaults, array_intersect_key($seoConfig, $defaults));
        
        return view('admin.indexation.index', compact('indexationConfig'));
    }

    /**
     * Sauvegarder la configuration d'indexation
     */
    public function update(Request $request)
    {
        $request->validate([
            'robots_index' => 'nullable|boolean',
            'robots_follow' => 'nullable|boolean',
            'robots_archive' => 'nullable|boolean',
            'robots_snippet' => 'nullable|boolean',
            'robots_imageindex' => 'nullable|boolean',
            'sitemap_enabled' => 'nullable|boolean',
            'sitemap_priority' => 'nullable|numeric|min:0|max:1',
            'sitemap_changefreq' => 'nullable|string|in:always,hourly,daily,weekly,monthly,yearly,never'
        ]);

        // Récupérer la configuration SEO existante
        $existingConfig = Setting::get('seo_config', '[]');
        $existingConfig = is_string($existingConfig) ? json_decode($existingConfig, true) : ($existingConfig ?? []);
        
        // Mettre à jour uniquement les paramètres d'indexation
        $existingConfig['robots_index'] = $request->boolean('robots_index', true);
        $existingConfig['robots_follow'] = $request->boolean('robots_follow', true);
        $existingConfig['robots_archive'] = $request->boolean('robots_archive', true);
        $existingConfig['robots_snippet'] = $request->boolean('robots_snippet', true);
        $existingConfig['robots_imageindex'] = $request->boolean('robots_imageindex', true);
        $existingConfig['sitemap_enabled'] = $request->boolean('sitemap_enabled', true);
        $existingConfig['sitemap_priority'] = $request->input('sitemap_priority', 0.8);
        $existingConfig['sitemap_changefreq'] = $request->input('sitemap_changefreq', 'weekly');
        
        // Sauvegarder la configuration
        Setting::set('seo_config', json_encode($existingConfig), 'json', 'seo');
        Setting::clearCache();
        
        return redirect()->route('admin.indexation.index')->with('success', 'Configuration d\'indexation sauvegardée avec succès !');
    }

    /**
     * Mettre à jour le sitemap via AJAX
     */
    public function updateSitemap(Request $request)
    {
        try {
            $sitemapService = new SitemapService();
            $sitemapService->generateSitemap();
            
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
}

