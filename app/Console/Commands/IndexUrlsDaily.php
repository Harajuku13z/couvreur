<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSearchConsoleService;
use App\Services\SitemapService;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class IndexUrlsDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:urls-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexe automatiquement 150 URLs par jour via Google Indexing API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Démarrage de l\'indexation quotidienne...');

        // Vérifier si la tâche quotidienne est activée
        $dailyIndexingEnabled = Setting::get('daily_indexing_enabled', false);
        
        if (!$dailyIndexingEnabled) {
            $this->warn('⚠️ L\'indexation quotidienne est désactivée.');
            $this->info('💡 Activez-la dans l\'admin: /admin/indexation');
            return 0;
        }

        // Vérifier que Google Search Console est configuré
        $googleService = new GoogleSearchConsoleService();
        
        if (!$googleService->isConfigured()) {
            $this->error('❌ Google Search Console n\'est pas configuré.');
            return 1;
        }

        // Récupérer toutes les URLs des sitemaps
        $sitemapService = new SitemapService();
        $allUrls = $sitemapService->getAllUrls();
        
        if (empty($allUrls)) {
            $this->warn('⚠️ Aucune URL trouvée dans les sitemaps.');
            return 0;
        }

        // Extraire uniquement les URLs (pas les métadonnées)
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
        
        $urls = array_unique($urls); // Éviter les doublons
        $this->info("📊 Total URLs dans sitemap : " . count($urls));

        // NOUVEAU : Vérifier le statut réel d'indexation depuis la base de données
        $urlsToIndex = [];
        $alreadyIndexedCount = 0;
        
        foreach ($urls as $url) {
            // Vérifier dans la table url_indexation_statuses
            $status = \App\Models\UrlIndexationStatus::where('url', $url)->first();
            
            if ($status && $status->indexed) {
                // URL déjà indexée selon Google
                $alreadyIndexedCount++;
                
                // Mais si la vérification date de plus de 7 jours, on peut re-vérifier
                if ($status->last_verification_time && $status->last_verification_time->lt(now()->subDays(7))) {
                    // Ajouter à la liste de vérification (pas indexation)
                    // Pour l'instant on skip, mais on pourrait implémenter une re-vérification périodique
                }
            } else {
                // URL non indexée ou jamais vérifiée → à indexer
                $urlsToIndex[] = $url;
            }
        }

        $this->info("✅ URLs déjà indexées (selon statuts vérifiés) : {$alreadyIndexedCount}");
        
        if (empty($urlsToIndex)) {
            $this->info('🎉 Toutes les URLs sont déjà indexées selon les vérifications précédentes !');
            $this->info('💡 Pour re-vérifier les statuts, utilisez : php artisan indexation:verify-all');
            return 0;
        }

        $totalUrls = count($urlsToIndex);
        $this->info("📤 URLs restantes à indexer : {$totalUrls}");

        // Limite quotidienne : 150 URLs
        $dailyLimit = 150;
        $urlsForToday = array_slice($urlsToIndex, 0, $dailyLimit);
        
        $this->info("📤 Indexation de " . count($urlsForToday) . " URLs aujourd'hui (limite: {$dailyLimit})");

        // Indexer les URLs
        $result = $googleService->indexUrls($urlsForToday, $dailyLimit);
        
        $successCount = $result['success'] ?? 0;
        $failedCount = $result['failed'] ?? 0;

        // Marquer les URLs réussies comme indexées
        if ($successCount > 0) {
            $successfulUrls = [];
            foreach ($result['results'] ?? [] as $item) {
                if (isset($item['result']['success']) && $item['result']['success']) {
                    $successfulUrls[] = $item['url'];
                }
            }
            
            if (!empty($successfulUrls)) {
                $this->markUrlsAsIndexed($successfulUrls);
                Log::info("✅ " . count($successfulUrls) . " URLs marquées comme indexées");
            }
        }

        // Mettre à jour les statistiques (même si successCount = 0, pour tracer les tentatives)
        $this->updateStatistics($successCount, $failedCount, count($urlsForToday));
        
        // Sauvegarder l'historique même en cas d'échec
        $history = Setting::get('google_indexing_history', '[]');
        $history = is_string($history) ? json_decode($history, true) : ($history ?? []);
        
        $history[] = [
            'date' => now()->toDateTimeString(),
            'total' => count($urlsForToday),
            'success' => $successCount,
            'failed' => $failedCount,
            'type' => 'daily_indexing',
            'timestamp' => time()
        ];
        
        // Garder seulement les 50 derniers envois
        $history = array_slice($history, -50);
        
        Setting::set('google_indexing_history', json_encode($history), 'json', 'seo');
        
        // Vider le cache pour que les nouvelles données soient visibles immédiatement
        Setting::clearCache();

        // Afficher le résumé
        $this->info("✅ Indexation terminée:");
        $this->info("   - {$successCount} URLs indexées avec succès");
        if ($failedCount > 0) {
            $this->warn("   - {$failedCount} URLs échouées");
        }
        
        $remaining = $totalUrls - count($urlsForToday);
        if ($remaining > 0) {
            $daysRemaining = ceil($remaining / $dailyLimit);
            $this->info("   - {$remaining} URLs restantes (environ {$daysRemaining} jour(s))");
        } else {
            $this->info("   - ✅ Toutes les URLs ont été indexées !");
        }

        Log::info("Indexation quotidienne terminée: {$successCount} réussies, {$failedCount} échouées sur " . count($urlsForToday) . " URLs");

        return 0;
    }

    /**
     * Récupérer les URLs déjà indexées
     */
    protected function getIndexedUrls()
    {
        $indexedData = Setting::get('indexed_urls', '[]');
        $indexedUrls = is_string($indexedData) ? json_decode($indexedData, true) : ($indexedData ?? []);
        
        return is_array($indexedUrls) ? $indexedUrls : [];
    }

    /**
     * Marquer des URLs comme indexées
     */
    protected function markUrlsAsIndexed(array $urls)
    {
        $indexedUrls = $this->getIndexedUrls();
        $indexedUrls = array_unique(array_merge($indexedUrls, $urls));
        
        Setting::set('indexed_urls', json_encode($indexedUrls), 'json', 'seo');
    }

    /**
     * Mettre à jour les statistiques
     */
    protected function updateStatistics($successCount, $failedCount, $totalProcessed)
    {
        $stats = Setting::get('daily_indexing_stats', '[]');
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
        
        Setting::set('daily_indexing_stats', json_encode($stats), 'json', 'seo');
    }
}

