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
        try {
            foreach ($defaults as $key => $default) {
                $seo[$key] = Setting::get("seo_page_{$pageName}_{$key}", $fallback[$key] ?? $default);
            }
        } catch (\Exception $e) {
            // Si la base de données n'est pas accessible, utiliser les fallbacks
            \Log::warning("Erreur lors de la récupération des SEO pour la page '{$pageName}': " . $e->getMessage());
            foreach ($defaults as $key => $default) {
                $seo[$key] = $fallback[$key] ?? $default;
            }
        }
        
        return $seo;
    }
    
    /**
     * Générer les balises meta pour une page avec garantie de valeurs non vides
     */
    public static function generateMetaTags($pageName, $customData = [])
    {
        try {
            $seo = self::getPageSeo($pageName, $customData);
        } catch (\Exception $e) {
            \Log::warning("Erreur lors de getPageSeo pour '{$pageName}': " . $e->getMessage());
            $seo = [];
        }
        
        // Fallbacks par défaut robustes - avec protection try-catch
        try {
            $companyName = setting('company_name', 'Votre Entreprise');
            $companySpecialization = setting('company_specialization', 'Travaux de Rénovation');
            $companyDescription = setting('company_description', '');
            $companyCity = setting('company_city', '');
            $companyRegion = setting('company_region', '');
        } catch (\Exception $e) {
            \Log::warning("Erreur lors de la récupération des settings dans generateMetaTags: " . $e->getMessage());
            $companyName = 'Votre Entreprise';
            $companySpecialization = 'Travaux de Rénovation';
            $companyDescription = '';
            $companyCity = '';
            $companyRegion = '';
        }
        
        // Construire le titre par défaut selon le type de page
        $defaultTitle = self::getDefaultTitleForPage($pageName, $companyName, $companySpecialization, $companyCity);
        
        // Construire la description par défaut
        $defaultDescription = self::getDefaultDescriptionForPage($pageName, $companyDescription, $companySpecialization, $companyCity, $companyRegion);
        
        $defaultImage = self::getDefaultImage();
        
        // Titre final - GARANTIR qu'il n'est jamais vide
        $finalTitle = trim($seo['meta_title'] ?? '') 
                   ?: trim($customData['title'] ?? '') 
                   ?: $defaultTitle;
        
        // Description finale - GARANTIR qu'elle n'est jamais vide
        $finalDescription = trim($seo['meta_description'] ?? '') 
                         ?: trim($customData['description'] ?? '') 
                         ?: $defaultDescription;
        
        // NE PAS tronquer les titres et descriptions - les afficher en entier
        // Les titres et descriptions générés par GPT sont déjà optimisés
        // Google peut afficher jusqu'à 60 caractères pour les titres (mais accepte plus)
        // et jusqu'à 320 caractères pour les descriptions (anciennement 160)
        // On laisse Google gérer l'affichage, on ne tronque pas côté serveur
        
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
        
        // GARANTIR que l'image n'est jamais vide
        if (empty($finalImage)) {
            $finalImage = $defaultImage;
        }
        
        // URL canonique
        $canonicalUrl = request()->url();
        
        $meta = [
            'title' => $finalTitle,
            'description' => $finalDescription,
            'og:title' => trim($seo['og_title'] ?? '') ?: $finalTitle,
            'og:description' => trim($seo['og_description'] ?? '') ?: $finalDescription,
            'og:image' => $finalImage,
            'og:url' => $canonicalUrl,
            'og:type' => $customData['type'] ?? 'website',
            'og:site_name' => $companyName,
            'twitter:title' => trim($seo['twitter_title'] ?? '') ?: trim($seo['og_title'] ?? '') ?: $finalTitle,
            'twitter:description' => trim($seo['twitter_description'] ?? '') ?: trim($seo['og_description'] ?? '') ?: $finalDescription,
            'twitter:image' => $finalImage,
            'canonical' => $canonicalUrl,
        ];
        
        // Validation finale - s'assurer qu'aucune valeur n'est vide
        $meta['title'] = $meta['title'] ?: $defaultTitle;
        $meta['description'] = $meta['description'] ?: $defaultDescription;
        $meta['og:title'] = $meta['og:title'] ?: $meta['title'];
        $meta['og:description'] = $meta['og:description'] ?: $meta['description'];
        $meta['twitter:title'] = $meta['twitter:title'] ?: $meta['og:title'];
        $meta['twitter:description'] = $meta['twitter:description'] ?: $meta['og:description'];
        $meta['og:image'] = $meta['og:image'] ?: $defaultImage;
        $meta['twitter:image'] = $meta['twitter:image'] ?: $defaultImage;
        
        return $meta;
    }
    
    /**
     * Obtenir le titre par défaut selon le type de page
     */
    private static function getDefaultTitleForPage($pageName, $companyName, $companySpecialization, $companyCity = '')
    {
        $titles = [
            'home' => $companyName . ' - ' . $companySpecialization,
            'services' => 'Nos Services - ' . $companyName,
            'blog' => 'Blog - ' . $companyName,
            'articles' => 'Blog - ' . $companyName,
            'portfolio' => 'Nos Réalisations - ' . $companyName,
            'ads' => 'Nos Services par Ville - ' . $companyName,
            'reviews' => 'Avis Clients - ' . $companyName,
            'contact' => 'Contact - ' . $companyName,
            'about' => 'À Propos - ' . $companyName,
        ];
        
        $title = $titles[$pageName] ?? ($companyName . ' - ' . $companySpecialization);
        
        if (!empty($companyCity)) {
            $title .= ' à ' . $companyCity;
        }
        
        return $title;
    }
    
    /**
     * Obtenir la description par défaut selon le type de page
     */
    private static function getDefaultDescriptionForPage($pageName, $companyDescription, $companySpecialization, $companyCity = '', $companyRegion = '')
    {
        $location = '';
        if (!empty($companyCity) && !empty($companyRegion)) {
            $location = ' à ' . $companyCity . ', ' . $companyRegion;
        } elseif (!empty($companyCity)) {
            $location = ' à ' . $companyCity;
        } elseif (!empty($companyRegion)) {
            $location = ' en ' . $companyRegion;
        }
        
        $descriptions = [
            'home' => $companyDescription ?: ('Expert en ' . $companySpecialization . $location . '. Devis gratuit, intervention rapide, qualité garantie.'),
            'services' => 'Découvrez tous nos services de ' . $companySpecialization . $location . '. Solutions complètes et professionnelles pour tous vos projets.',
            'blog' => 'Découvrez nos articles et conseils sur la rénovation et les travaux' . $location . '. Guides pratiques et actualités.',
            'articles' => 'Découvrez nos articles et conseils sur la rénovation et les travaux' . $location . '. Guides pratiques et actualités.',
            'portfolio' => 'Découvrez quelques-unes de nos réalisations récentes' . $location . '. Laissez-vous inspirer pour votre prochain projet.',
            'ads' => 'Découvrez nos services par ville' . $location . '. Solutions professionnelles de couverture et rénovation dans toute la région.',
            'reviews' => 'Découvrez les avis de nos clients satisfaits' . $location . '. Témoignages et retours d\'expérience sur nos services.',
            'contact' => 'Contactez-nous pour un devis gratuit' . $location . '. Intervention rapide, qualité garantie.',
            'about' => 'Découvrez notre entreprise spécialisée en ' . $companySpecialization . $location . '. Expertise et professionnalisme à votre service.',
        ];
        
        return $descriptions[$pageName] ?? ($companyDescription ?: ('Expert en ' . $companySpecialization . $location . '. Devis gratuit, intervention rapide, qualité garantie.'));
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
        try {
            $companyLogo = setting('company_logo');
            if ($companyLogo && file_exists(public_path($companyLogo))) {
                return url($companyLogo);
            }
        } catch (\Exception $e) {
            // Ignorer l'erreur et continuer avec les logos par défaut
        }
        
        // Vérifier si le logo existe dans différents emplacements
        $possibleLogos = [
            'logo/logo.png',
            'logo.png',
            'images/logo.png',
            'uploads/logo.png',
        ];
        
        foreach ($possibleLogos as $logoPath) {
            if (file_exists(public_path($logoPath))) {
                return url($logoPath);
            }
        }
        
        // Fallback: utiliser une image par défaut ou générer une URL
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
            'services' => 'images/og-services.jpg',
            'portfolio' => 'images/og-realisations.jpg',
            'blog' => setting('default_blog_og_image', 'images/og-blog.jpg'),
            'articles' => setting('default_blog_og_image', 'images/og-blog.jpg'),
            'ads' => 'images/og-services.jpg',
            'reviews' => 'images/og-avis-clients.jpg',
            'contact' => 'images/og-accueil.jpg',
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
