<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Review;
use App\Models\City;
use App\Support\FrenchDepartments;
use App\Services\DepartmentTopCitiesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $allSettings = Setting::getAll();

        // Get homepage configuration
        $homeConfig = $this->getHomeConfig($allSettings);
        
        // Set current page for SEO
        $currentPage = 'home';
        
        // Get services
        $servicesData = $this->settingValue($allSettings, 'services', '[]');
        $allServices = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // Si pas de services, créer des services par défaut
        if (empty($allServices)) {
            $allServices = [
                [
                    'name' => 'Demoussage de Toiture',
                    'description' => 'Service professionnel de demoussage pour redonner vie à votre toiture',
                    'image' => '',
                    'slug' => 'demoussage',
                    'is_featured' => true
                ],
                [
                    'name' => 'Réparation de Toiture',
                    'description' => 'Réparations et rénovations de toiture par nos experts',
                    'image' => '',
                    'slug' => 'reparation-toiture',
                    'is_featured' => true
                ],
                [
                    'name' => 'Couvreur Professionnel',
                    'description' => 'Services de couverture par des professionnels qualifiés',
                    'image' => '',
                    'slug' => 'couvreur',
                    'is_featured' => true
                ]
            ];
        }
        
        // Filtrer seulement les services mis en avant
        $services = array_filter($allServices, function($service) {
            return is_array($service) && ($service['is_featured'] ?? false) && ($service['is_visible'] ?? true);
        });
        
        // Get portfolio items (réalisations)
        $portfolioData = $this->settingValue($allSettings, 'portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        // Trier les réalisations par date de création/modification décroissante (plus récentes en premier)
        if (is_array($portfolioItems)) {
            usort($portfolioItems, function($a, $b) {
                $dateA = $a['created_at'] ?? $a['updated_at'] ?? '1970-01-01';
                $dateB = $b['created_at'] ?? $b['updated_at'] ?? '1970-01-01';
                return strtotime($dateB) - strtotime($dateA);
            });
        }
        
        // Si pas de portfolio, créer des réalisations par défaut
        if (empty($portfolioItems)) {
            $portfolioItems = [
                [
                    'title' => 'Rénovation Toiture Chilly',
                    'description' => 'Rénovation complète d\'une toiture à Chilly avec matériaux de qualité',
                    'images' => [],
                    'slug' => 'renovation-toiture-chilly',
                    'is_visible' => true
                ],
                [
                    'title' => 'Demoussage Professionnel',
                    'description' => 'Demoussage et nettoyage d\'une toiture ancienne',
                    'images' => [],
                    'slug' => 'demoussage-professionnel',
                    'is_visible' => true
                ]
            ];
        }
        
        // S'assurer que tous les éléments du portfolio ont un slug
        foreach ($portfolioItems as &$item) {
            if (!isset($item['slug']) || empty($item['slug'])) {
                $item['slug'] = \Illuminate\Support\Str::slug($item['title'] ?? 'realisation');
            }
        }
        
        // Get reviews — cached 10 min
        $reviewsData = Cache::remember('home_reviews', 600, function () {
            $list = Review::where('is_active', true)
                ->orderBy('review_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();
            $avg = Review::where('is_active', true)->avg('rating') ?? 5;
            $cnt = Review::where('is_active', true)->count();
            $subs = \App\Models\Submission::where('status', 'COMPLETED')->count();
            return compact('list', 'avg', 'cnt', 'subs');
        });
        $reviews = $reviewsData['list'];
        $averageRating = $reviewsData['avg'];
        $totalReviews  = $reviewsData['cnt'];
        $completedSubmissions = $reviewsData['subs'];
        $trustCounter = max(100, 100 + $completedSubmissions);
        
        // Get company settings
        $companySettings = [
            'name' => $this->settingValue($allSettings, 'company_name', 'Votre Entreprise'),
            'phone' => $this->settingValue($allSettings, 'company_phone', ''),
            'phone_raw' => $this->settingValue($allSettings, 'company_phone_raw', ''),
            'phone_2' => $this->settingValue($allSettings, 'company_phone_2', ''),
            'phone_2_raw' => $this->settingValue($allSettings, 'company_phone_2_raw', ''),
            'email' => $this->settingValue($allSettings, 'company_email', ''),
            'address' => $this->settingValue($allSettings, 'company_address', ''),
            'city' => $this->settingValue($allSettings, 'company_city', 'Paris'),
            'region' => $this->settingValue($allSettings, 'company_region', 'Île-de-France'),
            'description' => $this->settingValue($allSettings, 'company_description', ''),
            'certifications' => $this->settingValue($allSettings, 'company_certifications', ''),
            'specialization' => $this->settingValue($allSettings, 'company_specialization', 'Travaux de Rénovation'),
            'logo' => $this->settingValue($allSettings, 'company_logo', ''),
        ];
        
        // Villes principales — cached 1h
        $favoriteCities = Cache::remember('home_favorite_cities', 3600, function () {
            return City::where('is_favorite', true)
                ->where('is_active', true)
                ->orderBy('name')
                ->take(12)
                ->get();
        });
        
        // Get branding colors
        $branding = [
            'primary_color' => $this->settingValue($allSettings, 'primary_color', '#3b82f6'),
            'secondary_color' => $this->settingValue($allSettings, 'secondary_color', '#10b981'),
            'accent_color' => $this->settingValue($allSettings, 'accent_color', '#f59e0b'),
        ];
        
        // Préparer les variables SEO pour la page d'accueil
        $pageTitle = null; // Sera géré par SeoHelper
        $pageDescription = null; // Sera géré par SeoHelper
        $pageImage = null; // Sera géré par SeoHelper
        
        // Breadcrumbs pour la page d'accueil
        $breadcrumbs = [
            ['name' => 'Accueil', 'url' => route('home')]
        ];
        
        // FAQ (peut être configuré dans les settings)
        $faqsData = $this->settingValue($allSettings, 'faqs', '[]');
        $faqs = is_string($faqsData) ? json_decode($faqsData, true) : ($faqsData ?? []);
        if (!is_array($faqs)) {
            $faqs = [];
        }

        $departmentsMap = $this->buildDepartmentsMapViewData($homeConfig);

        $response = view('home', compact(
            'homeConfig',
            'services',
            'portfolioItems',
            'reviews',
            'averageRating',
            'totalReviews',
            'companySettings',
            'branding',
            'currentPage',
            'pageTitle',
            'pageDescription',
            'pageImage',
            'trustCounter',
            'completedSubmissions',
            'breadcrumbs',
            'faqs',
            'reviews', // Pour Schema.org
            'favoriteCities',
            'departmentsMap'
        ));

        // Cache HTTP côté navigateur/CDN : 2 min public, revalidation
        return response($response)->header('Cache-Control', 'public, max-age=120, stale-while-revalidate=60');
    }
    
    /**
     * Get or generate homepage configuration
     */
    private function getHomeConfig(array $allSettings = [])
    {
        $config = $this->settingValue($allSettings, 'homepage_config', null);
        
        if ($config && is_string($config)) {
            $config = json_decode($config, true);
        }
        
        // Default configuration
        if (!$config) {
            $config = [
                'layout' => 'classic',
                'hero' => [
                    'title' => $this->settingValue($allSettings, 'company_name', 'Votre Entreprise'),
                    'subtitle' => 'Expert en ' . $this->settingValue($allSettings, 'company_specialization', 'Travaux de Rénovation'),
                    'cta_text' => 'Demander un Devis Gratuit',
                    'show_phone' => true,
                    'background_image' => null,
                    'magazine_side_image' => null,
                ],
                'sections' => [
                    'services' => ['enabled' => true, 'title' => 'Nos Services', 'limit' => 6],
                    'portfolio' => ['enabled' => true, 'title' => 'Nos Réalisations', 'limit' => 6],
                    'reviews' => ['enabled' => true, 'title' => 'Avis de Nos Clients', 'limit' => 6],
                    'about' => ['enabled' => true, 'title' => 'Pourquoi Nous Choisir?'],
                    'cta' => ['enabled' => true, 'title' => 'Prêt à Démarrer Votre Projet?'],
                ],
                'stats' => [
                    ['label' => 'Projets Réalisés', 'value' => '500+', 'icon' => 'fa-check-circle'],
                    ['label' => 'Clients Satisfaits', 'value' => '98%', 'icon' => 'fa-smile'],
                    ['label' => 'Années d\'Expérience', 'value' => '15+', 'icon' => 'fa-award'],
                    ['label' => 'Garantie', 'value' => '10 ans', 'icon' => 'fa-shield-alt'],
                ],
            ];
        }

        $validHomeLayouts = ['classic', 'showcase', 'magazine', 'conversion'];
        if (!isset($config['layout']) || !in_array($config['layout'], $validHomeLayouts, true)) {
            $config['layout'] = 'classic';
        }

        if (!isset($config['hero']) || !is_array($config['hero'])) {
            $config['hero'] = [];
        }
        if (!array_key_exists('magazine_side_image', $config['hero'])) {
            $config['hero']['magazine_side_image'] = null;
        }

        if (!isset($config['ecology']) || !is_array($config['ecology'])) {
            $config['ecology'] = [];
        }
        $config['ecology']['badges'] = array_merge(
            ['materiaux_recycles' => true, 'energies_vertes' => true],
            $config['ecology']['badges'] ?? []
        );
        if (!isset($config['financing']) || !is_array($config['financing'])) {
            $config['financing'] = [];
        }
        $config['financing']['badges'] = array_merge(
            ['maprimerenov' => true, 'certificats_cee' => true],
            $config['financing']['badges'] ?? []
        );

        if (!isset($config['departments_map']) || !is_array($config['departments_map'])) {
            $config['departments_map'] = [
                'enabled' => false,
                'title' => 'Nos départements d\'intervention',
                'subtitle' => '',
                'codes' => [],
                'link_overrides' => [],
            ];
        }
        
        return $config;
    }

    /**
     * Données pour la carte France (Leaflet + GeoJSON) — départements configurés par code.
     *
     * @return array{show: bool, title: string, subtitle: string, items: array<int, array{code: string, name: string, url: string}>, geoJsonUrl: string}
     */
    private function buildDepartmentsMapViewData(array $homeConfig): array
    {
        $dm = $homeConfig['departments_map'] ?? [];
        $default = [
            'show' => false,
            'title' => 'Nos départements d\'intervention',
            'subtitle' => '',
            'items' => [],
            'geoJsonUrl' => asset('geo/departements.geojson'),
        ];

        if (!($dm['enabled'] ?? false)) {
            return $default;
        }

        $cacheKey = 'home_departments_map:' . md5(json_encode($dm));

        return Cache::remember($cacheKey, 3600, function () use ($dm, $default) {
            return $this->computeDepartmentsMapViewData($dm, $default);
        });
    }

    /**
     * @param array<string, mixed> $dm
     * @param array<string, mixed> $default
     */
    private function computeDepartmentsMapViewData(array $dm, array $default): array
    {
        $codes = $dm['codes'] ?? [];
        if (!is_array($codes)) {
            $codes = [];
        }

        $overrides = $dm['link_overrides'] ?? [];
        if (is_string($overrides)) {
            $decoded = json_decode($overrides, true);
            $overrides = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($overrides)) {
            $overrides = [];
        }

        $hasPopulationColumn = Cache::rememberForever('cities:has_population_column', function () {
            return Schema::hasColumn('cities', 'population');
        });
        $topCitiesService = $hasPopulationColumn ? null : app(DepartmentTopCitiesService::class);
        $departmentMeta = [];

        foreach ($codes as $raw) {
            $code = FrenchDepartments::normalizeCode((string) $raw);
            $name = FrenchDepartments::nameFromCode($code);
            if ($name === null) {
                continue;
            }

            $aliases = [$name, $code];
            if (preg_match('/^\d{2,3}$/', (string) $code)) {
                $aliases[] = (string) (int) $code;
            }

            $departmentMeta[$code] = [
                'code' => $code,
                'raw' => (string) $raw,
                'name' => $name,
                'aliases' => array_values(array_unique(array_filter($aliases))),
            ];
        }

        if ($departmentMeta === []) {
            return $default;
        }

        $citiesByDepartmentCode = [];

        if ($hasPopulationColumn) {
            $allAliases = [];
            foreach ($departmentMeta as $meta) {
                $allAliases = array_merge($allAliases, $meta['aliases']);
            }

            $candidateCities = City::query()
                ->where('is_active', true)
                ->whereIn('department', array_values(array_unique($allAliases)))
                ->orderByDesc('is_favorite')
                ->orderByRaw('COALESCE(population, 0) DESC')
                ->orderBy('name')
                ->get();

            foreach ($departmentMeta as $code => $meta) {
                $citiesByDepartmentCode[$code] = $candidateCities
                    ->filter(fn ($city) => in_array((string) $city->department, $meta['aliases'], true))
                    ->take(20)
                    ->values();
            }
        }

        $items = [];
        foreach ($departmentMeta as $code => $meta) {
            $name = $meta['name'];
            $raw = $meta['raw'];

            $url = $overrides[$code] ?? $overrides[$raw] ?? $overrides[(string) (int) $code] ?? null;
            if (empty($url)) {
                if ($hasPopulationColumn) {
                    $city = $citiesByDepartmentCode[$code]->first() ?? null;

                    $url = $city
                        ? route('ads.index') . '?city=' . urlencode($city->slug)
                        : route('contact');
                } else {
                    $top = $topCitiesService?->getTopCitiesByPopulation($name, 20) ?? [];
                    $first = $top[0] ?? null;

                    $city = null;
                    if ($first && !empty($first['postal_code'])) {
                        $city = City::query()
                            ->where('is_active', true)
                            ->where('department', $name)
                            ->where('postal_code', (string) $first['postal_code'])
                            ->first();
                    }
                    if (!$city && $first && !empty($first['name'])) {
                        $slug = \Illuminate\Support\Str::slug((string) $first['name']);
                        $city = City::query()
                            ->where('is_active', true)
                            ->where('department', $name)
                            ->where('slug', $slug)
                            ->first();
                    }

                    if ($city) {
                        $url = route('ads.index') . '?city=' . urlencode($city->slug);
                    } else {
                        $url = $first && !empty($first['name'])
                            ? route('ads.index') . '?city=' . urlencode(\Illuminate\Support\Str::slug((string) $first['name']))
                            : route('contact');
                    }
                }
            }

            if ($hasPopulationColumn) {
                $citiesInDept = $citiesByDepartmentCode[$code] ?? collect();

                $citiesForView = $citiesInDept->map(function ($c) {
                    return [
                        'name' => $c->name,
                        'url' => route('ads.index') . '?city=' . urlencode($c->slug),
                    ];
                })->values()->all();
            } else {
                // Sans colonne population sur `cities` : on récupère un top 20 par population (cache) et on affiche uniquement ces 20 villes.
                $top = $topCitiesService?->getTopCitiesByPopulation($name, 20) ?? [];
                $citiesForView = [];
                foreach ($top as $t) {
                    $city = null;
                    if (!empty($t['postal_code'])) {
                        $city = City::query()
                            ->where('is_active', true)
                            ->where('department', $name)
                            ->where('postal_code', (string) $t['postal_code'])
                            ->first();
                    }
                    if (!$city && !empty($t['name'])) {
                        $slug = \Illuminate\Support\Str::slug((string) $t['name']);
                        $city = City::query()
                            ->where('is_active', true)
                            ->where('department', $name)
                            ->where('slug', $slug)
                            ->first();
                    }

                    $citiesForView[] = [
                        'name' => $t['name'] ?? '',
                        'url' => $city
                            ? route('ads.index') . '?city=' . urlencode($city->slug)
                            : route('ads.index') . '?city=' . urlencode(\Illuminate\Support\Str::slug((string) ($t['name'] ?? ''))),
                    ];
                }
            }

            $items[] = [
                'code' => $code,
                'name' => $name,
                'url' => $url,
                'cities' => $citiesForView,
            ];
        }

        if ($items === []) {
            return $default;
        }

        $rawSubtitle = trim((string) ($dm['subtitle'] ?? ''));
        $discardedSubtitles = [
            'Cliquez sur un département mis en avant sur la carte.',
            'Cliquez sur un département mis en avant pour en savoir plus.',
        ];
        $subtitle = in_array($rawSubtitle, $discardedSubtitles, true) ? '' : $rawSubtitle;

        return [
            'show' => true,
            'title' => (string) ($dm['title'] ?? $default['title']),
            'subtitle' => $subtitle,
            'items' => $items,
            'geoJsonUrl' => $default['geoJsonUrl'],
        ];
    }

    private function settingValue(array $allSettings, string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $allSettings) ? $allSettings[$key] : $default;
    }
}




