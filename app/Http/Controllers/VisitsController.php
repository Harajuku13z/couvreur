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
     * Afficher les statistiques de visites (depuis la base de données interne)
     */
    public function index()
    {
        try {
            $days = request()->input('days', 30);
            $showAll = request()->input('all', false); // Paramètre pour afficher tous les pays
            // Convertir en booléen si c'est une chaîne
            if (is_string($showAll)) {
                $showAll = in_array(strtolower($showAll), ['1', 'true', 'yes', 'on']);
            }
            $period = now()->subDays($days);
            
            // Récupérer les visites depuis la base de données
            $query = \App\Models\Visit::excludeBots()
                ->where('visited_at', '>=', $period);
            
            // Filtrer par pays (France) par défaut, sauf si "all" est activé
            if (!$showAll) {
                $query->where(function($q) {
                    $q->where('country', 'France')
                      ->orWhere('country', 'FR')
                      ->orWhere('country_code', 'FR')
                      ->orWhereNull('country'); // Inclure les visites sans pays défini
                });
            }
            
            $visits = $query->orderBy('visited_at', 'desc')->get();
            
            // Calculer le total pour la France uniquement (même si on affiche tous les pays)
            $franceVisits = \App\Models\Visit::excludeBots()
                ->where('visited_at', '>=', $period)
                ->where(function($q) {
                    $q->where('country', 'France')
                      ->orWhere('country', 'FR')
                      ->orWhere('country_code', 'FR');
                })
                ->get();
            
            $totalFranceVisitors = $franceVisits->pluck('session_id')->unique()->count();
            
            // Statistiques globales
            $totalVisits = $visits->count();
            $uniqueVisitors = $visits->pluck('session_id')->unique()->count();
            $uniquePages = $visits->pluck('path')->unique()->count();
            
            // Visites par jour (pour le graphique)
            $visitsByDay = $visits->groupBy(function($visit) {
                return $visit->visited_at->format('Y-m-d');
            })->map(function($dayVisits) {
                return [
                    'date' => $dayVisits->first()->visited_at->format('Y-m-d'),
                    'visits' => $dayVisits->count(),
                    'visitors' => $dayVisits->pluck('session_id')->unique()->count()
                ];
            })->sortBy('date')->values();
            
            // Top pages
            $topPages = $visits->groupBy('path')
                ->map(function($pageVisits, $path) {
                    return [
                        'url' => $path,
                        'visits' => $pageVisits->count(),
                        'visitors' => $pageVisits->pluck('session_id')->unique()->count()
                    ];
                })
                ->sortByDesc('visits')
                ->take(10)
                ->values();
            
            // Top referrers
            $topReferrers = $visits->whereNotNull('referrer_url')
                ->groupBy(function($visit) {
                    $url = parse_url($visit->referrer_url, PHP_URL_HOST);
                    return $url ?: 'Direct';
                })
                ->map(function($refVisits, $domain) {
                    return [
                        'url' => $domain,
                        'visits' => $refVisits->count()
                    ];
                })
                ->sortByDesc('visits')
                ->take(10)
                ->values();
            
            // Top browsers
            $topBrowsers = $visits->whereNotNull('browser')
                ->groupBy('browser')
                ->map(function($browserVisits, $browser) {
                    return [
                        'browser' => $browser,
                        'sessions' => $browserVisits->pluck('session_id')->unique()->count()
                    ];
                })
                ->sortByDesc('sessions')
                ->take(10)
                ->values();
            
            // Top countries
            $topCountries = $visits->whereNotNull('country')
                ->groupBy('country')
                ->map(function($countryVisits, $country) {
                    return [
                        'country' => $country,
                        'sessions' => $countryVisits->pluck('session_id')->unique()->count()
                    ];
                })
                ->sortByDesc('sessions')
                ->take(10)
                ->values();
            
            // Device types
            $deviceTypes = $visits->groupBy('device_type')
                ->map(function($deviceVisits, $device) {
                    return [
                        'device_type' => $device ?: 'unknown',
                        'count' => $deviceVisits->count()
                    ];
                })
                ->values();
            
            $data = [
                'isConfigured' => true, // Toujours configuré avec le tracking interne
                'visitors' => $visitsByDay,
                'topPages' => $topPages,
                'topReferrers' => $topReferrers,
                'topBrowsers' => $topBrowsers,
                'topCountries' => $topCountries,
                'deviceTypes' => $deviceTypes,
                'stats' => [
                    'totalVisitors' => $uniqueVisitors,
                    'totalPageViews' => $totalVisits,
                    'uniquePages' => $uniquePages,
                    'avgPagesPerVisitor' => $uniqueVisitors > 0 ? round($totalVisits / $uniqueVisitors, 2) : 0,
                    'totalFranceVisitors' => $totalFranceVisitors
                ],
                'days' => $days,
                'showAll' => $showAll
            ];
            
            return view('admin.visits.index', $data);
            
        } catch (\Exception $e) {
            Log::error('Erreur VisitsController: ' . $e->getMessage());
            return view('admin.visits.index', [
                'isConfigured' => true,
                'error' => 'Erreur: ' . $e->getMessage(),
                'visitors' => [],
                'topPages' => [],
                'topReferrers' => [],
                'topBrowsers' => [],
                'topCountries' => [],
                'stats' => [
                    'totalVisitors' => 0,
                    'totalPageViews' => 0,
                    'uniquePages' => 0,
                    'avgPagesPerVisitor' => 0
                ]
            ]);
        }
    }
    
    /**
     * API pour récupérer les données de visites (AJAX)
     */
    public function getVisitsData(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $period = now()->subDays($days);
            
            $visits = \App\Models\Visit::excludeBots()
                ->where('visited_at', '>=', $period)
                ->get();
            
            // Visites par jour
            $visitsByDay = $visits->groupBy(function($visit) {
                return $visit->visited_at->format('Y-m-d');
            })->map(function($dayVisits) {
                return [
                    'date' => $dayVisits->first()->visited_at->format('Y-m-d'),
                    'visitors' => $dayVisits->pluck('session_id')->unique()->count(),
                    'pageViews' => $dayVisits->count()
                ];
            })->sortBy('date')->values();
            
            // Top pages
            $topPages = $visits->groupBy('path')
                ->map(function($pageVisits, $path) {
                    return [
                        'url' => $path,
                        'pageViews' => $pageVisits->count()
                    ];
                })
                ->sortByDesc('pageViews')
                ->take(10)
                ->values();
            
            // Top referrers
            $topReferrers = $visits->whereNotNull('referrer_url')
                ->groupBy(function($visit) {
                    $url = parse_url($visit->referrer_url, PHP_URL_HOST);
                    return $url ?: 'Direct';
                })
                ->map(function($refVisits, $domain) {
                    return [
                        'url' => $domain,
                        'pageViews' => $refVisits->count()
                    ];
                })
                ->sortByDesc('pageViews')
                ->take(10)
                ->values();
            
            $data = [
                'visitors' => $visitsByDay,
                'topPages' => $topPages,
                'topReferrers' => $topReferrers,
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

