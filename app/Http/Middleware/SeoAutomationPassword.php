<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoAutomationPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur a déjà saisi le mot de passe dans la session
        $passwordVerified = $request->session()->get('seo_automation_password_verified', false);
        $passwordVerifiedAt = $request->session()->get('seo_automation_password_verified_at', null);
        
        // Si le mot de passe a été vérifié il y a moins d'1 heure, laisser passer
        if ($passwordVerified && $passwordVerifiedAt) {
            $oneHourAgo = now()->subHour();
            if ($passwordVerifiedAt > $oneHourAgo) {
                return $next($request);
            }
        }
        
        // Si c'est la route de validation du mot de passe ou le formulaire, laisser passer
        if ($request->is('admin/seo-automation/verify-password') || 
            $request->is('admin/seo-automation/password') ||
            $request->routeIs('admin.seo-automation.password') ||
            $request->routeIs('admin.seo-automation.verify-password')) {
            return $next($request);
        }
        
        // Sinon, rediriger vers le formulaire de mot de passe
        return redirect()->route('admin.seo-automation.password')
            ->with('redirect_to', $request->fullUrl());
    }
}
