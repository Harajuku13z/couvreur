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
            $baseTitle = $metaForCity['meta_title'] ?? $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $metaForCity['meta_description'] ?? $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
            $pageKeywords = $metaForCity['meta_keywords'] ?? $ad->meta_keywords ?? '';
            $ogTitle = $metaForCity['og_title'] ?? $baseTitle;
            $ogDescription = $metaForCity['og_description'] ?? $pageDescription;
            $twitterTitle = $metaForCity['twitter_title'] ?? $ogTitle ?? $baseTitle;
            $twitterDescription = $metaForCity['twitter_description'] ?? $ogDescription ?? $pageDescription;
            
            // Ajouter le code postal au titre si pas déjà présent
            $postalCode = $cityModel->postal_code ?? '';
            if ($postalCode && strpos($baseTitle, $postalCode) === false) {
                // Extraire le mot-clé principal (keyword de l'annonce ou titre)
                $keyword = $ad->keyword ?? $ad->title ?? '';
                // Ajouter le code postal au titre
                $pageTitle = rtrim($baseTitle, '.') . ' ' . $postalCode;
            } else {
                $pageTitle = $baseTitle;
            }
            
            // Récupérer l'image du template
            $featuredImage = $ad->template->featured_image ?? null;
        } else {
            // Utiliser les métadonnées de l'annonce directement
            $baseTitle = $ad->meta_title ?? $ad->title ?? 'Service professionnel';
            $pageDescription = $ad->meta_description ?? 'Service professionnel à ' . $cityModel->name . '. Devis gratuit et intervention rapide.';
            $pageKeywords = $ad->meta_keywords ?? '';
            
            // Ajouter le code postal au titre si pas déjà présent
            $postalCode = $cityModel->postal_code ?? '';
            if ($postalCode && strpos($baseTitle, $postalCode) === false) {
                $pageTitle = rtrim($baseTitle, '.') . ' ' . $postalCode;
            } else {
                $pageTitle = $baseTitle;
            }
            
            $ogTitle = $pageTitle;
            $ogDescription = $pageDescription;
            $twitterTitle = $pageTitle;
            $twitterDescription = $pageDescription;
            
            // Pas d'image si pas de template
            $featuredImage = null;
        }
        
        // Extraire le mot-clé principal pour les alt des images
        $mainKeyword = $ad->keyword ?? $ad->title ?? '';
        if (empty($mainKeyword) && $ad->template) {
            $mainKeyword = $ad->template->service_name ?? '';
        }
        
        // Ajouter le code postal au mot-clé si présent
        $postalCode = $cityModel->postal_code ?? '';
        if ($postalCode && !empty($mainKeyword)) {
            $mainKeywordWithPostalCode = $mainKeyword . ' ' . $postalCode;
        } else {
            $mainKeywordWithPostalCode = $mainKeyword;
        }
        
        // Récupérer les portfolio items (réalisations)
        $portfolioData = \App\Models\Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        if (!is_array($portfolioItems)) {
            $portfolioItems = [];
        }
        // Filtrer seulement les éléments visibles
        $portfolioItems = array_filter($portfolioItems, function($item) {
            return isset($item['is_visible']) ? $item['is_visible'] : true;
        });
        
        // Générer les mots-clés étendus pour le SEO (invisibles mais visibles pour Google)
        $extendedKeywords = $this->generateExtendedKeywords($mainKeyword, $cityModel, $pageKeywords);
        
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
            'featuredImage',
            'portfolioItems',
            'mainKeyword',
            'mainKeywordWithPostalCode',
            'extendedKeywords'
        ));
    }
    
    /**
     * Générer des mots-clés étendus pour le SEO
     */
    protected function generateExtendedKeywords($mainKeyword, $city, $existingKeywords = '')
    {
        $keywords = [];
        
        // Ajouter les mots-clés existants
        if (!empty($existingKeywords)) {
            $keywords = array_merge($keywords, array_map('trim', explode(',', $existingKeywords)));
        }
        
        // Ajouter des variations avec la ville et le code postal
        $cityName = $city->name ?? '';
        $postalCode = $city->postal_code ?? '';
        
        if (!empty($mainKeyword)) {
            $keywords[] = $mainKeyword;
            if ($cityName) {
                $keywords[] = $mainKeyword . ' ' . $cityName;
                $keywords[] = $cityName . ' ' . $mainKeyword;
            }
            if ($postalCode) {
                $keywords[] = $mainKeyword . ' ' . $postalCode;
                $keywords[] = $postalCode . ' ' . $mainKeyword;
            }
            if ($cityName && $postalCode) {
                $keywords[] = $mainKeyword . ' ' . $cityName . ' ' . $postalCode;
                $keywords[] = 'Entreprise ' . $mainKeyword . ' ' . $cityName;
                $keywords[] = 'Professionnel ' . $mainKeyword . ' ' . $postalCode;
                $keywords[] = 'Devis ' . $mainKeyword . ' ' . $cityName;
                $keywords[] = 'Prix ' . $mainKeyword . ' ' . $postalCode;
                $keywords[] = 'Tarif ' . $mainKeyword . ' ' . $cityName;
            }
        }
        
        // Retourner des mots-clés uniques
        return array_unique(array_filter($keywords));
    }
}



