<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Jobs\ProcessSeoCityJob;
use App\Services\SeoAutomationManager;
use App\Services\SeoArticleScheduler;
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
    protected $description = 'Run SEO automation - Creates one article at a time, scheduled throughout the day';

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
        
        // NOUVEAU SYSTÈME : Planification horaire avec un article à la fois
        $scheduler = app(SeoArticleScheduler::class);
        $force = $this->option('force');
        
        // Vérifier si c'est le moment de créer un article (sauf si --force)
        if (!$force && !$scheduler->shouldCreateArticle()) {
            $stats = $scheduler->getScheduleStats();
            $nextTime = $scheduler->getNextScheduledTime();
            $now = now();
            
            $this->info("⏰ Pas encore le moment de créer un article.");
            $this->info("   Prochain créneau : " . ($stats['next_scheduled_time'] ?? 'N/A'));
            $this->info("   Articles aujourd'hui : {$stats['articles_today']}/{$stats['total_articles_per_day']}");
            $this->info("   Heure actuelle : " . $now->format('H:i'));
            
            if ($nextTime) {
                $isPast = $nextTime->isPast();
                $diffMinutes = abs($now->diffInMinutes($nextTime));
                $this->info("   Créneau dans le passé : " . ($isPast ? 'OUI' : 'NON'));
                $this->info("   Différence : {$diffMinutes} minutes");
            }
            
            $this->info("   Utilisez --force pour forcer la création maintenant.");
            
            // Logger pour debug
            \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Création refusée', [
                'next_time' => $nextTime ? $nextTime->format('H:i') : 'N/A',
                'current_time' => $now->format('H:i'),
                'is_past' => $nextTime ? $nextTime->isPast() : false,
                'diff_minutes' => $nextTime ? abs($now->diffInMinutes($nextTime)) : 0,
                'articles_today' => $stats['articles_today'],
                'total_per_day' => $stats['total_articles_per_day']
            ]);
            
            return 0;
        }
        
        // Récupérer la prochaine ville à traiter (rotation)
        $city = $scheduler->getNextCity();
        
        if (!$city) {
            $this->error('Aucune ville favorite à traiter.');
            \Illuminate\Support\Facades\Log::warning('RunSeoAutomations: Aucune ville favorite trouvée');
            return 0;
        }
        
        // Récupérer un mot-clé aléatoire
        $keyword = $scheduler->getRandomKeyword();
        
        if (!$keyword) {
            $this->error('Aucun mot-clé configuré. Configurez des mots-clés dans /admin/keywords');
            \Illuminate\Support\Facades\Log::warning('RunSeoAutomations: Aucun mot-clé disponible');
            return 0;
        }
        
        $stats = $scheduler->getScheduleStats();
        
        $this->info("📝 Création d'un article planifié");
        $this->info("   Ville : {$city->name} (#{$city->id})");
        $this->info("   Mot-clé : {$keyword}");
        $this->info("   Articles aujourd'hui : {$stats['articles_today']}/{$stats['total_articles_per_day']}");
        $this->info("   Intervalle : {$stats['interval_minutes']} minutes");
        
        \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Début création article planifié', [
            'city_id' => $city->id,
            'city_name' => $city->name,
            'keyword' => $keyword,
            'articles_today' => $stats['articles_today'],
            'total_per_day' => $stats['total_articles_per_day']
        ]);
        
        // Vérifier si on doit exécuter directement (sans queue) ou via queue
        $useDirectExecution = \App\Models\Setting::where('key', 'seo_automation_direct_execution')->value('value');
        $useDirectExecution = filter_var($useDirectExecution, FILTER_VALIDATE_BOOLEAN);
        
        // Par défaut, utiliser la queue si non défini (pour permettre le suivi)
        // L'exécution directe est plus fiable mais ne permet pas de voir les jobs en attente
        if ($useDirectExecution === false && $useDirectExecution !== true) {
            $useDirectExecution = false; // Par défaut, utiliser la queue
        }
        
        \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Mode d\'exécution', [
            'direct_execution' => $useDirectExecution,
            'city_id' => $city->id,
            'keyword' => $keyword
        ]);
        
        try {
            if ($useDirectExecution) {
                // EXÉCUTION DIRECTE (sans queue) - Plus fiable, pas besoin de worker
                $this->info("⚡ Mode exécution directe");
                
                $manager = app(SeoAutomationManager::class);
                
                $log = $manager->runForCity($city, $keyword, function($steps) {
                    // Callback pour le suivi (optionnel)
                });
                
                if ($log->status === 'indexed' || $log->status === 'published') {
                    $this->info("✅ Succès : " . ($log->article_url ?? 'Article créé'));
                    \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Article créé avec succès', [
                        'log_id' => $log->id,
                        'article_url' => $log->article_url
                    ]);
                    return 0;
                } else {
                    $this->error("❌ Échec : " . ($log->error_message ?? 'Erreur inconnue'));
                    \Illuminate\Support\Facades\Log::error('RunSeoAutomations: Échec création article', [
                        'log_id' => $log->id,
                        'error' => $log->error_message
                    ]);
                    return 1;
                }
            } else {
                // EXÉCUTION VIA QUEUE (ancien système)
                $this->info("📦 Mode queue (nécessite worker)");
                
                // Passer le mot-clé au job
                ProcessSeoCityJob::dispatch($city->id, $keyword)
                    ->onQueue('seo-automation');
                
                $this->info("✅ Job planifié dans la queue 'seo-automation'");
                $this->info("   Ville: {$city->name} (#{$city->id})");
                $this->info("   Mot-clé: {$keyword}");
                $this->info("💡 Exécutez: php artisan queue:work --queue=seo-automation");
                
                \Illuminate\Support\Facades\Log::info('RunSeoAutomations: Job dispatché', [
                    'city_id' => $city->id,
                    'city_name' => $city->name,
                    'keyword' => $keyword
                ]);
                
                return 0;
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur : " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('RunSeoAutomations: Erreur lors du traitement', [
                'city_id' => $city->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
