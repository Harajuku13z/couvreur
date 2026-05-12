<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Article;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SitemapService
{
    protected string $baseUrl;
    protected int $maxUrlsPerSitemap = 45000;
    protected AdSeoAuditService $adSeoAuditService;

    public function __construct()
    {
        $this->adSeoAuditService = app(AdSeoAuditService::class);

        $siteUrl = null;

        try {
            $siteUrl = Setting::get('site_url', null);
        } catch (\Exception $e) {
            Log::warning('Impossible d\'accéder au setting site_url: ' . $e->getMessage());
        }

        if (empty($siteUrl)) {
            $siteUrl = config('app.url', 'http://localhost');
        }

        if (!preg_match('/^https?:\/\//', $siteUrl)) {
            $siteUrl = 'https://' . $siteUrl;
        }

        $this->baseUrl = rtrim($siteUrl, '/');
    }

    public function generateSitemap(): array
    {
        try {
            $buckets = $this->collectSitemapBuckets();
            $sitemapDir = public_path('sitemap');

            if (!is_dir($sitemapDir)) {
                mkdir($sitemapDir, 0755, true);
            }

            $writtenFiles = [];
            $indexEntries = [];

            foreach ($buckets as $bucketName => $urls) {
                $chunks = array_chunk($urls, $this->maxUrlsPerSitemap);

                foreach ($chunks as $chunkIndex => $chunk) {
                    $suffix = count($chunks) > 1 ? '-' . ($chunkIndex + 1) : '';
                    $filename = $bucketName . $suffix . '.xml';
                    $path = $sitemapDir . '/' . $filename;

                    file_put_contents($path, $this->renderUrlSet($chunk));

                    $writtenFiles[] = $filename;
                    $indexEntries[] = [
                        'filename' => $filename,
                        'url' => $this->baseUrl . '/sitemap/' . $filename,
                        'lastmod' => Carbon::now()->toAtomString(),
                        'urls_count' => count($chunk),
                    ];
                }
            }

            $this->cleanupSitemapDirectory($writtenFiles);
            $this->cleanupLegacyRootSitemaps();
            $indexXml = $this->renderSitemapIndex($indexEntries);

            file_put_contents(public_path('sitemap.xml'), $indexXml);
            file_put_contents(public_path('sitemap_index.xml'), $indexXml);
            file_put_contents($sitemapDir . '/sitemap_index.xml', $indexXml);

            return [
                'success' => true,
                'sitemaps' => $indexEntries,
                'total_urls' => collect($buckets)->flatten(1)->count(),
                'index_path' => public_path('sitemap.xml'),
                'index_url' => $this->baseUrl . '/sitemap.xml',
            ];
        } catch (\Throwable $e) {
            Log::error('❌ Erreur lors de la génération du sitemap: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateSitemapIndex(): array
    {
        $result = $this->generateSitemap();

        if (!$result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'path' => public_path('sitemap/sitemap_index.xml'),
            'url' => $this->baseUrl . '/sitemap.xml',
            'sitemaps_count' => count($result['sitemaps']),
            'sitemaps' => $result['sitemaps'],
        ];
    }

    public function updateSitemap(): array
    {
        return $this->generateSitemap();
    }

    public function getAllUrls(): array
    {
        $allUrls = [];
        $files = glob(public_path('sitemap/*.xml')) ?: [];

        foreach ($files as $file) {
            if (basename($file) === 'sitemap_index.xml') {
                continue;
            }

            $xml = @simplexml_load_file($file);
            if (!$xml || !isset($xml->url)) {
                continue;
            }

            foreach ($xml->url as $url) {
                $allUrls[] = [
                    'url' => (string) $url->loc,
                    'lastmod' => isset($url->lastmod) ? (string) $url->lastmod : null,
                    'changefreq' => isset($url->changefreq) ? (string) $url->changefreq : null,
                    'priority' => isset($url->priority) ? (float) $url->priority : null,
                    'sitemap' => basename($file),
                ];
            }
        }

        return $allUrls;
    }

    protected function collectSitemapBuckets(): array
    {
        $buckets = [
            'pages-core' => $this->collectCorePages(),
            'services' => $this->collectServicePages(),
            'articles' => $this->collectArticlePages(),
            'portfolio' => $this->collectPortfolioPages(),
        ];

        foreach ($this->getAds() as $ad) {
            $serviceSlug = $this->slugifyBucket($ad['service_slug'] ?: $ad['service_name'] ?: $ad['keyword']);
            $department = $this->slugifyBucket($ad['department'] ?: 'sans-departement');
            $bucketName = $serviceSlug ? 'ads-service-' . $serviceSlug : 'ads-department-' . $department;

            $buckets[$bucketName][] = [
                'url' => $this->baseUrl . '/ads/' . $ad['slug'],
                'priority' => 0.75,
                'changefreq' => 'monthly',
                'lastmod' => $ad['updated_at'] ?? Carbon::now(),
            ];
        }

        return collect($buckets)
            ->map(fn ($urls) => array_values(array_filter($urls)))
            ->filter(fn ($urls) => !empty($urls))
            ->toArray();
    }

    protected function collectCorePages(): array
    {
        $pages = [
            ['path' => '', 'priority' => 1.0, 'changefreq' => 'daily'],
            ['path' => '/services', 'priority' => 0.9, 'changefreq' => 'weekly'],
            ['path' => '/portfolio', 'priority' => 0.7, 'changefreq' => 'monthly'],
            ['path' => '/reviews', 'priority' => 0.7, 'changefreq' => 'weekly'],
            ['path' => '/blog', 'priority' => 0.7, 'changefreq' => 'weekly'],
            ['path' => '/contact', 'priority' => 0.6, 'changefreq' => 'monthly'],
            ['path' => '/legal/mentions', 'priority' => 0.3, 'changefreq' => 'yearly'],
            ['path' => '/legal/privacy', 'priority' => 0.3, 'changefreq' => 'yearly'],
            ['path' => '/legal/cgv', 'priority' => 0.3, 'changefreq' => 'yearly'],
        ];

        return array_map(function ($page) {
            return [
                'url' => $this->baseUrl . $page['path'],
                'priority' => $page['priority'],
                'changefreq' => $page['changefreq'],
                'lastmod' => Carbon::now(),
            ];
        }, $pages);
    }

    protected function collectServicePages(): array
    {
        $urls = [];

        foreach ($this->getServices() as $service) {
            $urls[] = [
                'url' => $this->baseUrl . '/services/' . $service,
                'priority' => 0.8,
                'changefreq' => 'monthly',
                'lastmod' => Carbon::now(),
            ];
        }

        return $urls;
    }

    protected function collectArticlePages(): array
    {
        $urls = [];

        foreach ($this->getArticles() as $article) {
            $urls[] = [
                'url' => $this->baseUrl . '/blog/' . $article['slug'],
                'priority' => 0.7,
                'changefreq' => 'weekly',
                'lastmod' => $article['updated_at'] ?? Carbon::now(),
            ];
        }

        return $urls;
    }

    protected function collectPortfolioPages(): array
    {
        $urls = [];

        foreach ($this->getPortfolio() as $item) {
            $urls[] = [
                'url' => $this->baseUrl . '/portfolio/' . $item,
                'priority' => 0.5,
                'changefreq' => 'monthly',
                'lastmod' => Carbon::now(),
            ];
        }

        return $urls;
    }

    protected function renderUrlSet(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $urlData) {
            $lastmod = $urlData['lastmod'] ?? Carbon::now();
            if (is_string($lastmod)) {
                $lastmod = Carbon::parse($lastmod);
            }

            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($urlData['url'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . $lastmod->toAtomString() . "</lastmod>\n";
            $xml .= '    <changefreq>' . $urlData['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $urlData['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    protected function renderSitemapIndex(array $entries): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($entries as $entry) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>' . htmlspecialchars($entry['url'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . htmlspecialchars($entry['lastmod'], ENT_XML1) . "</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    protected function cleanupSitemapDirectory(array $allowedFiles): void
    {
        $files = glob(public_path('sitemap/*.xml')) ?: [];
        $allowedFiles[] = 'sitemap_index.xml';

        foreach ($files as $file) {
            if (!in_array(basename($file), $allowedFiles, true)) {
                @unlink($file);
            }
        }
    }

    protected function cleanupLegacyRootSitemaps(): void
    {
        $legacyFiles = glob(public_path('sitemap*.xml')) ?: [];

        foreach ($legacyFiles as $file) {
            $basename = basename($file);
            if (in_array($basename, ['sitemap.xml', 'sitemap_index.xml'], true)) {
                continue;
            }

            @unlink($file);
        }
    }

    protected function getServices(): array
    {
        try {
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);

            if (is_array($services)) {
                return collect($services)
                    ->filter(fn ($service) => ($service['is_visible'] ?? true) && ($service['is_active'] ?? true))
                    ->pluck('slug')
                    ->filter()
                    ->values()
                    ->toArray();
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ Impossible de récupérer les services: ' . $e->getMessage());
        }

        return [];
    }

    protected function getArticles(): array
    {
        try {
            return Article::where('status', 'published')
                ->select('slug', 'updated_at')
                ->get()
                ->map(fn ($article) => [
                    'slug' => $article->slug,
                    'updated_at' => $article->updated_at,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::warning('⚠️ Impossible de récupérer les articles: ' . $e->getMessage());
        }

        return [];
    }

    protected function getAds(): array
    {
        try {
            return Ad::with(['city:id,department', 'template:id,service_slug,service_name'])
                ->where('status', 'published')
                ->orderByDesc('updated_at')
                ->get()
                ->filter(fn ($ad) => $this->adSeoAuditService->analyze($ad)['is_indexable'])
                ->map(fn ($ad) => [
                    'slug' => $ad->slug,
                    'updated_at' => $ad->updated_at,
                    'keyword' => $ad->keyword,
                    'department' => $ad->city->department ?? null,
                    'service_slug' => $ad->template->service_slug ?? null,
                    'service_name' => $ad->template->service_name ?? null,
                ])
                ->toArray();
        } catch (\Exception $e) {
            Log::warning('⚠️ Impossible de récupérer les annonces: ' . $e->getMessage());
        }

        return [];
    }

    protected function getPortfolio(): array
    {
        try {
            $portfolioData = Setting::get('portfolio_items', '[]');
            $portfolio = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);

            if (is_array($portfolio)) {
                return collect($portfolio)
                    ->filter(fn ($item) => ($item['is_visible'] ?? true))
                    ->pluck('slug')
                    ->filter()
                    ->values()
                    ->toArray();
            }
        } catch (\Exception $e) {
            Log::warning('⚠️ Impossible de récupérer le portfolio: ' . $e->getMessage());
        }

        return [];
    }

    protected function slugifyBucket(?string $value): string
    {
        $slug = Str::slug((string) $value);

        return $slug !== '' ? $slug : 'generic';
    }
}
