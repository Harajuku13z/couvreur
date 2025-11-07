<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class VisitsController extends Controller
{
    /**
     * Afficher les statistiques de visites
     */
    public function index()
    {
        try {
            // Vérifier si Google Analytics est configuré
            $seoConfig = Setting::get('seo_config', []);
            $seoConfig = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
            $isConfigured = !empty($seoConfig['google_analytics']);
            
            $data = [
                'isConfigured' => $isConfigured,
                'visitors' => [],
                'pageViews' => [],
                'topPages' => [],
                'topReferrers' => [],
                'topBrowsers' => [],
                'topCountries' => [],
                'stats' => [
                    'totalVisitors' => 0,
                    'totalPageViews' => 0,
                    'avgSessionDuration' => 0,
                    'bounceRate' => 0
                ]
            ];
            
            if ($isConfigured && class_exists(\Spatie\Analytics\Facades\Analytics::class)) {
                try {
                    // Récupérer les visiteurs et pages vues des 30 derniers jours
                    $visitorsAndPageViews = \Spatie\Analytics\Facades\Analytics::fetchVisitorsAndPageViews(\Spatie\Analytics\Period::days(30));
                    $data['visitors'] = $visitorsAndPageViews;
                    
                    // Calculer les statistiques globales
                    $totalVisitors = 0;
                    $totalPageViews = 0;
                    foreach ($visitorsAndPageViews as $item) {
                        $totalVisitors += $item['visitors'];
                        $totalPageViews += $item['pageViews'];
                    }
                    $data['stats']['totalVisitors'] = $totalVisitors;
                    $data['stats']['totalPageViews'] = $totalPageViews;
                    
                    // Top pages
                    try {
                        $topPages = \Spatie\Analytics\Facades\Analytics::fetchMostVisitedPages(\Spatie\Analytics\Period::days(30), 10);
                        $data['topPages'] = $topPages;
                    } catch (\Exception $e) {
                        Log::warning('Erreur récupération top pages: ' . $e->getMessage());
                    }
                    
                    // Top referrers
                    try {
                        $topReferrers = \Spatie\Analytics\Facades\Analytics::fetchTopReferrers(\Spatie\Analytics\Period::days(30), 10);
                        $data['topReferrers'] = $topReferrers;
                    } catch (\Exception $e) {
                        Log::warning('Erreur récupération top referrers: ' . $e->getMessage());
                    }
                    
                    // Top browsers
                    try {
                        $topBrowsers = \Spatie\Analytics\Facades\Analytics::fetchTopBrowsers(\Spatie\Analytics\Period::days(30), 10);
                        $data['topBrowsers'] = $topBrowsers;
                    } catch (\Exception $e) {
                        Log::warning('Erreur récupération top browsers: ' . $e->getMessage());
                    }
                    
                    // Top countries
                    try {
                        $topCountries = \Spatie\Analytics\Facades\Analytics::fetchTopCountries(\Spatie\Analytics\Period::days(30), 10);
                        $data['topCountries'] = $topCountries;
                    } catch (\Exception $e) {
                        Log::warning('Erreur récupération top countries: ' . $e->getMessage());
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Erreur Analytics: ' . $e->getMessage());
                    $data['error'] = 'Erreur lors de la récupération des données Analytics: ' . $e->getMessage();
                }
            }
            
            return view('admin.visits.index', $data);
            
        } catch (\Exception $e) {
            Log::error('Erreur VisitsController: ' . $e->getMessage());
            return view('admin.visits.index', [
                'isConfigured' => false,
                'error' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * API pour récupérer les données de visites (AJAX)
     */
    public function getVisitsData(Request $request)
    {
        try {
            if (!class_exists(\Spatie\Analytics\Facades\Analytics::class)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Spatie Analytics non disponible'
                ], 500);
            }
            
            $days = $request->input('days', 30);
            $period = \Spatie\Analytics\Period::days((int)$days);
            
            $data = [
                'visitors' => \Spatie\Analytics\Facades\Analytics::fetchVisitorsAndPageViews($period),
                'topPages' => \Spatie\Analytics\Facades\Analytics::fetchMostVisitedPages($period, 10),
                'topReferrers' => \Spatie\Analytics\Facades\Analytics::fetchTopReferrers($period, 10),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur getVisitsData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

