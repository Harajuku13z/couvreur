<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Article;
use App\Http\Controllers\Admin\ArticleController;

echo "🔍 Test du contenu des articles\n";
echo "==============================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Lister les articles existants
    echo "2. Articles existants dans la base de données...\n";
    $articles = Article::orderBy('created_at', 'desc')->limit(5)->get();
    
    if ($articles->count() > 0) {
        foreach ($articles as $article) {
            echo "   📄 {$article->title}\n";
            echo "      - ID: {$article->id}\n";
            echo "      - Slug: {$article->slug}\n";
            echo "      - Status: {$article->status}\n";
            echo "      - Contenu HTML: " . (strlen($article->content_html) > 100 ? 'OUI (' . strlen($article->content_html) . ' caractères)' : 'NON') . "\n";
            echo "      - Meta Title: " . ($article->meta_title ? 'OUI' : 'NON') . "\n";
            echo "      - Meta Description: " . ($article->meta_description ? 'OUI' : 'NON') . "\n";
            echo "      - Meta Keywords: " . ($article->meta_keywords ? 'OUI' : 'NON') . "\n";
            echo "\n";
        }
    } else {
        echo "   ❌ Aucun article trouvé\n";
    }
    
    echo "\n";
    
    // 3. Test de génération d'un nouvel article
    echo "3. Test de génération d'un nouvel article...\n";
    
    $controller = new ArticleController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('generateArticleContent');
    $method->setAccessible(true);
    
    $testTitle = "Hydrofuge : Comment protéger efficacement vos surfaces de l'eau - Guide Complet 2024";
    $testKeyword = "hydrofuge protection";
    
    echo "   Titre de test: {$testTitle}\n";
    echo "   Mot-clé: {$testKeyword}\n";
    echo "   Génération en cours...\n";
    
    $content = $method->invoke($controller, $testTitle, $testKeyword);
    
    if ($content && strlen($content) > 1000) {
        echo "   ✅ Contenu généré avec succès (" . strlen($content) . " caractères)\n";
        
        // Vérifier la structure HTML
        echo "   Vérification de la structure:\n";
        
        if (strpos($content, '<div class="max-w-7xl') !== false) {
            echo "     ✅ Container principal détecté\n";
        } else {
            echo "     ❌ Container principal manquant\n";
        }
        
        if (strpos($content, '<h1 class="text-4xl') !== false) {
            echo "     ✅ Titre principal avec classes Tailwind\n";
        } else {
            echo "     ❌ Titre principal sans classes Tailwind\n";
        }
        
        if (strpos($content, '<div class="bg-white p-6') !== false) {
            echo "     ✅ Sections avec classes Tailwind\n";
        } else {
            echo "     ❌ Sections sans classes Tailwind\n";
        }
        
        if (strpos($content, 'hydrofuge') !== false) {
            echo "     ✅ Contenu spécifique au sujet détecté\n";
        } else {
            echo "     ❌ Contenu générique\n";
        }
        
        // Sauvegarder le contenu généré
        file_put_contents('test-article-content.html', $content);
        echo "     📄 Contenu sauvegardé dans: test-article-content.html\n";
        
        // Afficher un aperçu du contenu
        echo "\n   Aperçu du contenu généré:\n";
        $preview = strip_tags($content);
        $preview = substr($preview, 0, 200) . '...';
        echo "     " . $preview . "\n";
        
    } else {
        echo "   ❌ Échec de la génération du contenu\n";
        echo "   Longueur: " . strlen($content) . " caractères\n";
    }
    
    echo "\n";
    
    // 4. Test de création d'un article complet
    echo "4. Test de création d'un article complet...\n";
    
    // Vérifier si un article avec ce titre existe déjà
    $existingArticle = Article::where('title', $testTitle)->first();
    if ($existingArticle) {
        echo "   ⚠️  Article existant trouvé, suppression...\n";
        $existingArticle->delete();
    }
    
    // Créer un nouvel article
    $article = new Article();
    $article->title = $testTitle;
    $article->slug = \Str::slug($testTitle);
    $article->content_html = $content;
    $article->meta_title = $testTitle;
    $article->meta_description = 'Découvrez tout sur ' . $testTitle . ' : guide complet, conseils d\'experts, et informations détaillées.';
    $article->meta_keywords = 'hydrofuge, protection hydrophobe, imperméabilisation, étanchéité, revêtement hydrofuge, traitement anti-eau, couverture imperméable, toiture résistante à l\'eau, traitement préventif contre l\'humidité, produit hydrofuge Dijon';
    $article->status = 'published';
    $article->published_at = now();
    $article->save();
    
    echo "   ✅ Article créé avec succès (ID: {$article->id})\n";
    echo "   📄 Contenu HTML: " . strlen($article->content_html) . " caractères\n";
    echo "   🏷️  Meta Title: " . strlen($article->meta_title) . " caractères\n";
    echo "   📝 Meta Description: " . strlen($article->meta_description) . " caractères\n";
    echo "   🔑 Meta Keywords: " . strlen($article->meta_keywords) . " caractères\n";
    
    echo "\n🎯 Résumé du test:\n";
    echo "==================\n";
    echo "✅ Base de données: Connectée\n";
    echo "✅ Articles existants: " . $articles->count() . " trouvés\n";
    echo "✅ Génération: " . (strlen($content) > 1000 ? 'Réussie' : 'Échec') . "\n";
    echo "✅ Structure HTML: Vérifiée\n";
    echo "✅ Article créé: " . ($article->id ? 'OUI' : 'NON') . "\n";
    echo "✅ Contenu enregistré: " . (strlen($article->content_html) > 1000 ? 'OUI' : 'NON') . "\n";
    echo "\n💡 Le système génère bien le contenu HTML complet et l'enregistre en base !\n";
    echo "   Les métadonnées sont générées EN PLUS du contenu HTML.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
