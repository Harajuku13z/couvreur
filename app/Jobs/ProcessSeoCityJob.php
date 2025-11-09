<?php

namespace App\Jobs;

use App\Models\City;
use App\Services\SeoAutomationManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessSeoCityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $cityId;
    public $customKeyword;
    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct($cityId, $customKeyword = null)
    {
        $this->cityId = $cityId;
        $this->customKeyword = $customKeyword;
    }

    /**
     * Execute the job.
     */
    public function handle(SeoAutomationManager $manager): void
    {
        try {
            $city = City::find($this->cityId);
            
            if (!$city) {
                Log::warning('ProcessSeoCityJob: Ville non trouvée', [
                    'city_id' => $this->cityId
                ]);
                $this->fail(new \Exception("Ville #{$this->cityId} non trouvée"));
                return;
            }

            if (!$city->is_favorite) {
                Log::info('ProcessSeoCityJob: Ville non favorite, ignorée', [
                    'city_id' => $this->cityId,
                    'city_name' => $city->name
                ]);
                return; // Ne pas marquer comme échec, juste ignorer
            }

            Log::info('ProcessSeoCityJob: Début traitement de la ville', [
                'city_id' => $this->cityId,
                'city_name' => $city->name,
                'custom_keyword' => $this->customKeyword
            ]);

            $log = $manager->runForCity($city, $this->customKeyword);
            
            Log::info('ProcessSeoCityJob: Traitement terminé', [
                'city_id' => $this->cityId,
                'city_name' => $city->name,
                'status' => $log->status,
                'article_id' => $log->article_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('ProcessSeoCityJob: Exception non gérée', [
                'city_id' => $this->cityId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Marquer le job comme échoué pour qu'il soit dans failed_jobs
            $this->fail($e);
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessSeoCityJob: Job échoué définitivement', [
            'city_id' => $this->cityId,
            'custom_keyword' => $this->customKeyword,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
