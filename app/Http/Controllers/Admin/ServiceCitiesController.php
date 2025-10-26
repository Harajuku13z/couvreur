<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\City;
use Illuminate\Http\Request;

class ServiceCitiesController extends Controller
{
    /**
     * Afficher la page de génération d'annonces par service et villes
     */
    public function index()
    {
        // Récupérer les services depuis les settings
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        // Filtrer les services visibles
        $services = collect($services)->filter(function($service) {
            return isset($service['is_visible']) ? $service['is_visible'] : true;
        })->values()->toArray();
        
        $cities = City::orderBy('name')->get();
        
        return view('admin.ads.service-cities', compact('services', 'cities'));
    }

    /**
     * Générer des annonces pour un service et des villes
     */
    public function generate(Request $request)
    {
        try {
            $serviceId = $request->input('service_id');
            $cities = $request->input('cities', []);
            
            if (!$serviceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le service est requis'
                ]);
            }
            
            if (empty($cities)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins une ville doit être sélectionnée'
                ]);
            }
            
            if (count($cities) > 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum 2 villes autorisées'
                ]);
            }
            
            // Récupérer le service depuis les settings
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $service = collect($services)->firstWhere('id', $serviceId);
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service non trouvé'
                ]);
            }
            
            // Pour l'instant, retourner un succès simulé
            // TODO: Implémenter la logique de génération d'annonces
            return response()->json([
                'success' => true,
                'message' => 'Annonces générées avec succès pour le service ' . $service->title,
                'count' => count($cities)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur génération annonces par service: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les villes favorites
     */
    public function getFavoriteCities()
    {
        $cities = City::where('is_favorite', true)->orderBy('name')->get();
        return response()->json($cities);
    }

    /**
     * Récupérer les villes par région
     */
    public function getCitiesByRegion(Request $request)
    {
        $region = $request->get('region');
        $cities = City::where('region', $region)->orderBy('name')->get();
        return response()->json($cities);
    }
}