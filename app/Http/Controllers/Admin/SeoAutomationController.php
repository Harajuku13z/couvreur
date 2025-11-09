<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoAutomation;
use App\Models\City;
use App\Jobs\ProcessSeoCityJob;
use Illuminate\Http\Request;

class SeoAutomationController extends Controller
{
    /**
     * Afficher la liste des automations SEO
     */
    public function index()
    {
        $logs = SeoAutomation::with('city')
            ->latest()
            ->paginate(30);
        
        // Statistiques
        $stats = [
            'total' => SeoAutomation::count(),
            'pending' => SeoAutomation::where('status', 'pending')->count(),
            'published' => SeoAutomation::where('status', 'published')->count(),
            'indexed' => SeoAutomation::where('status', 'indexed')->count(),
            'failed' => SeoAutomation::where('status', 'failed')->count(),
        ];
        
        return view('admin.seo_automation.index', compact('logs', 'stats'));
    }

    /**
     * Forcer l'exécution pour une ville
     */
    public function runForCity(City $city)
    {
        // Dispatcher le job immédiatement
        ProcessSeoCityJob::dispatch($city->id)
            ->onQueue('seo-automation');
        
        return redirect()->back()
            ->with('success', "Tâche planifiée pour {$city->name}. Le traitement est en cours.");
    }

    /**
     * Relancer une automation échouée
     */
    public function retry(SeoAutomation $seoAutomation)
    {
        if (!$seoAutomation->city) {
            return redirect()->back()
                ->with('error', 'Ville non trouvée pour cette automation.');
        }

        ProcessSeoCityJob::dispatch($seoAutomation->city_id)
            ->onQueue('seo-automation');
        
        return redirect()->back()
            ->with('success', "Automation relancée pour {$seoAutomation->city->name}.");
    }
}
