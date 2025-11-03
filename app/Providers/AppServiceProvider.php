<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Listeners\UpdateSitemapListener;
use App\Listeners\SubmitToGoogleSearchConsoleListener;
use App\Events\AdCreated;
use App\Events\AdUpdated;
use App\Events\ArticleCreated;
use App\Events\ServiceUpdated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer les événements pour la mise à jour automatique du sitemap
        Event::listen(AdCreated::class, UpdateSitemapListener::class);
        Event::listen(AdUpdated::class, UpdateSitemapListener::class);
        Event::listen(ArticleCreated::class, UpdateSitemapListener::class);
        Event::listen(ServiceUpdated::class, UpdateSitemapListener::class);
        
        // Enregistrer les événements pour la soumission à Google Search Console
        Event::listen(AdCreated::class, SubmitToGoogleSearchConsoleListener::class);
        
        // S'assurer que MySQL est toujours utilisé comme connexion par défaut
        // Note: On vérifie seulement la configuration, pas la connexion active
        // pour éviter les erreurs si la DB n'est pas encore disponible
        try {
            $defaultConnection = config('database.default');
            
            if ($defaultConnection !== 'mysql') {
                Log::warning("La connexion par défaut n'est pas MySQL (driver: {$defaultConnection}), forçage vers MySQL...");
                config(['database.default' => 'mysql']);
                DB::purge();
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la configuration MySQL: ' . $e->getMessage());
        }
    }
}
