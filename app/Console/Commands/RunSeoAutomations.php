<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Jobs\ProcessSeoCityJob;
use App\Services\SeoAutomationManager;
use Illuminate\Console\Command;

class RunSeoAutomations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:run-automations {--city_id=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run SEO automation for favorite cities (one article per city)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Commande exécutée', [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone')
        ]);
        
        // Vérifier si l'automatisation est activée
        $automationEnabled = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
        $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
        
        // Par défaut, activé si non défini
        if ($automationEnabled === false && $automationEnabled !== true) {
            $automationEnabled = true;
        }
        
        if (!$automationEnabled) {
            $this->info('Automatisation SEO désactivée. Utilisez le bouton dans l\'admin pour l\'activer.');
            \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Automatisation désactivée');
            return 0;
        }
        
        $cityId = $this->option('city_id');
        
        if ($cityId) {
            $cities = City::where('id', $cityId)->where('is_favorite', true)->get();
        } else {
            $cities = City::where('is_favorite', true)->get();
        }

        if ($cities->isEmpty()) {
            $this->info('Aucune ville favorite à traiter.');
            \Illuminate\Support\Facades\Log::warning('RunSeoAutomations: Aucune ville favorite trouvée');
            return 0;
        }

        // Récupérer le nombre d'articles à générer par ville
        $articlesPerCity = (int)\App\Models\Setting::where('key', 'seo_automation_articles_per_city')->value('value') ?: 1;
        
        $this->info("Traitement de " . $cities->count() . " ville(s) favorite(s)...");
        $this->info("Nombre d'articles par ville : {$articlesPerCity}");
        
        \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Début du traitement', [
            'cities_count' => $cities->count(),
            'articles_per_city' => $articlesPerCity
        ]);

        // Vérifier si on doit exécuter directement (sans queue) ou via queue
        $useDirectExecution = \App\Models\Setting::where('key', 'seo_automation_direct_execution')->value('value');
        $useDirectExecution = filter_var($useDirectExecution, FILTER_VALIDATE_BOOLEAN);
        
        // Par défaut, utiliser l'exécution directe si non défini (plus fiable)
        if ($useDirectExecution === false && $useDirectExecution !== true) {
            $useDirectExecution = true;
        }

        if ($useDirectExecution) {
            // EXÉCUTION DIRECTE (sans queue) - Plus fiable, pas besoin de worker
            $this->info("⚡ Mode exécution directe (sans queue)");
            
            $manager = app(SeoAutomationManager::class);
            $totalProcessed = 0;
            $totalSuccess = 0;
            $totalFailed = 0;
            
            foreach ($cities as $cityIndex => $city) {
                $cityNumber = $cityIndex + 1;
                $this->info("Ville #{$cityNumber}: {$city->name} (#{$city->id})");
                
                // Générer le nombre d'articles demandé pour chaque ville
                for ($articleIndex = 0; $articleIndex < $articlesPerCity; $articleIndex++) {
                    try {
                        $this->line("  → Génération article " . ($articleIndex + 1) . "/{$articlesPerCity}...");
                        
                        $log = $manager->runForCity($city, null, function($steps) {
                            // Callback pour le suivi (optionnel)
                        });
                        
                        $totalProcessed++;
                        
                        if ($log->status === 'indexed' || $log->status === 'published') {
                            $totalSuccess++;
                            $this->info("  ✅ Succès : " . ($log->article_url ?? 'Article créé'));
                        } else {
                            $totalFailed++;
                            $this->error("  ❌ Échec : " . ($log->error_message ?? 'Erreur inconnue'));
                        }
                        
                        // Délai entre les articles pour éviter les rate limits
                        if ($articleIndex < $articlesPerCity - 1) {
                            sleep(5);
                        }
                    } catch (\Exception $e) {
                        $totalFailed++;
                        $this->error("  ❌ Erreur : " . $e->getMessage());
                        \Illuminate\Support\Facades\Log::error('RunSeoAutomations: Erreur lors du traitement', [
                            'city_id' => $city->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // Délai entre les villes
                if ($cityIndex < $cities->count() - 1) {
                    sleep(10);
                }
            }
            
            $this->info("");
            $this->info("✅ Traitement terminé :");
            $this->info("   - Total traité : {$totalProcessed}");
            $this->info("   - Succès : {$totalSuccess}");
            $this->info("   - Échecs : {$totalFailed}");
            
            \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Traitement terminé (direct)', [
                'total_processed' => $totalProcessed,
                'total_success' => $totalSuccess,
                'total_failed' => $totalFailed
            ]);
        } else {
            // EXÉCUTION VIA QUEUE (ancien système)
            $this->info("📦 Mode queue (nécessite worker)");
            
            $totalJobs = 0;
            foreach ($cities as $cityIndex => $city) {
                $cityNumber = $cityIndex + 1;
                $this->info("Ville #{$cityNumber}: {$city->name} (#{$city->id})");
                
                // Générer le nombre d'articles demandé pour chaque ville
                for ($articleIndex = 0; $articleIndex < $articlesPerCity; $articleIndex++) {
                    $jobIndex = ($cityIndex * $articlesPerCity) + $articleIndex;
                    
                    // Dispatcher le job avec un délai échelonné pour éviter les rate limits
                    ProcessSeoCityJob::dispatch($city->id)
                        ->onQueue('seo-automation')
                        ->delay(now()->addSeconds($jobIndex * 15));
                    
                    $totalJobs++;
                    
                    \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Job dispatché', [
                        'city_id' => $city->id,
                        'city_name' => $city->name,
                        'job_index' => $jobIndex
                    ]);
                }
            }

            $this->info("✅ {$totalJobs} job(s) planifié(s) dans la queue 'seo-automation'");
            $this->info("💡 Exécutez: php artisan queue:work --queue=seo-automation");
            
            \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Traitement terminé (queue)', [
                'total_jobs' => $totalJobs
            ]);
        }

        return 0;
    }
}
