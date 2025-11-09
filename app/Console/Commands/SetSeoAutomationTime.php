<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class SetSeoAutomationTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:set-time {time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Définir l\'heure d\'exécution de l\'automatisation SEO (format: HH:MM)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $time = $this->argument('time');
        
        // Valider le format
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            $this->error('Format invalide. Utilisez HH:MM (ex: 19:30)');
            return 1;
        }
        
        Setting::set('seo_automation_time', $time, 'string', 'seo');
        
        $this->info("✅ Heure d'automatisation SEO configurée à {$time} (heure Europe/Paris)");
        
        return 0;
    }
}

