<?php

namespace App\Helpers;

use App\Models\Article;
use App\Models\Setting;

class InternalLinkingHelper
{
    /**
     * Générer des liens internes automatiques dans un contenu
     */
    public static function generateInternalLinks($content, $currentPage = null)
    {
        // Récupérer les services
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // Récupérer les articles publiés
        $articles = Article::where('status', 'published')->get();
        
        // Créer un tableau de mots-clés et leurs liens
        $links = [];
        
        // Services
        foreach ($services as $service) {
            if (isset($service['name']) && isset($service['slug'])) {
                $links[$service['name']] = route('services.show', $service['slug']);
            }
        }
        
        // Articles (premiers mots du titre)
        foreach ($articles as $article) {
            $titleWords = explode(' ', $article->title);
            if (count($titleWords) >= 2) {
                $keyword = $titleWords[0] . ' ' . $titleWords[1];
                $links[$keyword] = route('blog.show', $article->slug);
            }
        }
        
        // Trier par longueur décroissante pour éviter les conflits
        uksort($links, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        // Remplacer les occurrences dans le contenu (maximum 3 liens par contenu)
        $linkCount = 0;
        $maxLinks = 3;
        
        foreach ($links as $keyword => $url) {
            if ($linkCount >= $maxLinks) break;
            
            // Éviter de lier si déjà dans un lien
            $pattern = '/(?<!<a[^>]*>)\b' . preg_quote($keyword, '/') . '\b(?!<\/a>)/i';
            
            if (preg_match($pattern, $content)) {
                $replacement = '<a href="' . $url . '" class="text-primary hover:underline font-semibold">' . $keyword . '</a>';
                $content = preg_replace($pattern, $replacement, $content, 1);
                $linkCount++;
            }
        }
        
        return $content;
    }
    
    /**
     * Générer des liens suggérés pour une page
     */
    public static function getSuggestedLinks($currentPage = null, $limit = 5)
    {
        $suggested = [];
        
        // Services
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        foreach (array_slice($services, 0, 3) as $service) {
            if (isset($service['name']) && isset($service['slug'])) {
                $suggested[] = [
                    'title' => $service['name'],
                    'url' => route('services.show', $service['slug']),
                    'type' => 'service'
                ];
            }
        }
        
        // Articles récents
        $articles = Article::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
        
        foreach ($articles as $article) {
            $suggested[] = [
                'title' => $article->title,
                'url' => route('blog.show', $article->slug),
                'type' => 'article'
            ];
        }
        
        return array_slice($suggested, 0, $limit);
    }
}

