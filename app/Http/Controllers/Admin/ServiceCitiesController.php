<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\City;
use Illuminate\Http\Request;

class ServiceCitiesController extends Controller
{
    /**
     * Afficher la page de génération d'annonces par service et villes
     */
    public function index()
    {
        $services = Service::where('status', 'published')->get();
        $cities = City::orderBy('name')->get();
        
        return view('admin.ads.service-cities', compact('services', 'cities'));
    }

    /**
     * Générer des annonces pour un service et des villes
     */
    public function generate(Request $request)
    {
        // Logique de génération d'annonces
        // Pour l'instant, rediriger vers la page de génération existante
        return redirect()->route('ads.generate.service-cities');
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