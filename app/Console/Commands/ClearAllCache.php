<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAllCache extends Command
{
    protected $signature = 'cache:clear-all';
    protected $description = 'Vider tous les caches Laravel';

    public function handle()
    {
        $this->info('🧹 Nettoyage de tous les caches...');
        $this->newLine();

        $commands = [
            'config:clear' => 'Configuration',
            'route:clear' => 'Routes',
            'view:clear' => 'Vues',
            'cache:clear' => 'Cache applicatif',
            'optimize:clear' => 'Optimisations',
        ];

        foreach ($commands as $command => $label) {
            $this->line("  Nettoyage: {$label}...");
            $this->call($command);
        }

        $this->newLine();
        $this->info('✅ Tous les caches ont été vidés!');
        return 0;
    }
}
