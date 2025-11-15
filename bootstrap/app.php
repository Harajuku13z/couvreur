<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'check.setup' => \App\Http\Middleware\CheckSetup::class,
            'seo.automation.password' => \App\Http\Middleware\SeoAutomationPassword::class,
        ]);
        
        // Ajouter le tracking des visites et canonical URL au groupe web
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisits::class,
            \App\Http\Middleware\CanonicalUrl::class,
        ]);
        
        // Exclure la route de tracking des appels du CSRF (sendBeacon ne peut pas envoyer de token)
        $middleware->validateCsrfTokens(except: [
            'api/track-phone-call',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
