<?php
/**
 * Script de test simple pour les types de travaux
 */

echo "🧪 Test simple des types de travaux...\n";

// Simuler la méthode getWorkTypesString
function getWorkTypesString($workTypes) {
    $workTypes = is_string($workTypes) ? json_decode($workTypes, true) : ($workTypes ?? []);
    
    $workTypeLabels = [
        'roof' => 'Toiture',
        'facade' => 'Façade',
        'isolation' => 'Isolation'
    ];
    
    $selectedTypes = [];
    foreach($workTypes as $type) {
        if(isset($workTypeLabels[$type])) {
            $selectedTypes[] = $workTypeLabels[$type];
        }
    }
    
    // Si aucun type traduit trouvé, retourner les types bruts
    if (empty($selectedTypes) && !empty($workTypes)) {
        return implode(', ', $workTypes);
    }
    
    return implode(', ', $selectedTypes);
}

// Tester avec différents cas
$testCases = [
    'JSON string normal' => '["roof","facade","isolation"]',
    'JSON string inconnu' => '["unknown1","unknown2"]',
    'Array normal' => ['roof', 'facade', 'isolation'],
    'Array inconnu' => ['unknown1', 'unknown2'],
    'Vide' => '[]',
    'Null' => null
];

foreach ($testCases as $description => $workTypes) {
    echo "\n📊 Test: $description\n";
    echo "📊 Input: " . ($workTypes ? (is_array($workTypes) ? json_encode($workTypes) : $workTypes) : 'null') . "\n";
    
    $result = getWorkTypesString($workTypes);
    echo "📊 Résultat: '$result'\n";
}

// Tester le remplacement dans un template
echo "\n🧪 Test de remplacement dans template:\n";
$template = "Bonjour {first_name}, vos types de travaux: {work_types}";
$workTypes = '["roof","facade"]';
$workTypesString = getWorkTypesString($workTypes);

$variables = [
    '{first_name}' => 'John',
    '{work_types}' => $workTypesString
];

$result = str_replace(array_keys($variables), array_values($variables), $template);
echo "📝 Template: $template\n";
echo "📊 Variables: " . json_encode($variables) . "\n";
echo "📝 Résultat: $result\n";

echo "\n✅ Test terminé\n";
?>
