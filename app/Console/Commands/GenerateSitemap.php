<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap using Spatie Laravel Sitemap';

    public function handle()
    {
        $this->info('🚀 Génération du sitemap en cours...');
        
        // URL depuis la config ou les settings - FORCER normesrenovationbretagne.fr
        $baseUrl = null;
        
        // 1. Vérifier le setting (mais REJETER sausercouverture.fr)
        $settingUrl = \App\Models\Setting::get('site_url', null);
        if (!empty($settingUrl) && strpos($settingUrl, 'sausercouverture.fr') === false) {
            if (strpos($settingUrl, 'normesrenovationbretagne.fr') !== false) {
                $baseUrl = $settingUrl;
            }
        }
        
        // 2. Vérifier APP_URL depuis .env (mais REJETER sausercouverture.fr)
        if (empty($baseUrl)) {
            $envUrl = config('app.url', null);
            if (!empty($envUrl) && strpos($envUrl, 'sausercouverture.fr') === false) {
                if (strpos($envUrl, 'normesrenovationbretagne.fr') !== false) {
                    $baseUrl = $envUrl;
                }
            }
        }
        
        // 3. Par défaut, utiliser normesrenovationbretagne.fr (TOUJOURS)
        if (empty($baseUrl)) {
            $baseUrl = 'https://normesrenovationbretagne.fr';
        }
        
        // S'assurer que l'URL a un protocole
        if (!preg_match('/^https?:\/\//', $baseUrl)) {
            $baseUrl = 'https://' . $baseUrl;
        }
        $baseUrl = rtrim($baseUrl, '/');
        
        // VÉRIFICATION FINALE : Rejeter sausercouverture.fr
        if (strpos($baseUrl, 'sausercouverture.fr') !== false) {
            $this->error('❌ ERREUR: sausercouverture.fr détectée, correction forcée !');
            $baseUrl = 'https://normesrenovationbretagne.fr';
        }
        
        $sitemap = Sitemap::create();
        
        // Page d'accueil
        $sitemap->add(Url::create($baseUrl)
            ->setLastModificationDate(Carbon::now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            ->setPriority(1.0));
        
        // Pages statiques
        $staticPages = [
            '/services' => ['priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            '/nos-realisations' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY],
            '/avis' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            '/blog' => ['priority' => 0.7, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            '/contact' => ['priority' => 0.6, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY],
            '/mentions-legales' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
            '/politique-confidentialite' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
            '/cgv' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
        ];
        
        foreach ($staticPages as $url => $config) {
            $sitemap->add(Url::create($baseUrl . $url)
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency($config['changefreq'])
                ->setPriority($config['priority']));
        }
        
        // Services
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $visibleServices = collect($services)->filter(function($service) {
            return ($service['is_visible'] ?? true) && ($service['is_active'] ?? true);
        });
        
        $this->info("📋 Ajout de {$visibleServices->count()} services...");
        
        foreach ($visibleServices as $service) {
            if (isset($service['slug'])) {
                $sitemap->add(Url::create($baseUrl . '/services/' . $service['slug'])
                    ->setLastModificationDate(Carbon::parse($service['updated_at'] ?? $service['created_at'] ?? now()))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8));
            }
        }
        
        // Articles
        $articles = Article::where('status', 'published')->get();
        $this->info("📰 Ajout de {$articles->count()} articles...");
        
        foreach ($articles as $article) {
            $sitemap->add(Url::create($baseUrl . '/blog/' . $article->slug)
                ->setLastModificationDate($article->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7));
        }
        
        // Annonces (toutes)
        $ads = Ad::orderBy('updated_at', 'desc')->limit(5000)->get();
        $this->info("📢 Ajout de {$ads->count()} annonces...");
        
        foreach ($ads as $ad) {
            $sitemap->add(Url::create($baseUrl . '/annonces/' . $ad->slug)
                ->setLastModificationDate($ad->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.6));
        }
        
        // Portfolio
        $portfolioItems = Setting::get('portfolio_items', '[]');
        if (is_string($portfolioItems)) {
            $portfolioItems = json_decode($portfolioItems, true) ?? [];
        }
        
        $visiblePortfolioItems = array_filter($portfolioItems, function($item) {
            return ($item['is_visible'] ?? true);
        });
        
        $this->info("🖼️ Ajout de " . count($visiblePortfolioItems) . " éléments de portfolio...");
        
        foreach ($visiblePortfolioItems as $item) {
            if (isset($item['slug'])) {
                $sitemap->add(Url::create($baseUrl . '/nos-realisations/' . $item['slug'])
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.5));
            }
        }
        
        // Sauvegarder le sitemap
        $sitemapPath = public_path('sitemap.xml');
        $sitemap->writeToFile($sitemapPath);
        
        $this->info("✅ Sitemap généré avec succès : {$sitemapPath}");
        $this->info("🌐 URL du sitemap : {$baseUrl}/sitemap.xml");
        
        return 0;
    }
}
