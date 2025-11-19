<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SimpleIndexationService;
use App\Models\Setting;

class SimpleIndexationController extends Controller
{
    protected $service;
    
    public function __construct()
    {
        $this->service = app(SimpleIndexationService::class);
    }
    
    /**
     * Page d'indexation simplifiée
     */
    public function index()
    {
        $stats = $this->service->getStats();
        
        $isGoogleConfigured = app(\App\Services\GoogleSearchConsoleService::class)->isConfigured();
        
        $googleCredentials = Setting::get('google_search_console_credentials', '');
        if (is_array($googleCredentials)) {
            $googleCredentials = json_encode($googleCredentials, JSON_PRETTY_PRINT);
        }
        
        $dailyIndexingEnabled = Setting::get('daily_indexing_enabled', false);
        $dailyIndexingEnabled = filter_var($dailyIndexingEnabled, FILTER_VALIDATE_BOOLEAN);
        
        return view('admin.indexation.simple', compact(
            'stats',
            'isGoogleConfigured',
            'googleCredentials',
            'dailyIndexingEnabled'
        ));
    }
    
    /**
     * Vérifier URLs via AJAX
     */
    public function verify(Request $request)
    {
        $limit = $request->input('limit', 50);
        
        try {
            $unverifiedUrls = [];
            $allUrls = $this->service->getAllSiteUrls();
            
            foreach ($allUrls as $url) {
                $status = \App\Models\UrlIndexationStatus::where('url', $url)->first();
                
                if (!$status || !$status->last_verification_time) {
                    $unverifiedUrls[] = $url;
                }
                
                if (count($unverifiedUrls) >= $limit) {
                    break;
                }
            }
            
            if (empty($unverifiedUrls)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Toutes les URLs ont déjà été vérifiées',
                    'stats' => [
                        'verified_now' => 0,
                        'indexed' => 0,
                        'not_indexed' => 0,
                        'errors' => 0,
                        'remaining' => 0
                    ]
                ]);
            }
            
            $results = $this->service->verifyUrls($unverifiedUrls, $limit);
            
            return response()->json([
                'success' => true,
                'message' => "{$results['total']} URLs vérifiées",
                'stats' => [
                    'verified_now' => $results['total'],
                    'indexed' => $results['indexed'],
                    'not_indexed' => $results['not_indexed'],
                    'errors' => $results['errors'],
                    'remaining' => count($allUrls) - \App\Models\UrlIndexationStatus::whereNotNull('last_verification_time')->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur vérification AJAX', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Indexer URLs via AJAX
     */
    public function indexNow(Request $request)
    {
        try {
            $result = $this->service->runDailyIndexing(150);
            
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Indexation terminée',
                'success_count' => $result['indexed'] ?? 0,
                'total' => $result['total'] ?? 0
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur indexation AJAX', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sauvegarder configuration
     */
    public function saveConfig(Request $request)
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
            
            Setting::set('google_search_console_credentials', $credentials, 'json', 'seo');
        }
        
        if ($request->has('daily_indexing_enabled')) {
            Setting::set('daily_indexing_enabled', $request->boolean('daily_indexing_enabled'), 'boolean', 'seo');
        }
        
        Setting::clearCache();
        
        return back()->with('success', 'Configuration sauvegardée avec succès !');
    }
}

