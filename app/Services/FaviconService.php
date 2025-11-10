<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class FaviconService
{
    /**
     * Génère toutes les tailles de favicon à partir d'une image source
     */
    public function generateFavicons($sourcePath, $outputDir = null)
    {
        if (!$outputDir) {
            $outputDir = public_path('favicons');
        }
        
        // Créer le dossier si nécessaire
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }
        
        $results = [
            'success' => false,
            'files' => [],
            'errors' => []
        ];
        
        try {
            // Tailles requises pour les favicons
            // Google recommande minimum 48x48px, optimal 192x192px pour apparaître dans les résultats
            $sizes = [
                '16x16' => 16,
                '32x32' => 32,
                '48x48' => 48,   // Minimum requis par Google
                '96x96' => 96,   // Recommandé par Google
                '180x180' => 180, // Apple Touch Icon
                '192x192' => 192, // Optimal pour Google Search Results (recommandé)
                '512x512' => 512  // Manifest PWA
            ];
            
            // Vérifier si GD est disponible
            if (!extension_loaded('gd')) {
                $results['errors'][] = 'Extension GD non disponible. Impossible de redimensionner les images.';
                return $results;
            }
            
            // Charger l'image source
            $sourceInfo = getimagesize($sourcePath);
            if (!$sourceInfo) {
                $results['errors'][] = 'Impossible de lire l\'image source.';
                return $results;
            }
            
            $sourceWidth = $sourceInfo[0];
            $sourceHeight = $sourceInfo[1];
            $sourceType = $sourceInfo[2];
            
            // Créer l'image source selon le type
            switch ($sourceType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                default:
                    $results['errors'][] = 'Format d\'image non supporté. Utilisez JPEG, PNG ou GIF.';
                    return $results;
            }
            
            if (!$sourceImage) {
                $results['errors'][] = 'Impossible de créer l\'image source.';
                return $results;
            }
            
            // Générer chaque taille
            foreach ($sizes as $sizeName => $size) {
                $outputPath = $outputDir . '/favicon-' . $sizeName . '.png';
                
                // Créer une nouvelle image avec la taille souhaitée
                $newImage = imagecreatetruecolor($size, $size);
                
                // Préserver la transparence pour PNG
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                imagefill($newImage, 0, 0, $transparent);
                
                // Redimensionner l'image
                imagecopyresampled(
                    $newImage, $sourceImage,
                    0, 0, 0, 0,
                    $size, $size,
                    $sourceWidth, $sourceHeight
                );
                
                // Sauvegarder l'image
                if (imagepng($newImage, $outputPath)) {
                    $results['files'][] = [
                        'size' => $sizeName,
                        'path' => 'favicons/favicon-' . $sizeName . '.png',
                        'full_path' => $outputPath
                    ];
                } else {
                    $results['errors'][] = "Impossible de créer l'image {$sizeName}.";
                }
                
                imagedestroy($newImage);
            }
            
            // Créer le favicon.ico (multi-icône) avec les tailles 16, 32, 48
            $icoPath = $this->createIcoFile($outputDir, $sourceImage, $sourceWidth, $sourceHeight);
            if ($icoPath) {
                $results['files'][] = [
                    'size' => 'ico',
                    'path' => 'favicon.ico',
                    'full_path' => $icoPath
                ];
            }
            
            // Créer apple-touch-icon.png (180x180)
            $appleIconPath = $outputDir . '/apple-touch-icon.png';
            $appleImage = imagecreatetruecolor(180, 180);
            imagealphablending($appleImage, false);
            imagesavealpha($appleImage, true);
            $transparent = imagecolorallocatealpha($appleImage, 0, 0, 0, 127);
            imagefill($appleImage, 0, 0, $transparent);
            imagecopyresampled(
                $appleImage, $sourceImage,
                0, 0, 0, 0,
                180, 180,
                $sourceWidth, $sourceHeight
            );
            if (imagepng($appleImage, $appleIconPath)) {
                $results['files'][] = [
                    'size' => 'apple-touch-icon',
                    'path' => 'favicons/apple-touch-icon.png',
                    'full_path' => $appleIconPath
                ];
            }
            imagedestroy($appleImage);
            
            imagedestroy($sourceImage);
            
            $results['success'] = true;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération favicons: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Crée un fichier ICO simple (copie du PNG 32x32)
     * Note: Pour un vrai ICO multi-icône, il faudrait une bibliothèque spécialisée
     */
    private function createIcoFile($outputDir, $sourceImage, $sourceWidth, $sourceHeight)
    {
        $icoPath = public_path('favicon.ico');
        
        // Créer une image 32x32 pour l'ICO
        $icoImage = imagecreatetruecolor(32, 32);
        imagealphablending($icoImage, false);
        imagesavealpha($icoImage, true);
        $transparent = imagecolorallocatealpha($icoImage, 0, 0, 0, 127);
        imagefill($icoImage, 0, 0, $transparent);
        imagecopyresampled(
            $icoImage, $sourceImage,
            0, 0, 0, 0,
            32, 32,
            $sourceWidth, $sourceHeight
        );
        
        // Sauvegarder comme PNG (les navigateurs modernes acceptent PNG comme ICO)
        if (imagepng($icoImage, $icoPath)) {
            imagedestroy($icoImage);
            return $icoPath;
        }
        
        imagedestroy($icoImage);
        return false;
    }
    
    /**
     * Génère le manifest.json avec toutes les icônes
     */
    public function generateManifestIcons($baseUrl = null)
    {
        if (!$baseUrl) {
            $baseUrl = url('/');
        }
        
        $icons = [];
        $sizes = ['192x192', '512x512'];
        
        foreach ($sizes as $size) {
            $iconPath = public_path("favicons/favicon-{$size}.png");
            if (file_exists($iconPath)) {
                $icons[] = [
                    'src' => $baseUrl . "/favicons/favicon-{$size}.png",
                    'sizes' => $size,
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ];
            }
        }
        
        return $icons;
    }
}

