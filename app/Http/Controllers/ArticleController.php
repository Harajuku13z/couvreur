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
        
        // Définir la page courante pour le SEO
        $currentPage = 'blog';
        
        return view('articles.index', compact('articles', 'currentPage'));
    }

    public function show(Article $article)
    {
        // Vérifier que l'article est publié
        if ($article->status !== 'published') {
            abort(404);
        }

        // Préparer les métadonnées SEO
        // NE PAS tronquer les titres et descriptions - utiliser le contenu complet
        $pageTitle = $article->meta_title ?: $article->title;
        // Si pas de meta_description, utiliser le début du contenu HTML (sans limite)
        if (empty($article->meta_description)) {
            $contentText = strip_tags($article->content_html ?? '');
            $pageDescription = !empty($contentText) ? $contentText : ($article->excerpt ?? '');
        } else {
            $pageDescription = $article->meta_description;
        }
        $pageKeywords = $article->meta_keywords ?? '';
        
        // Image Open Graph
        $pageImage = null;
        if (!empty($article->featured_image)) {
            $pageImage = asset($article->featured_image);
        } else {
            // Utiliser l'image configurée dans les settings, sinon l'image par défaut
            $defaultBlogImage = setting('default_blog_og_image', 'images/og-blog.jpg');
            if (file_exists(public_path($defaultBlogImage))) {
                $pageImage = asset($defaultBlogImage);
            } else {
                // Fallback sur l'image par défaut si celle configurée n'existe pas
                $fallbackImage = 'images/og-blog.jpg';
                if (file_exists(public_path($fallbackImage))) {
                    $pageImage = asset($fallbackImage);
                } else {
                    $companyLogo = setting('company_logo');
                    if ($companyLogo) {
                        $pageImage = asset($companyLogo);
                    }
                }
            }
        }
        
        $ogTitle = $article->og_title ?? $pageTitle;
        $ogDescription = $article->og_description ?? $pageDescription;
        $twitterTitle = $article->twitter_title ?? $ogTitle;
        $twitterDescription = $article->twitter_description ?? $ogDescription;
        
        $currentPage = 'articles';
        $pageType = 'article';

        // Récupérer les avis clients
        $reviews = Review::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('articles.show_new', compact(
            'article', 
            'reviews', 
            'pageTitle', 
            'pageDescription', 
            'pageKeywords', 
            'pageImage', 
            'ogTitle', 
            'ogDescription', 
            'twitterTitle', 
            'twitterDescription', 
            'currentPage', 
            'pageType'
        ));
    }
}