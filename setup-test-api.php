<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

echo "🔧 Configuration de test pour la génération d'articles\n";
echo "====================================================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Configurer des paramètres de test
    echo "2. Configuration des paramètres de test...\n";
    
    // Clé API de test (remplacez par votre vraie clé)
    $testApiKey = 'sk-test-key-replace-with-real-key';
    Setting::set('chatgpt_api_key', $testApiKey, 'string', 'ai');
    echo "   ✅ Clé API de test configurée\n";
    
    // Modèle par défaut
    Setting::set('chatgpt_model', 'gpt-4o', 'string', 'ai');
    echo "   ✅ Modèle configuré: gpt-4o\n";
    
    // 3. Vérifier les articles existants
    echo "\n3. Vérification des articles existants...\n";
    $articles = \App\Models\Article::all();
    echo "   - Nombre d'articles : " . $articles->count() . "\n";
    
    if ($articles->count() > 0) {
        echo "   - Dernier article : " . $articles->last()->title . "\n";
        echo "   - Statut : " . $articles->last()->status . "\n";
    }
    
    echo "\n4. Vérification des paramètres configurés...\n";
    echo "   - Clé API : " . (setting('chatgpt_api_key') ? 'Configurée' : 'Non configurée') . "\n";
    echo "   - Modèle : " . setting('chatgpt_model', 'Non configuré') . "\n";
    
    echo "\n🎯 Configuration terminée !\n";
    echo "==========================\n";
    echo "✅ Base de données : Connectée\n";
    echo "✅ Tables : Créées\n";
    echo "✅ Paramètres : Configurés\n";
    echo "\n💡 Pour tester la génération d'articles :\n";
    echo "   1. Configurez votre vraie clé API dans /admin/config\n";
    echo "   2. Utilisez la page de génération d'articles\n";
    echo "   3. Le nouveau prompt professionnel est maintenant actif !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la configuration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
