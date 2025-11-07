<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\VisitTrackingService;
use Symfony\Component\HttpFoundation\Response;

class TrackVisits
{
    protected $visitTrackingService;

    public function __construct(VisitTrackingService $visitTrackingService)
    {
        $this->visitTrackingService = $visitTrackingService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tracker la visite en arrière-plan (ne pas bloquer la requête)
        // On utilise @ pour supprimer les warnings si la table n'existe pas
        try {
            @$this->visitTrackingService->track($request);
        } catch (\Exception $e) {
            // Ignorer silencieusement les erreurs de tracking pour ne pas bloquer la requête
            // Ne logger que si on est en mode debug
            if (config('app.debug')) {
                \Log::warning('Erreur tracking visite middleware: ' . $e->getMessage());
            }
        }

        return $next($request);
    }
}

