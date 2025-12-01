<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapService;

class GenerateSitemapIndex extends Command
{
    protected $signature = 'sitemap:generate-index';
    protected $description = 'Generate sitemap index in sitemap/ directory';

    public function handle()
    {
        $this->info('🚀 Génération de l\'index de sitemap...');
        
        $sitemapService = new SitemapService();
        $result = $sitemapService->generateSitemapIndex();
        
        if ($result['success']) {
            $this->info("✅ Index créé avec succès!");
            $this->info("   📁 Chemin: {$result['path']}");
            $this->info("   🔗 URL: {$result['url']}");
            $this->info("   📊 Nombre de sitemaps: {$result['sitemaps_count']}");
            
            foreach ($result['sitemaps'] as $sitemap) {
                $this->line("      - {$sitemap['url']}");
            }
        } else {
            $this->error("❌ Erreur: " . ($result['error'] ?? 'Erreur inconnue'));
            return 1;
        }
        
        return 0;
    }
}

