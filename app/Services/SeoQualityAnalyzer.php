<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Str;

class SeoQualityAnalyzer
{
    /**
     * Analyser la qualité SEO d'un article
     */
    public function analyze(Article $article): array
    {
        $score = 0;
        $maxScore = 100;
        $issues = [];
        $strengths = [];
        
        // 1. Titre (10 points)
        $title = $article->title ?? '';
        if (!empty($title)) {
            $titleLength = strlen($title);
            if ($titleLength >= 30 && $titleLength <= 65) {
                $score += 10;
                $strengths[] = 'Titre optimisé (30-65 caractères)';
            } elseif ($titleLength < 30) {
                $score += 5;
                $issues[] = 'Titre trop court (< 30 caractères)';
            } else {
                $score += 5;
                $issues[] = 'Titre trop long (> 65 caractères, risque de troncature)';
            }
        } else {
            $issues[] = 'Titre manquant';
        }
        
        // 2. Meta description (10 points)
        $metaDesc = $article->meta_description ?? '';
        if (!empty($metaDesc)) {
            $metaLength = strlen($metaDesc);
            if ($metaLength >= 120 && $metaLength <= 160) {
                $score += 10;
                $strengths[] = 'Meta description optimisée (120-160 caractères)';
            } elseif ($metaLength < 120) {
                $score += 5;
                $issues[] = 'Meta description trop courte (< 120 caractères)';
            } else {
                $score += 5;
                $issues[] = 'Meta description trop longue (> 160 caractères)';
            }
        } else {
            $issues[] = 'Meta description manquante';
        }
        
        // 3. Mots-clés (10 points)
        $keywords = $article->meta_keywords ?? '';
        if (!empty($keywords)) {
            $keywordCount = count(explode(',', $keywords));
            if ($keywordCount >= 3 && $keywordCount <= 5) {
                $score += 10;
                $strengths[] = 'Mots-clés optimisés (3-5 mots-clés)';
            } else {
                $score += 5;
                $issues[] = 'Nombre de mots-clés non optimal';
            }
        } else {
            $issues[] = 'Mots-clés manquants';
        }
        
        // 4. Contenu HTML (30 points)
        $content = $article->content_html ?? '';
        if (!empty($content)) {
            $textContent = strip_tags($content);
            $wordCount = str_word_count($textContent);
            
            // Longueur du contenu
            if ($wordCount >= 1500 && $wordCount <= 3000) {
                $score += 10;
                $strengths[] = 'Contenu de longueur optimale (' . number_format($wordCount) . ' mots)';
            } elseif ($wordCount < 1000) {
                $score += 3;
                $issues[] = 'Contenu trop court (' . number_format($wordCount) . ' mots, minimum 1500 recommandé)';
            } else {
                $score += 7;
            }
            
            // Structure HTML
            $h2Count = substr_count($content, '<h2');
            $h3Count = substr_count($content, '<h3');
            $pCount = substr_count($content, '<p');
            $ulCount = substr_count($content, '<ul');
            $olCount = substr_count($content, '<ol');
            
            if ($h2Count >= 3) {
                $score += 5;
                $strengths[] = 'Structure bien organisée (' . $h2Count . ' sections H2)';
            } else {
                $score += 2;
                $issues[] = 'Peu de sections principales (H2)';
            }
            
            if ($h3Count >= 2) {
                $score += 3;
                $strengths[] = 'Sous-sections présentes (' . $h3Count . ' sous-sections H3)';
            }
            
            if ($pCount >= 10) {
                $score += 5;
            } else {
                $issues[] = 'Peu de paragraphes';
            }
            
            if ($ulCount > 0 || $olCount > 0) {
                $score += 4;
                $strengths[] = 'Listes présentes (améliore la lisibilité)';
            } else {
                $issues[] = 'Aucune liste (améliore la lisibilité)';
            }
            
            // Densité du mot-clé principal
            $focusKeyword = $article->focus_keyword ?? '';
            if (!empty($focusKeyword)) {
                $keywordLower = strtolower($focusKeyword);
                $textLower = strtolower($textContent);
                $keywordCount = substr_count($textLower, $keywordLower);
                $density = ($keywordCount / max($wordCount, 1)) * 100;
                
                if ($density >= 0.5 && $density <= 2.5) {
                    $score += 3;
                    $strengths[] = 'Densité du mot-clé optimale (' . number_format($density, 2) . '%)';
                } elseif ($density < 0.5) {
                    $issues[] = 'Densité du mot-clé trop faible (' . number_format($density, 2) . '%)';
                } else {
                    $issues[] = 'Densité du mot-clé trop élevée (' . number_format($density, 2) . '%, risque de sur-optimisation)';
                }
            }
        } else {
            $issues[] = 'Contenu manquant';
        }
        
        // 5. Image (10 points)
        if (!empty($article->featured_image)) {
            $score += 10;
            $strengths[] = 'Image à la une présente';
        } else {
            $issues[] = 'Image à la une manquante';
        }
        
        // 6. Slug/URL (10 points)
        $slug = $article->slug ?? '';
        if (!empty($slug)) {
            if (strlen($slug) <= 100 && !preg_match('/[^a-z0-9\-]/', $slug)) {
                $score += 10;
                $strengths[] = 'URL optimisée';
            } else {
                $score += 5;
                $issues[] = 'URL non optimisée';
            }
        }
        
        // 7. Statut de publication (10 points)
        if ($article->status === 'published' && $article->published_at) {
            $score += 10;
            $strengths[] = 'Article publié';
        } else {
            $issues[] = 'Article non publié';
        }
        
        // 8. Ville associée (10 points)
        if ($article->city_id) {
            $score += 10;
            $strengths[] = 'Ville associée (localisation SEO)';
        } else {
            $issues[] = 'Aucune ville associée';
        }
        
        // Calcul du pourcentage
        $percentage = round(($score / $maxScore) * 100);
        
        // Note littérale
        $grade = $this->getGrade($percentage);
        
        return [
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'grade' => $grade,
            'issues' => $issues,
            'strengths' => $strengths,
            'word_count' => $wordCount ?? 0,
            'h2_count' => $h2Count ?? 0,
            'h3_count' => $h3Count ?? 0,
        ];
    }
    
    /**
     * Obtenir une note littérale
     */
    protected function getGrade(int $percentage): string
    {
        if ($percentage >= 90) return 'Excellent';
        if ($percentage >= 75) return 'Très bon';
        if ($percentage >= 60) return 'Bon';
        if ($percentage >= 50) return 'Moyen';
        if ($percentage >= 40) return 'Faible';
        return 'Très faible';
    }
}

