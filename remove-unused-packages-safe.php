<?php

/**
 * Script pour supprimer les packages inutilisés de manière sûre
 * Ne supprime que les packages qui sont sûrs à supprimer
 */

$safeToRemove = [
    // Packages de développement (sûrs à supprimer en production)
    'fakerphp/faker',
    'laravel/pail',
    'laravel/pint',
    'laravel/sail',
    'mockery/mockery',
    'nunomaduro/collision',
    'phpunit/phpunit',
    
    // Packages Spatie non utilisés
    'spatie/browsershot',
    'spatie/crawler',
    'spatie/robots-txt',
    'spatie/temporary-directory',
    
    // Autres packages sûrs
    'staabm/side-effects-detector',
];

echo "🗑️  Suppression des packages inutilisés...\n\n";

$composerJsonPath = __DIR__ . '/composer.json';
$composerJson = json_decode(file_get_contents($composerJsonPath), true);

$removed = [];
$notFound = [];

foreach ($safeToRemove as $package) {
    // Vérifier dans require
    if (isset($composerJson['require'][$package])) {
        unset($composerJson['require'][$package]);
        $removed[] = $package;
        echo "✅ Supprimé de require: {$package}\n";
    }
    
    // Vérifier dans require-dev
    if (isset($composerJson['require-dev'][$package])) {
        unset($composerJson['require-dev'][$package]);
        $removed[] = $package;
        echo "✅ Supprimé de require-dev: {$package}\n";
    }
    
    if (!in_array($package, $removed)) {
        $notFound[] = $package;
    }
}

if (empty($removed)) {
    echo "ℹ️  Aucun package à supprimer trouvé dans composer.json\n";
    echo "   Les packages peuvent être des dépendances indirectes\n";
    echo "   Utilisez 'composer install --no-dev' en production\n";
    exit(0);
}

// Sauvegarder composer.json
file_put_contents($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "\n📝 composer.json mis à jour\n";
echo "📦 Packages supprimés: " . count($removed) . "\n\n";

if (!empty($notFound)) {
    echo "⚠️  Packages non trouvés dans composer.json (dépendances indirectes):\n";
    foreach ($notFound as $package) {
        echo "   - {$package}\n";
    }
    echo "\n";
}

echo "💡 Prochaines étapes:\n";
echo "   1. Exécutez: composer update\n";
echo "   2. Ou en production: composer install --no-dev --optimize-autoloader\n";
echo "   3. Vérifiez que votre site fonctionne toujours\n\n";

