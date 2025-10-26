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
}