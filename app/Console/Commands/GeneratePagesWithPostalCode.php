<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ad;
use App\Models\City;
use App\Models\AdTemplate;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GeneratePagesWithPostalCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:pages-postal-code 
                            {--template= : ID du template d\'annonce à utiliser}
                            {--city= : Slug de la ville ou ID}
                            {--all : Générer pour toutes les villes actives}
                            {--force : Supprimer les pages existantes avant création}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer des copies de pages d\'annonces avec code postal dans le titre et optimisation SEO';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Génération de pages avec code postal...');
        $this->info('');

        $templateId = $this->option('template');
        $cityOption = $this->option('city');
        $generateAll = $this->option('all');
        $force = $this->option('force');

        // Vérifier si on a un template spécifique
        if (!$templateId && !$generateAll) {
            $this->error('❌ Vous devez spécifier --template=ID ou --all');
            $this->info('');
            $this->info('Exemples:');
            $this->info('  php artisan generate:pages-postal-code --template=1');
            $this->info('  php artisan generate:pages-postal-code --template=1 --city=chevigny-saint-sauveur');
            $this->info('  php artisan generate:pages-postal-code --all');
            return 1;
        }

        // Si --all, générer pour tous les templates et toutes les villes
        if ($generateAll) {
            $templates = AdTemplate::all();
            $cities = City::where('is_active', true)->orWhere('active', true)->get();
            
            $this->info("📋 Génération pour {$templates->count()} template(s) et {$cities->count()} ville(s)...");
            $this->info('');

            $totalCreated = 0;
            
            foreach ($templates as $template) {
                foreach ($cities as $city) {
                    $created = $this->generatePagesForCityAndTemplate($template, $city, $force);
                    $totalCreated += $created;
                }
            }
            
            $this->info('');
            $this->info("✅ {$totalCreated} page(s) générée(s) au total");
            return 0;
        }

        // Récupérer le template
        $template = AdTemplate::find($templateId);
        if (!$template) {
            $this->error("❌ Template {$templateId} non trouvé");
            return 1;
        }

        // Si une ville spécifique est demandée
        if ($cityOption) {
            $city = City::where('slug', $cityOption)
                      ->orWhere('id', $cityOption)
                      ->first();
            
            if (!$city) {
                $this->error("❌ Ville '{$cityOption}' non trouvée");
                return 1;
            }

            $created = $this->generatePagesForCityAndTemplate($template, $city, $force);
            $this->info('');
            $this->info("✅ {$created} page(s) générée(s)");
            return 0;
        }

        // Générer pour toutes les villes actives avec ce template
        $cities = City::where('is_active', true)->orWhere('active', true)->get();
        $this->info("📋 Génération pour le template '{$template->service_name}' et {$cities->count()} ville(s)...");
        $this->info('');

        $totalCreated = 0;
        foreach ($cities as $city) {
            $created = $this->generatePagesForCityAndTemplate($template, $city, $force);
            $totalCreated += $created;
        }

        $this->info('');
        $this->info("✅ {$totalCreated} page(s) générée(s) au total");
        return 0;
    }

    /**
     * Générer des pages pour une ville et un template donnés
     */
    protected function generatePagesForCityAndTemplate($template, $city, $force = false)
    {
        // Vérifier si une annonce existe déjà
        $keyword = $template->keyword ?? $template->service_name ?? 'service';
        $existingSlug = Str::slug($keyword . '-' . $city->name);
        
        // Si force, supprimer l'annonce existante
        if ($force) {
            $existingAd = Ad::where('slug', $existingSlug)->first();
            if ($existingAd) {
                $existingAd->delete();
                $this->info("  🗑️  Annonce existante supprimée: {$existingSlug}");
            }
        }

        // Vérifier si l'annonce existe déjà
        $existingAd = Ad::where('slug', $existingSlug)->first();
        if ($existingAd) {
            $this->warn("  ⚠️  Annonce déjà existante: {$existingSlug} (utilisez --force pour la recréer)");
            return 0;
        }

        // Obtenir les métadonnées pour la ville
        $metaForCity = $template->getMetaForCity($city);
        
        // Extraire le mot-clé principal
        $mainKeyword = $template->keyword ?? $template->service_name ?? 'Service';
        
        // Créer le titre avec code postal
        $baseTitle = $metaForCity['meta_title'] ?? $template->meta_title ?? $template->service_name;
        $postalCode = $city->postal_code ?? '';
        
        // Ajouter le code postal au titre s'il n'est pas déjà présent
        $titleWithPostalCode = $baseTitle;
        if ($postalCode && strpos($baseTitle, $postalCode) === false) {
            $titleWithPostalCode = rtrim($baseTitle, '.') . ' ' . $postalCode;
        }
        
        // Créer la description avec code postal
        $description = $metaForCity['meta_description'] ?? $template->meta_description ?? '';
        if ($postalCode && strpos($description, $postalCode) === false) {
            $description = rtrim($description, '.') . ' (' . $postalCode . ').';
        }
        
        // Créer les mots-clés avec code postal
        $keywords = $metaForCity['meta_keywords'] ?? $template->meta_keywords ?? '';
        if ($postalCode) {
            $keywordsArray = array_filter(array_map('trim', explode(',', $keywords)));
            $keywordsArray[] = $mainKeyword . ' ' . $postalCode;
            $keywordsArray[] = $mainKeyword . ' ' . $city->name . ' ' . $postalCode;
            $keywords = implode(', ', array_unique($keywordsArray));
        }

        // Générer le contenu HTML avec code postal
        $contentHtml = $this->generateContentWithPostalCode($template, $city, $mainKeyword, $postalCode);

        try {
            // Créer la nouvelle annonce
            $ad = Ad::create([
                'title' => $titleWithPostalCode,
                'keyword' => $mainKeyword,
                'city_id' => $city->id,
                'template_id' => $template->id,
                'slug' => $existingSlug,
                'status' => 'published',
                'meta_title' => $titleWithPostalCode,
                'meta_description' => $description,
                'meta_keywords' => $keywords,
                'content_html' => $contentHtml,
                'published_at' => Carbon::now(),
            ]);

            $this->info("  ✅ Page créée: {$titleWithPostalCode} ({$city->name} - {$postalCode})");
            return 1;
        } catch (\Exception $e) {
            $this->error("  ❌ Erreur lors de la création: " . $e->getMessage());
            \Log::error('Erreur génération page avec code postal', [
                'template_id' => $template->id,
                'city_id' => $city->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Générer le contenu HTML avec code postal et mots-clés optimisés
     */
    protected function generateContentWithPostalCode($template, $city, $keyword, $postalCode)
    {
        // Récupérer le contenu original
        $originalContent = $template->content_html ?? '';
        
        // Remplacer les placeholders
        $replacements = [
            '[VILLE]' => $city->name,
            '[RÉGION]' => $city->region ?? '',
            '[DÉPARTEMENT]' => $city->department ?? '',
            '[CODE_POSTAL]' => $postalCode,
            '[MOT_CLÉ]' => $keyword,
        ];
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $originalContent);
        
        // Ajouter les mots-clés de façon invisible mais visible pour Google
        $hiddenKeywords = $this->generateHiddenKeywords($keyword, $city, $postalCode);
        
        // Ajouter les mots-clés à la fin du contenu (visibles pour Google mais cachés visuellement)
        $content .= "\n" . $hiddenKeywords;
        
        return $content;
    }

    /**
     * Générer les mots-clés cachés mais visibles pour Google
     */
    protected function generateHiddenKeywords($keyword, $city, $postalCode)
    {
        // Générer des variations de mots-clés
        $variations = [
            $keyword . ' ' . $city->name,
            $keyword . ' ' . $postalCode,
            $keyword . ' ' . $city->name . ' ' . $postalCode,
            'Entreprise ' . $keyword . ' ' . $city->name,
            'Professionnel ' . $keyword . ' ' . $postalCode,
            'Devis ' . $keyword . ' ' . $city->name,
            'Prix ' . $keyword . ' ' . $postalCode,
        ];
        
        // Créer un bloc de mots-clés invisible mais lisible par Google
        $keywordsText = implode(', ', array_unique($variations));
        
        // Créer un div caché avec les mots-clés (visibles pour Google, invisibles pour les utilisateurs)
        return '<div style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true">' . 
               htmlspecialchars($keywordsText) . 
               '</div>';
    }
}

