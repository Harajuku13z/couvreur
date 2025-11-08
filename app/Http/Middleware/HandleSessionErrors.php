<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleSessionErrors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Illuminate\Database\QueryException $e) {
            // Si l'erreur concerne la table sessions, basculer vers les sessions fichiers
            if (strpos($e->getMessage(), "Table '") !== false && strpos($e->getMessage(), ".sessions' doesn't exist") !== false) {
                Log::warning('Table sessions n\'existe pas, basculement vers sessions fichiers', [
                    'error' => $e->getMessage()
                ]);
                
                // Changer temporairement le driver de session vers 'file'
                config(['session.driver' => 'file']);
                
                // Vider le cache de session
                app('session')->flush();
                
                // Réessayer la requête
                try {
                    return $next($request);
                } catch (\Exception $retryException) {
                    Log::error('Erreur après basculement vers sessions fichiers: ' . $retryException->getMessage());
                    return response()->view('errors.500', [
                        'message' => 'Erreur de session. Veuillez exécuter les migrations : php artisan migrate'
                    ], 500);
                }
            }
            
            // Si c'est une autre erreur de base de données, la laisser passer
            throw $e;
        }
    }
}

