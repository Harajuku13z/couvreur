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
        $log = null;
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

            // Le manager crée son propre log, pas besoin d'en créer un ici
            $log = $manager->runForCity($city, $this->customKeyword);
            
            // Vérifier que le statut n'est plus "pending" après traitement
            if ($log && $log->status === 'pending') {
                Log::warning('ProcessSeoCityJob: Le statut est resté "pending" après traitement', [
                    'city_id' => $this->cityId,
                    'log_id' => $log->id
                ]);
                // Mettre à jour le statut en "failed" si toujours pending
                $log->update([
                    'status' => 'failed',
                    'error_message' => 'Le traitement n\'a pas abouti - statut resté en attente'
                ]);
            }
            
            Log::info('ProcessSeoCityJob: Traitement terminé', [
                'city_id' => $this->cityId,
                'city_name' => $city->name,
                'status' => $log->status ?? 'unknown',
                'article_id' => $log->article_id ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error('ProcessSeoCityJob: Exception non gérée', [
                'city_id' => $this->cityId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Mettre à jour le log si disponible
            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            } else {
                // Créer un log d'échec si aucun log n'existe
                try {
                    $city = City::find($this->cityId);
                    if ($city) {
                        \App\Models\SeoAutomation::create([
                            'city_id' => $city->id,
                            'status' => 'failed',
                            'error_message' => $e->getMessage()
                        ]);
                    }
                } catch (\Exception $logException) {
                    Log::error('ProcessSeoCityJob: Impossible de créer le log d\'échec', [
                        'error' => $logException->getMessage()
                    ]);
                }
            }
            
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
