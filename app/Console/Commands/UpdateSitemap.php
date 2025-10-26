<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapService;

class UpdateSitemap extends Command
{
    protected $signature = 'sitemap:update';
    protected $description = 'Update the sitemap with latest data';

    protected $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        parent::__construct();
        $this->sitemapService = $sitemapService;
    }

    public function handle()
    {
        $this->info('🔄 Mise à jour du sitemap...');
        
        if ($this->sitemapService->updateSitemap()) {
            $this->info('✅ Sitemap mis à jour avec succès !');
            return 0;
        } else {
            $this->error('❌ Erreur lors de la mise à jour du sitemap');
            return 1;
        }
    }
}
