<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpleIndexationService;
use App\Services\SitemapService;
use App\Services\GoogleSearchConsoleService;
use App\Models\Setting;
use App\Models\UrlIndexationStatus;
use Illuminate\Support\Facades\Log;

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
        
        // URL du site
        $siteUrl = Setting::get('site_url', request()->getSchemeAndHttpHost());
        
        return view('admin.indexation.index', compact(
            'stats',
            'isGoogleConfigured',
            'googleCredentials',
            'dailyIndexingEnabled',
            'siteUrl'
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
        
        // Indexation quotidienne
        if ($request->has('daily_indexing_enabled')) {
            $enabled = $request->boolean('daily_indexing_enabled');
            Setting::set('daily_indexing_enabled', $enabled, 'boolean', 'seo');
        }
        
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
                return response()->json([
                    'success' => true,
                    'message' => 'Sitemap régénéré avec succès',
                    'total_urls' => $result['total_urls'] ?? 0
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
            
            $filename = $request->input('filename', 'sitemap.xml');
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
            foreach ($xml->url as $url) {
                $urls[] = (string)$url->loc;
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
}

