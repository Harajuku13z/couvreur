<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Log;

class GenerateSitemapDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer automatiquement le sitemap (tâche planifiée quotidienne)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Génération automatique du sitemap...');
        
        try {
            Log::info('🔄 Génération automatique du sitemap...');
            
            $sitemapController = app(SitemapController::class);
            $sitemapController->index(); // Génère et met en cache le sitemap
            
            Log::info('✅ Sitemap généré automatiquement avec succès');
            $this->info('✅ Sitemap généré automatiquement avec succès');
            
            return 0;
        } catch (\Exception $e) {
            Log::error('❌ Erreur génération automatique sitemap: ' . $e->getMessage());
            $this->error('❌ Erreur: ' . $e->getMessage());
            
            return 1;
        }
    }
}

