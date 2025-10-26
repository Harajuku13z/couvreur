<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;

class GenerateSitemapSimple extends Command
{
    protected $signature = 'sitemap:generate-simple';
    protected $description = 'Generate sitemap without Spatie dependencies';

    public function handle()
    {
        $this->info('🚀 Génération du sitemap simple en cours...');
        
        // Forcer l'URL de production
        $baseUrl = 'https://sausercouverture.fr';
        
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
        
        // Articles
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
        
        // Annonces (toutes)
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
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . $baseUrl . '/nos-realisations/' . $item['slug'] . '</loc>' . "\n";
                $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
                $xml .= '    <changefreq>monthly</changefreq>' . "\n";
                $xml .= '    <priority>0.5</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }
        
        $xml .= '</urlset>';
        
        // Sauvegarder le sitemap
        $sitemapPath = public_path('sitemap.xml');
        file_put_contents($sitemapPath, $xml);
        
        $this->info("✅ Sitemap simple généré avec succès : {$sitemapPath}");
        $this->info("🌐 URL du sitemap : {$baseUrl}/sitemap.xml");
        
        return 0;
    }
}
