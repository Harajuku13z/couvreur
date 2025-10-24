<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Amélioration du contenu de fallback\n";
echo "=====================================\n\n";

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données...\n";
    $connection = \DB::connection();
    $connection->getPdo();
    echo "✅ Connexion à la base de données : OK\n\n";
    
    // 2. Créer un contenu de fallback amélioré pour l'hydrofuge
    echo "2. Création d'un contenu de fallback amélioré...\n";
    
    $title = "Comment hydrofuger sa toiture pour une protection optimale";
    $companyName = setting('company_name', 'Artisan Elfrick');
    $companyPhone = setting('company_phone', '0777840495');
    $companySpecialization = setting('company_specialization', 'Travaux de Rénovation');
    
    $improvedContent = '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
        
        <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                L\'hydrofuge de toiture est une technique essentielle pour protéger votre toit contre les intempéries et prolonger sa durée de vie. 
                Cette solution imperméabilisante permet de créer une barrière protectrice qui repousse l\'eau tout en laissant respirer les matériaux.
            </p>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                Chez ' . $companyName . ', nous sommes spécialisés dans ' . $companySpecialization . ' et nous vous accompagnons dans tous vos projets d\'hydrofuge de toiture en Essonne.
            </p>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 my-4">🛠️ Techniques d\'hydrofuge</h2>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                L\'hydrofuge peut être appliqué selon différentes techniques selon le type de toiture :
            </p>
            <ul class="list-disc list-inside text-gray-700 mb-2">
                <li class="mb-2">🏠 <strong>Hydrofuge pour tuiles :</strong> Protection des tuiles en terre cuite ou béton</li>
                <li class="mb-2">🏠 <strong>Hydrofuge pour ardoises :</strong> Traitement spécifique pour l\'ardoise naturelle</li>
                <li class="mb-2">🏠 <strong>Hydrofuge pour zinc :</strong> Protection des toitures en zinc</li>
                <li class="mb-2">🏠 <strong>Hydrofuge pour bac acier :</strong> Traitement des toitures industrielles</li>
            </ul>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 my-4">💡 Avantages de l\'hydrofuge</h2>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                L\'application d\'un traitement hydrofuge sur votre toiture présente de nombreux avantages :
            </p>
            <ul class="list-disc list-inside text-gray-700 mb-2">
                <li class="mb-2">✅ <strong>Protection contre l\'eau :</strong> Imperméabilisation efficace</li>
                <li class="mb-2">✅ <strong>Résistance aux UV :</strong> Protection contre le soleil</li>
                <li class="mb-2">✅ <strong>Anti-mousse :</strong> Prévention de la formation de mousse</li>
                <li class="mb-2">✅ <strong>Durée de vie :</strong> Prolongation de la longévité du toit</li>
                <li class="mb-2">✅ <strong>Économies :</strong> Réduction des coûts d\'entretien</li>
            </ul>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 my-4">⚡ Processus d\'application</h2>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                L\'application d\'un hydrofuge nécessite une préparation minutieuse et un savoir-faire professionnel :
            </p>
            <ol class="list-decimal list-inside text-gray-700 mb-2">
                <li class="mb-2"><strong>Nettoyage :</strong> Préparation de la surface à traiter</li>
                <li class="mb-2"><strong>Réparation :</strong> Correction des défauts existants</li>
                <li class="mb-2"><strong>Application :</strong> Pose du produit hydrofuge</li>
                <li class="mb-2"><strong>Séchage :</strong> Temps de séchage respecté</li>
                <li class="mb-2"><strong>Contrôle :</strong> Vérification de la qualité du traitement</li>
            </ol>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg mb-4">
            <h2 class="text-2xl font-semibold text-gray-800 my-4">❓ Questions Fréquentes</h2>
            <div class="mb-4">
                <h3 class="font-bold text-gray-800">Qu\'est-ce que l\'hydrofuge de toiture ?</h3>
                <p class="text-gray-700">L\'hydrofuge est un traitement imperméabilisant qui protège votre toiture contre l\'eau tout en laissant respirer les matériaux.</p>
            </div>
            <div class="mb-4">
                <h3 class="font-bold text-gray-800">Combien de temps dure un traitement hydrofuge ?</h3>
                <p class="text-gray-700">Un traitement hydrofuge de qualité peut durer entre 5 et 10 ans selon les conditions climatiques et l\'entretien.</p>
            </div>
            <div class="mb-4">
                <h3 class="font-bold text-gray-800">Quel est le prix d\'un hydrofuge de toiture ?</h3>
                <p class="text-gray-700">Le prix varie selon la surface, le type de toiture et la complexité du chantier. Contactez-nous pour un devis personnalisé.</p>
            </div>
            <div class="mb-4">
                <h3 class="font-bold text-gray-800">Peut-on faire l\'hydrofuge soi-même ?</h3>
                <p class="text-gray-700">Il est recommandé de faire appel à un professionnel pour garantir la qualité et la durabilité du traitement.</p>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
            <h2 class="text-2xl font-semibold text-gray-800 my-4">🎯 Conclusion</h2>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                L\'hydrofuge de toiture est un investissement judicieux pour protéger votre bien immobilier. 
                Cette technique professionnelle vous garantit une protection durable contre les intempéries.
            </p>
            <p class="text-gray-700 text-base leading-relaxed mb-4">
                N\'hésitez pas à contacter ' . $companyName . ' pour tous vos besoins en hydrofuge de toiture en Essonne. 
                Notre équipe de professionnels vous accompagne dans votre projet avec expertise et qualité.
            </p>
            <div class="text-center mt-6">
                <a href="tel:' . $companyPhone . '" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 inline-block">
                    📞 Appelez ' . $companyName . ' maintenant
                </a>
            </div>
        </div>
    </div>';
    
    // Sauvegarder le contenu amélioré
    file_put_contents('improved-fallback-content.html', $improvedContent);
    echo "   ✅ Contenu de fallback amélioré créé\n";
    echo "   📄 Sauvegardé dans: improved-fallback-content.html\n";
    echo "   📏 Longueur: " . strlen($improvedContent) . " caractères\n";
    
    echo "\n";
    
    // 3. Vérifier la structure
    echo "3. Vérification de la structure...\n";
    
    $structureChecks = [
        'max-w-7xl' => 'Container principal',
        'text-4xl font-bold' => 'Titre principal',
        'bg-white p-6 rounded-xl shadow' => 'Sections',
        'bg-green-50' => 'FAQ',
        'bg-blue-500' => 'Call-to-action',
        'hydrofuge' => 'Contenu spécifique',
        '🏠' => 'Emojis'
    ];
    
    foreach ($structureChecks as $check => $description) {
        if (strpos($improvedContent, $check) !== false) {
            echo "   ✅ {$description}: Présent\n";
        } else {
            echo "   ❌ {$description}: Manquant\n";
        }
    }
    
    echo "\n🎯 Résumé de l'amélioration:\n";
    echo "============================\n";
    echo "✅ Contenu de fallback amélioré\n";
    echo "✅ Structure HTML complète\n";
    echo "✅ Contenu spécifique à l'hydrofuge\n";
    echo "✅ Emojis et éléments visuels\n";
    echo "✅ Call-to-action intégré\n";
    echo "✅ FAQ pertinente\n";
    echo "\n💡 Le contenu de fallback est maintenant de qualité professionnelle !\n";
    echo "   Pour utiliser l'API OpenAI, configurez une vraie clé API dans /admin/config\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'amélioration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
