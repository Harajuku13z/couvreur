<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAdminBasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->getUser();
        $password = $request->getPassword();

        // Identifiants fixes pour l'accès à /useradmin
        $expectedUser = 'elizo';
        $expectedPassword = 'elizo';

        if ($user !== $expectedUser || $password !== $expectedPassword) {
            return response('Unauthorized.', 401, [
                'WWW-Authenticate' => 'Basic realm="UserAdmin"',
            ]);
        }

        return $next($request);
    }
}

