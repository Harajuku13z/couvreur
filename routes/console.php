<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Planification des tâches automatiques
Schedule::command('submissions:mark-abandoned')
    ->hourly() // Exécuter toutes les heures
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->runInBackground(); // Exécuter en arrière-plan

// Indexation quotidienne de 200 URLs via Google Indexing API
Schedule::command('index:urls-daily')
    ->dailyAt('02:00') // Exécuter chaque jour à 2h du matin
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->runInBackground() // Exécuter en arrière-plan
    ->when(function () {
        // Vérifier si l'indexation quotidienne est activée
        return \App\Models\Setting::get('daily_indexing_enabled', false);
    });

// Génération automatique du sitemap chaque jour à 3h du matin
Schedule::call(function () {
    try {
        \Illuminate\Support\Facades\Log::info('🔄 Génération automatique du sitemap...');
        $sitemapController = app(\App\Http\Controllers\SitemapController::class);
        $sitemapController->index(); // Génère et met en cache le sitemap
        \Illuminate\Support\Facades\Log::info('✅ Sitemap généré automatiquement avec succès');
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('❌ Erreur génération automatique sitemap: ' . $e->getMessage());
    }
})->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();

// Automatisation SEO : génération d'articles quotidiens pour les villes favorites
// Note: L'heure est récupérée dynamiquement dans when() pour permettre les changements en temps réel
// Utilise le fuseau horaire configuré dans config/app.php (Europe/Paris)
Schedule::command('seo:run-automations')
    ->everyMinute() // Vérifier chaque minute (le when() déterminera si on exécute à l'heure exacte)
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->onOneServer() // Exécuter sur un seul serveur (pour éviter les doublons)
    ->runInBackground() // Exécuter en arrière-plan
    ->when(function () {
        // Vérifier si l'automatisation est activée
        $automationEnabled = \App\Models\Setting::get('seo_automation_enabled', true);
        if (!filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN)) {
            \Illuminate\Support\Facades\Log::info('SeoAutomation: Désactivée', [
                'enabled' => $automationEnabled
            ]);
            return false;
        }
        
        // Vérifier si on est à l'heure configurée (utilise le fuseau horaire de l'app)
        $automationTime = \App\Models\Setting::get('seo_automation_time', '04:00');
        // Utiliser now() qui respecte le fuseau horaire configuré (Europe/Paris)
        $currentTime = now()->format('H:i');
        
        // Vérifier qu'il y a des villes favorites
        $favoriteCitiesCount = \App\Models\City::where('is_favorite', true)->count();
        
        // Log pour déboguer (seulement si on est proche de l'heure pour éviter trop de logs)
        $timeDiff = abs((int)str_replace(':', '', $currentTime) - (int)str_replace(':', '', $automationTime));
        if ($timeDiff <= 5 || $currentTime === $automationTime) {
            \Illuminate\Support\Facades\Log::info('SeoAutomation: Vérification horaire', [
                'current_time' => $currentTime,
                'automation_time' => $automationTime,
                'timezone' => config('app.timezone'),
                'matches' => $currentTime === $automationTime,
                'favorite_cities_count' => $favoriteCitiesCount,
                'time_diff_minutes' => $timeDiff
            ]);
        }
        
        // Vérifier les conditions
        if ($currentTime !== $automationTime) {
            return false; // Pas la bonne heure
        }
        
        if ($favoriteCitiesCount === 0) {
            \Illuminate\Support\Facades\Log::warning('SeoAutomation: Aucune ville favorite configurée');
            return false; // Pas de villes favorites
        }
        
        // Log de succès avant exécution
        \Illuminate\Support\Facades\Log::info('SeoAutomation: Conditions remplies, exécution prévue', [
            'current_time' => $currentTime,
            'automation_time' => $automationTime,
            'favorite_cities_count' => $favoriteCitiesCount
        ]);
        
        // Exécuter si toutes les conditions sont remplies
        return true;
    });
