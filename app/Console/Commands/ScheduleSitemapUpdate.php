<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapService;

class ScheduleSitemapUpdate extends Command
{
    protected $signature = 'sitemap:schedule-update';
    protected $description = 'Schedule automatic sitemap updates';

    protected $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        parent::__construct();
        $this->sitemapService = $sitemapService;
    }

    public function handle()
    {
        $this->info('🔄 Mise à jour programmée du sitemap...');
        
        if ($this->sitemapService->updateSitemap()) {
            $this->info('✅ Sitemap mis à jour avec succès !');
            $this->info('📅 Cette commande peut être programmée avec cron pour des mises à jour automatiques');
            return 0;
        } else {
            $this->error('❌ Erreur lors de la mise à jour du sitemap');
            return 1;
        }
    }
}
