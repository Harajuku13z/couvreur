<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté en tant qu'admin
        if (!session()->has('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
