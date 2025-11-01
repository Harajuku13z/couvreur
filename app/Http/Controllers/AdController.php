<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Models\Setting;
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
        
        // Chercher l'annonce par slug avec relation template
        $ad = Ad::with('template')->where('slug', $slug)->where('status', 'published')->first();
        
        if (!$ad) {
            abort(404, 'Annonce non trouvée');
        }
        
        // Récupérer la ville
        $cityModel = City::find($ad->city_id);
        
        if (!$cityModel) {
            abort(404, 'Ville non trouvée');
        }
        
        // Variables pour le SEO - utiliser getMetaForCity si template existe
        $currentPage = 'ads';
        
        // Si l'annonce a un template, utiliser getMetaForCity pour les métadonnées personnalisées
        if ($ad->template_id && $ad->template) {
            $metaForCity = $ad->template->getMetaForCity($cityModel);
            $pageTitle = $metaForCity['meta_title'] ?? $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $metaForCity['meta_description'] ?? $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
        } else {
            // Utiliser les métadonnées de l'annonce directement
            $pageTitle = $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
        }
        
        $pageImage = null; // Utiliser l'image par défaut du SeoHelper
        $pageType = 'website';
        
        // Récupérer des annonces similaires
        $relatedAds = Ad::where('city_id', $ad->city_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'published')
            ->take(3)
            ->get();
        
        // Récupérer les données de portfolio
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        // Filtrer les éléments de portfolio visibles
        $portfolioItems = array_filter($portfolioItems, function($item) {
            return is_array($item) && ($item['is_visible'] ?? true);
        });
        
        return view('ads.show', compact('ad', 'cityModel', 'currentPage', 'pageTitle', 'pageDescription', 'pageImage', 'pageType', 'relatedAds', 'portfolioItems'));
    }
}
