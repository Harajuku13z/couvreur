<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\City;

class DiagnoseSeoScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostiquer pourquoi le scheduler SEO ne se déclenche pas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnostic du scheduler SEO');
        $this->line('');
        
        // 1. Vérifier l'automatisation activée
        $automationEnabled = Setting::where('key', 'seo_automation_enabled')->value('value');
        $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
        if ($automationEnabled === false && $automationEnabled !== true) {
            $automationEnabled = true; // Par défaut
        }
        
        $this->line('1. Automatisation activée : ' . ($automationEnabled ? '✅ OUI' : '❌ NON'));
        if (!$automationEnabled) {
            $this->error('   → L\'automatisation est désactivée. Activez-la dans l\'admin.');
            return 1;
        }
        
        // 2. Vérifier l'heure configurée
        $automationTime = Setting::where('key', 'seo_automation_time')->value('value') ?? '04:00';
        $currentTime = now()->format('H:i');
        $timezone = config('app.timezone', 'Europe/Paris');
        
        $this->line('2. Heure configurée : ' . $automationTime);
        $this->line('   Heure actuelle : ' . $currentTime . ' (' . $timezone . ')');
        $this->line('   Correspondance : ' . ($currentTime === $automationTime ? '✅ OUI' : '❌ NON'));
        
        if ($currentTime !== $automationTime) {
            $this->warn('   → L\'heure actuelle ne correspond pas à l\'heure configurée.');
            $this->warn('   → Le scheduler se déclenchera à ' . $automationTime);
        }
        
        // 3. Vérifier les villes favorites
        $favoriteCities = City::where('is_favorite', true)->get();
        $favoriteCitiesCount = $favoriteCities->count();
        
        $this->line('3. Villes favorites : ' . ($favoriteCitiesCount > 0 ? '✅ ' . $favoriteCitiesCount : '❌ AUCUNE'));
        
        if ($favoriteCitiesCount === 0) {
            $this->error('   → Aucune ville favorite configurée. Marquez au moins une ville comme favorite.');
            return 1;
        } else {
            $this->line('   Villes favorites :');
            foreach ($favoriteCities as $city) {
                $this->line('     - ' . $city->name . ' (ID: ' . $city->id . ')');
            }
        }
        
        // 4. Résumé
        $this->line('');
        $this->info('📊 Résumé :');
        
        $allConditionsMet = $automationEnabled && ($currentTime === $automationTime) && ($favoriteCitiesCount > 0);
        
        if ($allConditionsMet) {
            $this->info('✅ Toutes les conditions sont remplies ! Le scheduler devrait s\'exécuter maintenant.');
            $this->line('');
            $this->line('Pour tester maintenant :');
            $this->line('  php artisan seo:run-automations');
        } else {
            $this->warn('⚠️  Certaines conditions ne sont pas remplies :');
            if (!$automationEnabled) {
                $this->warn('   - Automatisation désactivée');
            }
            if ($currentTime !== $automationTime) {
                $this->warn('   - Heure actuelle (' . $currentTime . ') ≠ Heure configurée (' . $automationTime . ')');
            }
            if ($favoriteCitiesCount === 0) {
                $this->warn('   - Aucune ville favorite');
            }
            $this->line('');
            $this->line('Le scheduler se déclenchera automatiquement quand toutes les conditions seront remplies.');
        }
        
        // 5. Vérifier le scheduler et les horaires planifiés
        $this->line('');
        $this->info('📅 Vérification du scheduler :');
        
        $scheduler = app(\App\Services\SeoArticleScheduler::class);
        $scheduleStats = $scheduler->getScheduleStats();
        $nextTime = $scheduler->getNextScheduledTime();
        $shouldCreate = $scheduler->shouldCreateArticle();
        
        $this->line('   - Articles aujourd\'hui : ' . ($scheduleStats['articles_today'] ?? 0) . '/' . ($scheduleStats['total_articles_per_day'] ?? 0));
        $this->line('   - Prochain créneau : ' . ($scheduleStats['next_scheduled_time'] ?? 'N/A'));
        $this->line('   - Doit créer maintenant : ' . ($shouldCreate ? '✅ OUI' : '❌ NON'));
        
        if ($nextTime) {
            $diffMinutes = abs(now()->diffInMinutes($nextTime));
            $this->line('   - Différence avec maintenant : ' . $diffMinutes . ' minutes');
        }
        
        // 6. Vérifier les articles créés aujourd'hui
        $this->line('');
        $this->info('📝 Articles créés aujourd\'hui :');
        $articlesToday = \App\Models\Article::whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        if ($articlesToday->count() > 0) {
            foreach ($articlesToday as $article) {
                $cityName = $article->city ? $article->city->name : 'N/A';
                $this->line('   - ' . $article->created_at->format('H:i') . ' : ' . $cityName . ' (ID: ' . $article->id . ')');
            }
        } else {
            $this->warn('   - Aucun article créé aujourd\'hui');
        }
        
        // 7. Vérifier les erreurs récentes
        $this->line('');
        $this->info('❌ Erreurs récentes (dernières 24h) :');
        $recentErrors = \App\Models\SeoAutomation::where('status', 'failed')
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        if ($recentErrors->count() > 0) {
            foreach ($recentErrors as $error) {
                $cityName = $error->city ? $error->city->name : 'N/A';
                $errorMsg = substr($error->error_message ?? 'Erreur inconnue', 0, 80);
                $this->line('   - ' . $error->created_at->format('Y-m-d H:i') . ' : ' . $cityName);
                $this->line('     → ' . $errorMsg);
            }
        } else {
            $this->info('   ✅ Aucune erreur récente');
        }
        
        // 8. Informations supplémentaires
        $this->line('');
        $this->info('ℹ️  Informations supplémentaires :');
        $articlesPerDay = (int)Setting::where('key', 'seo_automation_articles_per_day')->value('value') ?: 5;
        $this->line('   - Articles par jour par ville : ' . $articlesPerDay);
        $this->line('   - Intervalle entre articles : ' . ($scheduleStats['interval_minutes'] ?? 0) . ' minutes');
        $this->line('   - Prochaine exécution prévue : ' . ($currentTime < $automationTime ? 'Aujourd\'hui à ' . $automationTime : 'Demain à ' . $automationTime));
        
        // 9. Commandes de test
        $this->line('');
        $this->info('🧪 Commandes de test :');
        $this->line('   - Tester maintenant (force) : php artisan seo:run-automations --force');
        $this->line('   - Vérifier le scheduler : php artisan schedule:run');
        $this->line('   - Voir les tâches planifiées : php artisan schedule:list');
        
        return 0;
    }
}

