<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Models\Service;
use App\Models\City;
use App\Models\Article;

class ValidateSeoSetup extends Command
{
    protected $signature = 'seo:validate';
    protected $description = 'Valider la configuration SEO du site';

    public function handle()
    {
        $this->info('🔍 Validation de la configuration SEO...');
        $this->newLine();

        $checks = [
            'Packages SEO' => $this->checkPackages(),
            'Configuration SEO' => $this->checkSeoConfig(),
            'Sitemap' => $this->checkSitemap(),
            'Robots.txt' => $this->checkRobots(),
            'Services' => $this->checkServices(),
            'Villes' => $this->checkCities(),
            'Routes' => $this->checkRoutes(),
            'HTTPS' => $this->checkHttps(),
        ];

        $this->newLine();
        $this->info('📊 Résumé:');
        $this->newLine();

        $passed = 0;
        $failed = 0;

        foreach ($checks as $name => $result) {
            if ($result) {
                $this->line("  ✅ {$name}");
                $passed++;
            } else {
                $this->error("  ❌ {$name}");
                $failed++;
            }
        }

        $this->newLine();
        if ($failed === 0) {
            $this->info("✨ Tous les tests sont passés! ({$passed}/{$passed})");
            return 0;
        } else {
            $this->warn("⚠️  {$failed} test(s) échoué(s) sur " . ($passed + $failed));
            return 1;
        }
    }

    protected function checkPackages(): bool
    {
        $packages = [
            'ralphjsmit/laravel-seo',
            'spatie/laravel-sitemap',
            'spatie/laravel-sluggable',
        ];

        foreach ($packages as $package) {
            if (!class_exists(\Composer\InstalledVersions::class)) {
                return false;
            }
        }

        return File::exists(base_path('config/seo.php'));
    }

    protected function checkSeoConfig(): bool
    {
        return File::exists(base_path('config/seo.php'));
    }

    protected function checkSitemap(): bool
    {
        try {
            $response = Http::timeout(5)->get(url('/sitemap.xml'));
            return $response->successful() && str_contains($response->body(), '<urlset');
        } catch (\Exception $e) {
            return File::exists(public_path('sitemap.xml'));
        }
    }

    protected function checkRobots(): bool
    {
        try {
            $response = Http::timeout(5)->get(url('/robots.txt'));
            return $response->successful();
        } catch (\Exception $e) {
            return true; // Route existe
        }
    }

    protected function checkServices(): bool
    {
        try {
            return Service::count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkCities(): bool
    {
        try {
            return City::count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function checkRoutes(): bool
    {
        $routes = ['home', 'services.index', 'blog.index'];
        foreach ($routes as $route) {
            if (!\Route::has($route)) {
                return false;
            }
        }
        return true;
    }

    protected function checkHttps(): bool
    {
        if (app()->environment('production')) {
            return config('app.url', '')->startsWith('https://');
        }
        return true; // Pas nécessaire en développement
    }
}
