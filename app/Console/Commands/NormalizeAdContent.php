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

        $adsUpdated = 0;
        $adsChecked = 0;

        Ad::query()
            ->whereNotNull('content_html')
            ->orderBy('id')
            ->chunkById(200, function ($ads) use (&$adsUpdated, &$adsChecked, $dryRun) {
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
                }
            });

        $this->info("Annonces vérifiées : {$adsChecked}");
        $this->info($dryRun
            ? "Annonces à corriger : {$adsUpdated}"
            : "Annonces corrigées : {$adsUpdated}");

        if ($updateTemplates) {
            $templatesUpdated = 0;
            $templatesChecked = 0;

            AdTemplate::query()
                ->whereNotNull('content_html')
                ->orderBy('id')
                ->chunkById(100, function ($templates) use (&$templatesUpdated, &$templatesChecked, $dryRun) {
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
                    }
                });

            $this->info("Templates vérifiés : {$templatesChecked}");
            $this->info($dryRun
                ? "Templates à corriger : {$templatesUpdated}"
                : "Templates corrigés : {$templatesUpdated}");
        }

        return self::SUCCESS;
    }
}
