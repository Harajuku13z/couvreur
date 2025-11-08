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
            // Utiliser une approche simple : remplacer le texte qui n'est pas déjà dans une balise <a>
            $escapedKeyword = preg_quote($keyword, '/');
            
            // Pattern simple pour trouver le mot-clé
            $pattern = '/\b' . $escapedKeyword . '\b/i';
            
            // Vérifier d'abord si le mot-clé existe
            if (preg_match($pattern, $content)) {
                // Extraire toutes les positions du mot-clé
                $matches = [];
                preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
                
                foreach ($matches[0] as $match) {
                    $position = $match[1];
                    $matchLength = strlen($match[0]);
                    
                    // Extraire le contexte avant et après pour vérifier si on est dans un lien
                    $contextBefore = substr($content, max(0, $position - 500), 500);
                    $contextAfter = substr($content, $position, 500);
                    
                    // Trouver la dernière balise <a> ouvrante avant notre position
                    $lastOpenTagPos = strrpos($contextBefore, '<a');
                    $lastCloseTagPos = strrpos($contextBefore, '</a>');
                    
                    // Si on trouve un <a> ouvert (après le dernier </a>), on est probablement dans un lien
                    $isInLink = false;
                    if ($lastOpenTagPos !== false) {
                        // Vérifier si le <a> est après le dernier </a> (ou s'il n'y a pas de </a>)
                        if ($lastCloseTagPos === false || $lastOpenTagPos > $lastCloseTagPos) {
                            // On est dans un <a>, vérifier qu'il se ferme après notre position
                            $closeAfterPos = strpos($contextAfter, '</a>');
                            if ($closeAfterPos !== false) {
                                $isInLink = true;
                            }
                        }
                    }
                    
                    // Si on n'est pas dans un lien, on peut créer le lien
                    if (!$isInLink) {
                        $replacement = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="text-primary hover:underline font-semibold">' . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . '</a>';
                        $content = substr_replace($content, $replacement, $position, $matchLength);
                        $linkCount++;
                        break; // On ne remplace que la première occurrence valide
                    }
                }
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

