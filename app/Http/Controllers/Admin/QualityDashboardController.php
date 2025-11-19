<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentQualityValidator;
use App\Services\SmartIndexingStrategy;
use App\Models\Article;
use App\Models\Ad;
use Illuminate\Http\Request;

class QualityDashboardController extends Controller
{
    /**
     * Tableau de bord qualité SEO
     */
    public function index()
    {
        $validator = app(ContentQualityValidator::class);
        $indexingStrategy = app(SmartIndexingStrategy::class);
        
        // Obtenir les recommandations
        $recommendations = $indexingStrategy->getIndexingRecommendations();
        
        // Analyser un échantillon de contenu
        $sampleArticles = Article::where('status', 'published')->inRandomOrder()->limit(10)->get();
        $articleQuality = [];
        
        foreach ($sampleArticles as $article) {
            $validation = $validator->validateArticle($article);
            $articleQuality[] = [
                'id' => $article->id,
                'title' => $article->title,
                'score' => $validation['score'],
                'grade' => $validation['grade'],
                'word_count' => $validation['word_count'],
                'is_indexable' => $validation['is_indexable'],
                'issues' => $validation['issues'],
                'url' => route('blog.show', $article)
            ];
        }
        
        $sampleAds = Ad::where('status', 'published')->inRandomOrder()->limit(10)->get();
        $adQuality = [];
        
        foreach ($sampleAds as $ad) {
            $validation = $validator->validateAd($ad);
            $adQuality[] = [
                'id' => $ad->id,
                'title' => $ad->title,
                'score' => $validation['score'],
                'grade' => $validation['grade'],
                'word_count' => $validation['word_count'],
                'is_indexable' => $validation['is_indexable'],
                'issues' => $validation['issues'],
                'url' => route('ads.show', $ad)
            ];
        }
        
        return view('admin.quality.dashboard', compact(
            'recommendations',
            'articleQuality',
            'adQuality'
        ));
    }
    
    /**
     * Exécuter l'indexation intelligente
     */
    public function executeSmartIndexing(Request $request)
    {
        $validated = $request->validate([
            'phase' => 'required|string|in:1,2,3,4,all,recommendations'
        ]);
        
        $strategy = app(SmartIndexingStrategy::class);
        
        try {
            $results = $strategy->executeSmartIndexing($validated['phase']);
            
            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => 'Indexation intelligente exécutée avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}

