<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Jobs\ProcessSeoCityJob;
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
        // Vérifier si l'automatisation est activée
        $automationEnabled = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
        $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
        
        // Par défaut, activé si non défini
        if ($automationEnabled === false && $automationEnabled !== true) {
            $automationEnabled = true;
        }
        
        if (!$automationEnabled) {
            $this->info('Automatisation SEO désactivée. Utilisez le bouton dans l\'admin pour l\'activer.');
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
            return 0;
        }

        $this->info("Traitement de " . $cities->count() . " ville(s) favorite(s)...");

        foreach ($cities as $index => $city) {
            $cityNumber = $index + 1;
            $this->info("Ville #{$cityNumber}: {$city->name} (#{$city->id})");
            
            // Dispatcher le job avec un délai échelonné pour éviter les rate limits
            ProcessSeoCityJob::dispatch($city->id)
                ->onQueue('seo-automation')
                ->delay(now()->addSeconds($index * 15));
        }

        $this->info("✅ " . $cities->count() . " job(s) planifié(s) dans la queue 'seo-automation'");
        $this->info("💡 Exécutez: php artisan queue:work --queue=seo-automation");

        return 0;
    }
}
