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
// NOTE: Cette tâche est maintenant exécutée via HTTP (route /schedule/run) pour Hostinger
// Le système utilise un cron HTTP au lieu du scheduler Laravel
// Configuration: Configurez un cron dans Hostinger qui appelle /schedule/run?token=XXX
// L'intervalle d'exécution est configurable dans l'admin (par défaut: 1 minute)
// 
// Ancien code (désactivé - utilise maintenant HTTP):
// $cronInterval = (int)\App\Models\Setting::get('seo_automation_cron_interval', 1);
// $cronInterval = max(1, min(60, $cronInterval));
// $schedule = Schedule::command('seo:run-automations')
//     ->name('seo-run-automations')
//     ->withoutOverlapping()
//     ->onOneServer()
//     ->runInBackground();
// if ($cronInterval === 1) {
//     $schedule->everyMinute();
// } else {
//     $schedule->everyXMinutes($cronInterval);
// }
// $schedule->when(function () {
//     // ... vérifications ...
// });
