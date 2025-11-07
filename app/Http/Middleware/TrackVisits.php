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
        try {
            $this->visitTrackingService->track($request);
        } catch (\Exception $e) {
            // Ignorer les erreurs de tracking pour ne pas bloquer la requête
            \Log::error('Erreur tracking visite middleware: ' . $e->getMessage());
        }

        return $next($request);
    }
}

