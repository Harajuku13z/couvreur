<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class PortfolioController extends Controller
{
    /**
     * Afficher la liste des réalisations
     */
    public function index()
    {
        try {
            // Récupérer les éléments du portfolio depuis les settings
            $portfolioData = Setting::get('portfolio_items', '[]');
            $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        } catch (\Exception $e) {
            // Si la base de données n'est pas accessible, utiliser les données de test
            \Log::warning('Base de données non accessible, utilisation des données de test', ['error' => $e->getMessage()]);
            $portfolioItems = $this->getTestPortfolioData();
        }
        
        // S'assurer que $portfolioItems est toujours un tableau
        if (!is_array($portfolioItems)) {
            $portfolioItems = [];
        }
        
        // Ajouter un ID aux éléments qui n'en ont pas
        foreach ($portfolioItems as $index => &$item) {
            if (!isset($item['id'])) {
                $item['id'] = time() . rand(1000, 9999) . '_' . $index;
            }
        }
        
        // Détecter si c'est un accès admin ou public
        if (request()->is('admin/portfolio*')) {
            // Accès admin - vue complète avec gestion
            return view('admin.portfolio', compact('portfolioItems'));
        }
        
        // Accès public - vue simple avec seulement les éléments visibles
        // Filtrer les éléments visibles
        $visiblePortfolio = collect(array_filter($portfolioItems, function($item) {
            return isset($item['is_visible']) ? $item['is_visible'] : true;
        }));
        
        // Trier par date de création/modification décroissante (plus récentes en premier)
        $visiblePortfolio = $visiblePortfolio->sortByDesc(function($item) {
            $date = $item['created_at'] ?? $item['updated_at'] ?? '1970-01-01';
            return strtotime($date);
        });
        
        // Récupérer les types de services uniques pour les filtres
        $serviceTypes = $visiblePortfolio->pluck('work_type')->unique()->filter()->values()->toArray();
        
        // Récupérer les métadonnées SEO personnalisées pour la page portfolio
        try {
            $seoMeta = \App\Helpers\SeoHelper::getPageSeo('portfolio', [
                'meta_title' => 'Nos Réalisations',
                'meta_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
                'og_title' => 'Nos Réalisations',
                'og_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
            ]);
        } catch (\Exception $e) {
            // Fallback si la base de données n'est pas accessible
            $seoMeta = [
                'meta_title' => 'Nos Réalisations',
                'meta_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
                'og_title' => 'Nos Réalisations',
                'og_description' => 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet',
            ];
        }
        
        // Définir la page courante pour le SEO
        $currentPage = 'portfolio';
        
        // Préparer les variables SEO pour le layout
        $pageTitle = $seoMeta['meta_title'] ?? 'Nos Réalisations';
        $pageDescription = $seoMeta['meta_description'] ?? 'Découvrez quelques-unes de nos réalisations récentes et laissez-vous inspirer pour votre prochain projet';
        $pageImage = null;
        
        // Image par défaut pour le portfolio
        $defaultPortfolioImage = 'images/og-realisations.jpg';
        if (file_exists(public_path($defaultPortfolioImage))) {
            $pageImage = asset($defaultPortfolioImage);
        } else {
            $companyLogo = setting('company_logo');
            if ($companyLogo) {
                $pageImage = asset($companyLogo);
            }
        }
        
        return view('portfolio.index', compact('visiblePortfolio', 'serviceTypes', 'seoMeta', 'currentPage', 'pageTitle', 'pageDescription', 'pageImage'));
    }
    
    /**
     * Afficher les détails d'une réalisation
     */
    public function show($slug)
    {
        try {
            // Récupérer les éléments du portfolio depuis les settings
            $portfolioData = Setting::get('portfolio_items', '[]');
            $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        } catch (\Exception $e) {
            // Si la base de données n'est pas accessible, utiliser les données de test
            \Log::warning('Base de données non accessible, utilisation des données de test', ['error' => $e->getMessage()]);
            $portfolioItems = $this->getTestPortfolioData();
        }
        
        // Trouver l'élément par slug (titre slugifié) ou par ID (fallback)
        $portfolioItem = null;
        foreach ($portfolioItems as $item) {
            $itemSlug = $this->generateSlug($item['title'] ?? '');
            if ($itemSlug === $slug || (isset($item['id']) && $item['id'] == $slug)) {
                $portfolioItem = $item;
                break;
            }
        }
        
        if (!$portfolioItem) {
            abort(404, 'Réalisation non trouvée');
        }
        
        // S'assurer que les métadonnées SEO existent, sinon les générer
        if (empty($portfolioItem['meta_title']) || empty($portfolioItem['meta_description']) || empty($portfolioItem['meta_keywords'])) {
            $portfolioItem = $this->generateMissingSEO($portfolioItem);
        }
        
        // Préparer les métadonnées SEO pour le layout
        $pageTitle = $portfolioItem['meta_title'] ?? $portfolioItem['title'] . ' - Nos Réalisations';
        $pageDescription = $portfolioItem['meta_description'] ?? $portfolioItem['description'] ?? '';
        $pageKeywords = $portfolioItem['meta_keywords'] ?? '';
        
        // Image Open Graph
        $pageImage = null;
        if (!empty($portfolioItem['og_image'])) {
            $pageImage = asset($portfolioItem['og_image']);
        } elseif (!empty($portfolioItem['images']) && is_array($portfolioItem['images']) && !empty($portfolioItem['images'][0])) {
            $pageImage = asset($portfolioItem['images'][0]);
        } else {
            $defaultPortfolioImage = 'images/og-realisations.jpg';
            if (file_exists(public_path($defaultPortfolioImage))) {
                $pageImage = asset($defaultPortfolioImage);
            } else {
                $companyLogo = setting('company_logo');
                if ($companyLogo) {
                    $pageImage = asset($companyLogo);
                }
            }
        }
        
        $ogTitle = $portfolioItem['og_title'] ?? $pageTitle;
        $ogDescription = $portfolioItem['og_description'] ?? $pageDescription;
        $twitterTitle = $portfolioItem['twitter_title'] ?? $ogTitle;
        $twitterDescription = $portfolioItem['twitter_description'] ?? $ogDescription;
        
        $currentPage = 'portfolio';
        $pageType = 'website';
        
        // Récupérer d'autres réalisations pour la section "Autres projets"
        $otherItems = collect(array_filter($portfolioItems, function($item) use ($portfolioItem) {
            return isset($item['id']) && $item['id'] != $portfolioItem['id'] && (isset($item['is_visible']) ? $item['is_visible'] : true);
        }))->take(3);
        
        return view('portfolio.show', compact(
            'portfolioItem', 
            'otherItems', 
            'pageTitle', 
            'pageDescription', 
            'pageKeywords', 
            'pageImage', 
            'ogTitle', 
            'ogDescription', 
            'twitterTitle', 
            'twitterDescription', 
            'currentPage', 
            'pageType'
        ));
    }
    
    /**
     * Générer un slug à partir du titre
     */
    private function generateSlug($title)
    {
        // Convertir les caractères accentués
        $slug = strtolower($title);
        $slug = str_replace(['à', 'á', 'â', 'ã', 'ä', 'å', 'æ'], 'a', $slug);
        $slug = str_replace(['è', 'é', 'ê', 'ë'], 'e', $slug);
        $slug = str_replace(['ì', 'í', 'î', 'ï'], 'i', $slug);
        $slug = str_replace(['ò', 'ó', 'ô', 'õ', 'ö', 'ø'], 'o', $slug);
        $slug = str_replace(['ù', 'ú', 'û', 'ü'], 'u', $slug);
        $slug = str_replace(['ý', 'ÿ'], 'y', $slug);
        $slug = str_replace(['ñ'], 'n', $slug);
        $slug = str_replace(['ç'], 'c', $slug);
        
        // Supprimer les caractères non alphanumériques sauf espaces et tirets
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        // Remplacer les espaces multiples et tirets par un seul tiret
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        // Supprimer les tirets en début et fin
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Générer les métadonnées SEO manquantes
     */
    private function generateMissingSEO($portfolioItem)
    {
        $companyName = setting('company_name', 'Votre Entreprise');
        $companyCity = setting('company_city', '');
        $workTypeLabels = [
            'roof' => 'Toiture',
            'facade' => 'Façade', 
            'isolation' => 'Isolation',
            'mixed' => 'Travaux mixtes'
        ];
        $workTypeLabel = $workTypeLabels[$portfolioItem['work_type'] ?? 'mixed'] ?? 'Travaux';
        
        // Générer le titre SEO s'il manque
        if (empty($portfolioItem['meta_title'])) {
            $portfolioItem['meta_title'] = $portfolioItem['title'] . ' - ' . $workTypeLabel;
            if ($companyCity) {
                $portfolioItem['meta_title'] .= ' à ' . $companyCity;
            }
            $portfolioItem['meta_title'] .= ' | ' . $companyName;
        }
        
        // Générer la description SEO s'il manque
        if (empty($portfolioItem['meta_description'])) {
            $portfolioItem['meta_description'] = 'Découvrez notre réalisation ' . $portfolioItem['title'] . ' - ' . $workTypeLabel;
            if ($companyCity) {
                $portfolioItem['meta_description'] .= ' à ' . $companyCity;
            }
            $portfolioItem['meta_description'] .= '. ' . ($portfolioItem['description'] ? \Illuminate\Support\Str::limit($portfolioItem['description'], 100) : 'Réalisation professionnelle par ' . $companyName);
        }
        
        // Générer les mots-clés SEO s'ils manquent
        if (empty($portfolioItem['meta_keywords'])) {
            $keywords = [
                $workTypeLabel,
                'réalisation',
                'travaux',
                'rénovation',
                $companyName
            ];
            if ($companyCity) {
                $keywords[] = $companyCity;
            }
            if (($portfolioItem['work_type'] ?? '') === 'roof') {
                $keywords = array_merge($keywords, ['toiture', 'couverture', 'charpente']);
            } elseif (($portfolioItem['work_type'] ?? '') === 'facade') {
                $keywords = array_merge($keywords, ['façade', 'enduit', 'ravalement']);
            } elseif (($portfolioItem['work_type'] ?? '') === 'isolation') {
                $keywords = array_merge($keywords, ['isolation', 'thermique', 'énergie']);
            }
            $portfolioItem['meta_keywords'] = implode(', ', array_unique($keywords));
        }
        
        // Générer les métadonnées Open Graph s'ils manquent
        if (empty($portfolioItem['og_title'])) {
            $portfolioItem['og_title'] = $portfolioItem['meta_title'];
        }
        if (empty($portfolioItem['og_description'])) {
            $portfolioItem['og_description'] = $portfolioItem['meta_description'];
        }
        if (empty($portfolioItem['og_image']) && !empty($portfolioItem['images'])) {
            $portfolioItem['og_image'] = is_array($portfolioItem['images']) ? $portfolioItem['images'][0] : $portfolioItem['images'];
        }
        
        return $portfolioItem;
    }
    
    /**
     * Récupérer les données de test du portfolio
     */
    private function getTestPortfolioData()
    {
        $testFile = storage_path('app/portfolio-data.json');
        
        if (file_exists($testFile)) {
            $data = json_decode(file_get_contents($testFile), true);
            if ($data) {
                \Log::info('Portfolio data loaded from file', ['count' => count($data)]);
                return $data;
            }
        }
        
        // Données de test par défaut avec toutes les images disponibles
        return [
            [
                'id' => 'portfolio_' . time() . '_1',
                'title' => 'Rénovation Toiture - Maison Familiale',
                'description' => 'Rénovation complète d\'une toiture en tuiles avec remplacement de la charpente et pose d\'une nouvelle couverture. Travaux réalisés avec des matériaux de qualité supérieure.',
                'work_type' => 'roof',
                'images' => [
                    'uploads/portfolio/portfolio-1761026926-2964.jpeg',
                    'uploads/portfolio/portfolio-1761033014-5399.jpeg'
                ],
                'is_visible' => true,
                'created_at' => date('c'),
                'meta_title' => 'Rénovation Toiture - Maison Familiale | Couvreur Professionnel',
                'meta_description' => 'Découvrez notre réalisation de rénovation de toiture. Travaux professionnels avec matériaux de qualité.',
                'meta_keywords' => 'rénovation, toiture, couverture, charpente, travaux'
            ],
            [
                'id' => 'portfolio_' . time() . '_2',
                'title' => 'Demoussage et Traitement Hydrofuge',
                'description' => 'Demoussage professionnel suivi d\'un traitement hydrofuge pour protéger la toiture contre les intempéries et les mousses.',
                'work_type' => 'demoussage',
                'images' => [
                    'uploads/portfolio/portfolio-1761034156-5402.jpeg',
                    'uploads/portfolio/portfolio-1761065197-2846.jpeg'
                ],
                'is_visible' => true,
                'created_at' => date('c'),
                'meta_title' => 'Demoussage et Traitement Hydrofuge | Couvreur Professionnel',
                'meta_description' => 'Service de demoussage et traitement hydrofuge pour protéger votre toiture.',
                'meta_keywords' => 'demoussage, hydrofuge, traitement, toiture, protection'
            ],
            [
                'id' => 'portfolio_' . time() . '_3',
                'title' => 'Réparation de Toiture - Urgence',
                'description' => 'Intervention d\'urgence pour réparer une toiture endommagée par une tempête. Remplacement des tuiles cassées et vérification de l\'étanchéité.',
                'work_type' => 'roof',
                'images' => [
                    'uploads/portfolio/portfolio-1761065197-9694.jpeg',
                    'uploads/portfolio/portfolio-1761169189-7693.jpg'
                ],
                'is_visible' => true,
                'created_at' => date('c'),
                'meta_title' => 'Réparation de Toiture - Urgence | Couvreur Professionnel',
                'meta_description' => 'Réparation d\'urgence de toiture endommagée. Intervention rapide et professionnelle.',
                'meta_keywords' => 'réparation, toiture, urgence, tempête, étanchéité'
            ],
            [
                'id' => 'portfolio_' . time() . '_4',
                'title' => 'Isolation Thermique Toiture',
                'description' => 'Pose d\'une isolation thermique performante dans les combles pour améliorer l\'efficacité énergétique de la maison.',
                'work_type' => 'isolation',
                'images' => [
                    'uploads/portfolio/portfolio-1761169429-8038.jpeg'
                ],
                'is_visible' => true,
                'created_at' => date('c'),
                'meta_title' => 'Isolation Thermique Toiture | Couvreur Professionnel',
                'meta_description' => 'Isolation thermique performante pour améliorer l\'efficacité énergétique.',
                'meta_keywords' => 'isolation, thermique, énergie, combles, efficacité'
            ]
        ];
    }
}








