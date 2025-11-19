<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Ad;
use Illuminate\Support\Facades\Log;

/**
 * Validateur de qualité de contenu
 * Empêche l'indexation de pages de faible qualité
 */
class ContentQualityValidator
{
    /**
     * Valider la qualité d'un article ou d'une annonce
     * Retourne un score de 0 à 100 et des recommandations
     */
    public function validate($content, $metadata = [])
    {
        $score = 100;
        $issues = [];
        $recommendations = [];
        
        // 1. Longueur du contenu (CRITIQUE)
        $wordCount = str_word_count(strip_tags($content));
        
        if ($wordCount < 300) {
            $score -= 50;
            $issues[] = "Contenu trop court ({$wordCount} mots)";
            $recommendations[] = "Ajouter au minimum 300 mots de contenu unique";
        } elseif ($wordCount < 500) {
            $score -= 30;
            $issues[] = "Contenu insuffisant ({$wordCount} mots)";
            $recommendations[] = "Viser au moins 800-1000 mots pour un bon référencement";
        } elseif ($wordCount < 800) {
            $score -= 15;
            $issues[] = "Contenu court ({$wordCount} mots)";
            $recommendations[] = "Enrichir le contenu pour atteindre 1000+ mots";
        }
        
        // 2. Structure HTML (H1, H2, H3)
        $h1Count = substr_count(strtolower($content), '<h1');
        $h2Count = substr_count(strtolower($content), '<h2');
        $h3Count = substr_count(strtolower($content), '<h3');
        
        if ($h1Count === 0) {
            $score -= 20;
            $issues[] = "Aucun titre H1";
            $recommendations[] = "Ajouter un titre H1 unique";
        } elseif ($h1Count > 1) {
            $score -= 10;
            $issues[] = "Plusieurs titres H1 détectés";
            $recommendations[] = "Utiliser un seul H1 par page";
        }
        
        if ($h2Count === 0) {
            $score -= 15;
            $issues[] = "Aucun sous-titre H2";
            $recommendations[] = "Structurer le contenu avec des H2";
        }
        
        // 3. Paragraphes et lisibilité
        $pCount = substr_count(strtolower($content), '<p');
        
        if ($pCount < 3) {
            $score -= 15;
            $issues[] = "Trop peu de paragraphes";
            $recommendations[] = "Structurer le contenu en plusieurs paragraphes";
        }
        
        // 4. Liens internes
        $internalLinksCount = substr_count($content, '<a ');
        
        if ($internalLinksCount === 0) {
            $score -= 10;
            $issues[] = "Aucun lien interne";
            $recommendations[] = "Ajouter 2-3 liens internes pertinents";
        }
        
        // 5. Contenu dupliqué (patterns suspects)
        if ($this->hasDuplicatePatterns($content)) {
            $score -= 20;
            $issues[] = "Contenu possiblement dupliqué détecté";
            $recommendations[] = "Vérifier l'unicité du contenu";
        }
        
        // 6. Meta title et description
        if (isset($metadata['meta_title'])) {
            $titleLength = strlen($metadata['meta_title']);
            if ($titleLength < 30) {
                $score -= 10;
                $issues[] = "Meta title trop court ({$titleLength} caractères)";
                $recommendations[] = "Viser 50-60 caractères pour le meta title";
            } elseif ($titleLength > 70) {
                $score -= 5;
                $issues[] = "Meta title trop long ({$titleLength} caractères)";
                $recommendations[] = "Réduire le meta title à 50-60 caractères";
            }
        }
        
        if (isset($metadata['meta_description'])) {
            $descLength = strlen($metadata['meta_description']);
            if ($descLength < 100) {
                $score -= 10;
                $issues[] = "Meta description trop courte ({$descLength} caractères)";
                $recommendations[] = "Viser 150-160 caractères pour la meta description";
            } elseif ($descLength > 170) {
                $score -= 5;
                $issues[] = "Meta description trop longue ({$descLength} caractères)";
                $recommendations[] = "Réduire la meta description à 150-160 caractères";
            }
        }
        
        // 7. Images
        $imgCount = substr_count(strtolower($content), '<img');
        
        if ($imgCount === 0) {
            $score -= 5;
            $recommendations[] = "Ajouter au moins une image pertinente";
        }
        
        // Déterminer le grade
        $grade = $this->getGrade($score);
        
        // Déterminer si le contenu est indexable
        $isIndexable = $score >= 60; // Minimum 60/100 pour être indexé
        
        return [
            'score' => max(0, $score),
            'grade' => $grade,
            'is_indexable' => $isIndexable,
            'word_count' => $wordCount,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'metrics' => [
                'word_count' => $wordCount,
                'h1_count' => $h1Count,
                'h2_count' => $h2Count,
                'h3_count' => $h3Count,
                'paragraph_count' => $pCount,
                'internal_links' => $internalLinksCount,
                'images' => $imgCount,
                'meta_title_length' => isset($metadata['meta_title']) ? strlen($metadata['meta_title']) : 0,
                'meta_description_length' => isset($metadata['meta_description']) ? strlen($metadata['meta_description']) : 0,
            ]
        ];
    }
    
    /**
     * Détecter des patterns de contenu dupliqué
     */
    protected function hasDuplicatePatterns($content)
    {
        // Patterns suspects de contenu générique/dupliqué
        $suspectPatterns = [
            'Lorem ipsum',
            'VILLE',
            'RÉGION',
            'DÉPARTEMENT',
            'FORM_URL',
            '[PLACEHOLDER]',
            'xxx',
            'TODO',
            'À COMPLÉTER'
        ];
        
        foreach ($suspectPatterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }
        
        // Détecter les phrases répétées (indice de template mal rempli)
        $sentences = preg_split('/[.!?]+/', strip_tags($content));
        $sentences = array_filter($sentences, function($s) {
            return strlen(trim($s)) > 20;
        });
        
        if (count($sentences) !== count(array_unique($sentences))) {
            return true; // Phrases dupliquées détectées
        }
        
        return false;
    }
    
    /**
     * Obtenir le grade textuel
     */
    protected function getGrade($score)
    {
        if ($score >= 90) return 'A+ (Excellent)';
        if ($score >= 80) return 'A (Très bon)';
        if ($score >= 70) return 'B (Bon)';
        if ($score >= 60) return 'C (Acceptable)';
        if ($score >= 50) return 'D (Insuffisant)';
        return 'F (Mauvais)';
    }
    
    /**
     * Valider un article avant indexation
     */
    public function validateArticle(Article $article)
    {
        $metadata = [
            'meta_title' => $article->meta_title ?? $article->title,
            'meta_description' => $article->meta_description,
            'meta_keywords' => $article->meta_keywords,
        ];
        
        $validation = $this->validate($article->content_html, $metadata);
        
        // Enregistrer le score dans l'article (si colonne existe)
        try {
            if (\Schema::hasColumn('articles', 'quality_score')) {
                $article->update(['quality_score' => $validation['score']]);
            }
        } catch (\Exception $e) {
            // Ignorer si la colonne n'existe pas
        }
        
        return $validation;
    }
    
    /**
     * Valider une annonce avant indexation
     */
    public function validateAd(Ad $ad)
    {
        $metadata = [
            'meta_title' => $ad->meta_title ?? $ad->title,
            'meta_description' => $ad->meta_description,
            'meta_keywords' => $ad->meta_keywords,
        ];
        
        $validation = $this->validate($ad->content_html, $metadata);
        
        // Enregistrer le score dans l'annonce (si colonne existe)
        try {
            if (\Schema::hasColumn('ads', 'quality_score')) {
                $ad->update(['quality_score' => $validation['score']]);
            }
        } catch (\Exception $e) {
            // Ignorer si la colonne n'existe pas
        }
        
        return $validation;
    }
    
    /**
     * Obtenir les pages de faible qualité qui ne devraient pas être indexées
     */
    public function getLowQualityPages()
    {
        $lowQualityPages = [];
        
        // Articles de faible qualité
        $articles = Article::where('status', 'published')->get();
        foreach ($articles as $article) {
            $validation = $this->validateArticle($article);
            if (!$validation['is_indexable']) {
                $lowQualityPages[] = [
                    'type' => 'article',
                    'id' => $article->id,
                    'title' => $article->title,
                    'url' => route('blog.show', $article),
                    'score' => $validation['score'],
                    'issues' => $validation['issues']
                ];
            }
        }
        
        // Annonces de faible qualité
        $ads = Ad::where('status', 'published')->limit(100)->get(); // Limiter pour performance
        foreach ($ads as $ad) {
            $validation = $this->validateAd($ad);
            if (!$validation['is_indexable']) {
                $lowQualityPages[] = [
                    'type' => 'ad',
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'url' => route('ads.show', $ad),
                    'score' => $validation['score'],
                    'issues' => $validation['issues']
                ];
            }
        }
        
        return $lowQualityPages;
    }
}

