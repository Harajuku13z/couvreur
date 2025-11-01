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
        
        // Récupérer l'image du template ou de l'annonce
        $featuredImage = null;
        $pageTitle = null;
        $pageDescription = null;
        $pageKeywords = null;
        $ogTitle = null;
        $ogDescription = null;
        $twitterTitle = null;
        $twitterDescription = null;
        
        // Si l'annonce a un template, utiliser getMetaForCity pour les métadonnées personnalisées
        if ($ad->template_id && $ad->template) {
            $metaForCity = $ad->template->getMetaForCity($cityModel);
            $pageTitle = $metaForCity['meta_title'] ?? $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $metaForCity['meta_description'] ?? $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
            $pageKeywords = $metaForCity['meta_keywords'] ?? $ad->meta_keywords ?? '';
            $ogTitle = $metaForCity['og_title'] ?? $pageTitle;
            $ogDescription = $metaForCity['og_description'] ?? $pageDescription;
            $twitterTitle = $metaForCity['twitter_title'] ?? $ogTitle ?? $pageTitle;
            $twitterDescription = $metaForCity['twitter_description'] ?? $ogDescription ?? $pageDescription;
            
            // Récupérer l'image du template
            $featuredImage = $ad->template->featured_image ?? null;
        } else {
            // Utiliser les métadonnées de l'annonce directement
            $pageTitle = $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
            $pageKeywords = '';
            $ogTitle = $pageTitle;
            $ogDescription = $pageDescription;
            $twitterTitle = $pageTitle;
            $twitterDescription = $pageDescription;
            
            // Pas d'image si pas de template
            $featuredImage = null;
        }
        
        $pageImage = $featuredImage ? asset($featuredImage) : null; // Utiliser l'image du template ou l'image par défaut du SeoHelper
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
        
        return view('ads.show', compact('ad', 'cityModel', 'currentPage', 'pageTitle', 'pageDescription', 'pageKeywords', 'ogTitle', 'ogDescription', 'twitterTitle', 'twitterDescription', 'pageImage', 'pageType', 'relatedAds', 'portfolioItems', 'featuredImage'));
    }
}
