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
        // Récupérer les éléments du portfolio depuis les settings
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
        // Filtrer les éléments visibles
        $visiblePortfolio = collect(array_filter($portfolioItems, function($item) {
            return isset($item['is_visible']) ? $item['is_visible'] : true;
        }));
        
        // Récupérer les types de services uniques pour les filtres
        $serviceTypes = $visiblePortfolio->pluck('service_type')->unique()->filter()->values()->toArray();
        
        return view('portfolio.index', compact('visiblePortfolio', 'serviceTypes'));
    }
    
    /**
     * Afficher les détails d'une réalisation
     */
    public function show($slug)
    {
        // Récupérer les éléments du portfolio depuis les settings
        $portfolioData = Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        
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
        
        // Récupérer d'autres réalisations pour la section "Autres projets"
        $otherItems = collect(array_filter($portfolioItems, function($item) use ($portfolioItem) {
            return isset($item['id']) && $item['id'] != $portfolioItem['id'] && (isset($item['is_visible']) ? $item['is_visible'] : true);
        }))->take(3);
        
        return view('portfolio.show', compact('portfolioItem', 'otherItems'));
    }
    
    /**
     * Générer un slug à partir du titre
     */
    private function generateSlug($title)
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
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
}








