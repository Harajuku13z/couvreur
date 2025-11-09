<?php

namespace App\Jobs;

use App\Models\City;
use App\Services\SeoAutomationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessSeoCityJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $cityId;
    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * Execute the job.
     */
    public function handle(SeoAutomationManager $manager): void
    {
        $city = City::find($this->cityId);
        
        if (!$city) {
            Log::warning('ProcessSeoCityJob: Ville non trouvée', [
                'city_id' => $this->cityId
            ]);
            return;
        }

        if (!$city->is_favorite) {
            Log::info('ProcessSeoCityJob: Ville non favorite, ignorée', [
                'city_id' => $this->cityId,
                'city_name' => $city->name
            ]);
            return;
        }

        Log::info('ProcessSeoCityJob: Traitement de la ville', [
            'city_id' => $this->cityId,
            'city_name' => $city->name
        ]);

        $manager->runForCity($city);
    }
}
