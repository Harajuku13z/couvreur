<?php
/**
 * Script pour vérifier si la correction portfolio est déployée
 */

echo "🔍 Vérification de la correction portfolio...\n\n";

$filePath = 'resources/views/admin/portfolio.blade.php';

if (!file_exists($filePath)) {
    echo "❌ Fichier non trouvé : $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);

// Vérifier si l'ancien code est présent
if (strpos($content, '$services = setting(\'services\', []);') !== false) {
    echo "❌ PROBLÈME : L'ancien code est encore présent !\n";
    echo "   Le serveur n'a pas été mis à jour.\n\n";
    echo "📋 Actions à effectuer :\n";
    echo "1. git pull origin main\n";
    echo "2. php artisan cache:clear\n";
    echo "3. php artisan view:clear\n";
    exit(1);
}

// Vérifier si le nouveau code est présent
if (strpos($content, '$servicesData = setting(\'services\', []);') !== false && 
    strpos($content, 'is_string($servicesData) ? json_decode($servicesData, true)') !== false) {
    echo "✅ CORRECTION DÉPLOYÉE : Le nouveau code est présent !\n";
    echo "   La correction foreach devrait fonctionner.\n";
    exit(0);
}

echo "⚠️  Code inconnu détecté. Vérifiez manuellement le fichier.\n";
echo "   Recherchez les lignes autour de la ligne 100-110.\n";
