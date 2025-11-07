<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Models\Review;

class AdPublicController extends Controller
{
    public function index()
    {
        $ads = Ad::where('status', 'published')
            ->with('city')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(12);
        
        // Définir la page courante pour le SEO
        $currentPage = 'ads';
        
        return view('ads.index', compact('ads', 'currentPage'));
    }

    public function show(string $slug)
    {
        // Chercher l'annonce par slug avec relation template
        $ad = Ad::with('template', 'city')->where('slug', $slug)->where('status', 'published')->firstOrFail();
        
        $cityModel = $ad->city;
        
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
            $pageKeywords = $ad->meta_keywords ?? '';
            $ogTitle = $pageTitle;
            $ogDescription = $pageDescription;
            $twitterTitle = $pageTitle;
            $twitterDescription = $pageDescription;
            
            // Pas d'image si pas de template
            $featuredImage = null;
        }
        
        $pageImage = $featuredImage ? asset($featuredImage) : null;
        $pageType = 'website';
        
        // Récupérer des annonces similaires
        $relatedAds = Ad::where('city_id', $ad->city_id)
            ->where('id', '!=', $ad->id)
            ->where('status', 'published')
            ->take(3)
            ->get();
        
        return view('ads.show', compact(
            'ad', 
            'cityModel', 
            'currentPage', 
            'pageTitle', 
            'pageDescription', 
            'pageKeywords', 
            'ogTitle', 
            'ogDescription', 
            'twitterTitle', 
            'twitterDescription', 
            'pageImage', 
            'pageType', 
            'relatedAds', 
            'featuredImage'
        ));
    }
}



