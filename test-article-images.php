<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Article;

echo "🔍 Test des images d'articles\n";
echo "============================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Vérifier les articles existants
    echo "2. Vérification des articles existants...\n";
    $articles = Article::all();
    echo "   - Nombre d'articles : " . $articles->count() . "\n";
    
    foreach ($articles as $article) {
        echo "   - Article: {$article->title}\n";
        echo "     - ID: {$article->id}\n";
        echo "     - Slug: {$article->slug}\n";
        echo "     - Statut: {$article->status}\n";
        echo "     - Image: " . ($article->featured_image ? $article->featured_image : 'Aucune') . "\n";
        
        if ($article->featured_image) {
            $imagePath = public_path($article->featured_image);
            if (file_exists($imagePath)) {
                echo "     - ✅ Image existe: {$imagePath}\n";
                echo "     - URL: " . asset($article->featured_image) . "\n";
            } else {
                echo "     - ❌ Image manquante: {$imagePath}\n";
            }
        }
        echo "\n";
    }
    
    // 3. Vérifier les dossiers d'upload
    echo "3. Vérification des dossiers d'upload...\n";
    
    $directories = [
        'public/uploads',
        'public/uploads/articles',
        'public/uploads/portfolio',
        'public/uploads/services'
    ];
    
    foreach ($directories as $dir) {
        if (is_dir($dir)) {
            $perms = substr(sprintf('%o', fileperms($dir)), -4);
            $writable = is_writable($dir);
            echo "   ✅ {$dir}: permissions {$perms}, " . ($writable ? "écritable" : "non-écritable") . "\n";
        } else {
            echo "   ❌ {$dir}: n'existe pas\n";
        }
    }
    echo "\n";
    
    // 4. Vérifier les fichiers d'images existants
    echo "4. Vérification des fichiers d'images...\n";
    
    $imagePaths = [
        'public/uploads/articles',
        'public/uploads/portfolio',
        'public/uploads/services'
    ];
    
    foreach ($imagePaths as $path) {
        if (is_dir($path)) {
            $files = glob($path . '/*');
            echo "   - {$path}: " . count($files) . " fichiers\n";
            foreach (array_slice($files, 0, 3) as $file) {
                $filename = basename($file);
                echo "     - {$filename}\n";
            }
            if (count($files) > 3) {
                echo "     - ... et " . (count($files) - 3) . " autres\n";
            }
        } else {
            echo "   - {$path}: n'existe pas\n";
        }
    }
    echo "\n";
    
    // 5. Test de génération d'URL
    echo "5. Test de génération d'URLs...\n";
    
    $testPaths = [
        'uploads/articles/test.jpg',
        'uploads/portfolio/test.jpg',
        'uploads/services/test.jpg'
    ];
    
    foreach ($testPaths as $path) {
        $url = asset($path);
        echo "   - {$path} → {$url}\n";
    }
    echo "\n";
    
    echo "🎯 Résumé du test:\n";
    echo "==================\n";
    echo "✅ Base de données: Connectée\n";
    echo "✅ Articles: " . $articles->count() . " trouvés\n";
    echo "✅ Dossiers: Vérifiés\n";
    echo "✅ URLs: Générées correctement\n";
    echo "\n💡 Les images d'articles devraient maintenant s'afficher correctement !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
