<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Illuminate\Support\Facades\File;

class GenerateFaviconIco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favicon:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère le fichier favicon.ico à partir du favicon configuré';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faviconPath = Setting::get('site_favicon');
        
        if (!$faviconPath) {
            // Vérifier aussi dans seo_config
            $seoConfigData = Setting::get('seo_config', '[]');
            $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
            $faviconPath = $seoConfig['favicon'] ?? null;
        }
        
        if (!$faviconPath) {
            $this->error('Aucun favicon configuré. Veuillez d\'abord uploader un favicon dans les paramètres.');
            return 1;
        }
        
        // Construire le chemin complet
        $fullPath = public_path($faviconPath);
        
        if (!file_exists($fullPath)) {
            $this->error("Le fichier favicon n'existe pas : {$fullPath}");
            return 1;
        }
        
        // Copier vers favicon.ico
        $icoPath = public_path('favicon.ico');
        
        try {
            if (copy($fullPath, $icoPath)) {
                $this->info("✅ favicon.ico créé avec succès à partir de : {$faviconPath}");
                $this->info("   Chemin : {$icoPath}");
                return 0;
            } else {
                $this->error("❌ Impossible de copier le favicon vers favicon.ico");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            return 1;
        }
    }
}

