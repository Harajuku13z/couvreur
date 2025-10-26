<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Helpers\SeoHelper;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Afficher une annonce publique
     */
    public function show($service, $city)
    {
        // Reconstituer le slug complet
        $slug = $service . '-' . $city;
        
        // Chercher l'annonce par slug
        $ad = Ad::where('slug', $slug)->first();
        
        if (!$ad) {
            abort(404);
        }
        
        // Récupérer la ville
        $cityModel = City::find($ad->city_id);
        
        if (!$cityModel) {
            abort(404);
        }
        
        // Variables pour le SEO
        $currentPage = 'ads';
        $pageTitle = $ad->meta_title ?? $ad->title;
        $pageDescription = $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
        $pageImage = null; // Utiliser l'image par défaut du SeoHelper
        $pageType = 'website';
        
        // Récupérer des annonces similaires
        $relatedAds = Ad::where('city_id', $ad->city_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'published')
            ->take(3)
            ->get();
        
        return view('ads.show', compact('ad', 'cityModel', 'currentPage', 'pageTitle', 'pageDescription', 'pageImage', 'pageType', 'relatedAds'));
    }
}
