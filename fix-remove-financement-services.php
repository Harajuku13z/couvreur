<?php

// =====================================================
// SCRIPT DE CORRECTION RAPIDE - TEXTE "Financement et aides" (SERVICES)
// =====================================================
// À exécuter en production (via SSH) pour supprimer
// les paragraphes "Financement et aides" du contenu des services
// stockés dans le setting JSON "services".
//
// Exemple de texte ciblé :
// "Financement et aides
//  Profitez des aides financières telles que MaPrimeRénov, les Certificats d'Économies d'Énergie,
//  l'éco-Prêt à Taux Zéro, la TVA réduite pour vos travaux de Réparation & Pose de Nouvelle Toiture.
//  Contactez-nous pour plus d'informations."
//
// Commande d'exécution :
// php fix-remove-financement-services.php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

echo "🔧 Suppression du texte 'Financement et aides' dans les services...\n\n";

// Paragraphes à supprimer (mêmes variantes que pour les annonces)
$paragraphsToRemove = [
    // Variante 1 (rénovation de toiture)
    "Profitez des aides financières pour vos travaux de rénovation de toiture: MaPrimeRénov, Crédit d'impôt, éco-PTZ et TVA réduite. Contactez-nous pour plus d'informations.",
    // Variante 2 (Réparation & Pose de Nouvelle Toiture)
    "Profitez des aides financières telles que MaPrimeRénov, les Certificats d'Économies d'Énergie, l'éco-Prêt à Taux Zéro, la TVA réduite pour vos travaux de Réparation & Pose de Nouvelle Toiture. Contactez-nous pour plus d'informations.",
    // Variante 3 (rognage de souches)
    "Découvrez les aides disponibles pour votre projet de rognage de souches, telles que MaPrimeRénov, les CEE, l'éco-PTZ, la TVA réduite, etc. Nos experts peuvent vous conseiller sur les possibilités de financement adaptées à vos besoins.",
];

$headingsToRemove = [
    "Financement et aides",
];

// Récupérer les services depuis les settings
$servicesData = Setting::get('services', '[]');
$services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);

if (!is_array($services)) {
    echo "❌ Les settings 'services' ne sont pas un tableau JSON valide. Abandon.\n";
    exit(1);
}

$totalChecked = count($services);
$totalModified = 0;

foreach ($services as $index => $service) {
    $modified = false;

    // Champs potentiellement concernés dans la structure JSON des services
    $fields = ['description', 'short_description', 'meta_description'];

    foreach ($fields as $field) {
        if (!isset($service[$field]) || !is_string($service[$field]) || $service[$field] === '') {
            continue;
        }

        $original = $service[$field];
        $updated = $original;

        foreach ($paragraphsToRemove as $paragraph) {
            if ($paragraph && strpos($updated, $paragraph) !== false) {
                $updated = str_replace($paragraph, '', $updated);
                $modified = true;
            }
        }

        foreach ($headingsToRemove as $heading) {
            if ($heading && strpos($updated, $heading) !== false) {
                $updated = str_replace($heading, '', $updated);
                $modified = true;
            }
        }

        if ($modified && $updated !== $original) {
            // Nettoyage basique : réduire les multiples lignes vides
            $updated = preg_replace("/\n{3,}/", "\n\n", $updated);
            $services[$index][$field] = $updated;
        }
    }

    if ($modified) {
        $name = $service['name'] ?? ($service['title'] ?? 'Service sans nom');
        echo "   ✅ Service corrigé : {$name}\n";
        $totalModified++;
    }
}

if ($totalModified > 0) {
    // Sauvegarder les services nettoyés
    Setting::set('services', json_encode($services), 'json', 'services');
    Setting::clearCache();
}

echo "\n🔍 Résultat de la correction (SERVICES)...\n";
echo "   - Services vérifiés : {$totalChecked}\n";
echo "   - Services modifiés : {$totalModified}\n";

echo "\n🎉 Correction des services terminée.\n";

