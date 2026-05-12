<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpleIndexationService;
use App\Services\SitemapService;
use App\Services\GoogleSearchConsoleService;
use App\Models\Setting;
use App\Models\UrlIndexationStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Contrôleur d'indexation SIMPLIFIÉ et FONCTIONNEL
 */
class IndexationController extends Controller
{
    protected $indexationService;
    
    public function __construct()
    {
        $this->indexationService = app(SimpleIndexationService::class);
    }
    
    /**
     * Page principale d'indexation
     */
    public function index()
    {
        // Stats
        $stats = $this->indexationService->getStats();
        
        // Config Google
        $isGoogleConfigured = app(GoogleSearchConsoleService::class)->isConfigured();
        
        $googleCredentials = Setting::get('google_search_console_credentials', '');
        if (is_array($googleCredentials)) {
            $googleCredentials = json_encode($googleCredentials, JSON_PRETTY_PRINT);
        }
        
        // Indexation quotidienne
        $dailyIndexingEnabled = Setting::get('daily_indexing_enabled', false);
        $dailyIndexingEnabled = filter_var($dailyIndexingEnabled, FILTER_VALIDATE_BOOLEAN);

        // Qualité SEO
        $adsAutoNoindexLowQuality = Setting::get('ads_auto_noindex_low_quality', true);
        $adsAutoNoindexLowQuality = filter_var($adsAutoNoindexLowQuality, FILTER_VALIDATE_BOOLEAN);
        
        // URL du site
        $siteUrl = Setting::get('site_url', request()->getSchemeAndHttpHost());
        $sitemapIndexUrl = rtrim($siteUrl, '/') . '/sitemap.xml';
        $sitemapFiles = $this->getSitemapFiles();
        $latestAuditReports = $this->getLatestAuditReports();
        $siteReleaseName = config('app.release_name', 'SEO Indexation');
        $siteVersion = config('app.version', 'dev');
        
        return view('admin.indexation.index', compact(
            'stats',
            'isGoogleConfigured',
            'googleCredentials',
            'dailyIndexingEnabled',
            'siteUrl',
            'sitemapIndexUrl',
            'sitemapFiles',
            'adsAutoNoindexLowQuality',
            'latestAuditReports',
            'siteReleaseName',
            'siteVersion'
        ));
    }

    /**
     * Sauvegarder configuration
     */
    public function update(Request $request)
    {
        $request->validate([
            'site_url' => 'required|url',
            'google_search_console_credentials' => 'nullable|string',
        ]);
        
        Setting::set('site_url', $request->input('site_url'), 'string', 'seo');
        
        if ($request->filled('google_search_console_credentials')) {
            $credentials = $request->input('google_search_console_credentials');
            
            // Valider JSON
                $decoded = json_decode($credentials, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'JSON invalide : ' . json_last_error_msg());
                }
                
            // Valider service_account
                if (!isset($decoded['type']) || $decoded['type'] !== 'service_account') {
                return back()->with('error', 'Le JSON doit être un compte de service (type: service_account)');
            }
            
            Setting::set('google_search_console_credentials', $credentials, 'json', 'seo');
        }
        
        // Les cases à cocher doivent pouvoir enregistrer aussi la valeur false.
        Setting::set(
            'daily_indexing_enabled',
            $request->boolean('daily_indexing_enabled'),
            'boolean',
            'seo'
        );

        Setting::set(
            'ads_auto_noindex_low_quality',
            $request->boolean('ads_auto_noindex_low_quality'),
            'boolean',
            'seo'
        );
        
        Setting::clearCache();
        
        return back()->with('success', '✅ Configuration sauvegardée avec succès !');
    }
    
    /**
     * Régénérer sitemap
     */
    public function updateSitemap(Request $request)
    {
        try {
            $sitemapService = app(SitemapService::class);
            $result = $sitemapService->generateSitemap();
            
            if ($result['success']) {
                // Générer aussi l'index de sitemap
                $indexResult = $sitemapService->generateSitemapIndex();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Sitemap régénéré avec succès',
                    'total_urls' => $result['total_urls'] ?? 0,
                    'index_url' => $indexResult['url'] ?? null
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Erreur inconnue'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Erreur régénération sitemap', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier URLs (AJAX)
     */
    public function verifyUrls(Request $request)
    {
        try {
            $limit = $request->input('limit', 50);
            
            // Récupérer URLs non vérifiées
            $allUrls = $this->indexationService->getAllSiteUrls();
            $urlsToVerify = [];
            
            foreach ($allUrls as $url) {
                $status = UrlIndexationStatus::where('url', $url)->first();
                
                if (!$status || !$status->last_verification_time) {
                    $urlsToVerify[] = $url;
                }
                
                if (count($urlsToVerify) >= $limit) {
                    break;
                }
            }
            
            if (empty($urlsToVerify)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Toutes les URLs ont déjà été vérifiées',
                    'stats' => [
                        'verified' => 0,
                        'indexed' => 0,
                        'not_indexed' => 0,
                        'errors' => 0,
                        'remaining' => 0
                    ]
                ]);
            }
            
            // Vérifier
            $results = $this->indexationService->verifyUrls($urlsToVerify, $limit);
            
            // Calculer restantes
            $totalVerified = UrlIndexationStatus::whereNotNull('last_verification_time')->count();
            $remaining = count($allUrls) - $totalVerified;
            
            return response()->json([
                'success' => true,
                'message' => "{$results['total']} URLs vérifiées",
                'stats' => [
                    'verified' => $results['total'],
                    'indexed' => $results['indexed'],
                    'not_indexed' => $results['not_indexed'],
                    'errors' => $results['errors'],
                    'remaining' => max(0, $remaining)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur vérification URLs AJAX', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Indexer URLs non indexées (AJAX)
     */
    public function indexUrls(Request $request)
    {
        try {
            $limit = $request->input('limit', 150);
            
            $result = $this->indexationService->runDailyIndexing($limit);
            
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Indexation terminée',
                'success_count' => $result['success'] ?? 0,
                'failed_count' => $result['failed'] ?? 0,
                'total' => $result['total'] ?? 0
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur indexation URLs AJAX', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soumettre sitemap à Google
     */
    public function submitSitemap(Request $request)
    {
        try {
            $request->validate([
                'filename' => 'required|string'
            ]);
            
            $filename = ltrim($request->input('filename', 'sitemap.xml'), '/');
            $sitemapPath = public_path($filename);
            
            if (!file_exists($sitemapPath)) {
                return response()->json([
                    'success' => false,
                    'message' => "Sitemap '{$filename}' non trouvé"
                ], 404);
            }
            
            // Lire sitemap
            $xml = simplexml_load_file($sitemapPath);
            if (!$xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sitemap XML invalide'
                ], 400);
            }
            
            // Extraire URLs
            $urls = [];

            if (isset($xml->url)) {
                foreach ($xml->url as $url) {
                    $urls[] = (string) $url->loc;
                }
            } elseif (isset($xml->sitemap)) {
                foreach ($xml->sitemap as $sitemapEntry) {
                    $loc = (string) $sitemapEntry->loc;
                    if (!$loc) {
                        continue;
                    }

                    $path = parse_url($loc, PHP_URL_PATH);
                    if (!$path) {
                        continue;
                    }

                    $nestedPath = public_path(ltrim($path, '/'));
                    if (!file_exists($nestedPath)) {
                        continue;
                    }

                    $nestedXml = simplexml_load_file($nestedPath);
                    if ($nestedXml && isset($nestedXml->url)) {
                        foreach ($nestedXml->url as $url) {
                            $urls[] = (string) $url->loc;
                        }
                    }
                }
            }
            
            if (empty($urls)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune URL dans le sitemap'
                ], 400);
            }
            
            // Indexer (max 200)
            $urlsToIndex = array_slice($urls, 0, 200);
            $results = $this->indexationService->indexUrls($urlsToIndex, 200);
            
            return response()->json([
                'success' => true,
                'message' => "Sitemap soumis : {$results['success']} URLs envoyées",
                'success_count' => $results['success'],
                'failed_count' => $results['failed'],
                'total' => $results['total']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur soumission sitemap', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    protected function getSitemapFiles(): array
    {
        $files = glob(public_path('sitemap/*.xml')) ?: [];

        $mapped = collect($files)
            ->filter(fn ($file) => basename($file) !== 'sitemap_index.xml')
            ->map(function ($file) {
                $relativePath = 'sitemap/' . basename($file);
                $urlCount = 0;

                try {
                    $xml = simplexml_load_file($file);
                    if ($xml && isset($xml->url)) {
                        $urlCount = count($xml->url);
                    }
                } catch (\Throwable $e) {
                    $urlCount = 0;
                }

                return [
                    'filename' => basename($file),
                    'relative_path' => $relativePath,
                    'category' => $this->humanizeSitemapName(basename($file)),
                    'url_count' => $urlCount,
                    'size_kb' => round(filesize($file) / 1024, 1),
                    'modified_at' => date('d/m/Y H:i', filemtime($file)),
                    'public_url' => url($relativePath),
                ];
            })
            ->sortBy('filename')
            ->values()
            ->all();

        return $mapped;
    }

    protected function getLatestAuditReports(): array
    {
        $reports = glob(storage_path('app/seo-audits/*')) ?: [];

        return collect($reports)
            ->sortByDesc(fn ($path) => filemtime($path))
            ->take(5)
            ->map(function ($path) {
                return [
                    'filename' => basename($path),
                    'modified_at' => date('d/m/Y H:i', filemtime($path)),
                    'size_kb' => round(filesize($path) / 1024, 1),
                ];
            })
            ->values()
            ->all();
    }

    protected function humanizeSitemapName(string $filename): string
    {
        $name = str_replace('.xml', '', $filename);
        $name = str_replace(['ads-service-', 'ads-department-', 'pages-core'], ['Services annonces ', 'Département ', 'Pages coeur'], $name);
        $name = str_replace(['services', 'articles', 'portfolio'], ['Services', 'Articles', 'Portfolio'], $name);

        return Str::headline($name);
    }
}
