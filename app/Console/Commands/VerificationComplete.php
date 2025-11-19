<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SimpleIndexationService;
use App\Models\UrlIndexationStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VerificationComplete extends Command
{
    protected $signature = 'indexation:verifier-tout 
                            {--limit=100 : Nombre d\'URLs à vérifier par session}
                            {--force : Vérifier même URLs récemment vérifiées}
                            {--export : Exporter rapport CSV}';
    
    protected $description = 'Vérification complète de TOUS les liens + Rapport URLs non indexées';

    public function handle()
    {
        $this->info('🔍 VÉRIFICATION COMPLÈTE DE TOUS LES LIENS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $service = app(SimpleIndexationService::class);
        $limit = (int)$this->option('limit');
        $force = $this->option('force');
        $export = $this->option('export');
        
        // Récupérer TOUTES les URLs du sitemap
        $this->info('📊 Analyse du sitemap...');
        $allUrls = $service->getAllSiteUrls();
        $totalUrls = count($allUrls);
        
        $this->info("   Total URLs dans sitemap : " . number_format($totalUrls));
        $this->newLine();
        
        // Statistiques avant vérification
        $stats = $service->getStats();
        $this->info('📈 État actuel :');
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['URLs suivies en BDD', $stats['total_tracked']],
                ['✅ Indexées', $stats['indexed']],
                ['⚠️ Non indexées', $stats['not_indexed']],
                ['❌ Jamais vérifiées', $stats['never_verified']],
                ['Taux indexation', $stats['total_tracked'] > 0 ? round($stats['indexed'] / $stats['total_tracked'] * 100, 1) . '%' : '0%'],
            ]
        );
        $this->newLine();
        
        // Identifier URLs à vérifier
        $urlsToVerify = [];
        
        foreach ($allUrls as $url) {
            $status = UrlIndexationStatus::where('url', $url)->first();
            
            if ($force) {
                // Mode force : tout vérifier
                $urlsToVerify[] = $url;
            } else {
                // Normal : seulement non vérifiées ou anciennes (> 7j)
                if (!$status || !$status->last_verification_time || 
                    $status->last_verification_time->lt(now()->subDays(7))) {
                    $urlsToVerify[] = $url;
                }
            }
        }
        
        $toVerifyCount = count($urlsToVerify);
        
        if ($toVerifyCount === 0) {
            $this->info('✅ Toutes les URLs ont été vérifiées récemment (< 7 jours) !');
            $this->newLine();
            $this->info('💡 Utilisez --force pour forcer la re-vérification');
            return 0;
        }
        
        $this->warn("⏳ URLs à vérifier : " . number_format($toVerifyCount));
        
        // Appliquer limite
        if ($toVerifyCount > $limit) {
            $this->warn("⚠️  Limite appliquée : {$limit} URLs (sur {$toVerifyCount})");
            $this->info("💡 Relancez la commande pour continuer après");
            $urlsToVerify = array_slice($urlsToVerify, 0, $limit);
        }
        
        $this->newLine();
        
        // Confirmation
        if (!$this->confirm("Vérifier " . count($urlsToVerify) . " URLs via Google Search Console API ?", true)) {
            $this->warn('Annulé');
            return 0;
        }
        
        $this->newLine();
        $this->info('🚀 Démarrage vérification...');
        $this->newLine();
        
        // Barre de progression
        $bar = $this->output->createProgressBar(count($urlsToVerify));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Démarrage...');
        $bar->start();
        
        $results = [
            'total' => 0,
            'indexed' => 0,
            'not_indexed' => 0,
            'errors' => 0,
            'indexed_urls' => [],
            'not_indexed_urls' => [],
            'error_urls' => []
        ];
        
        foreach ($urlsToVerify as $url) {
            $bar->setMessage("Vérification : " . substr($url, -40));
            
            $result = $service->verifyUrl($url);
            $results['total']++;
            
            if ($result['success']) {
                if ($result['indexed']) {
                    $results['indexed']++;
                    $results['indexed_urls'][] = [
                        'url' => $url,
                        'coverage_state' => $result['details']['coverage_state'] ?? null,
                        'last_crawl' => $result['details']['last_crawl_time'] ?? null
                    ];
                } else {
                    $results['not_indexed']++;
                    $results['not_indexed_urls'][] = [
                        'url' => $url,
                        'coverage_state' => $result['details']['coverage_state'] ?? null,
                        'indexing_state' => $result['details']['indexing_state'] ?? null,
                        'reason' => $this->determineReason($result['details'] ?? [])
                    ];
                }
            } else {
                $results['errors']++;
                $results['error_urls'][] = [
                    'url' => $url,
                    'error' => $result['error'] ?? 'Erreur inconnue'
                ];
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Résultats finaux
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RÉSULTATS DE LA VÉRIFICATION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $this->table(
            ['Statut', 'Nombre', 'Pourcentage'],
            [
                ['✅ Indexées', $results['indexed'], round($results['indexed'] / $results['total'] * 100, 1) . '%'],
                ['⚠️ Non indexées', $results['not_indexed'], round($results['not_indexed'] / $results['total'] * 100, 1) . '%'],
                ['❌ Erreurs', $results['errors'], round($results['errors'] / $results['total'] * 100, 1) . '%'],
                ['📊 Total vérifié', $results['total'], '100%'],
            ]
        );
        
        $this->newLine();
        
        // Afficher URLs NON INDEXÉES (les plus importantes)
        if (count($results['not_indexed_urls']) > 0) {
            $this->error('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->error('⚠️  URLS NON INDEXÉES (' . count($results['not_indexed_urls']) . ')');
            $this->error('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();
            
            // Afficher les 20 premières
            $toShow = array_slice($results['not_indexed_urls'], 0, 20);
            
            foreach ($toShow as $item) {
                $this->warn("URL : " . $item['url']);
                $this->line("  État : " . ($item['coverage_state'] ?? 'Inconnu'));
                $this->line("  Raison : " . $item['reason']);
                $this->newLine();
            }
            
            if (count($results['not_indexed_urls']) > 20) {
                $remaining = count($results['not_indexed_urls']) - 20;
                $this->warn("... et {$remaining} autres URLs non indexées");
                $this->newLine();
            }
        }
        
        // Exporter rapport CSV
        if ($export && count($results['not_indexed_urls']) > 0) {
            $this->exportRapport($results);
        }
        
        // Recommandations
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('💡 RECOMMANDATIONS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        if ($results['not_indexed'] > 0) {
            $this->warn("1. Indexer les {$results['not_indexed']} URLs non indexées :");
            $this->info("   → php artisan indexation:simple index --limit=150");
            $this->newLine();
        }
        
        if ($toVerifyCount > $limit) {
            $remaining = $toVerifyCount - $limit;
            $this->warn("2. Continuer la vérification ({$remaining} URLs restantes) :");
            $this->info("   → php artisan indexation:verifier-tout --limit=100");
            $this->newLine();
        }
        
        if ($export) {
            $this->info("3. Consulter le rapport détaillé :");
            $this->info("   → storage/app/indexation/rapport-" . date('Y-m-d') . ".csv");
            $this->newLine();
        }
        
        $this->info("4. Activer indexation quotidienne automatique :");
        $this->info("   → Via /admin/indexation ou");
        $this->info("   → php artisan tinker");
        $this->info("   >>> App\\Models\\Setting::set('daily_indexing_enabled', true);");
        
        Log::info('Vérification complète terminée', $results);
        
        return 0;
    }
    
    /**
     * Déterminer la raison de non-indexation
     */
    protected function determineReason(array $details): string
    {
        $coverage = $details['coverage_state'] ?? '';
        $indexing = $details['indexing_state'] ?? '';
        $pageState = $details['page_fetch_state'] ?? '';
        
        if (stripos($coverage, 'EXCLUDED') !== false) {
            if (stripos($indexing, 'ROBOTS') !== false) {
                return 'Bloquée par robots.txt';
            }
            if (stripos($indexing, 'NOINDEX') !== false) {
                return 'Balise noindex présente';
            }
            return 'Exclue par Google (vérifier règles)';
        }
        
        if (stripos($coverage, 'DISCOVERED') !== false) {
            return 'Découverte mais pas encore explorée';
        }
        
        if (stripos($pageState, '404') !== false || stripos($pageState, 'NOT_FOUND') !== false) {
            return 'Page 404 (n\'existe pas)';
        }
        
        if (stripos($pageState, 'SOFT_404') !== false) {
            return 'Soft 404 (contenu vide ou erreur)';
        }
        
        if (stripos($coverage, 'CRAWLED') !== false && stripos($coverage, 'NOT_INDEXED') !== false) {
            return 'Explorée mais non indexée (qualité insuffisante)';
        }
        
        return 'En attente d\'exploration Google';
    }
    
    /**
     * Exporter rapport CSV
     */
    protected function exportRapport(array $results)
    {
        $this->newLine();
        $this->info('📄 Exportation du rapport...');
        
        $filename = 'indexation/rapport-' . date('Y-m-d-His') . '.csv';
        
        // Créer dossier si n'existe pas
        if (!Storage::exists('indexation')) {
            Storage::makeDirectory('indexation');
        }
        
        // Générer CSV
        $csv = "URL;Statut;Coverage State;Indexing State;Raison;Date Vérification\n";
        
        // URLs indexées
        foreach ($results['indexed_urls'] as $item) {
            $csv .= '"' . $item['url'] . '";';
            $csv .= '"Indexée ✅";';
            $csv .= '"' . ($item['coverage_state'] ?? 'N/A') . '";';
            $csv .= '"N/A";';
            $csv .= '"URL dans l\'index Google";';
            $csv .= '"' . date('Y-m-d H:i:s') . '"';
            $csv .= "\n";
        }
        
        // URLs NON indexées
        foreach ($results['not_indexed_urls'] as $item) {
            $csv .= '"' . $item['url'] . '";';
            $csv .= '"Non indexée ⚠️";';
            $csv .= '"' . ($item['coverage_state'] ?? 'N/A') . '";';
            $csv .= '"' . ($item['indexing_state'] ?? 'N/A') . '";';
            $csv .= '"' . $item['reason'] . '";';
            $csv .= '"' . date('Y-m-d H:i:s') . '"';
            $csv .= "\n";
        }
        
        // URLs en erreur
        foreach ($results['error_urls'] as $item) {
            $csv .= '"' . $item['url'] . '";';
            $csv .= '"Erreur ❌";';
            $csv .= '"N/A";';
            $csv .= '"N/A";';
            $csv .= '"' . $item['error'] . '";';
            $csv .= '"' . date('Y-m-d H:i:s') . '"';
            $csv .= "\n";
        }
        
        Storage::put($filename, $csv);
        
        $path = storage_path('app/' . $filename);
        $this->info("✅ Rapport exporté : {$path}");
        $this->newLine();
    }
}

