<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmartIndexingStrategy;

class SmartIndexing extends Command
{
    protected $signature = 'indexing:smart {--phase=all : Phase à exécuter (1,2,3,4 ou all)}';
    protected $description = 'Indexation intelligente par phases basée sur la qualité du contenu';

    public function handle()
    {
        $this->info('🚀 Démarrage de l\'indexation intelligente...');
        
        $phase = $this->option('phase');
        $strategy = app(SmartIndexingStrategy::class);
        
        $this->info("Phase sélectionnée : {$phase}");
        $this->newLine();
        
        // Afficher les recommandations d'abord
        if ($phase === 'all' || $phase === 'recommendations') {
            $this->info('📊 Analyse et Recommandations :');
            $this->newLine();
            
            $recommendations = $strategy->getIndexingRecommendations();
            
            $this->table(
                ['Priorité', 'Problème', 'Solution'],
                collect($recommendations['recommendations'])->map(function($rec) {
                    return [
                        $rec['priority'],
                        $rec['issue'],
                        $rec['solution']
                    ];
                })
            );
            
            $this->newLine();
            $this->info('Statistiques :');
            foreach ($recommendations['stats'] as $key => $value) {
                $this->line("  - {$key}: {$value}");
            }
            $this->newLine();
            
            if ($phase === 'recommendations') {
                return 0;
            }
        }
        
        // Exécuter l'indexation
        $results = $strategy->executeSmartIndexing($phase);
        
        // Afficher les résultats
        if (isset($results['phase_1']['indexed'])) {
            $this->info('✅ Phase 1 (Pages stratégiques): ' . count($results['phase_1']['indexed']) . ' indexées');
        }
        
        if (isset($results['phase_2']['indexed'])) {
            $this->info('✅ Phase 2 (Articles de qualité): ' . count($results['phase_2']['indexed']) . ' indexés');
        }
        
        if (isset($results['phase_3']['indexed'])) {
            $this->info('✅ Phase 3 (Annonces villes prioritaires): ' . count($results['phase_3']['indexed']) . ' indexées');
        }
        
        if (isset($results['phase_4']['indexed'])) {
            $this->info('✅ Phase 4 (Contenu restant): ' . count($results['phase_4']['indexed']) . ' indexés');
        }
        
        $this->newLine();
        $this->info('📈 Résumé :');
        $this->line("  Total indexé : {$results['summary']['total_indexed']}");
        $this->line("  Total ignoré : {$results['summary']['total_skipped']}");
        
        $this->newLine();
        $this->info('✅ Indexation intelligente terminée !');
        
        return 0;
    }
}

