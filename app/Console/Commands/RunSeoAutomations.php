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
            $this->info("Ville #{$index + 1}: {$city->name} (#{$city->id})");
            
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
