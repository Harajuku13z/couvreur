<?php

namespace App\Console\Commands;

use App\Helpers\SeoHelper;
use App\Models\Ad;
use App\Models\AdTemplate;
use Illuminate\Console\Command;

class NormalizeAdContent extends Command
{
    protected $signature = 'ads:normalize-content
                            {--templates : Corriger aussi les templates d\'annonces}
                            {--dry-run : Afficher le nombre de contenus à corriger sans sauvegarder}';

    protected $description = 'Corrige le HTML des annonces déjà créées (texte noir, suppression des blocs dupliqués, etc.)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updateTemplates = (bool) $this->option('templates');

        $this->newLine();
        $this->info($dryRun
            ? 'Analyse des annonces existantes en cours...'
            : 'Normalisation des annonces existantes en cours...');

        $adsUpdated = 0;
        $adsChecked = 0;
        $adsTotal = Ad::query()->whereNotNull('content_html')->count();

        $this->line("Annonces à parcourir : {$adsTotal}");
        $progressBar = $this->output->createProgressBar($adsTotal);
        $progressBar->start();

        Ad::query()
            ->whereNotNull('content_html')
            ->orderBy('id')
            ->chunkById(200, function ($ads) use (&$adsUpdated, &$adsChecked, $dryRun, $progressBar) {
                foreach ($ads as $ad) {
                    $adsChecked++;
                    $original = (string) $ad->content_html;
                    $normalized = SeoHelper::sanitizeAdContentHtml($original);

                    if ($normalized === '' || $normalized === $original) {
                        continue;
                    }

                    $adsUpdated++;

                    if (! $dryRun) {
                        $ad->forceFill(['content_html' => $normalized])->save();
                    }

                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Annonces vérifiées : {$adsChecked}");
        $this->info($dryRun
            ? "Annonces à corriger : {$adsUpdated}"
            : "Annonces corrigées : {$adsUpdated}");

        if ($updateTemplates) {
            $this->newLine();
            $this->info($dryRun
                ? 'Analyse des templates d’annonces en cours...'
                : 'Normalisation des templates d’annonces en cours...');

            $templatesUpdated = 0;
            $templatesChecked = 0;
            $templatesTotal = AdTemplate::query()->whereNotNull('content_html')->count();

            $this->line("Templates à parcourir : {$templatesTotal}");
            $templateProgressBar = $this->output->createProgressBar($templatesTotal);
            $templateProgressBar->start();

            AdTemplate::query()
                ->whereNotNull('content_html')
                ->orderBy('id')
                ->chunkById(100, function ($templates) use (&$templatesUpdated, &$templatesChecked, $dryRun, $templateProgressBar) {
                    foreach ($templates as $template) {
                        $templatesChecked++;
                        $original = (string) $template->content_html;
                        $normalized = SeoHelper::sanitizeAdContentHtml($original);

                        if ($normalized === '' || $normalized === $original) {
                            continue;
                        }

                        $templatesUpdated++;

                        if (! $dryRun) {
                            $template->forceFill(['content_html' => $normalized])->save();
                        }

                        $templateProgressBar->advance();
                    }
                });

            $templateProgressBar->finish();
            $this->newLine(2);
            $this->info("Templates vérifiés : {$templatesChecked}");
            $this->info($dryRun
                ? "Templates à corriger : {$templatesUpdated}"
                : "Templates corrigés : {$templatesUpdated}");
        }

        return self::SUCCESS;
    }
}
