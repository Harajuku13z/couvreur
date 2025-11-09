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

// Automatisation SEO : génération d'articles quotidiens pour les villes favorites
// Note: L'heure est récupérée dynamiquement dans la closure pour permettre les changements en temps réel
Schedule::command('seo:run-automations')
    ->daily(function () {
        // Récupérer l'heure dynamiquement depuis les settings
        $automationTime = \App\Models\Setting::get('seo_automation_time', '04:00');
        $currentTime = now()->format('H:i');
        return $currentTime === $automationTime;
    })
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->onOneServer() // Exécuter sur un seul serveur (pour éviter les doublons)
    ->runInBackground() // Exécuter en arrière-plan
    ->when(function () {
        // Vérifier si l'automatisation est activée
        $automationEnabled = \App\Models\Setting::get('seo_automation_enabled', true);
        return filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
    });
