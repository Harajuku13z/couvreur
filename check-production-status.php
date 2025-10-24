<?php

// Script de diagnostic pour vérifier l'état de la production
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "🔍 Diagnostic de l'état de la production\n";
echo "=====================================\n\n";

// Vérifier la connexion à la base de données
try {
    DB::connection()->getPdo();
    echo "✅ Connexion à la base de données : OK\n";
} catch (Exception $e) {
    echo "❌ Connexion à la base de données : ÉCHEC\n";
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}

// Vérifier les tables principales
$tables = [
    'articles' => 'Table des articles',
    'services' => 'Table des services (si elle existe)',
    'settings' => 'Table des paramètres',
    'submissions' => 'Table des soumissions',
    'cities' => 'Table des villes',
    'ads' => 'Table des annonces'
];

echo "\n📋 Vérification des tables :\n";
foreach ($tables as $table => $description) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo "✅ {$description} : {$count} enregistrements\n";
    } else {
        echo "❌ {$description} : Table manquante\n";
    }
}

// Vérifier les services dans les paramètres
echo "\n🔧 Vérification des services :\n";
try {
    $servicesData = DB::table('settings')->where('key', 'services')->first();
    if ($servicesData) {
        $services = json_decode($servicesData->value, true);
        if (is_array($services)) {
            echo "✅ Services dans les paramètres : " . count($services) . " services\n";
        } else {
            echo "⚠️  Services dans les paramètres : Format invalide\n";
        }
    } else {
        echo "❌ Aucun service trouvé dans les paramètres\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur lors de la vérification des services : " . $e->getMessage() . "\n";
}

// Vérifier les permissions de stockage
echo "\n📁 Vérification du stockage :\n";
$storagePaths = [
    'storage/app/public' => 'Répertoire de stockage principal',
    'storage/app/public/uploads' => 'Répertoire d\'uploads',
    'storage/app/public/uploads/services' => 'Répertoire des services',
    'storage/app/public/uploads/articles' => 'Répertoire des articles'
];

foreach ($storagePaths as $path => $description) {
    $fullPath = base_path($path);
    if (is_dir($fullPath)) {
        $writable = is_writable($fullPath) ? 'Écriture OK' : 'Pas d\'écriture';
        echo "✅ {$description} : {$writable}\n";
    } else {
        echo "❌ {$description} : Répertoire manquant\n";
    }
}

// Vérifier le lien symbolique
echo "\n🔗 Vérification du lien symbolique :\n";
$symlinkPath = public_path('storage');
if (is_link($symlinkPath)) {
    echo "✅ Lien symbolique public/storage : OK\n";
} elseif (is_dir($symlinkPath)) {
    echo "⚠️  public/storage est un répertoire (devrait être un lien)\n";
} else {
    echo "❌ Lien symbolique public/storage : Manquant\n";
}

echo "\n🎯 Recommandations :\n";
echo "- Si des tables manquent, exécutez : php artisan migrate\n";
echo "- Si le lien symbolique manque, exécutez : php artisan storage:link\n";
echo "- Si les répertoires manquent, créez-les avec les bonnes permissions\n";
