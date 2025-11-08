<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;

class GenerateSitemapManual extends Command
{
    protected $signature = 'sitemap:generate-manual';
    protected $description = 'Generate sitemap manually without Spatie dependencies';

    public function handle()
    {
        $this->info('🚀 Génération du sitemap manuel en cours...');
        
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
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Page d'accueil
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . $baseUrl . '</loc>' . "\n";
        $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Pages statiques
        $staticPages = [
            '/services' => ['priority' => 0.9, 'changefreq' => 'weekly'],
            '/nos-realisations' => ['priority' => 0.8, 'changefreq' => 'monthly'],
            '/avis' => ['priority' => 0.8, 'changefreq' => 'weekly'],
            '/blog' => ['priority' => 0.7, 'changefreq' => 'weekly'],
            '/contact' => ['priority' => 0.6, 'changefreq' => 'monthly'],
            '/mentions-legales' => ['priority' => 0.3, 'changefreq' => 'yearly'],
            '/politique-confidentialite' => ['priority' => 0.3, 'changefreq' => 'yearly'],
            '/cgv' => ['priority' => 0.3, 'changefreq' => 'yearly'],
        ];
        
        foreach ($staticPages as $url => $config) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . $url . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>' . $config['changefreq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $config['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // Services
        try {
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
                    $xml .= '  <url>' . "\n";
                    $xml .= '    <loc>' . $baseUrl . '/services/' . $service['slug'] . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . Carbon::parse($service['updated_at'] ?? $service['created_at'] ?? now())->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
                    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                    $xml .= '    <priority>0.8</priority>' . "\n";
                    $xml .= '  </url>' . "\n";
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Erreur lors de la récupération des services : " . $e->getMessage());
        }
        
        // Articles
        try {
            $articles = Article::where('status', 'published')->get();
            $this->info("📰 Ajout de {$articles->count()} articles...");
            
            foreach ($articles as $article) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . $baseUrl . '/blog/' . $article->slug . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $article->updated_at->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.7</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Erreur lors de la récupération des articles : " . $e->getMessage());
        }
        
        // Annonces (toutes)
        try {
            $ads = Ad::orderBy('updated_at', 'desc')->limit(5000)->get();
            $this->info("📢 Ajout de {$ads->count()} annonces...");
            
            foreach ($ads as $ad) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . $baseUrl . '/annonces/' . $ad->slug . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $ad->updated_at->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.6</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Erreur lors de la récupération des annonces : " . $e->getMessage());
        }
        
        // Portfolio
        try {
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
                    $xml .= '  <url>' . "\n";
                    $xml .= '    <loc>' . $baseUrl . '/nos-realisations/' . $item['slug'] . '</loc>' . "\n";
                    $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
                    $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                    $xml .= '    <priority>0.5</priority>' . "\n";
                    $xml .= '  </url>' . "\n";
                }
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Erreur lors de la récupération du portfolio : " . $e->getMessage());
        }
        
        $xml .= '</urlset>';
        
        // DÉSACTIVÉ : Cette commande entre en conflit avec SitemapService
        // Utiliser 'sitemap:reset' à la place pour générer les sitemaps correctement
        $this->warn("⚠️  Cette commande est désactivée car elle entre en conflit avec SitemapService.");
        $this->warn("⚠️  Utilisez 'php artisan sitemap:reset --force' à la place.");
        $this->warn("⚠️  SitemapService génère des sitemaps avec 2000 URLs par fichier.");
        
        // NE PAS écraser sitemap.xml
        // $sitemapPath = public_path('sitemap.xml');
        // file_put_contents($sitemapPath, $xml);
        
        return 0;
    }
}
