<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalUrl
{
    /**
     * Handle an incoming request.
     * Ajoute automatiquement un header Link canonical pour éviter le contenu dupliqué
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne pas ajouter de canonical pour les routes admin ou API
        if ($request->is('admin/*') || $request->is('api/*')) {
            return $response;
        }

        // Construire l'URL canonique propre (sans query strings inutiles)
        $canonicalUrl = $this->buildCanonicalUrl($request);

        // Ajouter le header Link canonical
        $response->header('Link', '<' . $canonicalUrl . '>; rel="canonical"');

        return $response;
    }

    /**
     * Construire l'URL canonique propre
     */
    protected function buildCanonicalUrl(Request $request): string
    {
        // Récupérer l'URL de base
        $url = $request->url();

        // Supprimer les query strings inutiles (garder seulement ceux qui sont importants pour le SEO)
        $queryParams = $request->query();
        $importantParams = ['page', 'ref', 'utm_source', 'utm_medium', 'utm_campaign'];
        
        $filteredParams = [];
        foreach ($importantParams as $param) {
            if (isset($queryParams[$param])) {
                $filteredParams[$param] = $queryParams[$param];
            }
        }

        // Reconstruire l'URL avec seulement les paramètres importants
        if (!empty($filteredParams)) {
            $url .= '?' . http_build_query($filteredParams);
        }

        // S'assurer que l'URL ne se termine pas par un slash (sauf pour la racine)
        if ($url !== url('/') && str_ends_with($url, '/')) {
            $url = rtrim($url, '/');
        }

        return $url;
    }
}
