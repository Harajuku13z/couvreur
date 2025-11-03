<?php

namespace App\Listeners;

use App\Services\GoogleSearchConsoleService;
use App\Services\SitemapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SubmitToGoogleSearchConsoleListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $googleService;
    protected $sitemapService;

    public function __construct(GoogleSearchConsoleService $googleService, SitemapService $sitemapService)
    {
        $this->googleService = $googleService;
        $this->sitemapService = $sitemapService;
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            // Récupérer l'annonce depuis l'événement
            $ad = $event->ad ?? null;
            
            if (!$ad) {
                Log::warning('⚠️ Pas d\'annonce trouvée dans l\'événement');
                return;
            }

            // Vérifier que l'annonce est publiée
            if ($ad->status !== 'published') {
                Log::info("ℹ️ Annonce non publiée, pas de soumission à Google : {$ad->slug}");
                return;
            }

            // Construire l'URL de l'annonce
            $adUrl = '/annonces/' . $ad->slug;
            
            Log::info("🔔 Soumission de l'annonce à Google Search Console : {$adUrl}");
            
            // 1. Mettre à jour le sitemap
            Log::info('🔄 Mise à jour du sitemap...');
            $this->sitemapService->updateSitemap();
            Log::info('✅ Sitemap mis à jour');
            
            // 2. Soumettre l'URL de l'annonce à Google
            $this->googleService->submitUrl($adUrl);
            
            // 3. Soumettre le sitemap mis à jour à Google Search Console
            Log::info('🗺️ Soumission du sitemap à Google Search Console...');
            $this->googleService->submitSitemap();
            
            Log::info("✅ Annonce soumise avec succès à Google Search Console : {$adUrl}");
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur lors de la soumission à Google Search Console : ' . $e->getMessage());
        }
    }
}

