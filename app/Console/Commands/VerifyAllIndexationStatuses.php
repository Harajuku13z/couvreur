<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSearchConsoleService;
use App\Services\SitemapService;
use App\Models\UrlIndexationStatus;
use Illuminate\Support\Facades\Log;

class VerifyAllIndexationStatuses extends Command
{
    protected $signature = 'indexation:verify-all {--limit=50 : Nombre maximum d\'URLs à vérifier par exécution} {--force : Vérifier même les URLs récemment vérifiées}';
    protected $description = 'Vérifie le statut réel d\'indexation de toutes les URLs du site via Google Search Console';

    public function handle()
    {
        $this->info('🔍 VÉRIFICATION STATUTS D\'INDEXATION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $limit = (int)$this->option('limit');
        $force = $this->option('force');
        
        // Vérifier Google Search Console configuré
        $googleService = new GoogleSearchConsoleService();
        
        if (!$googleService->isConfigured()) {
            $this->error('❌ Google Search Console non configuré');
            $this->warn('💡 Configurez-le dans /admin/indexation');
            return 1;
        }
        
        $this->info('✅ Google Search Console configuré');
        $this->newLine();
        
        // Récupérer toutes les URLs du sitemap
        $sitemapService = new SitemapService();
        $allUrls = $sitemapService->getAllUrls();
        
        if (empty($allUrls)) {
            $this->warn('⚠️ Aucune URL trouvée dans les sitemaps');
            return 0;
        }
        
        // Extraire les URLs
        $urls = [];
        foreach ($allUrls as $item) {
            if (is_array($item)) {
                $url = $item['url'] ?? null;
            } else {
                $url = $item;
            }
            if (!empty($url) && is_string($url)) {
                $urls[] = $url;
            }
        }
        
        $urls = array_unique($urls);
        $totalUrls = count($urls);
        
        $this->info("📊 Total URLs dans sitemap : {$totalUrls}");
        $this->newLine();
        
        // Filtrer les URLs à vérifier
        $urlsToVerify = [];
        $skipCount = 0;
        
        if (!$force) {
            foreach ($urls as $url) {
                $status = UrlIndexationStatus::where('url', $url)->first();
                
                // Vérifier si besoin de re-vérifier
                if ($status && $status->last_verification_time) {
                    // Si vérifié il y a moins de 24h, skip (sauf --force)
                    if ($status->last_verification_time->gt(now()->subHours(24))) {
                        $skipCount++;
                        continue;
                    }
                }
                
                $urlsToVerify[] = $url;
            }
        } else {
            $urlsToVerify = $urls;
        }
        
        if ($skipCount > 0) {
            $this->info("⏭️  {$skipCount} URLs récemment vérifiées (< 24h) - ignorées");
            $this->info("💡 Utilisez --force pour tout re-vérifier");
            $this->newLine();
        }
        
        if (empty($urlsToVerify)) {
            $this->info('✅ Toutes les URLs ont été vérifiées récemment !');
            $this->newLine();
            $this->displayStats();
            return 0;
        }
        
        $toVerifyCount = count($urlsToVerify);
        $this->info("🔍 URLs à vérifier : {$toVerifyCount}");
        
        // Appliquer la limite
        if ($toVerifyCount > $limit) {
            $urlsToVerify = array_slice($urlsToVerify, 0, $limit);
            $this->warn("⚠️  Limite appliquée : {$limit} URLs (sur {$toVerifyCount})");
            $this->info("💡 Relancez la commande pour continuer");
        }
        
        $this->newLine();
        $this->info("📤 Vérification de " . count($urlsToVerify) . " URLs...");
        $this->newLine();
        
        // Barre de progression
        $bar = $this->output->createProgressBar(count($urlsToVerify));
        $bar->start();
        
        $stats = [
            'total' => 0,
            'indexed' => 0,
            'not_indexed' => 0,
            'errors' => 0,
        ];
        
        foreach ($urlsToVerify as $url) {
            try {
                $result = $googleService->verifyIndexationStatus($url);
                $stats['total']++;
                
                if ($result['success']) {
                    if ($result['indexed'] ?? false) {
                        $stats['indexed']++;
                    } else {
                        $stats['not_indexed']++;
                    }
                } else {
                    $stats['errors']++;
                }
                
                // Petite pause pour ne pas surcharger l'API (2 secondes entre chaque)
                sleep(2);
                
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Erreur vérification URL', [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Afficher résultats
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RÉSULTATS DE LA VÉRIFICATION');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $this->table(
            ['Statut', 'Nombre', 'Pourcentage'],
            [
                ['✅ Indexées', $stats['indexed'], round($stats['indexed'] / $stats['total'] * 100, 1) . '%'],
                ['❌ Non indexées', $stats['not_indexed'], round($stats['not_indexed'] / $stats['total'] * 100, 1) . '%'],
                ['⚠️  Erreurs', $stats['errors'], round($stats['errors'] / $stats['total'] * 100, 1) . '%'],
                ['📊 Total vérifié', $stats['total'], '100%'],
            ]
        );
        
        $this->newLine();
        
        // Stats globales
        $this->displayStats();
        
        // Recommandations
        if ($stats['not_indexed'] > 0) {
            $this->newLine();
            $this->warn("💡 {$stats['not_indexed']} URLs non indexées détectées");
            $this->info("   → Lancez l'indexation quotidienne : php artisan index:urls-daily");
            $this->info("   → Ou indexez manuellement via /admin/indexation");
        }
        
        if ($toVerifyCount > $limit) {
            $remaining = $toVerifyCount - $limit;
            $this->newLine();
            $this->warn("⚠️  {$remaining} URLs restantes à vérifier");
            $this->info("   → Relancez la commande pour continuer");
            $this->info("   → Ou augmentez la limite : --limit=100");
        }
        
        Log::info('Vérification statuts indexation terminée', $stats);
        
        return 0;
    }
    
    /**
     * Afficher les statistiques globales
     */
    protected function displayStats()
    {
        $this->info('📈 STATISTIQUES GLOBALES (Base de données)');
        $this->newLine();
        
        $total = UrlIndexationStatus::count();
        $indexed = UrlIndexationStatus::where('indexed', true)->count();
        $notIndexed = UrlIndexationStatus::where('indexed', false)->count();
        $neverVerified = UrlIndexationStatus::whereNull('last_verification_time')->count();
        
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['URLs suivies', $total],
                ['Indexées (vérifiées)', $indexed],
                ['Non indexées', $notIndexed],
                ['Jamais vérifiées', $neverVerified],
                ['Taux d\'indexation', $total > 0 ? round($indexed / $total * 100, 1) . '%' : 'N/A'],
            ]
        );
    }

    /**
     * Récupérer les URLs déjà indexées (ancien système, gardé pour compatibilité)
     */
    protected function getIndexedUrls()
    {
        // Utiliser la base de données comme source de vérité
        return UrlIndexationStatus::where('indexed', true)
            ->pluck('url')
            ->toArray();
    }

    /**
     * Marquer des URLs comme indexées
     */
    protected function markUrlsAsIndexed(array $urls)
    {
        // Ne plus utiliser Settings, tout passe par UrlIndexationStatus maintenant
        // Cette méthode est gardée pour compatibilité mais ne fait rien
        // Les URLs sont marquées automatiquement via verifyIndexationStatus
    }

    /**
     * Mettre à jour les statistiques
     */
    protected function updateStatistics($successCount, $failedCount, $totalProcessed)
    {
        $stats = \App\Models\Setting::get('daily_indexing_stats', '[]');
        $stats = is_string($stats) ? json_decode($stats, true) : ($stats ?? []);
        
        $today = date('Y-m-d');
        
        if (!isset($stats[$today])) {
            $stats[$today] = [
                'date' => $today,
                'success' => 0,
                'failed' => 0,
                'total' => 0
            ];
        }
        
        $stats[$today]['success'] += $successCount;
        $stats[$today]['failed'] += $failedCount;
        $stats[$today]['total'] += $totalProcessed;
        
        // Garder seulement les 30 derniers jours
        $stats = array_slice($stats, -30, 30, true);
        
        \App\Models\Setting::set('daily_indexing_stats', json_encode($stats), 'json', 'seo');
    }
}

