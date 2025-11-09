<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class PortfolioImageService
{
    /**
     * Récupérer les images de réalisations selon le mot-clé/service
     * 
     * @param string $keyword Mot-clé pour trouver le service correspondant
     * @param int $limit Nombre maximum d'images à retourner
     * @return array Liste d'URLs d'images
     */
    public function getImagesByKeyword(string $keyword, int $limit = 5): array
    {
        try {
            // Mapper les mots-clés aux types de travaux
            $keywordLower = strtolower($keyword);
            $workType = null;
            
            // Détecter le type de travaux selon le mot-clé
            if (strpos($keywordLower, 'toiture') !== false || 
                strpos($keywordLower, 'toit') !== false || 
                strpos($keywordLower, 'couverture') !== false ||
                strpos($keywordLower, 'charpente') !== false ||
                strpos($keywordLower, 'tuile') !== false ||
                strpos($keywordLower, 'ardoise') !== false) {
                $workType = 'roof';
            } elseif (strpos($keywordLower, 'façade') !== false || 
                      strpos($keywordLower, 'facade') !== false ||
                      strpos($keywordLower, 'enduit') !== false ||
                      strpos($keywordLower, 'ravalement') !== false) {
                $workType = 'facade';
            } elseif (strpos($keywordLower, 'isolation') !== false || 
                      strpos($keywordLower, 'isolant') !== false ||
                      strpos($keywordLower, 'thermique') !== false ||
                      strpos($keywordLower, 'phonique') !== false) {
                $workType = 'isolation';
            }
            
            // Récupérer les réalisations
            $portfolioData = Setting::get('portfolio_items', '[]');
            $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
            
            if (!is_array($portfolioItems) || empty($portfolioItems)) {
                return [];
            }
            
            $images = [];
            
            // Filtrer par type de travaux si détecté
            if ($workType) {
                foreach ($portfolioItems as $item) {
                    if (isset($item['work_type']) && $item['work_type'] === $workType) {
                        if (isset($item['images']) && is_array($item['images']) && !empty($item['images'])) {
                            foreach ($item['images'] as $image) {
                                if (count($images) >= $limit) {
                                    break 2;
                                }
                                if (!empty($image)) {
                                    // Utiliser asset() pour générer l'URL correcte
                                    $imageUrl = str_starts_with($image, 'http') ? $image : asset($image);
                                    $images[] = [
                                        'url' => $imageUrl,
                                        'title' => $item['title'] ?? 'Réalisation',
                                        'work_type' => $workType
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            
            // Si pas assez d'images avec le type spécifique, prendre des images mixtes ou autres
            if (count($images) < $limit) {
                foreach ($portfolioItems as $item) {
                    if (count($images) >= $limit) {
                        break;
                    }
                    
                    // Ignorer si déjà pris
                    $itemWorkType = $item['work_type'] ?? 'mixed';
                    if ($workType && $itemWorkType === $workType) {
                        continue;
                    }
                    
                    // Priorité aux travaux mixtes si pas de type spécifique
                    if (!$workType || $itemWorkType === 'mixed') {
                        if (isset($item['images']) && is_array($item['images']) && !empty($item['images'])) {
                            foreach ($item['images'] as $image) {
                                if (count($images) >= $limit) {
                                    break 2;
                                }
                                if (!empty($image)) {
                                    // Utiliser asset() pour générer l'URL correcte
                                    $imageUrl = str_starts_with($image, 'http') ? $image : asset($image);
                                    $images[] = [
                                        'url' => $imageUrl,
                                        'title' => $item['title'] ?? 'Réalisation',
                                        'work_type' => $itemWorkType
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            
            return array_slice($images, 0, $limit);
            
        } catch (\Exception $e) {
            Log::error('Erreur récupération images portfolio', [
                'keyword' => $keyword,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}

