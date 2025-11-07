<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Review;
use Illuminate\Http\Request;

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
            'reviews' // Pour Schema.org
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
                'hero' => [
                    'title' => Setting::get('company_name', 'Votre Entreprise'),
                    'subtitle' => 'Expert en ' . (Setting::get('company_specialization', 'Travaux de Rénovation')),
                    'cta_text' => 'Demander un Devis Gratuit',
                    'show_phone' => true,
                    'background_image' => null,
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
        
        return $config;
    }
}






