<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Review;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(12);
        
        return view('articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        // Vérifier que l'article est publié
        if ($article->status !== 'published') {
            abort(404);
        }

        // Récupérer les avis clients
        $reviews = Review::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('articles.show', compact('article', 'reviews'));
    }
}