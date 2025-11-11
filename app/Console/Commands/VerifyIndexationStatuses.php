<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSearchConsoleService;
use App\Models\UrlIndexationStatus;
use Illuminate\Support\Facades\Log;

class VerifyIndexationStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexation:verify-statuses 
                            {--limit=50 : Nombre d\'URLs à vérifier}
                            {--hours=24 : Vérifier les URLs non vérifiées depuis X heures}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier le statut réel d\'indexation des URLs via l\'API URL Inspection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des statuts d\'indexation...');

        $googleService = new GoogleSearchConsoleService();
        
        if (!$googleService->isConfigured()) {
            $this->error('❌ Google Search Console n\'est pas configuré.');
            return 1;
        }

        $limit = (int) $this->option('limit');
        $hours = (int) $this->option('hours');

        // Récupérer les URLs à vérifier
        $urlsToVerify = UrlIndexationStatus::needsVerification($hours)
            ->orderBy('last_submission_time', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->pluck('url')
            ->toArray();

        if (empty($urlsToVerify)) {
            $this->info('✅ Aucune URL à vérifier pour le moment.');
            return 0;
        }

        $this->info("📊 Vérification de " . count($urlsToVerify) . " URLs...");

        $bar = $this->output->createProgressBar(count($urlsToVerify));
        $bar->start();

        $indexed = 0;
        $notIndexed = 0;
        $errors = 0;

        foreach ($urlsToVerify as $url) {
            try {
                $result = $googleService->verifyIndexationStatus($url);
                
                if ($result['success']) {
                    if ($result['indexed'] ?? false) {
                        $indexed++;
                    } else {
                        $notIndexed++;
                    }
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error('Erreur vérification URL', [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }

            $bar->advance();
            
            // Petite pause pour éviter les limites de rate
            usleep(200000); // 0.2 seconde
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Vérification terminée:");
        $this->line("   - Indexées: {$indexed}");
        $this->line("   - Non indexées: {$notIndexed}");
        $this->line("   - Erreurs: {$errors}");

        return 0;
    }
}
