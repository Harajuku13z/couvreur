<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SeoValidationService
{
    /**
     * Valider le favicon selon les critères Google
     */
    public function validateFavicon()
    {
        $results = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'info' => []
        ];
        
        // Récupérer le favicon
        $faviconPath = Setting::get('site_favicon');
        $seoConfig = Setting::get('seo_config', []);
        $seoConfig = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
        
        if (!$faviconPath && !empty($seoConfig['favicon'])) {
            $faviconPath = $seoConfig['favicon'];
        }
        
        if (empty($faviconPath)) {
            $results['errors'][] = 'Aucun favicon configuré';
            return $results;
        }
        
        $fullPath = public_path($faviconPath);
        
        if (!file_exists($fullPath)) {
            $results['errors'][] = "Le fichier favicon n'existe pas : {$faviconPath}";
            return $results;
        }
        
        // Vérifier la taille
        $imageInfo = @getimagesize($fullPath);
        if ($imageInfo) {
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            if ($width < 48 || $height < 48) {
                $results['errors'][] = "Le favicon est trop petit : {$width}x{$height}px (minimum 48x48px requis)";
            } elseif ($width > 512 || $height > 512) {
                $results['warnings'][] = "Le favicon est très grand : {$width}x{$height}px (recommandé max 512x512px)";
            } else {
                $results['info'][] = "Taille du favicon : {$width}x{$height}px ✓";
            }
        }
        
        // Vérifier le format
        $extension = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
        $allowedFormats = ['png', 'svg', 'ico', 'gif', 'jpg', 'jpeg'];
        if (!in_array($extension, $allowedFormats)) {
            $results['errors'][] = "Format non supporté : {$extension} (formats acceptés : PNG, SVG, ICO, GIF, JPG)";
        } else {
            $results['info'][] = "Format du favicon : " . strtoupper($extension) . " ✓";
        }
        
        // Vérifier l'accessibilité HTTPS
        $faviconUrl = asset($faviconPath);
        if (strpos($faviconUrl, 'https://') === false) {
            $results['warnings'][] = "Le favicon doit être accessible en HTTPS";
        } else {
            $results['info'][] = "Favicon accessible en HTTPS ✓";
        }
        
        // Vérifier que le fichier n'est pas bloqué par robots.txt
        $robotsPath = public_path('robots.txt');
        if (file_exists($robotsPath)) {
            $robotsContent = file_get_contents($robotsPath);
            if (strpos($robotsContent, 'Disallow: /' . $faviconPath) !== false) {
                $results['errors'][] = "Le favicon est bloqué par robots.txt";
            } else {
                $results['info'][] = "Favicon non bloqué par robots.txt ✓";
            }
        }
        
        $results['valid'] = empty($results['errors']);
        $results['favicon_url'] = $faviconUrl;
        $results['favicon_path'] = $faviconPath;
        
        return $results;
    }
    
    /**
     * Valider l'image Open Graph selon les critères Google
     */
    public function validateOgImage($imageUrl = null)
    {
        $results = [
            'valid' => false,
            'errors' => [],
            'warnings' => [],
            'info' => []
        ];
        
        if (empty($imageUrl)) {
            $seoConfig = Setting::get('seo_config', []);
            $seoConfig = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
            $imagePath = $seoConfig['og_image'] ?? null;
            
            if ($imagePath) {
                $imageUrl = asset($imagePath);
            } else {
                $companyLogo = Setting::get('company_logo');
                if ($companyLogo) {
                    $imageUrl = asset($companyLogo);
                }
            }
        }
        
        if (empty($imageUrl)) {
            $results['errors'][] = 'Aucune image Open Graph configurée';
            return $results;
        }
        
        // Vérifier que c'est une URL complète
        if (strpos($imageUrl, 'http') !== 0) {
            $results['errors'][] = "L'URL de l'image n'est pas complète : {$imageUrl}";
            return $results;
        }
        
        // Vérifier HTTPS
        if (strpos($imageUrl, 'https://') === false) {
            $results['errors'][] = "L'image doit être accessible en HTTPS";
        } else {
            $results['info'][] = "Image accessible en HTTPS ✓";
        }
        
        // Essayer de récupérer les dimensions de l'image
        try {
            $response = Http::timeout(5)->head($imageUrl);
            if ($response->successful()) {
                $results['info'][] = "Image accessible (HTTP {$response->status()}) ✓";
                
                // Essayer de récupérer les dimensions
                $imageInfo = @getimagesize($imageUrl);
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                    
                    if ($width < 1200 || $height < 630) {
                        $results['errors'][] = "L'image est trop petite : {$width}x{$height}px (minimum 1200x630px requis)";
                    } else {
                        $ratio = $width / $height;
                        $recommendedRatio = 1200 / 630; // ~1.91:1
                        
                        if (abs($ratio - $recommendedRatio) > 0.2) {
                            $results['warnings'][] = "Ratio de l'image : " . round($ratio, 2) . ":1 (recommandé ~1.91:1)";
                        } else {
                            $results['info'][] = "Taille de l'image : {$width}x{$height}px (ratio optimal) ✓";
                        }
                    }
                }
            } else {
                $results['errors'][] = "L'image n'est pas accessible (HTTP {$response->status()})";
            }
        } catch (\Exception $e) {
            $results['warnings'][] = "Impossible de vérifier l'accessibilité de l'image : " . $e->getMessage();
        }
        
        $results['valid'] = empty($results['errors']);
        $results['image_url'] = $imageUrl;
        
        return $results;
    }
    
    /**
     * Valider toutes les balises meta pour Google
     */
    public function validateMetaTags($pageUrl = null)
    {
        $results = [
            'favicon' => $this->validateFavicon(),
            'og_image' => $this->validateOgImage(),
            'meta_tags' => []
        ];
        
        // Vérifier les balises meta essentielles
        $seoConfig = Setting::get('seo_config', []);
        $seoConfig = is_string($seoConfig) ? json_decode($seoConfig, true) : ($seoConfig ?? []);
        
        $requiredTags = [
            'og:title' => $seoConfig['og_title'] ?? $seoConfig['meta_title'] ?? '',
            'og:description' => $seoConfig['og_description'] ?? $seoConfig['meta_description'] ?? '',
            'og:image' => $seoConfig['og_image'] ?? '',
            'og:type' => 'website',
            'og:url' => $pageUrl ?? url('/'),
        ];
        
        foreach ($requiredTags as $tag => $value) {
            $results['meta_tags'][$tag] = [
                'present' => !empty($value),
                'value' => $value
            ];
        }
        
        return $results;
    }
}

