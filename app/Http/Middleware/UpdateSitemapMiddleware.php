<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SitemapService;
use Illuminate\Support\Facades\Log;

class UpdateSitemapMiddleware
{
    protected $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Vérifier si c'est une requête POST qui pourrait affecter le sitemap
        if ($request->isMethod('POST') && $this->shouldUpdateSitemap($request)) {
            try {
                Log::info('🔄 Mise à jour automatique du sitemap après modification...');
                $this->sitemapService->updateSitemap();
                Log::info('✅ Sitemap mis à jour automatiquement');
            } catch (\Exception $e) {
                Log::error('❌ Erreur lors de la mise à jour automatique du sitemap : ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Déterminer si le sitemap doit être mis à jour
     */
    protected function shouldUpdateSitemap(Request $request)
    {
        $path = $request->path();
        
        // Mettre à jour le sitemap pour ces routes
        $updateRoutes = [
            'admin/ads',
            'admin/articles',
            'admin/services',
            'admin/portfolio',
            'admin/config'
        ];

        foreach ($updateRoutes as $route) {
            if (str_contains($path, $route)) {
                return true;
            }
        }

        return false;
    }
}
