<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Service;
use App\Models\Ad;
use App\Models\Article;
use App\Models\City;
use App\Models\Setting;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap using Spatie Laravel Sitemap';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Génération du sitemap en cours...');
        
        $sitemap = Sitemap::create();
        
        // Page d'accueil
        $sitemap->add(Url::create('/')
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
            $sitemap->add(Url::create($url)
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
        
        // Filtrer les services visibles
        $visibleServices = collect($services)->filter(function($service) {
            return ($service['is_visible'] ?? true) && ($service['is_active'] ?? true);
        });
        
        $this->info("📋 Ajout de {$visibleServices->count()} services...");
        
        foreach ($visibleServices as $service) {
            if (isset($service['slug'])) {
                $sitemap->add(Url::create('/services/' . $service['slug'])
                    ->setLastModificationDate(Carbon::parse($service['updated_at'] ?? $service['created_at'] ?? now()))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8));
            }
        }
        
        // Articles
        $articles = Article::where('status', 'published')->get();
        $this->info("📰 Ajout de {$articles->count()} articles...");
        
        foreach ($articles as $article) {
            $sitemap->add(Url::create('/blog/' . $article->slug)
                ->setLastModificationDate($article->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7));
        }
        
        // Annonces (limitées pour éviter un sitemap trop volumineux)
        $ads = Ad::whereIn('status', ['published', 'draft'])
            ->orderBy('updated_at', 'desc')
            ->limit(5000)
            ->get();
        $this->info("📢 Ajout de {$ads->count()} annonces...");
        
        foreach ($ads as $ad) {
            $sitemap->add(Url::create('/annonces/' . $ad->slug)
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
                $sitemap->add(Url::create('/nos-realisations/' . $item['slug'])
                    ->setLastModificationDate(Carbon::now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.5));
            }
        }
        
        // Sauvegarder le sitemap
        $sitemapPath = public_path('sitemap.xml');
        $sitemap->writeToFile($sitemapPath);
        
        $this->info("✅ Sitemap généré avec succès : {$sitemapPath}");
        $this->info("🌐 URL du sitemap : " . url('/sitemap.xml'));
        
        return 0;
    }
}
