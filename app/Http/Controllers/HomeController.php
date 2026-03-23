<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Review;
use App\Models\City;
use App\Support\FrenchDepartments;
use App\Services\DepartmentTopCitiesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        // Get homepage configuration
        $homeConfig = $this->getHomeConfig();
        
        // Set current page for SEO
        $currentPage = 'home';
        
        // Get services
        $servicesData = Setting::get('services', '[]');
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
        $portfolioData = Setting::get('portfolio_items', '[]');
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
        
        // Get reviews
        $reviews = Review::where('is_active', true)
            ->orderBy('review_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();
        
        // Calculate average rating
        $averageRating = Review::where('is_active', true)->avg('rating') ?? 5;
        $totalReviews = Review::where('is_active', true)->count();
        
        // Compteur de confiance : minimum 100 + nombre de submissions réussies
        $completedSubmissions = \App\Models\Submission::where('status', 'COMPLETED')->count();
        $trustCounter = max(100, 100 + $completedSubmissions); // Minimum 100, puis + submissions réussies
        
        // Get company settings
        $companySettings = [
            'name' => Setting::get('company_name', 'Votre Entreprise'),
            'phone' => Setting::get('company_phone', ''),
            'email' => Setting::get('company_email', ''),
            'address' => Setting::get('company_address', ''),
            'city' => Setting::get('company_city', 'Paris'),
            'region' => Setting::get('company_region', 'Île-de-France'),
            'description' => Setting::get('company_description', ''),
            'certifications' => Setting::get('company_certifications', ''),
        ];
        
        // Villes principales (pour le maillage interne sur la home)
        $favoriteCities = City::where('is_favorite', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->take(12)
            ->get();
        
        // Get branding colors
        $branding = [
            'primary_color' => Setting::get('primary_color', '#3b82f6'),
            'secondary_color' => Setting::get('secondary_color', '#10b981'),
            'accent_color' => Setting::get('accent_color', '#f59e0b'),
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
        $faqsData = Setting::get('faqs', '[]');
        $faqs = is_string($faqsData) ? json_decode($faqsData, true) : ($faqsData ?? []);
        if (!is_array($faqs)) {
            $faqs = [];
        }

        $departmentsMap = $this->buildDepartmentsMapViewData($homeConfig);
        
        return view('home', compact(
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
    }
    
    /**
     * Get or generate homepage configuration
     */
    private function getHomeConfig()
    {
        $config = Setting::get('homepage_config', null);
        
        if ($config && is_string($config)) {
            $config = json_decode($config, true);
        }
        
        // Default configuration
        if (!$config) {
            $config = [
                'layout' => 'classic',
                'hero' => [
                    'title' => Setting::get('company_name', 'Votre Entreprise'),
                    'subtitle' => 'Expert en ' . (Setting::get('company_specialization', 'Travaux de Rénovation')),
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

        $hasPopulationColumn = Schema::hasColumn('cities', 'population');
        $topCitiesService = $hasPopulationColumn ? null : app(DepartmentTopCitiesService::class);

        $items = [];
        foreach ($codes as $raw) {
            $code = FrenchDepartments::normalizeCode((string) $raw);
            $name = FrenchDepartments::nameFromCode($code);
            if ($name === null) {
                continue;
            }

            $url = $overrides[$code] ?? $overrides[$raw] ?? $overrides[(string) (int) $code] ?? null;
            if (empty($url)) {
                if ($hasPopulationColumn) {
                    $city = City::query()
                        ->where('is_active', true)
                        ->where('department', $name)
                        ->orderByDesc('is_favorite')
                        ->orderByRaw('COALESCE(population, 0) DESC')
                        ->orderBy('name')
                        ->first();

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
                $citiesInDept = City::query()
                    ->where('is_active', true)
                    ->where(function ($q) use ($name, $code) {
                        $q->where('department', $name)
                            ->orWhere('department', $code);
                        // Certaines bases enregistrent le département en chiffres seuls (ex. 49 ou 9 pour 09)
                        if (preg_match('/^\d{2,3}$/', (string) $code)) {
                            $q->orWhere('department', (string) (int) $code);
                        }
                    })
                    ->orderByDesc('is_favorite')
                    ->orderByRaw('COALESCE(population, 0) DESC')
                    ->orderBy('name')
                    ->limit(20)
                    ->get();

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
}






