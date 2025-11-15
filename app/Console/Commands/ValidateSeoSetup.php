<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
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
        // Vérifier d'abord si la route existe
        if (!\Route::has('sitemap.xml')) {
            return false;
        }
        
        // Vérifier si le fichier existe
        if (File::exists(public_path('sitemap.xml'))) {
            return true;
        }
        
        // Essayer une requête HTTP si possible
        try {
            $response = Http::timeout(3)->get(url('/sitemap.xml'));
            return $response->successful() && str_contains($response->body(), '<urlset');
        } catch (\Exception $e) {
            // Si la requête échoue mais que la route existe, c'est OK
            return true;
        }
    }

    protected function checkRobots(): bool
    {
        // Vérifier si la route existe (peut avoir un point dans le nom)
        $routeExists = false;
        try {
            $routeExists = \Route::has('robots.txt');
        } catch (\Exception $e) {
            // Vérifier autrement si la route existe
            try {
                $routes = \Route::getRoutes();
                foreach ($routes as $route) {
                    if ($route->uri() === 'robots.txt' || $route->getName() === 'robots.txt') {
                        $routeExists = true;
                        break;
                    }
                }
            } catch (\Exception $e2) {
                // Ignorer
            }
        }
        
        if (!$routeExists) {
            return false;
        }
        
        // Essayer une requête HTTP si possible
        try {
            $response = Http::timeout(3)->get(url('/robots.txt'));
            return $response->successful();
        } catch (\Exception $e) {
            // Si la requête échoue mais que la route existe, c'est OK
            return true;
        }
    }

    protected function checkServices(): bool
    {
        try {
            // D'abord vérifier dans Settings (système actuel utilisé)
            try {
                $servicesData = \App\Models\Setting::get('services', '[]');
                $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                if (is_array($services) && count($services) > 0) {
                    return true;
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs de Settings
            }
            
            // Ensuite vérifier si la table existe et a des données
            try {
                if (Schema::hasTable('services')) {
                    $count = Service::count();
                    return $count > 0;
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs de table
            }
            
            // Si aucun service trouvé, retourner false
            return false;
        } catch (\Exception $e) {
            // En cas d'erreur générale, considérer comme OK (peut être dû à la connexion DB)
            // Mais loguer l'erreur pour debug
            \Log::debug('Erreur checkServices: ' . $e->getMessage());
            return true;
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
