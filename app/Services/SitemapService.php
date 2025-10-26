<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SitemapService
{
    protected $baseUrl = 'https://sausercouverture.fr';

    /**
     * Générer le sitemap complet
     */
    public function generateSitemap()
    {
        try {
            Log::info('🚀 Génération du sitemap en cours...');
            
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            
            // Page d'accueil
            $xml .= $this->generateUrl($this->baseUrl, 1.0, 'daily');
            
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
                $xml .= $this->generateUrl($this->baseUrl . $url, $config['priority'], $config['changefreq']);
            }
            
            // Services
            $services = $this->getServices();
            Log::info("📋 Ajout de " . count($services) . " services...");
            foreach ($services as $service) {
                $xml .= $this->generateUrl($this->baseUrl . '/services/' . $service, 0.8, 'monthly');
            }
            
            // Articles
            $articles = $this->getArticles();
            Log::info("📰 Ajout de " . count($articles) . " articles...");
            foreach ($articles as $article) {
                $xml .= $this->generateUrl($this->baseUrl . '/blog/' . $article, 0.7, 'monthly');
            }
            
            // Annonces
            $ads = $this->getAds();
            Log::info("📢 Ajout de " . count($ads) . " annonces...");
            foreach ($ads as $ad) {
                $xml .= $this->generateUrl($this->baseUrl . '/annonces/' . $ad, 0.6, 'monthly');
            }
            
            // Portfolio
            $portfolio = $this->getPortfolio();
            Log::info("🖼️ Ajout de " . count($portfolio) . " éléments de portfolio...");
            foreach ($portfolio as $item) {
                $xml .= $this->generateUrl($this->baseUrl . '/nos-realisations/' . $item, 0.5, 'monthly');
            }
            
            $xml .= '</urlset>';
            
            // Sauvegarder le sitemap
            $sitemapPath = public_path('sitemap.xml');
            file_put_contents($sitemapPath, $xml);
            
            Log::info("✅ Sitemap généré avec succès : {$sitemapPath}");
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la génération du sitemap : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer une URL pour le sitemap
     */
    protected function generateUrl($url, $priority, $changefreq)
    {
        return '  <url>' . "\n" .
               '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n" .
               '    <lastmod>' . Carbon::now()->format('Y-m-d\TH:i:s+01:00') . '</lastmod>' . "\n" .
               '    <changefreq>' . $changefreq . '</changefreq>' . "\n" .
               '    <priority>' . $priority . '</priority>' . "\n" .
               '  </url>' . "\n";
    }

    /**
     * Récupérer les services
     */
    protected function getServices()
    {
        try {
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (is_array($services)) {
                return collect($services)->filter(function($service) {
                    return ($service['is_visible'] ?? true) && ($service['is_active'] ?? true);
                })->pluck('slug')->filter()->toArray();
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Impossible de récupérer les services : " . $e->getMessage());
        }
        
        // Services par défaut
        return ['test-service', 'couvreur', 'couverture', 'hydrofuge'];
    }

    /**
     * Récupérer les articles
     */
    protected function getArticles()
    {
        try {
            return Article::where('status', 'published')->pluck('slug')->toArray();
        } catch (\Exception $e) {
            Log::warning("⚠️ Impossible de récupérer les articles : " . $e->getMessage());
        }
        
        // Articles par défaut
        return [
            'hydrofuge-comment-proteger-efficacement-vos-surfaces-de-leau-guide-complet-2024',
            'guide-complet-hydrofuge-de-toiture-protection-et-impermeabilisation-2024'
        ];
    }

    /**
     * Récupérer les annonces
     */
    protected function getAds()
    {
        try {
            return Ad::orderBy('updated_at', 'desc')->limit(5000)->pluck('slug')->toArray();
        } catch (\Exception $e) {
            Log::warning("⚠️ Impossible de récupérer les annonces : " . $e->getMessage());
        }
        
        // Annonces par défaut
        return [
            'test-couvreur-2-chantilly',
            'test-couvreur-2-senlis',
            'test-couvreur-chantilly',
            'hydrofuge-vitry-en-charollais',
            'test-service-chantilly'
        ];
    }

    /**
     * Récupérer le portfolio
     */
    protected function getPortfolio()
    {
        try {
            $portfolioData = Setting::get('portfolio_items', '[]');
            $portfolio = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
            
            if (is_array($portfolio)) {
                return collect($portfolio)->filter(function($item) {
                    return ($item['is_visible'] ?? true);
                })->pluck('slug')->filter()->toArray();
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Impossible de récupérer le portfolio : " . $e->getMessage());
        }
        
        // Portfolio par défaut
        return ['renovation-de-toiture-a-avrainville'];
    }

    /**
     * Mettre à jour le sitemap automatiquement
     */
    public function updateSitemap()
    {
        return $this->generateSitemap();
    }
}
