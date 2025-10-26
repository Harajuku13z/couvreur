<?php

echo "🔧 Diagnostic et correction de l'authentification admin\n";
echo "======================================================\n\n";

// Vérifier si l'utilisateur peut accéder à la page admin
echo "1. Test d'accès à la page admin SEO...\n";

// Simuler une requête vers la page admin SEO
$url = 'https://sausercouverture.fr/admin/seo/pages';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: Mozilla/5.0 (compatible; AdminTest/1.0)',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ],
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Impossible d'accéder à la page (erreur réseau)\n";
} else {
    // Vérifier si la page contient le formulaire de connexion
    if (strpos($response, 'Connexion') !== false || strpos($response, 'Nom d\'utilisateur') !== false) {
        echo "❌ La page affiche le formulaire de connexion\n";
        echo "   → L'utilisateur n'est pas authentifié\n";
    } elseif (strpos($response, 'Configuration SEO par Page') !== false) {
        echo "✅ La page affiche correctement la configuration SEO\n";
    } else {
        echo "⚠️  Réponse inattendue de la page\n";
        echo "   → Contenu reçu : " . substr(strip_tags($response), 0, 200) . "...\n";
    }
}

echo "\n2. Solutions recommandées :\n";
echo "===========================\n";
echo "✅ Solution 1 : Se connecter manuellement\n";
echo "   - Allez sur https://sausercouverture.fr/admin/login\n";
echo "   - Utilisez admin/admin\n";
echo "   - Puis accédez à https://sausercouverture.fr/admin/seo/pages\n\n";

echo "✅ Solution 2 : Vérifier la configuration de session\n";
echo "   - Vérifiez que les sessions fonctionnent sur le serveur\n";
echo "   - Vérifiez que les cookies sont acceptés\n\n";

echo "✅ Solution 3 : Tester l'authentification\n";
echo "   - Testez d'abord https://sausercouverture.fr/admin/login\n";
echo "   - Vérifiez que la connexion fonctionne\n";
echo "   - Puis testez https://sausercouverture.fr/admin/seo/pages\n\n";

echo "🔍 Informations de debug :\n";
echo "- URL testée : $url\n";
echo "- Taille de la réponse : " . strlen($response) . " caractères\n";
echo "- Contient 'Connexion' : " . (strpos($response, 'Connexion') !== false ? 'Oui' : 'Non') . "\n";
echo "- Contient 'SEO' : " . (strpos($response, 'SEO') !== false ? 'Oui' : 'Non') . "\n";
