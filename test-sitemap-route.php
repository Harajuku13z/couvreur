<?php

echo "🔍 Test de la route admin.seo.update-sitemap\n";
echo "===========================================\n\n";

// Test 1: Vérifier que la route existe
echo "1. Vérification de la route...\n";
$output = shell_exec('php artisan route:list | grep update-sitemap');
if ($output && strpos($output, 'admin.seo.update-sitemap') !== false) {
    echo "✅ Route trouvée: " . trim($output) . "\n";
} else {
    echo "❌ Route non trouvée\n";
    exit(1);
}

// Test 2: Tester l'accès à la route (sans authentification)
echo "\n2. Test d'accès à la route (sans authentification)...\n";
$url = 'https://sausercouverture.fr/admin/seo/update-sitemap';
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: TestScript/1.0'
        ],
        'timeout' => 10
    ]
]);

$response = @file_get_contents($url, false, $context);
$httpCode = 0;
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
            $httpCode = (int)$matches[1];
            break;
        }
    }
}

echo "Code de réponse HTTP: $httpCode\n";
if ($httpCode === 401 || $httpCode === 403) {
    echo "✅ Route accessible mais authentification requise (normal)\n";
} elseif ($httpCode === 404) {
    echo "❌ Route non trouvée (404)\n";
} elseif ($httpCode === 200) {
    echo "⚠️  Route accessible sans authentification (problème de sécurité)\n";
} else {
    echo "⚠️  Code de réponse inattendu: $httpCode\n";
}

// Test 3: Vérifier la méthode du contrôleur
echo "\n3. Vérification du contrôleur...\n";
$controllerFile = __DIR__ . '/app/Http/Controllers/SeoController.php';
if (file_exists($controllerFile)) {
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'public function updateSitemap') !== false) {
        echo "✅ Méthode updateSitemap trouvée dans SeoController\n";
    } else {
        echo "❌ Méthode updateSitemap non trouvée dans SeoController\n";
    }
} else {
    echo "❌ Fichier SeoController non trouvé\n";
}

echo "\n📋 Instructions pour résoudre le problème :\n";
echo "==========================================\n";
echo "1. Connectez-vous d'abord sur https://sausercouverture.fr/admin/login\n";
echo "2. Utilisez admin/admin\n";
echo "3. Puis testez le bouton de mise à jour du sitemap\n";
echo "4. Si le problème persiste, vérifiez les logs Laravel\n";
