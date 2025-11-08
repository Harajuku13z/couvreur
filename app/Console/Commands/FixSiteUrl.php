<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class FixSiteUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-url:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige définitivement le setting site_url pour utiliser normesrenovationbretagne.fr';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction du setting site_url...');
        
        $currentUrl = Setting::get('site_url', null);
        $correctUrl = 'https://normesrenovationbretagne.fr';
        
        if (!empty($currentUrl)) {
            $this->line("   URL actuelle: {$currentUrl}");
        } else {
            $this->line("   Aucune URL configurée");
        }
        
        // Vérifier si l'URL contient sausercouverture.fr
        if (!empty($currentUrl) && strpos($currentUrl, 'sausercouverture.fr') !== false) {
            $this->warn("⚠️  Ancienne URL détectée: {$currentUrl}");
            $this->info("   Correction vers: {$correctUrl}");
            
            Setting::set('site_url', $correctUrl, 'string', 'seo');
            Setting::clearCache();
            
            $this->info("✅ Setting site_url corrigé avec succès !");
        } else if (!empty($currentUrl) && strpos($currentUrl, 'normesrenovationbretagne.fr') !== false) {
            $this->info("✅ L'URL est déjà correcte: {$currentUrl}");
        } else {
            // Forcer la bonne URL même si elle n'est pas configurée
            $this->info("   Configuration de l'URL: {$correctUrl}");
            Setting::set('site_url', $correctUrl, 'string', 'seo');
            Setting::clearCache();
            $this->info("✅ Setting site_url configuré avec succès !");
        }
        
        // Vérification finale
        $finalUrl = Setting::get('site_url', null);
        $this->newLine();
        $this->info("📋 URL finale: {$finalUrl}");
        
        if (strpos($finalUrl, 'sausercouverture.fr') !== false) {
            $this->error("❌ ERREUR: L'URL contient encore sausercouverture.fr !");
            return 1;
        }
        
        if (strpos($finalUrl, 'normesrenovationbretagne.fr') === false) {
            $this->error("❌ ERREUR: L'URL ne contient pas normesrenovationbretagne.fr !");
            return 1;
        }
        
        $this->info("✅ Vérification réussie !");
        return 0;
    }
}

