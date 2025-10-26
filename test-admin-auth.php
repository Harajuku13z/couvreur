<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = new Application(realpath(__DIR__));

// Test de l'authentification admin
echo "🔍 Test de l'authentification admin\n";
echo "=====================================\n\n";

// Simuler une session admin
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'admin';
$_SESSION['admin_login_time'] = date('Y-m-d H:i:s');

echo "✅ Session admin simulée :\n";
echo "- admin_logged_in: " . ($_SESSION['admin_logged_in'] ? 'true' : 'false') . "\n";
echo "- admin_username: " . ($_SESSION['admin_username'] ?? 'non défini') . "\n";
echo "- admin_login_time: " . ($_SESSION['admin_login_time'] ?? 'non défini') . "\n\n";

// Test de la route admin.seo.pages
echo "🌐 Test de la route admin.seo.pages\n";
echo "===================================\n";

try {
    // Simuler une requête vers admin.seo.pages
    $request = Request::create('/admin/seo/pages', 'GET');
    
    // Ajouter la session à la requête
    $request->setLaravelSession(session());
    
    echo "✅ Requête créée vers /admin/seo/pages\n";
    echo "✅ Session attachée à la requête\n";
    
    // Vérifier si la session contient admin_logged_in
    if (session()->has('admin_logged_in')) {
        echo "✅ Session admin détectée\n";
    } else {
        echo "❌ Session admin non détectée\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur lors du test : " . $e->getMessage() . "\n";
}

echo "\n📋 Instructions pour corriger le problème :\n";
echo "1. Allez sur https://sausercouverture.fr/admin/login\n";
echo "2. Connectez-vous avec admin/admin\n";
echo "3. Puis allez sur https://sausercouverture.fr/admin/seo/pages\n";
echo "\n🔧 Alternative : Vérifiez que la session est bien persistante\n";
