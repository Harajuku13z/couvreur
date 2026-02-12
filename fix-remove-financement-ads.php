<?php

// =====================================================
// SCRIPT DE CORRECTION RAPIDE - TEXTE "Financement et aides" (ANNONCES)
// =====================================================
// À exécuter en production (via SSH) pour supprimer
// les paragraphes "Financement et aides" des templates d'annonces existants.
//
// Exemple de texte ciblé :
// "Profitez des aides financières pour vos travaux de rénovation de toiture: MaPrimeRénov,
//  Crédit d'impôt, éco-PTZ et TVA réduite. Contactez-nous pour plus d'informations."
//
// Commande d'exécution :
// php fix-remove-financement-ads.php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AdTemplate;

echo "🔧 Suppression du texte 'Financement et aides' dans les templates d'annonces...\n\n";

// Paragraphes à supprimer (exactement tels qu'ils apparaissent dans le contenu)
$paragraphsToRemove = [
    // Variante 1 (rénovation de toiture)
    "Profitez des aides financières pour vos travaux de rénovation de toiture: MaPrimeRénov, Crédit d'impôt, éco-PTZ et TVA réduite. Contactez-nous pour plus d'informations.",
    // Variante 2 (Réparation & Pose de Nouvelle Toiture)
    "Profitez des aides financières telles que MaPrimeRénov, les Certificats d'Économies d'Énergie, l'éco-Prêt à Taux Zéro, la TVA réduite pour vos travaux de Réparation & Pose de Nouvelle Toiture. Contactez-nous pour plus d'informations.",
];

// Éventuels titres simples à nettoyer si présents sans HTML (sécurité)
$headingsToRemove = [
    "Financement et aides",
];

$totalChecked = 0;
$totalModified = 0;

try {
    $templates = AdTemplate::query()->orderBy('id')->cursor();

    foreach ($templates as $template) {
        $totalChecked++;
        $modified = false;

        // Champs texte potentiellement concernés
        $fields = ['content_html', 'short_description', 'long_description'];

        foreach ($fields as $field) {
            $original = $template->{$field};

            if (!is_string($original) || $original === '') {
                continue;
            }

            $updated = $original;

            // Supprimer les paragraphes ciblés
            foreach ($paragraphsToRemove as $paragraph) {
                if ($paragraph && strpos($updated, $paragraph) !== false) {
                    $updated = str_replace($paragraph, '', $updated);
                    $modified = true;
                }
            }

            // Supprimer les titres nus éventuels (sans HTML)
            foreach ($headingsToRemove as $heading) {
                if ($heading && strpos($updated, $heading) !== false) {
                    $updated = str_replace($heading, '', $updated);
                    $modified = true;
                }
            }

            // Nettoyage basique des espaces multiples / lignes vides
            if ($modified && $updated !== $original) {
                // Remplacer les multiples retours à la ligne par deux maximum
                $updated = preg_replace("/\n{3,}/", "\n\n", $updated);
                $template->{$field} = $updated;
            }
        }

        if ($modified) {
            $template->save();
            $totalModified++;
            echo "   ✅ Template ID {$template->id} corrigé ({$template->name})\n";
        }
    }

    echo "\n🔍 Résultat de la correction (ANNONCES)...\n";
    echo "   - Templates vérifiés : {$totalChecked}\n";
    echo "   - Templates modifiés : {$totalModified}\n";

    echo "\n🎉 Correction des annonces terminée.\n";

} catch (Exception $e) {
    echo "\n❌ Erreur lors de la correction : " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

