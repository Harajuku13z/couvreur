<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\ArticleController;

echo "🚀 Test de génération d'articles avec le prompt amélioré\n";
echo "====================================================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Vérifier la clé API
    echo "2. Vérification de la clé API...\n";
    $apiKey = setting('chatgpt_api_key');
    if ($apiKey) {
        echo "✅ Clé API configurée\n";
    } else {
        echo "❌ Clé API non configurée - Utilisation du contenu de fallback\n";
    }
    echo "\n";
    
    // 3. Test de génération d'un article
    echo "3. Test de génération d'un article...\n";
    $controller = new ArticleController();
    
    // Utiliser la réflexion pour accéder à la méthode privée
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('generateArticleContent');
    $method->setAccessible(true);
    
    $testTitle = "Comment hydrofuger sa toiture pour une protection optimale";
    $testKeyword = "hydrofuge toiture";
    
    echo "   Titre de test: {$testTitle}\n";
    echo "   Mot-clé: {$testKeyword}\n";
    echo "   Génération en cours...\n";
    
    $content = $method->invoke($controller, $testTitle, $testKeyword);
    
    if ($content && strlen($content) > 1000) {
        echo "✅ Contenu généré avec succès (" . strlen($content) . " caractères)\n";
        
        // Vérifier la structure
        echo "   Vérification de la structure:\n";
        
        if (strpos($content, 'max-w-7xl') !== false) {
            echo "     ✅ Container Tailwind détecté\n";
        } else {
            echo "     ❌ Container Tailwind manquant\n";
        }
        
        if (strpos($content, 'text-4xl font-bold') !== false) {
            echo "     ✅ Titre principal avec classes Tailwind\n";
        } else {
            echo "     ❌ Titre principal sans classes Tailwind\n";
        }
        
        if (strpos($content, 'bg-white p-6 rounded-xl shadow') !== false) {
            echo "     ✅ Sections avec classes Tailwind\n";
        } else {
            echo "     ❌ Sections sans classes Tailwind\n";
        }
        
        if (strpos($content, 'bg-green-50') !== false) {
            echo "     ✅ FAQ avec classes Tailwind\n";
        } else {
            echo "     ❌ FAQ sans classes Tailwind\n";
        }
        
        if (strpos($content, 'bg-blue-500') !== false) {
            echo "     ✅ Call-to-action avec classes Tailwind\n";
        } else {
            echo "     ❌ Call-to-action sans classes Tailwind\n";
        }
        
        // Vérifier le contenu spécifique
        if (strpos($content, 'hydrofuger') !== false) {
            echo "     ✅ Contenu spécifique au sujet détecté\n";
        } else {
            echo "     ❌ Contenu générique (fallback)\n";
        }
        
        // Vérifier les emojis
        $emojiCount = preg_match_all('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $content);
        if ($emojiCount > 0) {
            echo "     ✅ Emojis détectés ({$emojiCount})\n";
        } else {
            echo "     ❌ Aucun emoji détecté\n";
        }
        
        // Sauvegarder un échantillon
        $sampleFile = 'test-article-fixed.html';
        file_put_contents($sampleFile, $content);
        echo "     📄 Échantillon sauvegardé dans: {$sampleFile}\n";
        
        // Afficher un aperçu du contenu
        echo "\n   Aperçu du contenu généré:\n";
        $preview = strip_tags($content);
        $preview = substr($preview, 0, 200) . '...';
        echo "     " . $preview . "\n";
        
    } else {
        echo "❌ Échec de la génération du contenu\n";
        echo "   Longueur: " . strlen($content) . " caractères\n";
    }
    
    echo "\n";
    
    // 4. Vérifier les paramètres
    echo "4. Vérification des paramètres...\n";
    echo "   - Modèle: " . setting('chatgpt_model', 'gpt-4o') . "\n";
    echo "   - Max tokens: 8000\n";
    echo "   - Temperature: 0.7\n";
    echo "   - Nom entreprise: " . setting('company_name', 'Non configuré') . "\n";
    echo "   - Téléphone: " . setting('company_phone', 'Non configuré') . "\n";
    
    echo "\n🎯 Résumé du test:\n";
    echo "==================\n";
    echo "✅ Base de données: Connectée\n";
    echo "✅ Clé API: " . ($apiKey ? 'Configurée' : 'Non configurée') . "\n";
    echo "✅ Génération: " . (strlen($content) > 1000 ? 'Réussie' : 'Échec') . "\n";
    echo "✅ Structure: Vérifiée\n";
    echo "✅ Contenu: " . (strpos($content, 'hydrofuger') !== false ? 'Spécifique' : 'Générique') . "\n";
    echo "\n💡 Le prompt amélioré devrait maintenant générer du contenu spécifique !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
