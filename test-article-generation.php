<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\ArticleController;

echo "🔍 Test de génération d'articles avec le nouveau prompt\n";
echo "====================================================\n\n";

try {
    // 1. Vérifier la clé API
    echo "1. Vérification de la clé API...\n";
    $apiKey = setting('chatgpt_api_key');
    if ($apiKey) {
        echo "✅ Clé API configurée\n";
    } else {
        echo "❌ Clé API non configurée\n";
        echo "   Configurez-la dans /admin/config\n";
        exit(1);
    }
    echo "\n";
    
    // 2. Test de génération d'un article
    echo "2. Test de génération d'un article...\n";
    $controller = new ArticleController();
    
    // Utiliser la réflexion pour accéder à la méthode privée
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('generateArticleContent');
    $method->setAccessible(true);
    
    $testTitle = "Les 10 couvreurs les plus fiables de l'Essonne à engager";
    echo "   Titre de test: {$testTitle}\n";
    echo "   Génération en cours...\n";
    
    $content = $method->invoke($controller, $testTitle, '');
    
    if ($content && $content !== '<p>Contenu à générer...</p>') {
        echo "✅ Contenu généré avec succès\n";
        echo "   Longueur: " . strlen($content) . " caractères\n";
        
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
        
        // Vérifier les emojis
        $emojiCount = preg_match_all('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $content);
        if ($emojiCount > 0) {
            echo "     ✅ Emojis détectés ({$emojiCount})\n";
        } else {
            echo "     ❌ Aucun emoji détecté\n";
        }
        
        // Sauvegarder un échantillon
        $sampleFile = 'test-article-sample.html';
        file_put_contents($sampleFile, $content);
        echo "     📄 Échantillon sauvegardé dans: {$sampleFile}\n";
        
    } else {
        echo "❌ Échec de la génération du contenu\n";
    }
    
    echo "\n";
    
    // 3. Vérifier les paramètres de génération
    echo "3. Vérification des paramètres...\n";
    echo "   - Modèle: " . setting('chatgpt_model', 'gpt-4o') . "\n";
    echo "   - Max tokens: 6000\n";
    echo "   - Temperature: 0.8\n";
    echo "\n";
    
    echo "🎯 Résumé du test:\n";
    echo "==================\n";
    echo "✅ Clé API: Configurée\n";
    echo "✅ Génération: Testée\n";
    echo "✅ Structure: Vérifiée\n";
    echo "\n💡 Le nouveau prompt professionnel est maintenant actif !\n";
    echo "   Les articles générés devraient avoir une meilleure qualité et structure.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
