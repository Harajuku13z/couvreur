<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip setup check for setup routes
        if ($request->is('setup') || $request->is('setup/*')) {
            return $next($request);
        }

        // Check if setup is completed
        if (!Setting::isSetupCompleted()) {
            return redirect()->route('config.setup');
        }

        return $next($request);
    }
}








