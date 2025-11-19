<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SimpleIndexationService;
use App\Models\UrlIndexationStatus;
use Illuminate\Support\Facades\Log;

class IndexationSimple extends Command
{
    protected $signature = 'indexation:simple 
                            {action : verify, index, stats}
                            {--limit=50 : Nombre d\'URLs à traiter}
                            {--url= : URL spécifique à vérifier/indexer}';
    
    protected $description = 'Gestion simplifiée de l\'indexation (vérifier, indexer, stats)';

    public function handle()
    {
        $action = $this->argument('action');
        $service = app(SimpleIndexationService::class);
        
        switch ($action) {
            case 'stats':
                return $this->showStats($service);
                
            case 'verify':
                return $this->verifyUrls($service);
                
            case 'index':
                return $this->indexUrls($service);
                
            default:
                $this->error("Action invalide. Utilisez: verify, index ou stats");
                return 1;
        }
    }
    
    /**
     * Afficher les statistiques
     */
    protected function showStats($service)
    {
        $this->info('📊 STATISTIQUES D\'INDEXATION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $stats = $service->getStats();
        
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['URLs dans sitemap', $stats['total_sitemap']],
                ['URLs suivies en BDD', $stats['total_tracked']],
                ['✅ Indexées (vérifiées)', $stats['indexed']],
                ['⚠️ Non indexées (vérifiées)', $stats['not_indexed']],
                ['❌ Jamais vérifiées', $stats['never_verified']],
                ['🕐 Vérifiées < 24h', $stats['verified_24h']],
                ['Taux d\'indexation', $stats['total_tracked'] > 0 ? round($stats['indexed'] / $stats['total_tracked'] * 100, 1) . '%' : 'N/A'],
            ]
        );
        
        $this->newLine();
        
        // Recommandations
        if ($stats['never_verified'] > 0) {
            $this->warn("💡 {$stats['never_verified']} URLs jamais vérifiées");
            $this->info("   → Lancez : php artisan indexation:simple verify --limit=100");
        }
        
        if ($stats['not_indexed'] > 0) {
            $this->warn("💡 {$stats['not_indexed']} URLs non indexées");
            $this->info("   → Lancez : php artisan indexation:simple index --limit=150");
        }
        
        if ($stats['indexed'] > 0) {
            $percentage = round($stats['indexed'] / $stats['total_tracked'] * 100, 1);
            if ($percentage >= 80) {
                $this->info("🎉 Excellent taux d'indexation ({$percentage}%) !");
            } elseif ($percentage >= 50) {
                $this->info("👍 Bon taux d'indexation ({$percentage}%)");
            } else {
                $this->warn("⚠️ Taux d'indexation faible ({$percentage}%)");
            }
        }
        
        return 0;
    }
    
    /**
     * Vérifier les URLs
     */
    protected function verifyUrls($service)
    {
        $limit = (int)$this->option('limit');
        $url = $this->option('url');
        
        if ($url) {
            // Vérifier une URL spécifique
            $this->info("🔍 Vérification de : {$url}");
            $this->newLine();
            
            $result = $service->verifyUrl($url);
            
            if ($result['success']) {
                $status = $result['indexed'] ? '✅ INDEXÉE' : '⚠️ NON INDEXÉE';
                $this->info("Statut : {$status}");
                
                if (isset($result['details']['coverage_state'])) {
                    $this->info("Coverage : {$result['details']['coverage_state']}");
                }
                if (isset($result['details']['last_crawl_time'])) {
                    $this->info("Dernière exploration : {$result['details']['last_crawl_time']}");
                }
                
                return 0;
            } else {
                $this->error("❌ Erreur : " . ($result['error'] ?? 'Inconnue'));
                return 1;
            }
        }
        
        // Vérifier plusieurs URLs
        $this->info("🔍 VÉRIFICATION BATCH");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();
        
        // Récupérer URLs à vérifier (priorité : jamais vérifiées)
        $allUrls = $service->getAllSiteUrls();
        $urlsToVerify = [];
        
        foreach ($allUrls as $url) {
            $status = UrlIndexationStatus::where('url', $url)->first();
            
            if (!$status || !$status->last_verification_time) {
                $urlsToVerify[] = $url;
            }
        }
        
        if (empty($urlsToVerify)) {
            $this->info('✅ Toutes les URLs ont déjà été vérifiées !');
            $this->info('💡 Utilisez --limit avec un nombre plus grand pour re-vérifier');
            return 0;
        }
        
        $this->info("URLs à vérifier : " . count($urlsToVerify));
        $this->info("Limite appliquée : {$limit}");
        $this->newLine();
        
        $urlsToVerify = array_slice($urlsToVerify, 0, $limit);
        
        // Barre de progression
        $bar = $this->output->createProgressBar(count($urlsToVerify));
        $bar->start();
        
        $results = [
            'total' => 0,
            'indexed' => 0,
            'not_indexed' => 0,
            'errors' => 0
        ];
        
        foreach ($urlsToVerify as $url) {
            $result = $service->verifyUrl($url);
            $results['total']++;
            
            if ($result['success']) {
                if ($result['indexed']) {
                    $results['indexed']++;
                } else {
                    $results['not_indexed']++;
                }
            } else {
                $results['errors']++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Résultats
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RÉSULTATS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $this->table(
            ['Statut', 'Nombre', '%'],
            [
                ['✅ Indexées', $results['indexed'], round($results['indexed'] / $results['total'] * 100, 1) . '%'],
                ['⚠️ Non indexées', $results['not_indexed'], round($results['not_indexed'] / $results['total'] * 100, 1) . '%'],
                ['❌ Erreurs', $results['errors'], round($results['errors'] / $results['total'] * 100, 1) . '%'],
            ]
        );
        
        $this->newLine();
        
        if ($results['not_indexed'] > 0) {
            $this->warn("💡 {$results['not_indexed']} URLs non indexées détectées");
            $this->info("   → Indexez-les : php artisan indexation:simple index --limit={$results['not_indexed']}");
        }
        
        return 0;
    }
    
    /**
     * Indexer les URLs non indexées
     */
    protected function indexUrls($service)
    {
        $limit = (int)$this->option('limit');
        $url = $this->option('url');
        
        if ($url) {
            // Indexer une URL spécifique
            $this->info("📤 Indexation de : {$url}");
            $this->newLine();
            
            $result = $service->indexUrl($url);
            
            if ($result['success']) {
                $this->info("✅ Demande d'indexation envoyée avec succès");
                $this->info("💡 Vérifiez le statut dans 3-7 jours");
                return 0;
            } else {
                $this->error("❌ Erreur : " . ($result['error'] ?? 'Inconnue'));
                return 1;
            }
        }
        
        // Indexer plusieurs URLs
        $this->info("📤 INDEXATION BATCH");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();
        
        // Récupérer URLs non indexées
        $urlsToIndex = $service->getUnindexedUrls($limit);
        
        if (empty($urlsToIndex)) {
            $this->info('✅ Aucune URL à indexer !');
            $this->info('💡 Toutes les URLs sont déjà indexées ou en attente de vérification');
            return 0;
        }
        
        $this->info("URLs à indexer : " . count($urlsToIndex));
        $this->info("Limite quotidienne : {$limit}");
        $this->newLine();
        
        // Confirmation
        if (!$this->confirm("Envoyer " . count($urlsToIndex) . " URLs à Google Indexing API ?", true)) {
            $this->warn('Annulé');
            return 0;
        }
        
        $this->newLine();
        
        // Barre de progression
        $bar = $this->output->createProgressBar(count($urlsToIndex));
        $bar->start();
        
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0
        ];
        
        foreach ($urlsToIndex as $url) {
            $result = $service->indexUrl($url);
            $results['total']++;
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Résultats
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RÉSULTATS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $this->table(
            ['Statut', 'Nombre'],
            [
                ['✅ Envoyées avec succès', $results['success']],
                ['❌ Échouées', $results['failed']],
                ['📊 Total', $results['total']],
            ]
        );
        
        $this->newLine();
        $this->info("💡 Les URLs seront indexées par Google dans 3-7 jours");
        $this->info("💡 Vérifiez le statut avec : php artisan indexation:simple verify --limit=50");
        
        Log::info('Indexation batch terminée', $results);
        
        return 0;
    }
}

