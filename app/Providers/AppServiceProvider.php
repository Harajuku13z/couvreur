<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Listeners\UpdateSitemapListener;
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
        
        // S'assurer que MySQL est toujours utilisé comme connexion par défaut
        try {
            $connection = DB::connection();
            $driverName = $connection->getDriverName();
            
            if ($driverName !== 'mysql') {
                Log::warning("La connexion par défaut n'est pas MySQL (driver: {$driverName}), forçage vers MySQL...");
                config(['database.default' => 'mysql']);
                DB::purge();
                DB::reconnect('mysql');
            }
            
            // Test de connexion au démarrage
            DB::select('SELECT 1');
            Log::info('Connexion MySQL vérifiée et active au démarrage');
        } catch (\Exception $e) {
            Log::error('Erreur de connexion MySQL au démarrage: ' . $e->getMessage());
            // Tentative de reconnexion
            try {
                DB::reconnect('mysql');
                DB::select('SELECT 1');
                Log::info('Reconnexion MySQL réussie');
            } catch (\Exception $reconnectException) {
                Log::error('Impossible de se reconnecter à MySQL: ' . $reconnectException->getMessage());
            }
        }
    }
}
