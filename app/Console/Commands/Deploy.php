<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class Deploy extends Command
{
    protected $signature = 'deploy';
    protected $description = 'Déployer l\'application avec optimisations SEO';

    public function handle()
    {
        $this->info('🚀 Déploiement en cours...');
        $this->newLine();

        try {
            // Mode maintenance
            $this->line('  📴 Activation du mode maintenance...');
            Artisan::call('down', ['--render' => 'errors::503']);

            // Optimiser l'autoloader
            $this->line('  📦 Optimisation de l\'autoloader...');
            $this->call('composer', ['install', '--optimize-autoloader', '--no-dev']);

            // Migrations
            $this->line('  🗄️  Exécution des migrations...');
            Artisan::call('migrate', ['--force' => true]);

            // Cache de configuration
            $this->line('  ⚙️  Mise en cache de la configuration...');
            Artisan::call('config:cache');

            // Cache des routes
            $this->line('  🛣️  Mise en cache des routes...');
            Artisan::call('route:cache');

            // Cache des vues
            $this->line('  👁️  Mise en cache des vues...');
            Artisan::call('view:cache');

            // Génération du sitemap
            $this->line('  🗺️  Génération du sitemap...');
            try {
                $sitemapService = app(\App\Services\SitemapService::class);
                $sitemapService->generateSitemap();
            } catch (\Exception $e) {
                $this->warn("  ⚠️  Erreur génération sitemap: " . $e->getMessage());
            }

            // Désactiver le mode maintenance
            $this->line('  ✅ Désactivation du mode maintenance...');
            Artisan::call('up');

            $this->newLine();
            $this->info('✨ Déploiement terminé avec succès!');
            return 0;

        } catch (\Exception $e) {
            Artisan::call('up'); // S'assurer de désactiver le mode maintenance
            $this->error('❌ Erreur lors du déploiement: ' . $e->getMessage());
            return 1;
        }
    }
}
