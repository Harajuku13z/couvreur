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
    public $timeout = 600; // Augmenté à 10 minutes pour la génération complète

    /**
     * Create a new job instance.
     */
    public function __construct($cityId, $customKeyword = null)
    {
        $this->cityId = $cityId;
        $this->customKeyword = $customKeyword;
        $this->onQueue('seo-automation'); // Définir la queue par défaut
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

            // Récupérer l'heure planifiée pour respecter les horaires
            $scheduler = app(\App\Services\SeoArticleScheduler::class);
            $scheduledTime = $scheduler->getNextScheduledTime();
            
            Log::info('ProcessSeoCityJob: Heure planifiée récupérée', [
                'scheduled_time' => $scheduledTime ? $scheduledTime->format('Y-m-d H:i:s') : 'null'
            ]);

            // Le manager crée son propre log, pas besoin d'en créer un ici
            $log = $manager->runForCity($city, $this->customKeyword, null, $scheduledTime);
            
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
            
            // Nettoyer le message d'erreur pour éviter les problèmes UTF-8
            $cleanedErrorMessage = $this->cleanErrorMessage($e->getMessage());
            
            // Mettre à jour le log si disponible
            if ($log) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $cleanedErrorMessage
                ]);
            } else {
                // Créer un log d'échec si aucun log n'existe
                try {
                    $city = City::find($this->cityId);
                    if ($city) {
                        \App\Models\SeoAutomation::create([
                            'city_id' => $city->id,
                            'status' => 'failed',
                            'error_message' => $cleanedErrorMessage
                        ]);
                    }
                } catch (\Exception $logException) {
                    Log::error('ProcessSeoCityJob: Impossible de créer le log d\'échec', [
                        'error' => $this->cleanErrorMessage($logException->getMessage())
                    ]);
                }
            }
            
            // Marquer le job comme échoué pour qu'il soit dans failed_jobs
            $this->fail($e);
        }
    }
    
    /**
     * Nettoie les messages d'erreur pour éviter les problèmes UTF-8
     */
    protected function cleanErrorMessage($message)
    {
        if (!is_string($message)) {
            return (string)$message;
        }
        
        // Supprimer les caractères UTF-8 invalides
        $cleaned = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
        // Supprimer les caractères de contrôle non valides
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
        // Vérifier que c'est bien de l'UTF-8 valide
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            // Si toujours invalide, utiliser iconv avec ignore
            $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $message);
            if ($cleaned === false) {
                // Dernier recours : supprimer tous les caractères non-ASCII
                $cleaned = preg_replace('/[^\x20-\x7E]/', '', $message);
            }
        }
        return $cleaned;
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessSeoCityJob: Job échoué définitivement', [
            'city_id' => $this->cityId,
            'custom_keyword' => $this->customKeyword,
            'exception' => $this->cleanErrorMessage($exception->getMessage()),
            'trace' => $this->cleanErrorMessage($exception->getTraceAsString())
        ]);
    }
}
