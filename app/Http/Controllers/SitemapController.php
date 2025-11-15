<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use App\Models\Service;
use App\Models\City;
use App\Models\Article;

class SitemapController extends Controller
{
    protected $maxUrlsPerSitemap = 3000; // Limite augmentée à 3000 URLs par sitemap

    /**
     * Générer le sitemap index qui liste tous les autres sitemaps
     */
    public function index()
    {
        // Cache pendant 24 heures
        $sitemapIndexXml = Cache::remember('sitemap_index_xml', 86400, function () {
            // Collecter toutes les URLs
            $allUrls = $this->collectAllUrls();
            
            // Diviser en chunks de 3000 URLs maximum
            $urlChunks = array_chunk($allUrls, $this->maxUrlsPerSitemap);
            $sitemapFiles = [];
            
            // Générer un sitemap pour chaque chunk (sitemap1.xml, sitemap2.xml, etc.)
            foreach ($urlChunks as $index => $urlChunk) {
                $sitemapNumber = $index + 1;
                $filename = "sitemap{$sitemapNumber}.xml";
                $sitemapPath = public_path($filename);
                
                // Créer le sitemap
                $sitemap = Sitemap::create();
                foreach ($urlChunk as $urlData) {
                    try {
                        $url = Url::create($urlData['url'])
                            ->setPriority($urlData['priority'] ?? 0.8)
                            ->setChangeFrequency($urlData['changefreq'] ?? Url::CHANGE_FREQUENCY_WEEKLY);
                        
                        if (isset($urlData['lastmod'])) {
                            $url->setLastModificationDate($urlData['lastmod']);
                        }
                        
                        $sitemap->add($url);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                // Sauvegarder le sitemap
                $sitemap->writeToFile($sitemapPath);
                $sitemapFiles[] = [
                    'filename' => $filename,
                    'url' => url($filename),
                    'urls_count' => count($urlChunk)
                ];
            }
            
            // Créer le sitemap index qui liste tous les sitemaps
            $sitemapIndex = SitemapIndex::create();
            foreach ($sitemapFiles as $sitemapFile) {
                $sitemapIndex->add($sitemapFile['url']);
            }
            
            // Sauvegarder le sitemap index dans sitemap.xml (c'est le fichier principal)
            $indexPath = public_path('sitemap.xml');
            $sitemapIndex->writeToFile($indexPath);
            
            // Optionnel : Sauvegarder aussi dans sitemap_index.xml pour compatibilité
            $indexPathAlt = public_path('sitemap_index.xml');
            $sitemapIndex->writeToFile($indexPathAlt);
            
            // Retourner le XML du sitemap index
            return $sitemapIndex->render();
        });

        return response($sitemapIndexXml, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Collecter toutes les URLs du site
     */
    protected function collectAllUrls()
    {
        $urls = [];

        // Page d'accueil (priorité maximale)
        try {
            $urls[] = [
                'url' => route('home'),
                'priority' => 1.0,
                'changefreq' => Url::CHANGE_FREQUENCY_DAILY,
                'lastmod' => now()
            ];
        } catch (\Exception $e) {
            // Ignorer
        }

        // Services actifs
        try {
            $services = Service::active()->ordered()->get();
            foreach ($services as $service) {
                try {
                    $urls[] = [
                        'url' => route('services.show', $service),
                        'priority' => 0.9,
                        'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY,
                        'lastmod' => $service->updated_at
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Ignorer
        }

        // Villes actives (SEO local)
        try {
            $cities = City::active()->get();
            foreach ($cities as $city) {
                try {
                    $urls[] = [
                        'url' => route('locale.show', $city),
                        'priority' => 0.8,
                        'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY,
                        'lastmod' => $city->updated_at
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Ignorer
        }

        // Articles publiés
        try {
            $articles = Article::published()->latest()->get();
            foreach ($articles as $article) {
                try {
                    $urls[] = [
                        'url' => route('blog.show', $article),
                        'priority' => 0.7,
                        'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY,
                        'lastmod' => $article->updated_at
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Ignorer
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
                    $urls[] = [
                        'url' => route($page['route']),
                        'priority' => $page['priority'],
                        'changefreq' => $page['freq'],
                        'lastmod' => now()
                    ];
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
                    $urls[] = [
                        'url' => route($page['route']),
                        'priority' => $page['priority'],
                        'changefreq' => Url::CHANGE_FREQUENCY_YEARLY,
                        'lastmod' => now()
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $urls;
    }
}
