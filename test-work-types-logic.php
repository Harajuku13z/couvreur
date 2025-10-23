<?php
/**
 * Script de test pour la logique des types de travaux
 */

echo "🧪 Test de la logique des types de travaux...\n";

// Simuler une soumission avec des types de travaux
$workTypes = ['roof', 'facade', 'isolation'];
$workTypesJson = json_encode($workTypes);

echo "📊 Types de travaux simulés: " . $workTypesJson . "\n";

// Décoder comme dans le code
$decodedTypes = is_string($workTypesJson) ? json_decode($workTypesJson, true) : ($workTypesJson ?? []);

echo "📊 Types décodés: " . print_r($decodedTypes, true) . "\n";

// Labels de traduction
$workTypeLabels = [
    'roof' => 'Toiture',
    'facade' => 'Façade',
    'isolation' => 'Isolation'
];

// Traduction
$selectedTypes = [];
foreach($decodedTypes as $type) {
    if(isset($workTypeLabels[$type])) {
        $selectedTypes[] = $workTypeLabels[$type];
    }
}

echo "📊 Types traduits: " . print_r($selectedTypes, true) . "\n";
echo "📊 Résultat final: " . implode(', ', $selectedTypes) . "\n";

// Test avec des types non reconnus
echo "\n🧪 Test avec types non reconnus:\n";
$unknownTypes = ['unknown1', 'unknown2'];
$unknownTypesJson = json_encode($unknownTypes);

echo "📊 Types inconnus: " . $unknownTypesJson . "\n";

$decodedUnknown = is_string($unknownTypesJson) ? json_decode($unknownTypesJson, true) : ($unknownTypesJson ?? []);
$selectedUnknown = [];
foreach($decodedUnknown as $type) {
    if(isset($workTypeLabels[$type])) {
        $selectedUnknown[] = $workTypeLabels[$type];
    }
}

echo "📊 Types traduits (inconnus): " . print_r($selectedUnknown, true) . "\n";

// Fallback
if (empty($selectedUnknown) && !empty($decodedUnknown)) {
    echo "📊 Fallback vers types bruts: " . implode(', ', $decodedUnknown) . "\n";
}

echo "\n✅ Test terminé\n";
?>
