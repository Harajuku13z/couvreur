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
        
        // 5. Informations supplémentaires
        $this->line('');
        $this->info('ℹ️  Informations supplémentaires :');
        $articlesPerCity = (int)Setting::where('key', 'seo_automation_articles_per_city')->value('value') ?: 1;
        $this->line('   - Articles par ville : ' . $articlesPerCity);
        $this->line('   - Prochaine exécution prévue : ' . ($currentTime < $automationTime ? 'Aujourd\'hui à ' . $automationTime : 'Demain à ' . $automationTime));
        
        return 0;
    }
}

