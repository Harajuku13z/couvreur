<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Service;
use App\Models\City;
use App\Models\Article;

class SitemapController extends Controller
{
    /**
     * Générer le sitemap avec cache
     */
    public function index()
    {
        // Cache pendant 24 heures
        $sitemapXml = Cache::remember('sitemap_xml', 86400, function () {
            $sitemap = Sitemap::create();

            // Page d'accueil (priorité maximale)
            $sitemap->add(Url::create(route('home'))
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

            // Services actifs
            $services = Service::active()->ordered()->get();
            foreach ($services as $service) {
                try {
                    $sitemap->add(Url::create(route('services.show', $service))
                        ->setPriority(0.9)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setLastModificationDate($service->updated_at));
                } catch (\Exception $e) {
                    // Ignorer les erreurs de route
                    continue;
                }
            }

            // Villes actives (SEO local)
            $cities = City::active()->get();
            foreach ($cities as $city) {
                try {
                    $sitemap->add(Url::create(route('locale.show', $city))
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setLastModificationDate($city->updated_at));
                } catch (\Exception $e) {
                    // Ignorer les erreurs de route
                    continue;
                }
            }

            // Articles publiés
            $articles = Article::published()->latest()->get();
            foreach ($articles as $article) {
                try {
                    $sitemap->add(Url::create(route('blog.show', $article))
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setLastModificationDate($article->updated_at));
                } catch (\Exception $e) {
                    // Ignorer les erreurs de route
                    continue;
                }
            }

            // Pages statiques importantes
            $staticPages = [
                ['route' => 'services.index', 'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
                ['route' => 'blog.index', 'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
                ['route' => 'devis.gratuit', 'priority' => 0.8, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
                ['route' => 'contact', 'priority' => 0.7, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
                ['route' => 'reviews.all', 'priority' => 0.6, 'freq' => Url::CHANGE_FREQUENCY_WEEKLY],
                ['route' => 'portfolio.index', 'priority' => 0.6, 'freq' => Url::CHANGE_FREQUENCY_MONTHLY],
            ];

            foreach ($staticPages as $page) {
                try {
                    if (\Route::has($page['route'])) {
                        $sitemap->add(Url::create(route($page['route']))
                            ->setPriority($page['priority'])
                            ->setChangeFrequency($page['freq']));
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Pages légales (priorité faible)
            $legalPages = [
                ['route' => 'legal.mentions', 'priority' => 0.3],
                ['route' => 'legal.privacy', 'priority' => 0.3],
                ['route' => 'legal.cgv', 'priority' => 0.3],
            ];

            foreach ($legalPages as $page) {
                try {
                    if (\Route::has($page['route'])) {
                        $sitemap->add(Url::create(route($page['route']))
                            ->setPriority($page['priority'])
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return $sitemap->render();
        });

        return response($sitemapXml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
