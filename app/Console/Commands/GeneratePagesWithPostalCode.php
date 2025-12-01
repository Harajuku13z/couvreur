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
                            {--force : Supprimer les pages existantes avant création}
                            {--update : Mettre à jour les pages existantes}';

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
        $update = $this->option('update');

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
                    $created = $this->generatePagesForCityAndTemplate($template, $city, $force, $update);
                    $totalCreated += $created;
                }
            }
            
            $this->info('');
            $this->info("✅ {$totalCreated} page(s) générée(s)/mise(s) à jour au total");
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

            $created = $this->generatePagesForCityAndTemplate($template, $city, $force, $update);
            $this->info('');
            $this->info("✅ {$created} page(s) générée(s)/mise(s) à jour");
            return 0;
        }

        // Générer pour toutes les villes actives avec ce template
        $cities = City::where('is_active', true)->orWhere('active', true)->get();
        $this->info("📋 Génération pour le template '{$template->service_name}' et {$cities->count()} ville(s)...");
        $this->info('');

        $totalCreated = 0;
        foreach ($cities as $city) {
            $created = $this->generatePagesForCityAndTemplate($template, $city, $force, $update);
            $totalCreated += $created;
        }

        $this->info('');
        $this->info("✅ {$totalCreated} page(s) générée(s)/mise(s) à jour au total");
        return 0;
    }

    /**
     * Générer des pages pour une ville et un template donnés
     */
    protected function generatePagesForCityAndTemplate($template, $city, $force = false, $update = false)
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
        if ($existingAd && !$update && !$force) {
            $this->warn("  ⚠️  Annonce déjà existante: {$existingSlug} (utilisez --update pour la mettre à jour ou --force pour la recréer)");
            return 0;
        }

        // Obtenir les métadonnées pour la ville
        $metaForCity = $template->getMetaForCity($city);
        
        // Extraire le mot-clé principal
        $mainKeyword = $template->keyword ?? $template->service_name ?? 'Service';
        
        // Créer le titre avec code postal
        $baseTitle = $metaForCity['meta_title'] ?? $template->meta_title ?? $template->service_name;
        $postalCode = $city->postal_code ?? '';
        
        // Ajouter le code postal au titre systématiquement
        $titleWithPostalCode = $baseTitle;
        if ($postalCode) {
            // Retirer le code postal existant s'il y en a un
            $titleWithPostalCode = preg_replace('/\s*\d{5}\s*/', '', $baseTitle);
            $titleWithPostalCode = rtrim(trim($titleWithPostalCode), '.') . ' ' . $postalCode;
        }
        
        // Créer la description avec code postal systématiquement
        $description = $metaForCity['meta_description'] ?? $template->meta_description ?? '';
        if ($postalCode) {
            // Retirer le code postal existant s'il y en a un
            $description = preg_replace('/\s*\(\d{5}\)\s*/', '', $description);
            $description = preg_replace('/\s*\d{5}\s*/', '', $description);
            $description = rtrim(trim($description), '.') . ' (' . $postalCode . ').';
        }
        
        // Créer les mots-clés avec code postal
        $keywords = $metaForCity['meta_keywords'] ?? $template->meta_keywords ?? '';
        if ($postalCode) {
            $keywordsArray = array_filter(array_map('trim', explode(',', $keywords)));
            $keywordsArray[] = $mainKeyword . ' ' . $postalCode;
            $keywordsArray[] = $mainKeyword . ' ' . $city->name . ' ' . $postalCode;
            $keywords = implode(', ', array_unique($keywordsArray));
        }

        // Générer le contenu HTML avec code postal (passer aussi les mots-clés)
        $contentHtml = $this->generateContentWithPostalCode($template, $city, $mainKeyword, $postalCode, $keywords);

        try {
            // Si l'annonce existe déjà et qu'on veut la mettre à jour
            if ($existingAd && ($update || $force)) {
                $existingAd->update([
                    'title' => $titleWithPostalCode,
                    'meta_title' => $titleWithPostalCode,
                    'meta_description' => $description,
                    'meta_keywords' => $keywords,
                    'content_html' => $contentHtml,
                ]);
                
                    $action = $force ? 'recréée' : 'mise à jour';
                $this->info("  ✅ Page {$action}: {$titleWithPostalCode} ({$city->name} - {$postalCode})");
                return 1;
            }
            
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
    protected function generateContentWithPostalCode($template, $city, $keyword, $postalCode, $metaKeywords = '')
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
        $hiddenKeywords = $this->generateHiddenKeywords($keyword, $city, $postalCode, $metaKeywords);
        
        // Ajouter les mots-clés à la fin du contenu (visibles pour Google mais cachés visuellement)
        $content .= "\n" . $hiddenKeywords;
        
        return $content;
    }

    /**
     * Générer les mots-clés cachés mais visibles pour Google avec codes postaux par dizaines et centaines
     * Utilise les mots-clés configurés dans meta_keywords et les codes postaux des villes actives
     */
    protected function generateHiddenKeywords($keyword, $city, $postalCode, $metaKeywords = '')
    {
        $variations = [];
        
        // Extraire tous les mots-clés depuis meta_keywords
        $allKeywords = [];
        if (!empty($metaKeywords)) {
            $allKeywords = array_filter(array_map('trim', explode(',', $metaKeywords)));
        }
        // Ajouter aussi le mot-clé principal
        if (!empty($keyword)) {
            $allKeywords[] = $keyword;
        }
        // Retirer les doublons
        $allKeywords = array_unique($allKeywords);
        
        // Si pas de mots-clés, utiliser seulement le keyword principal
        if (empty($allKeywords)) {
            $allKeywords = [$keyword];
        }
        
        // Mots-clés de base avec le code postal actuel
        foreach ($allKeywords as $kw) {
            if (empty($kw)) continue;
            $variations[] = $kw . ' ' . $city->name;
            $variations[] = $kw . ' ' . $postalCode;
            $variations[] = $kw . ' ' . $city->name . ' ' . $postalCode;
            $variations[] = 'Entreprise ' . $kw . ' ' . $city->name;
            $variations[] = 'Professionnel ' . $kw . ' ' . $postalCode;
            $variations[] = 'Devis ' . $kw . ' ' . $city->name;
            $variations[] = 'Prix ' . $kw . ' ' . $postalCode;
        }
        
        // Générer des codes postaux par dizaines avec TOUS les mots-clés configurés
        if ($postalCode && strlen($postalCode) == 5 && is_numeric($postalCode)) {
            $codePrefix = substr($postalCode, 0, 3); // Les 3 premiers chiffres (ex: 974)
            $codeSuffix = intval(substr($postalCode, 3, 2)); // Les 2 derniers chiffres (ex: 10)
            
            // Pour chaque mot-clé configuré, générer des variations par dizaines (00 à 90)
            foreach ($allKeywords as $kw) {
                if (empty($kw)) continue;
                for ($i = 0; $i <= 90; $i += 10) {
                    $postalCodeVariation = $codePrefix . str_pad($i, 2, '0', STR_PAD_LEFT);
                    $variations[] = $kw . ' ' . $postalCodeVariation;
                    $variations[] = $kw . ' ' . $city->name . ' ' . $postalCodeVariation;
                }
            }
            
            // Générer des variations par centaines avec les codes postaux des villes actives
            // Récupérer tous les codes postaux uniques des villes actives
            $activeCityPostalCodes = City::where(function($q) {
                $q->where('is_active', true)->orWhere('active', true);
            })
            ->whereNotNull('postal_code')
            ->distinct()
            ->pluck('postal_code')
            ->filter(function($code) {
                return strlen($code) == 5 && is_numeric($code);
            })
            ->unique()
            ->toArray();
            
            // Pour chaque mot-clé configuré, combiner avec chaque code postal actif
            foreach ($allKeywords as $kw) {
                if (empty($kw)) continue;
                foreach ($activeCityPostalCodes as $activePostalCode) {
                    // Générer quelques variations avec chaque code postal actif
                    $variations[] = $kw . ' ' . $activePostalCode;
                    // Limiter à éviter trop de variations (prendre un échantillon)
                    if (count($variations) > 500) break 2; // Limite de sécurité
                }
            }
        }
        
        // Ajouter des variations supplémentaires avec code postal actuel
        if ($postalCode) {
            foreach ($allKeywords as $kw) {
                if (empty($kw)) continue;
                $variations[] = $kw . ' code postal ' . $postalCode;
                $variations[] = $kw . ' ' . $city->name . ' code postal ' . $postalCode;
            }
        }
        
        // Retirer les doublons
        $variations = array_unique($variations);
        
        // Limiter à un nombre raisonnable mais plus élevé pour inclure toutes les variations importantes
        $variations = array_slice($variations, 0, 500); // Limiter à 500 variations max
        
        // Créer un bloc de mots-clés invisible mais lisible par Google
        $keywordsText = implode(', ', $variations);
        
        // Créer un div caché avec les mots-clés (visibles pour Google, invisibles pour les utilisateurs)
        return '<div style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true">' . 
               htmlspecialchars($keywordsText) . 
               '</div>';
    }
}

