<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManualAdController extends Controller
{
    /**
     * Afficher la page de création manuelle
     */
    public function index()
    {
        // Récupérer les services depuis les settings
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // Récupérer les villes favorites
        $favoriteCities = City::where('is_favorite', true)
            ->orderBy('name')
            ->get();
        
        // Récupérer toutes les régions pour le filtrage
        $regions = City::distinct()
            ->pluck('region')
            ->filter()
            ->sort()
            ->values();
        
        // Statistiques
        $totalCities = City::count();
        $favoriteCount = $favoriteCities->count();
        $totalAds = Ad::count();
        
        return view('admin.ads.manual', compact(
            'services', 
            'favoriteCities', 
            'regions', 
            'totalCities', 
            'favoriteCount', 
            'totalAds'
        ));
    }
    
    /**
     * Créer une annonce manuellement
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:500',
            'city_id' => 'required|exists:cities,id',
            'service_slug' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|string',
            'keyword' => 'nullable|string|max:255'
        ]);
        
        try {
            $ad = Ad::create([
                'title' => $request->title,
                'keyword' => $request->keyword ?? $request->title, // Utiliser le mot-clé fourni ou le titre
                'city_id' => $request->city_id,
                'slug' => Str::slug($request->title . '-' . $request->city_id),
                'status' => $request->status,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'content_html' => $request->content,
            ]);
            
            return back()->with('success', 'Annonce créée avec succès !');
            
        } catch (\Exception $e) {
            Log::error('Manual ad creation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }
    
    /**
     * Récupérer les villes par région (AJAX)
     */
    public function getCitiesByRegion(Request $request)
    {
        $region = $request->get('region');
        
        if (!$region) {
            return response()->json(['cities' => []]);
        }
        
        $cities = City::where('region', $region)
            ->orderBy('name')
            ->get(['id', 'name', 'postal_code', 'department', 'is_favorite']);
            
        return response()->json(['cities' => $cities]);
    }
    
    /**
     * Récupérer les villes favorites (AJAX)
     */
    public function getFavoriteCities()
    {
        $cities = City::where('is_favorite', true)
            ->orderBy('name')
            ->get(['id', 'name', 'postal_code', 'department', 'region']);
            
        return response()->json(['cities' => $cities]);
    }
}