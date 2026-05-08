<?php

namespace App\Helpers;

/**
 * Helper pour compresser et redimensionner les images à l'upload.
 * Utilise la librairie GD (disponible sur toutes les installations PHP modernes).
 * Réduit massivement la taille des images sans perte de qualité visible.
 */
class ImageHelper
{
    /**
     * Compresse une image uploadée, la redimensionne si nécessaire, et la sauvegarde.
     *
     * @param string $sourcePath  Chemin temporaire du fichier uploadé
     * @param string $destPath    Chemin de destination final
     * @param int    $maxWidth    Largeur max en pixels (défaut 1200)
     * @param int    $maxHeight   Hauteur max en pixels (défaut 900)
     * @param int    $quality     Qualité JPEG 0-100 (défaut 82)
     * @return string             Chemin du fichier final (peut être .jpg même si original .png)
     */
    public static function compressAndSave(
        string $sourcePath,
        string $destPath,
        int $maxWidth = 1200,
        int $maxHeight = 900,
        int $quality = 82
    ): string {
        // Si GD non disponible, simple copie
        if (!function_exists('imagecreatefromjpeg')) {
            copy($sourcePath, $destPath);
            return $destPath;
        }

        $info = @getimagesize($sourcePath);
        if (!$info) {
            copy($sourcePath, $destPath);
            return $destPath;
        }

        [$origW, $origH, $type] = $info;

        // Chargement selon le type
        $src = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG  => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            IMAGETYPE_GIF  => @imagecreatefromgif($sourcePath),
            default        => false,
        };

        if (!$src) {
            copy($sourcePath, $destPath);
            return $destPath;
        }

        // Calcul des nouvelles dimensions
        [$newW, $newH] = self::fitDimensions($origW, $origH, $maxWidth, $maxHeight);

        // Création de l'image de destination
        $dst = imagecreatetruecolor($newW, $newH);

        // Fond blanc (pour les PNG avec transparence → JPEG)
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Toujours sauvegarder en JPEG (sauf PNG avec transparence connue)
        $finalPath = preg_replace('/\.(png|gif|webp|bmp)$/i', '.jpg', $destPath);
        imagejpeg($dst, $finalPath, $quality);

        imagedestroy($src);
        imagedestroy($dst);

        // Supprimer la source temporaire si différente de la destination
        if ($sourcePath !== $finalPath && file_exists($sourcePath) && $sourcePath !== $destPath) {
            @unlink($sourcePath);
        }

        return $finalPath;
    }

    /**
     * Calcule les nouvelles dimensions en respectant le ratio.
     */
    private static function fitDimensions(int $w, int $h, int $maxW, int $maxH): array
    {
        if ($w <= $maxW && $h <= $maxH) {
            return [$w, $h];
        }
        $ratio = min($maxW / $w, $maxH / $h);
        return [(int) round($w * $ratio), (int) round($h * $ratio)];
    }

    /**
     * Raccourci : compresse une image uploadée (UploadedFile Laravel) dans un dossier.
     * Retourne le chemin relatif depuis public/.
     */
    public static function uploadAndCompress(
        \Illuminate\Http\UploadedFile $file,
        string $destDir,
        string $prefix = 'img',
        int $maxWidth = 1200,
        int $maxHeight = 900,
        int $quality = 82
    ): string {
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Toujours sauvegarder en .jpg après compression
        $fileName = $prefix . '_' . time() . '_' . uniqid() . '.jpg';
        $tmpName  = 'tmp_' . $fileName;

        // Déplacer le fichier temporairement
        $file->move($destDir, $tmpName);
        $tmpPath = $destDir . '/' . $tmpName;

        // Compresser vers le chemin final
        $finalPath = $destDir . '/' . $fileName;
        $actualFinal = self::compressAndSave($tmpPath, $finalPath, $maxWidth, $maxHeight, $quality);

        // Supprimer le tmp si différent
        if (file_exists($tmpPath) && $tmpPath !== $actualFinal) {
            @unlink($tmpPath);
        }

        // Retourner le chemin relatif depuis public/
        return ltrim(str_replace(public_path(), '', $actualFinal), '/\\');
    }
}
