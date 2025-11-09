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
        
        // Récupérer les villes favorites
        $favoriteCities = City::where('is_favorite', true)->orderBy('name')->get();
        
        // Récupérer les services
        $servicesData = \App\Models\Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        if (!is_array($services)) {
            $services = [];
        }
        
        return view('admin.seo_automation.index', compact('logs', 'stats', 'favoriteCities', 'services'));
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

        ProcessSeoCityJob::dispatch($seoAutomation->city_id, null)
            ->onQueue('seo-automation');
        
        return redirect()->back()
            ->with('success', "Automation relancée pour {$seoAutomation->city->name}.");
    }

    /**
     * Lancer la génération avec paramètres personnalisés
     */
    public function run(Request $request)
    {
        $validated = $request->validate([
            'number_of_articles' => 'required|integer|min:1|max:50',
            'keyword' => 'nullable|string|max:255',
            'service_id' => 'nullable|string',
            'city_ids' => 'nullable|array',
            'city_ids.*' => 'exists:cities,id',
        ]);

        $numberOfArticles = $validated['number_of_articles'];
        $customKeyword = $validated['keyword'] ?? null;
        $serviceId = $validated['service_id'] ?? null;
        $cityIds = $validated['city_ids'] ?? [];

        // Si un service est sélectionné, récupérer son nom comme mot-clé
        if ($serviceId && !$customKeyword) {
            $servicesData = \App\Models\Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            foreach ($services as $service) {
                if (isset($service['id']) && $service['id'] === $serviceId) {
                    $customKeyword = $service['name'] ?? null;
                    break;
                }
            }
        }

        // Si aucune ville spécifiée, utiliser toutes les villes favorites
        if (empty($cityIds)) {
            $cities = City::where('is_favorite', true)->get();
        } else {
            $cities = City::whereIn('id', $cityIds)->get();
        }

        if ($cities->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucune ville sélectionnée ou favorite trouvée.');
        }

        $dispatched = 0;
        $delayCounter = 0;
        foreach ($cities as $cityIndex => $city) {
            // Créer le nombre d'articles demandé pour chaque ville
            for ($articleIndex = 0; $articleIndex < $numberOfArticles; $articleIndex++) {
                ProcessSeoCityJob::dispatch($city->id, $customKeyword)
                    ->onQueue('seo-automation')
                    ->delay(now()->addSeconds($delayCounter * 5)); // Délai échelonné de 5 secondes
                $dispatched++;
                $delayCounter++;
            }
        }

        $message = "✅ {$dispatched} job(s) planifié(s) pour " . $cities->count() . " ville(s)";
        if ($customKeyword) {
            $message .= " avec le mot-clé/service: {$customKeyword}";
        }

        return redirect()->back()
            ->with('success', $message);
    }
}
