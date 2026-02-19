<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SitemapService
{
    protected $baseUrl;
    // Nombre max d'URLs par fichier sitemap physique (pour rester sous les limites Google)
    protected $maxUrlsPerSitemap = 2000;

    public function __construct()
    {
        // Utiliser l'URL depuis la configuration (setting ou APP_URL)
        $siteUrl = null;
        
        // 1. Vérifier le setting site_url
        try {
            $settingUrl = Setting::get('site_url', null);
            if (!empty($settingUrl)) {
                $siteUrl = $settingUrl;
            }
        } catch (\Exception $e) {
            // Si la base de données n'est pas accessible, ignorer et continuer
            \Log::warning('Impossible d\'accéder au setting site_url: ' . $e->getMessage());
        }
        
        // 2. Vérifier APP_URL depuis .env
        if (empty($siteUrl)) {
            $envUrl = config('app.url', null);
            if (!empty($envUrl)) {
                $siteUrl = $envUrl;
            }
        }
        
        // 3. Par défaut, utiliser APP_URL ou localhost
        if (empty($siteUrl)) {
            $siteUrl = config('app.url', 'http://localhost');
        }
        // Ne jamais forcer/rejeter un domaine ici: utiliser la configuration réelle du site
        
        // S'assurer que l'URL a un protocole (https:// ou http://)
        if (!preg_match('/^https?:\/\//', $siteUrl)) {
            // Si pas de protocole, ajouter https://
            $siteUrl = 'https://' . $siteUrl;
        }
        
        // S'assurer que l'URL ne se termine pas par /
        $this->baseUrl = rtrim($siteUrl, '/');

        // Log pour debug (seulement si pas d'erreur)
        try {
            \Log::info("🔗 SitemapService baseUrl: {$this->baseUrl}");
        } catch (\Exception $e) {
            // Ignorer les erreurs de log
        }
    }

    /**
     * Générer le sitemap complet avec système en cascade
     */
    public function generateSitemap()
    {
        try {
            Log::info('🚀 Génération du sitemap en cours...');
            
            // Collecter toutes les URLs
            $allUrls = $this->collectAllUrls();
            
            Log::info("📊 Total d'URLs collectées: " . count($allUrls));
            
            // Diviser en chunks de 2000 URLs maximum
            $urlChunks = array_chunk($allUrls, $this->maxUrlsPerSitemap);
            $sitemapFiles = [];
            
            // Générer un sitemap pour chaque chunk
            foreach ($urlChunks as $index => $urlChunk) {
                $sitemapNumber = $index + 1;
                $filename = $index === 0 ? 'sitemap.xml' : "sitemap{$sitemapNumber}.xml";
                $sitemapPath = public_path($filename);
            
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            
                foreach ($urlChunk as $urlData) {
                    $xml .= $this->generateUrl(
                        $urlData['url'],
                        $urlData['priority'],
                        $urlData['changefreq'],
                        $urlData['lastmod'] ?? null
                    );
                }
                
                $xml .= '</urlset>';
                
                file_put_contents($sitemapPath, $xml);
                $sitemapFiles[] = [
                    'filename' => $filename,
                    'url' => $this->baseUrl . '/' . $filename, // Utiliser baseUrl au lieu de url() pour éviter localhost
                    'urls_count' => count($urlChunk)
                ];
                
                Log::info("✅ Sitemap généré: {$filename} (" . count($urlChunk) . " URLs)");
            }
            
            // NE PAS créer de sitemap_index.xml - Google préfère sitemap.xml avec toutes les URLs
            // Si plusieurs sitemaps, on garde sitemap.xml avec 2000 URLs et les autres sitemap2.xml, sitemap3.xml, etc.
            // Google peut découvrir automatiquement les autres sitemaps via robots.txt ou soumission manuelle
            
            // Supprimer les anciens sitemaps qui ne sont plus nécessaires
            $this->cleanupOldSitemaps(count($sitemapFiles));
            
            Log::info("✅ Génération terminée: " . count($sitemapFiles) . " sitemap(s) créé(s)");
            
            return [
                'success' => true,
                'sitemaps' => $sitemapFiles,
                'total_urls' => count($allUrls)
            ];
            
        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la génération du sitemap : " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Collecter toutes les URLs du site
     */
    protected function collectAllUrls()
    {
        $urls = [];
        
        // Page d'accueil
        $urls[] = [
            'url' => $this->baseUrl,
            'priority' => 1.0,
            'changefreq' => 'daily',
            'lastmod' => Carbon::now()
        ];
            
            // Pages statiques (URLs doivent correspondre aux routes réelles pour éviter 404)
            $staticPages = [
                '/services' => ['priority' => 0.9, 'changefreq' => 'weekly'],
                '/portfolio' => ['priority' => 0.8, 'changefreq' => 'monthly'],
                '/reviews' => ['priority' => 0.8, 'changefreq' => 'weekly'],
                '/blog' => ['priority' => 0.7, 'changefreq' => 'weekly'],
                '/contact' => ['priority' => 0.6, 'changefreq' => 'monthly'],
                '/legal/mentions' => ['priority' => 0.3, 'changefreq' => 'yearly'],
                '/legal/privacy' => ['priority' => 0.3, 'changefreq' => 'yearly'],
                '/legal/cgv' => ['priority' => 0.3, 'changefreq' => 'yearly'],
            ];
            
        foreach ($staticPages as $path => $config) {
            $urls[] = [
                'url' => $this->baseUrl . $path,
                'priority' => $config['priority'],
                'changefreq' => $config['changefreq'],
                'lastmod' => Carbon::now()
            ];
            }
            
            // Services
            $services = $this->getServices();
            Log::info("📋 Ajout de " . count($services) . " services...");
            foreach ($services as $service) {
            $urls[] = [
                'url' => $this->baseUrl . '/services/' . $service,
                'priority' => 0.8,
                'changefreq' => 'monthly',
                'lastmod' => Carbon::now()
            ];
            }
            
            // Articles
            $articles = $this->getArticles();
            Log::info("📰 Ajout de " . count($articles) . " articles...");
            foreach ($articles as $article) {
            $urls[] = [
                'url' => $this->baseUrl . '/blog/' . $article['slug'],
                'priority' => 0.7,
                'changefreq' => 'monthly',
                'lastmod' => $article['updated_at'] ?? Carbon::now()
            ];
            }
            
            // Annonces
            $ads = $this->getAds();
            Log::info("📢 Ajout de " . count($ads) . " annonces...");
            foreach ($ads as $ad) {
            $urls[] = [
                // Utiliser le nouveau chemin canonique /ads/ (les anciennes URLs /annonces/ sont en 301)
                'url' => $this->baseUrl . '/ads/' . $ad['slug'],
                'priority' => 0.6,
                'changefreq' => 'monthly',
                'lastmod' => $ad['updated_at'] ?? Carbon::now()
            ];
            }
            
            // Portfolio (route réelle: /portfolio/{slug})
            $portfolio = $this->getPortfolio();
            Log::info("🖼️ Ajout de " . count($portfolio) . " éléments de portfolio...");
            foreach ($portfolio as $item) {
            $urls[] = [
                'url' => $this->baseUrl . '/portfolio/' . $item,
                'priority' => 0.5,
                'changefreq' => 'monthly',
                'lastmod' => Carbon::now()
            ];
            }
            
        return $urls;
    }

    /**
     * Générer un sitemap index (DÉSACTIVÉ - on n'utilise plus sitemap_index.xml dans public/)
     * Cette méthode est conservée pour compatibilité mais ne fait rien
     */
    protected function generateSitemapIndexOld($sitemapFiles)
    {
        // DÉSACTIVÉ : On ne génère plus de sitemap_index.xml dans public/
        // Google préfère sitemap.xml avec 2000 URLs et les autres sitemap2.xml, sitemap3.xml, etc.
        // Les autres sitemaps peuvent être découverts via robots.txt ou soumission manuelle
        Log::info("ℹ️ Sitemap index désactivé - utilisation de sitemap.xml avec 2000 URLs");
        
        // Supprimer sitemap_index.xml s'il existe dans public/
        $indexPath = public_path('sitemap_index.xml');
        if (file_exists($indexPath)) {
            unlink($indexPath);
            Log::info("🗑️ Sitemap index supprimé: sitemap_index.xml");
        }
    }

    /**
     * Nettoyer les anciens sitemaps
     */
    protected function cleanupOldSitemaps($currentCount)
    {
        $sitemapFiles = glob(public_path('sitemap*.xml'));
        
        foreach ($sitemapFiles as $file) {
            $filename = basename($file);
            
            // SUPPRIMER sitemap_index.xml (on n'en veut plus)
            if ($filename === 'sitemap_index.xml') {
                unlink($file);
                Log::info("🗑️ Sitemap index supprimé: {$filename}");
                continue;
            }
            
            // Ne pas supprimer sitemap.xml
            if ($filename === 'sitemap.xml') {
                continue;
            }
            
            // Vérifier si c'est un sitemap numéroté qui dépasse le nombre actuel
            if (preg_match('/^sitemap(\d+)\.xml$/', $filename, $matches)) {
                $number = (int)$matches[1];
                if ($number > $currentCount) {
                    unlink($file);
                    Log::info("🗑️ Ancien sitemap supprimé: {$filename}");
                }
            }
        }
    }

    /**
     * Générer une URL pour le sitemap
     */
    protected function generateUrl($url, $priority, $changefreq, $lastmod = null)
    {
        if ($lastmod === null) {
            $lastmod = Carbon::now();
        } elseif (is_string($lastmod)) {
            $lastmod = Carbon::parse($lastmod);
        }
        
        return '  <url>' . "\n" .
               '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n" .
               '    <lastmod>' . $lastmod->format('Y-m-d\TH:i:s+00:00') . '</lastmod>' . "\n" .
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
        
        return [];
    }

    /**
     * Récupérer les articles avec leurs dates de modification
     */
    protected function getArticles()
    {
        try {
            return Article::where('status', 'published')
                ->select('slug', 'updated_at')
                ->get()
                ->map(function($article) {
                    return [
                        'slug' => $article->slug,
                        'updated_at' => $article->updated_at
                    ];
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::warning("⚠️ Impossible de récupérer les articles : " . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Récupérer TOUTES les annonces publiées pour le sitemap.
     * Plus de limite volontaire sur le nombre d'annonces : toutes les pages
     * d'annonces actives sont déclarées à Google, qui gère ensuite l'indexation.
     */
    protected function getAds()
    {
        try {
            return Ad::where('status', 'published')
                ->orderBy('updated_at', 'desc')
                ->select('slug', 'updated_at')
                ->get()
                ->map(fn($ad) => ['slug' => $ad->slug, 'updated_at' => $ad->updated_at])
                ->toArray();
        } catch (\Exception $e) {
            Log::warning("⚠️ Impossible de récupérer les annonces : " . $e->getMessage());
        }

        return [];
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
        
        return [];
    }

    /**
     * Mettre à jour le sitemap automatiquement
     */
    public function updateSitemap()
    {
        return $this->generateSitemap();
    }

    /**
     * Générer un index de sitemap dans le dossier sitemap/
     * Cet index référence tous les sitemaps disponibles
     */
    public function generateSitemapIndex()
    {
        try {
            Log::info('📋 Génération de l\'index de sitemap dans sitemap/...');
            
            // S'assurer que le dossier sitemap existe
            $sitemapDir = public_path('sitemap');
            if (!is_dir($sitemapDir)) {
                mkdir($sitemapDir, 0755, true);
                Log::info("📁 Dossier sitemap créé: {$sitemapDir}");
            }
            
            // Trouver tous les fichiers sitemap*.xml dans public/
            $sitemapFiles = glob(public_path('sitemap*.xml'));
            $sitemapUrls = [];
            
            foreach ($sitemapFiles as $file) {
                $filename = basename($file);
                
                // Ignorer sitemap_index.xml s'il existe dans public/
                if ($filename === 'sitemap_index.xml') {
                    continue;
                }
                
                // Construire l'URL complète du sitemap
                $sitemapUrl = $this->baseUrl . '/' . $filename;
                $sitemapUrls[] = [
                    'url' => $sitemapUrl,
                    'lastmod' => file_exists($file) ? date('c', filemtime($file)) : Carbon::now()->format('c')
                ];
                
                Log::info("  ✓ Ajouté au index: {$filename}");
            }
            
            if (empty($sitemapUrls)) {
                Log::warning("⚠️ Aucun sitemap trouvé pour l'index");
                return [
                    'success' => false,
                    'error' => 'Aucun sitemap trouvé'
                ];
            }
            
            // Générer le XML de l'index
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
            
            foreach ($sitemapUrls as $sitemap) {
                $xml .= '  <sitemap>' . "\n";
                $xml .= '    <loc>' . htmlspecialchars($sitemap['url']) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . htmlspecialchars($sitemap['lastmod']) . '</lastmod>' . "\n";
                $xml .= '  </sitemap>' . "\n";
            }
            
            $xml .= '</sitemapindex>';
            
            // Sauvegarder l'index dans sitemap/sitemap_index.xml
            $indexPath = $sitemapDir . '/sitemap_index.xml';
            file_put_contents($indexPath, $xml);
            
            Log::info("✅ Index de sitemap créé: sitemap/sitemap_index.xml (" . count($sitemapUrls) . " sitemaps référencés)");
            
            return [
                'success' => true,
                'path' => $indexPath,
                'url' => $this->baseUrl . '/sitemap/sitemap_index.xml',
                'sitemaps_count' => count($sitemapUrls),
                'sitemaps' => $sitemapUrls
            ];
            
        } catch (\Exception $e) {
            Log::error("❌ Erreur lors de la génération de l'index de sitemap : " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer toutes les URLs de tous les sitemaps
     */
    public function getAllUrls()
    {
        $allUrls = [];
        $sitemapFiles = glob(public_path('sitemap*.xml'));
        
        foreach ($sitemapFiles as $file) {
            $filename = basename($file);
            
            // Ignorer le sitemap index
            if ($filename === 'sitemap_index.xml') {
                continue;
            }
            
            $xml = file_get_contents($file);
            $xml = simplexml_load_string($xml);
            
            if ($xml && isset($xml->url)) {
                foreach ($xml->url as $url) {
                    $allUrls[] = [
                        'url' => (string)$url->loc,
                        'lastmod' => isset($url->lastmod) ? (string)$url->lastmod : null,
                        'changefreq' => isset($url->changefreq) ? (string)$url->changefreq : null,
                        'priority' => isset($url->priority) ? (float)$url->priority : null,
                        'sitemap' => $filename
                    ];
                }
            }
        }
        
        return $allUrls;
    }
}
