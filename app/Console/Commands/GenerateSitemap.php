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
        
        // Déterminer dynamiquement le domaine de base (sans forcer de domaine tiers)
        // Ordre de priorité: Setting(site_url) > APP_URL > domaine de la requête (si disponible)
        $baseUrl = \App\Models\Setting::get('site_url', null);
        if (empty($baseUrl)) {
            $baseUrl = config('app.url', null);
        }
        if (empty($baseUrl)) {
            try {
                $baseUrl = request()->getSchemeAndHttpHost();
            } catch (\Throwable $e) {
                $baseUrl = 'http://localhost';
            }
        }
        // Normaliser le format de l'URL
        if (!preg_match('/^https?:\/\//', $baseUrl)) {
            $baseUrl = 'https://' . ltrim($baseUrl, '/');
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
        
        // Pages statiques (URLs réelles pour éviter 404)
        $staticPages = [
            '/services' => ['priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            '/portfolio' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY],
            '/reviews' => ['priority' => 0.8, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            '/blog' => ['priority' => 0.7, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY],
            '/contact' => ['priority' => 0.6, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY],
            '/legal/mentions' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
            '/legal/privacy' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
            '/legal/cgv' => ['priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY],
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
        
        // Annonces publiées uniquement (éviter 404 sur brouillons)
        $ads = Ad::where('status', 'published')->orderBy('updated_at', 'desc')->limit(5000)->get();
        $this->info("📢 Ajout de {$ads->count()} annonces...");
        
        foreach ($ads as $ad) {
            $sitemap->add(Url::create($baseUrl . '/ads/' . $ad->slug)
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
                $sitemap->add(Url::create($baseUrl . '/portfolio/' . $item['slug'])
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.5));
            }
        }
        
        // DÉSACTIVÉ : Cette commande entre en conflit avec SitemapService
        // Utiliser 'sitemap:reset' à la place pour générer les sitemaps correctement
        $this->warn("⚠️  Cette commande est désactivée car elle entre en conflit avec SitemapService.");
        $this->warn("⚠️  Utilisez 'php artisan sitemap:reset --force' à la place.");
        $this->warn("⚠️  SitemapService génère des sitemaps avec 2000 URLs par fichier.");
        
        // NE PAS écraser sitemap.xml
        // $sitemapPath = public_path('sitemap.xml');
        // $sitemap->writeToFile($sitemapPath);
        
        return 0;
    }
}
