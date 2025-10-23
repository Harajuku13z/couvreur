<?php
/**
 * Script de test pour vérifier les images d'articles
 */

echo "🔍 Test des images d'articles...\n";

// Vérifier si le lien symbolique existe
$storageLink = public_path('storage');
if (is_link($storageLink)) {
    echo "✅ Lien symbolique public/storage existe\n";
    echo "📁 Pointe vers: " . readlink($storageLink) . "\n";
} else {
    echo "❌ Lien symbolique public/storage manquant\n";
}

// Vérifier le dossier storage/app/public/articles
$articlesDir = storage_path('app/public/articles');
if (is_dir($articlesDir)) {
    echo "✅ Dossier storage/app/public/articles existe\n";
    $files = scandir($articlesDir);
    $imageFiles = array_filter($files, function($file) {
        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    });
    
    if (count($imageFiles) > 0) {
        echo "📸 Images trouvées: " . count($imageFiles) . "\n";
        foreach (array_slice($imageFiles, 0, 3) as $file) {
            echo "  - $file\n";
        }
    } else {
        echo "❌ Aucune image trouvée dans storage/app/public/articles\n";
    }
} else {
    echo "❌ Dossier storage/app/public/articles n'existe pas\n";
}

// Vérifier les permissions
$storagePath = storage_path('app/public');
if (is_dir($storagePath)) {
    $perms = substr(sprintf('%o', fileperms($storagePath)), -4);
    echo "🔐 Permissions storage/app/public: $perms\n";
}

$publicStoragePath = public_path('storage');
if (is_dir($publicStoragePath)) {
    $perms = substr(sprintf('%o', fileperms($publicStoragePath)), -4);
    echo "🔐 Permissions public/storage: $perms\n";
}

// Test d'URL
echo "\n🌐 Test d'URLs:\n";
$testImage = 'articles/test.jpg';
$url1 = url('storage/' . $testImage);
$url2 = request()->getSchemeAndHttpHost() . '/storage/' . $testImage;

echo "URL avec url(): $url1\n";
echo "URL manuelle: $url2\n";

echo "\n✅ Test terminé\n";
?>
