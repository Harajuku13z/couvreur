<?php
/**
 * Script de test pour l'upload d'image d'article
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ArticleController;

echo "🧪 Test d'upload d'image d'article...\n";

// Créer une requête simulée
$request = new Request();
$request->files->set('image', new \Illuminate\Http\UploadedFile(
    __DIR__ . '/public/images/hero-1.jpeg', // Utiliser une image existante
    'test-article.jpg',
    'image/jpeg',
    null,
    true
));

// Créer le contrôleur
$controller = new ArticleController();

try {
    $response = $controller->uploadImage($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['success']) {
        echo "✅ Upload réussi!\n";
        echo "📸 URL de l'image: " . $data['image_url'] . "\n";
        
        // Vérifier si l'image est accessible
        $imagePath = str_replace(url('storage/'), storage_path('app/public/'), $data['image_url']);
        if (file_exists($imagePath)) {
            echo "✅ Fichier physique existe: $imagePath\n";
        } else {
            echo "❌ Fichier physique manquant: $imagePath\n";
        }
    } else {
        echo "❌ Upload échoué: " . $data['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n✅ Test terminé\n";
?>
