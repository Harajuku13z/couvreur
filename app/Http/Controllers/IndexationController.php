<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Services\SitemapService;
use App\Services\GoogleSearchConsoleService;
use App\Services\IndexJumpService;

class IndexationController extends Controller
{
    /**
     * Afficher la page de gestion de l'indexation
     */
    public function index()
    {
        $seoConfigData = Setting::get('seo_config', '[]');
        $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
        
        // Valeurs par défaut pour l'indexation
        $defaults = [
            'robots_index' => true,
            'robots_follow' => true,
            'robots_archive' => true,
            'robots_snippet' => true,
            'robots_imageindex' => true,
            'sitemap_enabled' => true,
            'sitemap_priority' => 0.8,
            'sitemap_changefreq' => 'weekly',
        ];
        
        $indexationConfig = array_merge($defaults, array_intersect_key($seoConfig, $defaults));
        
        // Récupérer les credentials Google Search Console
        $googleCredentials = Setting::get('google_search_console_credentials', '');
        $googleCredentialsArray = [];
        if (!empty($googleCredentials)) {
            $googleCredentialsArray = is_string($googleCredentials) ? json_decode($googleCredentials, true) : $googleCredentials;
        }
        
        // Récupérer l'état de l'indexation quotidienne
        $dailyIndexingEnabled = Setting::get('daily_indexing_enabled', false);
        
        // Récupérer les statistiques d'indexation quotidienne
        $dailyStats = Setting::get('daily_indexing_stats', '[]');
        $dailyStats = is_string($dailyStats) ? json_decode($dailyStats, true) : ($dailyStats ?? []);
        
        // Récupérer les URLs déjà indexées
        $indexedUrls = Setting::get('indexed_urls', '[]');
        $indexedUrls = is_string($indexedUrls) ? json_decode($indexedUrls, true) : ($indexedUrls ?? []);
        $indexedCount = is_array($indexedUrls) ? count($indexedUrls) : 0;
        
        // Récupérer les informations sur les sitemaps
        $sitemapService = new SitemapService();
        $sitemapFiles = glob(public_path('sitemap*.xml'));
        $sitemapInfo = [];
        foreach ($sitemapFiles as $file) {
            $filename = basename($file);
            if ($filename === 'sitemap_index.xml') {
                continue;
            }
            $sitemapInfo[] = [
                'filename' => $filename,
                'url' => url($filename),
                'size' => filesize($file),
                'last_modified' => filemtime($file)
            ];
        }
        
        // Vérifier si Google Search Console est configuré
        $googleService = new GoogleSearchConsoleService();
        $isGoogleConfigured = $googleService->isConfigured();
        
        // Récupérer l'historique des envois à Google
        $submissionHistory = Setting::get('google_indexing_history', '[]');
        $submissionHistory = is_string($submissionHistory) ? json_decode($submissionHistory, true) : ($submissionHistory ?? []);
        
        // Récupérer le nombre total d'URLs dans les sitemaps
        $totalUrlsInSitemap = 0;
        try {
            $allUrls = $sitemapService->getAllUrls();
            $totalUrlsInSitemap = count($allUrls);
        } catch (\Exception $e) {
            \Log::warning('Impossible de compter les URLs: ' . $e->getMessage());
        }
        
        // Vérifier si IndexJump est configuré
        $indexJumpService = new IndexJumpService();
        $isIndexJumpConfigured = $indexJumpService->isConfigured();
        $indexJumpToken = Setting::get('indexjump_token', '3d93dd2657466b97a401e540aaf9c72e');
        
        return view('admin.indexation.index', compact(
            'indexationConfig', 
            'googleCredentialsArray', 
            'sitemapInfo', 
            'isGoogleConfigured',
            'submissionHistory',
            'totalUrlsInSitemap',
            'dailyIndexingEnabled',
            'dailyStats',
            'indexedCount',
            'isIndexJumpConfigured',
            'indexJumpToken'
        ));
    }

    /**
     * Réinitialiser et régénérer tous les sitemaps
     */
    public function resetSitemaps(Request $request)
    {
        try {
            // Supprimer tous les anciens sitemaps
            $sitemapFiles = glob(public_path('sitemap*.xml'));
            $deletedCount = 0;
            
            foreach ($sitemapFiles as $file) {
                if (unlink($file)) {
                    $deletedCount++;
                    \Log::info("🗑️ Sitemap supprimé: " . basename($file));
                }
            }
            
            // Vérifier que site_url est bien configuré
            $siteUrl = Setting::get('site_url', null);
            if (empty($siteUrl)) {
                // Utiliser APP_URL depuis .env
                $siteUrl = config('app.url', 'https://normesrenovationbretagne.fr');
            }
            
            // S'assurer que l'URL est correcte
            if (!preg_match('/^https?:\/\//', $siteUrl)) {
                $siteUrl = 'https://' . $siteUrl;
            }
            $siteUrl = rtrim($siteUrl, '/');
            
            // Mettre à jour le setting si nécessaire
            if (Setting::get('site_url') !== $siteUrl) {
                Setting::set('site_url', $siteUrl, 'string', 'seo');
                Setting::clearCache();
                \Log::info("✅ site_url mis à jour: {$siteUrl}");
            }
            
            // Régénérer tous les sitemaps
            $sitemapService = new SitemapService();
            $result = $sitemapService->generateSitemap();
            
            return response()->json([
                'success' => true,
                'message' => "Réinitialisation réussie : {$deletedCount} ancien(s) sitemap(s) supprimé(s), " . count($result['sitemaps']) . " nouveau(x) sitemap(s) généré(s)",
                'deleted_count' => $deletedCount,
                'generated_count' => count($result['sitemaps']),
                'total_urls' => $result['total_urls'],
                'site_url' => $siteUrl
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur réinitialisation sitemaps: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sauvegarder la configuration d'indexation
     */
    public function update(Request $request)
    {
        $request->validate([
            'robots_index' => 'nullable|boolean',
            'robots_follow' => 'nullable|boolean',
            'robots_archive' => 'nullable|boolean',
            'robots_snippet' => 'nullable|boolean',
            'robots_imageindex' => 'nullable|boolean',
            'sitemap_enabled' => 'nullable|boolean',
            'sitemap_priority' => 'nullable|numeric|min:0|max:1',
            'sitemap_changefreq' => 'nullable|string|in:always,hourly,daily,weekly,monthly,yearly,never',
            'google_search_console_credentials' => 'nullable|string',
            'site_url' => 'nullable|url'
        ]);

        // Récupérer la configuration SEO existante
        $existingConfig = Setting::get('seo_config', '[]');
        $existingConfig = is_string($existingConfig) ? json_decode($existingConfig, true) : ($existingConfig ?? []);
        
        // Mettre à jour uniquement les paramètres d'indexation
        $existingConfig['robots_index'] = $request->boolean('robots_index', true);
        $existingConfig['robots_follow'] = $request->boolean('robots_follow', true);
        $existingConfig['robots_archive'] = $request->boolean('robots_archive', true);
        $existingConfig['robots_snippet'] = $request->boolean('robots_snippet', true);
        $existingConfig['robots_imageindex'] = $request->boolean('robots_imageindex', true);
        $existingConfig['sitemap_enabled'] = $request->boolean('sitemap_enabled', true);
        $existingConfig['sitemap_priority'] = $request->input('sitemap_priority', 0.8);
        $existingConfig['sitemap_changefreq'] = $request->input('sitemap_changefreq', 'weekly');
        
        // Sauvegarder la configuration
        Setting::set('seo_config', json_encode($existingConfig), 'json', 'seo');
        Setting::clearCache();
        
        // Sauvegarder les credentials Google Search Console
        if ($request->has('google_search_console_credentials')) {
            $credentials = $request->input('google_search_console_credentials');
            Setting::set('google_search_console_credentials', $credentials, 'json', 'seo');
        }
        
        // Sauvegarder l'URL du site
        if ($request->has('site_url')) {
            Setting::set('site_url', $request->input('site_url'), 'string', 'general');
        }
        
        return redirect()->route('admin.indexation.index')->with('success', 'Configuration d\'indexation sauvegardée avec succès !');
    }

    /**
     * Mettre à jour le sitemap via AJAX
     */
    public function updateSitemap(Request $request)
    {
        try {
            $sitemapService = new SitemapService();
            $result = $sitemapService->generateSitemap();
            
            if ($result['success']) {
                $sitemapFiles = [];
                foreach ($result['sitemaps'] as $sitemap) {
                    $filePath = public_path($sitemap['filename']);
                    if (file_exists($filePath)) {
                        $sitemapFiles[] = [
                            'filename' => $sitemap['filename'],
                            'url' => $sitemap['url'],
                            'urls_count' => $sitemap['urls_count'],
                            'size' => filesize($filePath),
                            'last_modified' => date('d/m/Y H:i:s', filemtime($filePath))
                        ];
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sitemap(s) mis à jour avec succès',
                    'total_urls' => $result['total_urls'],
                    'sitemaps' => $sitemapFiles
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . ($result['error'] ?? 'Erreur inconnue')
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour sitemap: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du sitemap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer toutes les URLs des sitemaps
     */
    public function getAllUrls(Request $request)
    {
        try {
            $sitemapService = new SitemapService();
            $urls = $sitemapService->getAllUrls();
            
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 100);
            $total = count($urls);
            $offset = ($page - 1) * $perPage;
            $paginatedUrls = array_slice($urls, $offset, $perPage);
            
            return response()->json([
                'success' => true,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
                'urls' => $paginatedUrls
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération URLs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des URLs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Indexer des URLs via Google Search Console API
     */
    public function indexUrls(Request $request)
    {
        try {
            $request->validate([
                'urls' => 'required|array',
                'urls.*' => 'required|url'
            ]);
            
            $googleService = new GoogleSearchConsoleService();
            
            if (!$googleService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Search Console n\'est pas configuré. Veuillez ajouter vos credentials.'
                ], 400);
            }
            
            $urls = $request->input('urls');
            $result = $googleService->indexUrls($urls);
            
            return response()->json([
                'success' => true,
                'message' => "Indexation terminée: {$result['success']} réussies, {$result['failed']} échouées",
                'total' => $result['total'],
                'success_count' => $result['success'],
                'failed_count' => $result['failed'],
                'results' => $result['results']
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur indexation URLs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'indexation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester la connexion Google Search Console
     */
    public function testGoogleConnection(Request $request)
    {
        try {
            $googleService = new GoogleSearchConsoleService();
            $result = $googleService->testConnection();
            
            // Si la connexion réussit, tester aussi l'indexation
            if ($result['success']) {
                $indexingTest = $googleService->testIndexing();
                $result['indexing_test'] = $indexingTest;
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer toutes les URLs du sitemap à Google (par sitemap et par batch)
     */
    public function submitAllUrlsToGoogle(Request $request)
    {
        try {
            $googleService = new GoogleSearchConsoleService();
            
            if (!$googleService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Search Console n\'est pas configuré. Veuillez ajouter vos credentials.'
                ], 400);
            }
            
            $sitemapService = new SitemapService();
            
            // Récupérer tous les fichiers sitemap (sauf l'index)
            $sitemapFiles = glob(public_path('sitemap*.xml'));
            $sitemapFiles = array_filter($sitemapFiles, function($file) {
                return basename($file) !== 'sitemap_index.xml';
            });
            
            if (empty($sitemapFiles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun sitemap trouvé. Veuillez d\'abord générer le sitemap.'
                ], 400);
            }
            
            // Taille des batches (200 URLs par batch pour éviter les limites)
            $batchSize = 200;
            $totalSuccess = 0;
            $totalFailed = 0;
            $totalProcessed = 0;
            $sitemapResults = [];
            
            // Traiter chaque sitemap séparément
            foreach ($sitemapFiles as $sitemapFile) {
                $filename = basename($sitemapFile);
                \Log::info("Traitement du sitemap: {$filename}");
                
                // Lire le sitemap
                $xml = file_get_contents($sitemapFile);
                $xml = simplexml_load_string($xml);
                
                if (!$xml || !isset($xml->url)) {
                    \Log::warning("Sitemap {$filename} vide ou invalide");
                    continue;
                }
                
                // Extraire les URLs de ce sitemap
                $sitemapUrls = [];
                foreach ($xml->url as $url) {
                    $sitemapUrls[] = (string)$url->loc;
                }
                
                if (empty($sitemapUrls)) {
                    continue;
                }
                
                // Traiter par batch
                $batches = array_chunk($sitemapUrls, $batchSize);
                $sitemapSuccess = 0;
                $sitemapFailed = 0;
                
                foreach ($batches as $batchIndex => $batch) {
                    \Log::info("Traitement batch " . ($batchIndex + 1) . "/" . count($batches) . " du sitemap {$filename} (" . count($batch) . " URLs)");
                    
                    // Envoyer le batch à Google
                    $batchResult = $googleService->indexUrls($batch, $batchSize);
                    
                    $sitemapSuccess += $batchResult['success'];
                    $sitemapFailed += $batchResult['failed'];
                    $totalSuccess += $batchResult['success'];
                    $totalFailed += $batchResult['failed'];
                    $totalProcessed += count($batch);
                    
                    // Pause entre les batches pour éviter les limites de rate
                    if ($batchIndex < count($batches) - 1) {
                        sleep(2); // 2 secondes entre chaque batch
                    }
                }
                
                $sitemapResults[] = [
                    'sitemap' => $filename,
                    'total' => count($sitemapUrls),
                    'success' => $sitemapSuccess,
                    'failed' => $sitemapFailed
                ];
                
                // Pause entre les sitemaps
                sleep(1);
            }
            
            // Sauvegarder l'historique
            $history = Setting::get('google_indexing_history', '[]');
            $history = is_string($history) ? json_decode($history, true) : ($history ?? []);
            
            $history[] = [
                'date' => now()->toDateTimeString(),
                'total' => $totalProcessed,
                'success' => $totalSuccess,
                'failed' => $totalFailed,
                'sitemaps_processed' => count($sitemapResults),
                'timestamp' => time()
            ];
            
            // Garder seulement les 50 derniers envois
            $history = array_slice($history, -50);
            
            Setting::set('google_indexing_history', json_encode($history), 'json', 'seo');
            
            \Log::info("Indexation terminée: {$totalSuccess} réussies, {$totalFailed} échouées sur {$totalProcessed} URLs");
            
            return response()->json([
                'success' => true,
                'message' => "Indexation terminée: {$totalSuccess} URLs envoyées avec succès, {$totalFailed} échouées",
                'total' => $totalProcessed,
                'success_count' => $totalSuccess,
                'failed_count' => $totalFailed,
                'sitemaps_processed' => count($sitemapResults),
                'sitemap_results' => $sitemapResults,
                'date' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi URLs à Google: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer les URLs d'un sitemap spécifique à Google (par batch de 200)
     */
    public function submitSitemapToGoogle(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string'
            ]);

            $googleService = new GoogleSearchConsoleService();
            
            if (!$googleService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Search Console n\'est pas configuré. Veuillez ajouter vos credentials.'
                ], 400);
            }
            
            $filename = $request->input('filename');
            $sitemapFile = public_path($filename);
            
            if (!file_exists($sitemapFile)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le fichier sitemap '{$filename}' n'existe pas."
                ], 404);
            }
            
            // Lire le sitemap
            $xml = file_get_contents($sitemapFile);
            $xml = simplexml_load_string($xml);
            
            if (!$xml || !isset($xml->url)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le sitemap '{$filename}' est vide ou invalide."
                ], 400);
            }
            
            // Extraire les URLs
            $sitemapUrls = [];
            foreach ($xml->url as $url) {
                $sitemapUrls[] = (string)$url->loc;
            }
            
            if (empty($sitemapUrls)) {
                return response()->json([
                    'success' => false,
                    'message' => "Aucune URL trouvée dans le sitemap '{$filename}'."
                ], 400);
            }
            
            // Taille des batches (200 URLs par batch)
            $batchSize = 200;
            $batches = array_chunk($sitemapUrls, $batchSize);
            $totalSuccess = 0;
            $totalFailed = 0;
            
            \Log::info("Traitement du sitemap {$filename}: " . count($sitemapUrls) . " URLs en " . count($batches) . " batch(s)");
            
            // Traiter chaque batch
            foreach ($batches as $batchIndex => $batch) {
                \Log::info("Traitement batch " . ($batchIndex + 1) . "/" . count($batches) . " du sitemap {$filename} (" . count($batch) . " URLs)");
                
                // Envoyer le batch à Google
                $batchResult = $googleService->indexUrls($batch, $batchSize);
                
                $totalSuccess += $batchResult['success'];
                $totalFailed += $batchResult['failed'];
                
                // Pause entre les batches pour éviter les limites de rate
                if ($batchIndex < count($batches) - 1) {
                    sleep(2); // 2 secondes entre chaque batch
                }
            }
            
            // Sauvegarder l'historique
            $history = Setting::get('google_indexing_history', '[]');
            $history = is_string($history) ? json_decode($history, true) : ($history ?? []);
            
            $history[] = [
                'date' => now()->toDateTimeString(),
                'total' => count($sitemapUrls),
                'success' => $totalSuccess,
                'failed' => $totalFailed,
                'sitemap' => $filename,
                'timestamp' => time()
            ];
            
            // Garder seulement les 50 derniers envois
            $history = array_slice($history, -50);
            
            Setting::set('google_indexing_history', json_encode($history), 'json', 'seo');
            
            \Log::info("Indexation sitemap {$filename} terminée: {$totalSuccess} réussies, {$totalFailed} échouées sur " . count($sitemapUrls) . " URLs");
            
            return response()->json([
                'success' => true,
                'message' => "Sitemap '{$filename}': {$totalSuccess} URLs envoyées avec succès, {$totalFailed} échouées",
                'total' => count($sitemapUrls),
                'success_count' => $totalSuccess,
                'failed_count' => $totalFailed,
                'sitemap' => $filename,
                'date' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi sitemap à Google: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activer/désactiver l'indexation quotidienne
     */
    public function toggleDailyIndexing(Request $request)
    {
        try {
            $enabled = $request->boolean('enabled', false);
            Setting::set('daily_indexing_enabled', $enabled, 'boolean', 'seo');
            Setting::clearCache();
            
            return response()->json([
                'success' => true,
                'message' => $enabled ? 'Indexation quotidienne activée' : 'Indexation quotidienne désactivée',
                'enabled' => $enabled
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur toggle indexation quotidienne: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser la liste des URLs indexées
     */
    public function resetIndexedUrls(Request $request)
    {
        try {
            Setting::set('indexed_urls', json_encode([]), 'json', 'seo');
            Setting::clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Liste des URLs indexées réinitialisée'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur réinitialisation URLs indexées: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exécuter manuellement l'indexation quotidienne
     */
    public function runDailyIndexing(Request $request)
    {
        try {
            \Artisan::call('index:urls-daily');
            $output = \Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Indexation quotidienne exécutée',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur exécution indexation quotidienne: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester l'indexation d'une seule URL
     */
    public function testSingleUrl(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $url = $request->input('url');
            
            $googleService = new GoogleSearchConsoleService();
            
            if (!$googleService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Search Console n\'est pas configuré. Veuillez ajouter vos credentials.'
                ], 400);
            }

            // Indexer l'URL
            $result = $googleService->indexUrl($url);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "URL indexée avec succès: {$url}",
                    'url' => $url
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Erreur lors de l\'indexation',
                    'error_code' => $result['error_code'] ?? null,
                    'error_details' => $result['error_details'] ?? null,
                    'url' => $url
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'URL invalide. Veuillez entrer une URL complète (ex: https://example.com/page)',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur test indexation URL: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester la connexion IndexJump
     */
    public function testIndexJumpConnection(Request $request)
    {
        try {
            $indexJumpService = new IndexJumpService();
            $result = $indexJumpService->getBalance();
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester l'indexation d'une seule URL via IndexJump
     */
    public function testIndexJumpUrl(Request $request)
    {
        try {
            $request->validate([
                'url' => 'required|url'
            ]);

            $url = $request->input('url');
            $bot = $request->input('bot', 0); // 0 = GoogleBot par défaut
            
            $indexJumpService = new IndexJumpService();
            
            if (!$indexJumpService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IndexJump n\'est pas configuré. Veuillez ajouter votre token.'
                ], 400);
            }

            $result = $indexJumpService->indexUrl($url, $bot);
            
            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'URL invalide. Veuillez entrer une URL complète (ex: https://example.com/page)',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur test IndexJump URL: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un sitemap à IndexJump
     */
    public function submitSitemapToIndexJump(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string',
                'bot' => 'nullable|integer|in:0,1,2'
            ]);

            $indexJumpService = new IndexJumpService();
            
            if (!$indexJumpService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IndexJump n\'est pas configuré. Veuillez ajouter votre token.'
                ], 400);
            }
            
            $filename = $request->input('filename');
            $bot = $request->input('bot', 0);
            $sitemapFile = public_path($filename);
            
            if (!file_exists($sitemapFile)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le fichier sitemap '{$filename}' n'existe pas."
                ], 404);
            }
            
            $xml = file_get_contents($sitemapFile);
            $xml = simplexml_load_string($xml);
            
            if (!$xml || !isset($xml->url)) {
                return response()->json([
                    'success' => false,
                    'message' => "Le sitemap '{$filename}' est vide ou invalide."
                ], 400);
            }
            
            $sitemapUrls = [];
            foreach ($xml->url as $url) {
                $sitemapUrls[] = (string)$url->loc;
            }
            
            if (empty($sitemapUrls)) {
                return response()->json([
                    'success' => false,
                    'message' => "Aucune URL trouvée dans le sitemap '{$filename}'."
                ], 400);
            }
            
            // Envoyer par batch de 100 URLs (limite recommandée pour IndexJump)
            $batchSize = 100;
            $batches = array_chunk($sitemapUrls, $batchSize);
            $totalSuccess = 0;
            $totalFailed = 0;
            
            \Log::info("Traitement du sitemap {$filename} avec IndexJump: " . count($sitemapUrls) . " URLs en " . count($batches) . " batch(s)");
            
            foreach ($batches as $batchIndex => $batch) {
                \Log::info("Traitement batch " . ($batchIndex + 1) . "/" . count($batches) . " du sitemap {$filename} (" . count($batch) . " URLs)");
                
                $batchResult = $indexJumpService->indexUrls($batch, $bot);
                
                if ($batchResult['success']) {
                    $totalSuccess += count($batch);
                } else {
                    $totalFailed += count($batch);
                    \Log::warning("Échec batch IndexJump: " . ($batchResult['message'] ?? 'Erreur inconnue'));
                }
                
                // Pause entre les batches pour éviter les limites de rate
                if ($batchIndex < count($batches) - 1) {
                    sleep(2);
                }
            }
            
            \Log::info("Indexation IndexJump sitemap {$filename} terminée: {$totalSuccess} réussies, {$totalFailed} échouées sur " . count($sitemapUrls) . " URLs");
            
            return response()->json([
                'success' => true,
                'message' => "Sitemap '{$filename}': {$totalSuccess} URLs envoyées avec succès, {$totalFailed} échouées",
                'total' => count($sitemapUrls),
                'success_count' => $totalSuccess,
                'failed_count' => $totalFailed,
                'sitemap' => $filename,
                'date' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi sitemap à IndexJump: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sauvegarder le token IndexJump
     */
    public function updateIndexJumpToken(Request $request)
    {
        try {
            $request->validate([
                'indexjump_token' => 'nullable|string|max:255'
            ]);

            if ($request->has('indexjump_token')) {
                Setting::set('indexjump_token', $request->input('indexjump_token'), 'string', 'seo');
                Setting::clearCache();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Token IndexJump sauvegardé avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur sauvegarde token IndexJump: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}

