<?php

namespace App\Helpers;

use App\Models\Setting;

class SeoHelper
{
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
        
        $meta = [
            'title' => $seo['meta_title'] ?: ($customData['title'] ?? ''),
            'description' => $seo['meta_description'] ?: ($customData['description'] ?? ''),
            'og:title' => $seo['og_title'] ?: $seo['meta_title'] ?: ($customData['title'] ?? ''),
            'og:description' => $seo['og_description'] ?: $seo['meta_description'] ?: ($customData['description'] ?? ''),
            'og:image' => $seo['og_image'] ? asset($seo['og_image']) : ($customData['image'] ?? asset('images/og-default.jpg')),
            'og:url' => request()->url(),
            'og:type' => $customData['type'] ?? 'website',
            'twitter:title' => $seo['twitter_title'] ?: $seo['og_title'] ?: $seo['meta_title'] ?: ($customData['title'] ?? ''),
            'twitter:description' => $seo['twitter_description'] ?: $seo['og_description'] ?: $seo['meta_description'] ?: ($customData['description'] ?? ''),
            'twitter:image' => $seo['twitter_image'] ? asset($seo['twitter_image']) : $seo['og_image'] ? asset($seo['og_image']) : ($customData['image'] ?? asset('images/og-default.jpg')),
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
     * Obtenir l'image Open Graph par défaut pour une page
     */
    public static function getDefaultOgImage($pageName)
    {
        $defaultImages = [
            'home' => 'images/og-accueil.jpg',
            'services' => 'images/og-services.jpg',
            'portfolio' => 'images/og-realisations.jpg',
            'blog' => 'images/og-blog.jpg',
            'ads' => 'images/og-services.jpg',
            'reviews' => 'images/og-avis-clients.jpg',
            'contact' => 'images/og-services.jpg',
            'about' => 'images/og-accueil.jpg',
        ];
        
        return $defaultImages[$pageName] ?? 'images/og-default.jpg';
    }
}
