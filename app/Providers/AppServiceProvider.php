<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\UpdateSitemapListener;
use App\Events\AdCreated;
use App\Events\AdUpdated;
use App\Events\ArticleCreated;
use App\Events\ServiceUpdated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enregistrer les événements pour la mise à jour automatique du sitemap
        Event::listen(AdCreated::class, UpdateSitemapListener::class);
        Event::listen(AdUpdated::class, UpdateSitemapListener::class);
        Event::listen(ArticleCreated::class, UpdateSitemapListener::class);
        Event::listen(ServiceUpdated::class, UpdateSitemapListener::class);
    }
}
