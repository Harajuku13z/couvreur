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
     */
    public function generate(Request $request)
    {
        try {
            $keyword = $request->input('keyword');
            $cities = $request->input('cities', []);
            
            if (!$keyword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot-clé est requis'
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
            
            // Pour l'instant, retourner un succès simulé
            // TODO: Implémenter la logique de génération d'annonces
            return response()->json([
                'success' => true,
                'message' => 'Annonces générées avec succès',
                'count' => count($cities)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur génération annonces par mot-clé: ' . $e->getMessage());
            
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