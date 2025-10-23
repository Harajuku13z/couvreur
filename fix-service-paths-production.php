<?php

/**
 * Script pour corriger les chemins des images des services sur le serveur de production
 * À exécuter sur le serveur de production
 */

echo "🔧 Correction des chemins d'images des services - Production\n";
echo "==========================================================\n\n";

// Vérifier si nous sommes sur le serveur de production
if (!file_exists('/public_html')) {
    echo "❌ Ce script doit être exécuté sur le serveur de production\n";
    exit(1);
}

// Charger l'application Laravel
require_once '/public_html/vendor/autoload.php';
$app = require_once '/public_html/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

try {
    // Récupérer les services
    $servicesData = Setting::get('services', '[]');
    $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
    
    if (empty($services)) {
        echo "❌ Aucun service trouvé dans la base de données.\n";
        exit(1);
    }
    
    echo "📊 Services trouvés: " . count($services) . "\n\n";
    
    $updated = false;
    
    foreach ($services as $index => $service) {
        echo "--- Service: " . ($service['name'] ?? 'N/A') . " ---\n";
        
        // Corriger featured_image
        if (isset($service['featured_image'])) {
            $currentPath = $service['featured_image'];
            echo "Image actuelle: " . $currentPath . "\n";
            
            // Si le chemin ne commence pas par 'uploads/services/', le corriger
            if (strpos($currentPath, 'uploads/services/') !== 0) {
                $filename = basename($currentPath);
                $newPath = 'uploads/services/' . $filename;
                $services[$index]['featured_image'] = $newPath;
                echo "✅ Corrigé: {$currentPath} → {$newPath}\n";
                $updated = true;
            } else {
                echo "✅ Chemin déjà correct\n";
            }
        }
        
        // Corriger og_image
        if (isset($service['og_image'])) {
            $currentPath = $service['og_image'];
            echo "OG Image actuelle: " . $currentPath . "\n";
            
            if (strpos($currentPath, 'uploads/services/') !== 0) {
                $filename = basename($currentPath);
                $newPath = 'uploads/services/' . $filename;
                $services[$index]['og_image'] = $newPath;
                echo "✅ OG Image corrigée: {$currentPath} → {$newPath}\n";
                $updated = true;
            } else {
                echo "✅ OG Image déjà correcte\n";
            }
        }
        
        // Corriger twitter_image
        if (isset($service['twitter_image'])) {
            $currentPath = $service['twitter_image'];
            echo "Twitter Image actuelle: " . $currentPath . "\n";
            
            if (strpos($currentPath, 'uploads/services/') !== 0) {
                $filename = basename($currentPath);
                $newPath = 'uploads/services/' . $filename;
                $services[$index]['twitter_image'] = $newPath;
                echo "✅ Twitter Image corrigée: {$currentPath} → {$newPath}\n";
                $updated = true;
            } else {
                echo "✅ Twitter Image déjà correcte\n";
            }
        }
        
        echo "\n";
    }
    
    if ($updated) {
        // Sauvegarder les services corrigés
        Setting::set('services', json_encode($services), 'json', 'services');
        echo "✅ Services corrigés et sauvegardés!\n";
    } else {
        echo "✅ Tous les chemins d'images sont déjà corrects!\n";
    }
    
    // Vérifier les fichiers physiques
    echo "\n📁 Vérification des fichiers physiques:\n";
    $servicesDir = '/public_html/uploads/services';
    if (is_dir($servicesDir)) {
        $files = scandir($servicesDir);
        $imageFiles = array_filter($files, function($file) {
            return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });
        
        echo "Images trouvées dans public/uploads/services/: " . count($imageFiles) . "\n";
        foreach ($imageFiles as $file) {
            echo "  - {$file}\n";
        }
    } else {
        echo "❌ Répertoire public/uploads/services/ non trouvé\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🎉 Script terminé avec succès!\n";
echo "💡 Les images des services devraient maintenant s'afficher correctement.\n";