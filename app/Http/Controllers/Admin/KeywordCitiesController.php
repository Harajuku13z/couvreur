<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class KeywordCitiesController extends Controller
{
    /**
     * Afficher la page de génération d'annonces par mots-clés et villes
     */
    public function index()
    {
        $cities = City::orderBy('name')->get();
        
        // Mots-clés prédéfinis
        $keywords = [
            'couverture',
            'toiture',
            'façade',
            'isolation',
            'hydrofuge',
            'ravalement',
            'peinture',
            'enduit',
            'bardage',
            'charpente'
        ];
        
        return view('admin.ads.keyword-cities', compact('cities', 'keywords'));
    }

    /**
     * Générer des annonces pour un mot-clé et des villes
     * Utilise directement la logique d'AdGenerationController
     */
    public function generate(Request $request)
    {
        // Déléguer à AdGenerationController
        $adGenerationController = new \App\Http\Controllers\AdGenerationController();
        return $adGenerationController->generateByKeywordCities($request);
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