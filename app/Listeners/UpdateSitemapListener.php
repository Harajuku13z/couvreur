<?php

namespace App\Listeners;

use App\Services\SitemapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateSitemapListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        // DÉSACTIVÉ : La mise à jour automatique cause des problèmes
        // Utiliser la commande sitemap:reset manuellement ou via l'interface admin
        // try {
        //     Log::info('🔄 Mise à jour automatique du sitemap...');
        //     $this->sitemapService->updateSitemap();
        //     Log::info('✅ Sitemap mis à jour avec succès');
        // } catch (\Exception $e) {
        //     Log::error('❌ Erreur lors de la mise à jour du sitemap : ' . $e->getMessage());
        // }
    }
}
