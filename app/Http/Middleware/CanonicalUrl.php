<?php

namespace App\Http\Middleware;

use App\Helpers\SeoHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalUrl
{
    /**
     * Handle an incoming request.
     * Ajoute automatiquement un header Link canonical pour éviter le contenu dupliqué.
     * Utilise site_url pour garantir une canonical unique (évite "Duplicate without user-selected canonical").
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Ne pas ajouter de canonical pour les routes admin, API ou médias (fichiers/binaire)
        if ($request->is('admin/*') || $request->is('api/*') || $request->is('media/*')) {
            return $response;
        }

        // Ne pas ajouter de canonical sur les réponses non HTML (images, pdf, streams, etc.)
        $contentType = $response->headers->get('Content-Type');
        if ($contentType && stripos($contentType, 'text/html') === false) {
            return $response;
        }

        $canonicalUrl = SeoHelper::getCanonicalUrl($request);

        if (method_exists($response, 'header')) {
            $response->header('Link', '<' . $canonicalUrl . '>; rel="canonical"');
        } else {
            $response->headers->set('Link', '<' . $canonicalUrl . '>; rel="canonical"');
        }

        return $response;
    }
}
