<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;

class GenerateCompleteSitemap extends Command
{
    protected $signature = 'sitemap:generate-complete';
    protected $description = 'Generate complete sitemap with all data (services, articles, ads, portfolio)';

    public function handle()
    {
        $this->info('🚀 Génération du sitemap complet en cours...');
        
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
        
        // Services - Essayer de récupérer depuis la base de données, sinon utiliser les services par défaut
        $services = [];
        try {
            $servicesData = Setting::get('services', '[]');
            $servicesFromDb = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (is_array($servicesFromDb)) {
                $services = collect($servicesFromDb)->filter(function($service) {
                    return ($service['is_visible'] ?? true) && ($service['is_active'] ?? true);
                })->pluck('slug')->filter()->toArray();
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Impossible de récupérer les services depuis la DB, utilisation des services par défaut");
        }
        
        // Services par défaut si aucun service trouvé
        if (empty($services)) {
            $services = ['test-service', 'couvreur', 'couverture', 'hydrofuge'];
        }
        
        $this->info("📋 Ajout de " . count($services) . " services...");
        
        foreach ($services as $service) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . '/services/' . $service . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // Articles - Essayer de récupérer depuis la base de données, sinon utiliser les articles par défaut
        $articles = [];
        try {
            $articlesFromDb = Article::where('status', 'published')->get();
            $articles = $articlesFromDb->pluck('slug')->toArray();
        } catch (\Exception $e) {
            $this->warn("⚠️ Impossible de récupérer les articles depuis la DB, utilisation des articles par défaut");
        }
        
        // Articles par défaut si aucun article trouvé
        if (empty($articles)) {
            $articles = [
                'hydrofuge-comment-proteger-efficacement-vos-surfaces-de-leau-guide-complet-2024',
                'guide-complet-hydrofuge-de-toiture-protection-et-impermeabilisation-2024'
            ];
        }
        
        $this->info("📰 Ajout de " . count($articles) . " articles...");
        
        foreach ($articles as $article) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . '/blog/' . $article . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // Annonces - Essayer de récupérer depuis la base de données, sinon utiliser les annonces par défaut
        $ads = [];
        try {
            $adsFromDb = Ad::orderBy('updated_at', 'desc')->limit(5000)->get();
            $ads = $adsFromDb->pluck('slug')->toArray();
        } catch (\Exception $e) {
            $this->warn("⚠️ Impossible de récupérer les annonces depuis la DB, utilisation des annonces par défaut");
        }
        
        // Annonces par défaut si aucune annonce trouvée
        if (empty($ads)) {
            $ads = [
                'test-couvreur-2-chantilly',
                'test-couvreur-2-senlis',
                'test-couvreur-chantilly',
                'hydrofuge-vitry-en-charollais',
                'test-service-chantilly'
            ];
        }
        
        $this->info("📢 Ajout de " . count($ads) . " annonces...");
        
        foreach ($ads as $ad) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . '/annonces/' . $ad . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.6</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // Portfolio - Essayer de récupérer depuis la base de données, sinon utiliser le portfolio par défaut
        $portfolio = [];
        try {
            $portfolioData = Setting::get('portfolio_items', '[]');
            $portfolioFromDb = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
            
            if (is_array($portfolioFromDb)) {
                $portfolio = collect($portfolioFromDb)->filter(function($item) {
                    return ($item['is_visible'] ?? true);
                })->pluck('slug')->filter()->toArray();
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ Impossible de récupérer le portfolio depuis la DB, utilisation du portfolio par défaut");
        }
        
        // Portfolio par défaut si aucun élément trouvé
        if (empty($portfolio)) {
            $portfolio = ['renovation-de-toiture-a-avrainville'];
        }
        
        $this->info("🖼️ Ajout de " . count($portfolio) . " éléments de portfolio...");
        
        foreach ($portfolio as $item) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . $baseUrl . '/nos-realisations/' . $item . '</loc>' . "\n";
            $xml .= '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.5</priority>' . "\n";
            $xml .= '  </url>' . "\n";
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
