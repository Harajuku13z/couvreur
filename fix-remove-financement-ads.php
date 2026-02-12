<?php

// =====================================================
// SCRIPT DE CORRECTION RAPIDE - TEXTE "Financement et aides"
// =====================================================
// À exécuter en production (via SSH) pour supprimer
// le bloc "Financement et aides" (TITRE + CONTENU) :
//   - des templates d'annonces (AdTemplate)
//   - des annonces déjà créées (Ad)
//   - des services (JSON "services" dans les settings)
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
use App\Models\Ad;
use App\Models\Setting;

echo "🔧 Suppression de la section 'Financement et aides' (titre + contenu) dans :\n";
echo "   - Templates d'annonces\n";
echo "   - Annonces existantes\n";
echo "   - Services\n\n";

// Paragraphes à supprimer (exactement tels qu'ils apparaissent dans le contenu)
$paragraphsToRemove = [
    // Variante 1 (rénovation de toiture)
    "Profitez des aides financières pour vos travaux de rénovation de toiture: MaPrimeRénov, Crédit d'impôt, éco-PTZ et TVA réduite. Contactez-nous pour plus d'informations.",
    // Variante 2 (Réparation & Pose de Nouvelle Toiture)
    "Profitez des aides financières telles que MaPrimeRénov, les Certificats d'Économies d'Énergie, l'éco-Prêt à Taux Zéro, la TVA réduite pour vos travaux de Réparation & Pose de Nouvelle Toiture. Contactez-nous pour plus d'informations.",
    // Variante 3 (rognage de souches)
    "Découvrez les aides disponibles pour votre projet de rognage de souches, telles que MaPrimeRénov, les CEE, l'éco-PTZ, la TVA réduite, etc. Nos experts peuvent vous conseiller sur les possibilités de financement adaptées à vos besoins.",
];

// Titres simples à nettoyer si présents sans HTML (sécurité)
$headingsToRemove = [
    "Financement et aides",
];

// Blocs HTML complets à supprimer (div "Financement et aides" + paragraphe)
// Basé sur le template généré par l'IA (bg-yellow-50 + h4 Financement et aides)
$blockPatterns = [
    '#<div\s+class="bg-yellow-50[^"]*">.*?<h4[^>]*>\s*Financement et aides\s*</h4>.*?</div>#si',
];

/**
 * Nettoyer un texte : enlever titre, contenu et bloc HTML complet.
 *
 * @return array [string $updatedText, bool $modified]
 */
function cleanFinancementText(string $text, array $paragraphsToRemove, array $headingsToRemove, array $blockPatterns): array
{
    $original = $text;
    $updated = $text;
    $modified = false;

    // Supprimer les blocs HTML complets
    foreach ($blockPatterns as $pattern) {
        $new = preg_replace($pattern, '', $updated);
        if ($new !== null && $new !== $updated) {
            $updated = $new;
            $modified = true;
        }
    }

    // Supprimer les paragraphes ciblés (contenu texte)
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

    if ($modified && $updated !== $original) {
        // Nettoyage basique des espaces multiples / lignes vides
        $updated = preg_replace("/\n{3,}/", "\n\n", $updated);
    }

    return [$updated, $modified];
}

// =========================
// 1) Templates d'annonces
// =========================

$totalTemplatesChecked = 0;
$totalTemplatesModified = 0;

try {
    $templates = AdTemplate::query()->orderBy('id')->cursor();

    foreach ($templates as $template) {
        $totalTemplatesChecked++;
        $templateModified = false;

        // Champs texte potentiellement concernés
        $fields = ['content_html', 'short_description', 'long_description'];

        foreach ($fields as $field) {
            $original = $template->{$field};

            if (!is_string($original) || $original === '') {
                continue;
            }

            [$updated, $fieldModified] = cleanFinancementText($original, $paragraphsToRemove, $headingsToRemove, $blockPatterns);

            if ($fieldModified) {
                $template->{$field} = $updated;
                $templateModified = true;
            }
        }

        if ($templateModified) {
            $template->save();
            $totalTemplatesModified++;
            echo "   ✅ Template ID {$template->id} corrigé ({$template->name})\n";
        }
    }

} catch (Exception $e) {
    echo "\n❌ Erreur lors de la correction : " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// =========================
// 2) Annonces existantes
// =========================

echo "\n📣 Nettoyage des annonces existantes...\n";

$totalAdsChecked = 0;
$totalAdsModified = 0;

try {
    $ads = Ad::query()->orderBy('id')->cursor();

    foreach ($ads as $ad) {
        $totalAdsChecked++;
        $adModified = false;

        // Champs concernés : contenu HTML + meta_description
        $fields = ['content_html', 'meta_description'];

        foreach ($fields as $field) {
            $original = $ad->{$field};

            if (!is_string($original) || $original === '') {
                continue;
            }

            [$updated, $fieldModified] = cleanFinancementText($original, $paragraphsToRemove, $headingsToRemove, $blockPatterns);

            if ($fieldModified) {
                $ad->{$field} = $updated;
                $adModified = true;
            }
        }

        if ($adModified) {
            $ad->save();
            $totalAdsModified++;
            echo "   ✅ Annonce ID {$ad->id} corrigée ({$ad->title})\n";
        }
    }

} catch (Exception $e) {
    echo "\n❌ Erreur lors du nettoyage des annonces : " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// =========================
// 3) Services (settings JSON)
// =========================

echo "\n🛠️ Nettoyage des services (settings)...\n";

try {
    $servicesData = Setting::get('services', '[]');
    $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);

    if (!is_array($services)) {
        echo "   ❌ Les settings 'services' ne sont pas un tableau JSON valide. Section services ignorée.\n";
    } else {
        $totalServicesChecked = count($services);
        $totalServicesModified = 0;

        foreach ($services as $index => $service) {
            $serviceModified = false;

            $fields = ['description', 'short_description', 'meta_description'];

            foreach ($fields as $field) {
                if (!isset($service[$field]) || !is_string($service[$field]) || $service[$field] === '') {
                    continue;
                }

                $original = $service[$field];
                [$updated, $fieldModified] = cleanFinancementText($original, $paragraphsToRemove, $headingsToRemove, $blockPatterns);

                if ($fieldModified) {
                    $services[$index][$field] = $updated;
                    $serviceModified = true;
                }
            }

            if ($serviceModified) {
                $name = $service['name'] ?? ($service['title'] ?? 'Service sans nom');
                echo "   ✅ Service corrigé : {$name}\n";
                $totalServicesModified++;
            }
        }

        if ($totalServicesModified > 0) {
            Setting::set('services', json_encode($services), 'json', 'services');
            Setting::clearCache();
        }

        echo "\n🔍 Résultat de la correction (SERVICES)...\n";
        echo "   - Services vérifiés : {$totalServicesChecked}\n";
        echo "   - Services modifiés : {$totalServicesModified}\n";
    }

} catch (Exception $e) {
    echo "\n❌ Erreur lors du nettoyage des services : " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

// =========================
// Récap global
// =========================

echo "\n✅ RÉCAP GLOBAL\n";
echo "   - Templates d'annonces vérifiés : {$totalTemplatesChecked}, modifiés : {$totalTemplatesModified}\n";
echo "   - Annonces vérifiées : {$totalAdsChecked}, modifiées : {$totalAdsModified}\n";

echo "\n🎉 Correction terminée pour annonces + templates + services.\n";

