<?php

// Script pour nettoyer les services existants et supprimer les prestations dupliquées
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

echo "🧹 Nettoyage des services existants\n";
echo "==================================\n\n";

// Récupérer les services
$servicesData = Setting::get('services', '[]');
$services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);

if (!is_array($services)) {
    echo "❌ Aucun service trouvé\n";
    exit(1);
}

echo "📊 Services trouvés : " . count($services) . "\n\n";

$cleanedServices = [];
$cleanedCount = 0;

foreach ($services as $index => $service) {
    echo "🔍 Traitement du service : " . ($service['name'] ?? 'Sans nom') . "\n";
    
    if (isset($service['description'])) {
        $originalLength = strlen($service['description']);
        
        // Supprimer les prestations automatiques
        $cleanedDescription = cleanDescription($service['description']);
        
        if ($cleanedDescription !== $service['description']) {
            $service['description'] = $cleanedDescription;
            $cleanedCount++;
            echo "  ✅ Nettoyé (taille réduite de {$originalLength} à " . strlen($cleanedDescription) . " caractères)\n";
        } else {
            echo "  ℹ️  Aucun nettoyage nécessaire\n";
        }
    }
    
    $cleanedServices[] = $service;
}

// Sauvegarder les services nettoyés
Setting::set('services', $cleanedServices, 'json');

echo "\n🎉 Nettoyage terminé !\n";
echo "📈 Services modifiés : {$cleanedCount}\n";
echo "💾 Services sauvegardés avec succès\n";

function cleanDescription($description)
{
    // Patterns pour détecter et supprimer les prestations automatiques
    $patterns = [
        // Supprimer les sections de prestations automatiques
        '/<div class="prestations-section[^>]*>.*?<\/div>/s',
        '/<h3[^>]*>.*?Nos Prestations.*?<\/h3>/i',
        '/<div class="grid grid-cols-1 md:grid-cols-2 gap-4">.*?<\/div>/s',
        // Supprimer les prestations avec icônes automatiques
        '/<div class="flex items-center p-4 bg-gray-50[^>]*>.*?<\/div>/s',
        // Supprimer les listes de prestations automatiques
        '/<ul[^>]*class="[^"]*prestations[^"]*"[^>]*>.*?<\/ul>/s',
        '/<ol[^>]*class="[^"]*prestations[^"]*"[^>]*>.*?<\/ol>/s',
    ];
    
    $cleanedDescription = $description;
    
    foreach ($patterns as $pattern) {
        $cleanedDescription = preg_replace($pattern, '', $cleanedDescription);
    }
    
    // Nettoyer les espaces multiples et les lignes vides
    $cleanedDescription = preg_replace('/\s+/', ' ', $cleanedDescription);
    $cleanedDescription = preg_replace('/\n\s*\n/', "\n", $cleanedDescription);
    $cleanedDescription = trim($cleanedDescription);
    
    return $cleanedDescription;
}
