<?php

namespace App\Helpers;

use App\Models\Setting;

class SeoHelper
{
    /**
     * Convertir un chemin d'image en URL complète
     */
    private static function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }
        
        // Si c'est déjà une URL complète, la retourner telle quelle
        if (str_starts_with($imagePath, 'http')) {
            return $imagePath;
        }
        
        // Sinon, générer l'URL complète
        return url($imagePath);
    }
    /**
     * Obtenir les métadonnées SEO pour une page spécifique
     */
    public static function getPageSeo($pageName, $fallback = [])
    {
        $defaults = [
            'meta_title' => '',
            'meta_description' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'twitter_title' => '',
            'twitter_description' => '',
            'twitter_image' => '',
        ];
        
        $seo = [];
        foreach ($defaults as $key => $default) {
            $seo[$key] = Setting::get("seo_page_{$pageName}_{$key}", $fallback[$key] ?? $default);
        }
        
        return $seo;
    }
    
    /**
     * Générer les balises meta pour une page
     */
    public static function generateMetaTags($pageName, $customData = [])
    {
        $seo = self::getPageSeo($pageName, $customData);
        
        // Fallbacks par défaut
        $defaultTitle = setting('company_name', 'Sauser Couverture') . ' - ' . setting('company_specialization', 'Expert en Couverture');
        $defaultDescription = setting('company_description', 'Expert en travaux de couverture et rénovation. Devis gratuit, intervention rapide, qualité garantie.');
        $defaultImage = self::getDefaultImage();
        
        // Titre final
        $finalTitle = $seo['meta_title'] ?: $customData['title'] ?: $defaultTitle;
        
        // Description finale
        $finalDescription = $seo['meta_description'] ?: $customData['description'] ?: $defaultDescription;
        
        // Image finale (logique selon le type de page)
        if (self::shouldUseDefaultImage($pageName, $customData['image'] ?? null)) {
            // Pages qui doivent utiliser le logo du site par défaut
            $finalImage = $defaultImage;
        } else {
            // Pages qui peuvent utiliser des images spécifiques (services, articles, reviews)
            $finalImage = self::getImageUrl($customData['image'] ?? null) ?: 
                         self::getImageUrl($seo['og_image']) ?: 
                         self::getDefaultOgImage($pageName) ?:
                         $defaultImage;
        }
        
        $meta = [
            'title' => $finalTitle,
            'description' => $finalDescription,
            'og:title' => $seo['og_title'] ?: $finalTitle,
            'og:description' => $seo['og_description'] ?: $finalDescription,
            'og:image' => $finalImage,
            'og:url' => request()->url(),
            'og:type' => $customData['type'] ?? 'website',
            'twitter:title' => $seo['twitter_title'] ?: $seo['og_title'] ?: $finalTitle,
            'twitter:description' => $seo['twitter_description'] ?: $seo['og_description'] ?: $finalDescription,
            'twitter:image' => $finalImage,
        ];
        
        return $meta;
    }
    
    /**
     * Générer le HTML des balises meta
     */
    public static function renderMetaTags($pageName, $customData = [])
    {
        $meta = self::generateMetaTags($pageName, $customData);
        
        $html = '';
        
        // Title
        if (!empty($meta['title'])) {
            $html .= '<title>' . e($meta['title']) . '</title>' . "\n";
        }
        
        // Meta description
        if (!empty($meta['description'])) {
            $html .= '<meta name="description" content="' . e($meta['description']) . '">' . "\n";
        }
        
        // Open Graph
        if (!empty($meta['og:title'])) {
            $html .= '<meta property="og:title" content="' . e($meta['og:title']) . '">' . "\n";
        }
        if (!empty($meta['og:description'])) {
            $html .= '<meta property="og:description" content="' . e($meta['og:description']) . '">' . "\n";
        }
        if (!empty($meta['og:image'])) {
            $html .= '<meta property="og:image" content="' . e($meta['og:image']) . '">' . "\n";
            $html .= '<meta property="og:image:width" content="1200">' . "\n";
            $html .= '<meta property="og:image:height" content="630">' . "\n";
        }
        if (!empty($meta['og:url'])) {
            $html .= '<meta property="og:url" content="' . e($meta['og:url']) . '">' . "\n";
        }
        if (!empty($meta['og:type'])) {
            $html .= '<meta property="og:type" content="' . e($meta['og:type']) . '">' . "\n";
        }
        
        // Twitter Cards
        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        if (!empty($meta['twitter:title'])) {
            $html .= '<meta name="twitter:title" content="' . e($meta['twitter:title']) . '">' . "\n";
        }
        if (!empty($meta['twitter:description'])) {
            $html .= '<meta name="twitter:description" content="' . e($meta['twitter:description']) . '">' . "\n";
        }
        if (!empty($meta['twitter:image'])) {
            $html .= '<meta name="twitter:image" content="' . e($meta['twitter:image']) . '">' . "\n";
        }
        
        return $html;
    }
    
    /**
     * Obtenir l'image par défaut (logo du site)
     */
    private static function getDefaultImage()
    {
        // Priorité: logo de l'entreprise > logo par défaut
        $companyLogo = setting('company_logo');
        if ($companyLogo) {
            return url($companyLogo);
        }
        
        // Fallback: logo par défaut
        return url('logo/logo.png');
    }
    
    /**
     * Déterminer si une page doit utiliser l'image par défaut du site
     */
    private static function shouldUseDefaultImage($pageName, $customImage = null)
    {
        // Si une image personnalisée est fournie, l'utiliser
        if ($customImage) {
            return false;
        }
        
        // Pages qui doivent utiliser l'image par défaut du site (logo)
        $defaultImagePages = ['home', 'portfolio', 'blog', 'ads', 'reviews', 'contact', 'about', 'services'];
        
        // Pages qui peuvent utiliser des images spécifiques
        $specificImagePages = ['articles'];
        
        return in_array($pageName, $defaultImagePages);
    }
    
    /**
     * Obtenir l'image Open Graph par défaut pour une page
     */
    public static function getDefaultOgImage($pageName)
    {
        $defaultImages = [
            'home' => 'images/og-accueil.jpg',
            'portfolio' => 'images/og-realisations.jpg',
            'blog' => 'images/og-blog.jpg',
            'reviews' => 'images/og-avis-clients.jpg',
            'about' => 'images/og-accueil.jpg',
        ];
        
        $pageImage = $defaultImages[$pageName] ?? null;
        if ($pageImage && file_exists(public_path($pageImage))) {
            return url($pageImage);
        }
        
        // Si l'image de page n'existe pas, utiliser le logo
        return self::getDefaultImage();
    }
}
