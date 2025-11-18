<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Planification des tâches automatiques
Schedule::command('submissions:mark-abandoned')
    ->name('mark-abandoned-submissions')
    ->hourly() // Exécuter toutes les heures
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->runInBackground(); // Exécuter en arrière-plan

// Indexation quotidienne de 200 URLs via Google Indexing API
Schedule::command('index:urls-daily')
    ->name('index-urls-daily')
    ->dailyAt('02:00') // Exécuter chaque jour à 2h du matin
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->runInBackground() // Exécuter en arrière-plan
    ->when(function () {
        // Vérifier si l'indexation quotidienne est activée
        return \App\Models\Setting::get('daily_indexing_enabled', false);
    });

// Génération automatique du sitemap chaque jour à 3h du matin
Schedule::command('sitemap:generate-daily')
    ->name('generate-sitemap')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground();

// Automatisation SEO : génération d'articles quotidiens pour les villes favorites
// NOTE: Le système peut être exécuté via HTTP (route /schedule/run) OU via le scheduler Laravel
// Si le cron HTTP n'est pas configuré, le scheduler Laravel prendra le relais
// Configuration HTTP: Configurez un cron dans Hostinger qui appelle /schedule/run?token=XXX
// Configuration Laravel: Le scheduler Laravel s'exécute automatiquement si configuré
// L'intervalle d'exécution est configurable dans l'admin (par défaut: 1 minute)

$cronInterval = (int)\App\Models\Setting::get('seo_automation_cron_interval', 1);
$cronInterval = max(1, min(60, $cronInterval));

$schedule = Schedule::command('seo:run-automations')
    ->name('seo-run-automations')
    ->withoutOverlapping()
    ->runInBackground();

// Configurer la fréquence selon l'intervalle
// Laravel ne supporte pas everyXMinutes(), on utilise cron() pour les intervalles personnalisés
if ($cronInterval === 1) {
    $schedule->everyMinute();
} elseif ($cronInterval === 5) {
    $schedule->everyFiveMinutes();
} elseif ($cronInterval === 10) {
    $schedule->everyTenMinutes();
} elseif ($cronInterval === 15) {
    $schedule->everyFifteenMinutes();
} elseif ($cronInterval === 30) {
    $schedule->everyThirtyMinutes();
} else {
    // Pour les autres intervalles, utiliser une expression cron
    // Exemple: */X * * * * signifie toutes les X minutes
    $schedule->cron("*/{$cronInterval} * * * *");
}

// Vérifier que l'automatisation est activée et qu'il y a des villes favorites
$schedule->when(function () {
    $automationEnabled = \App\Models\Setting::get('seo_automation_enabled', true);
    $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
    
    if (!$automationEnabled) {
        return false;
    }
    
    $favoriteCitiesCount = \App\Models\City::where('is_favorite', true)->count();
    
    return $favoriteCitiesCount > 0;
});
