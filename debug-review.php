<?php

echo "🔍 Debug de l'avis avec photo...\n";

// Configuration de la base de données
$host = 'localhost';
$dbname = 'u182601382_jdrenov';
$username = 'u182601382_jdrenov';
$password = 'Harajuku1993@';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier les avis récents
    echo "\n📋 Avis récents (derniers 5) :\n";
    $recentReviews = $pdo->query("
        SELECT id, author_name, rating, review_text, is_active, review_photos, created_at 
        FROM reviews 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
    foreach ($recentReviews as $review) {
        echo "ID: {$review['id']}\n";
        echo "Nom: {$review['author_name']}\n";
        echo "Note: {$review['rating']}\n";
        echo "Actif: " . ($review['is_active'] ? 'Oui' : 'Non') . "\n";
        echo "Photos: " . ($review['review_photos'] ?: 'Aucune') . "\n";
        echo "Date: {$review['created_at']}\n";
        echo "---\n";
    }
    
    // Vérifier la structure de la table
    echo "\n📊 Structure de la table reviews :\n";
    $columns = $pdo->query("DESCRIBE reviews")->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Vérifier les avis avec photos
    echo "\n🖼️ Avis avec photos :\n";
    $reviewsWithPhotos = $pdo->query("
        SELECT id, author_name, review_photos, is_active 
        FROM reviews 
        WHERE review_photos IS NOT NULL AND review_photos != 'null' AND review_photos != ''
        ORDER BY created_at DESC
    ")->fetchAll();
    
    foreach ($reviewsWithPhotos as $review) {
        echo "ID: {$review['id']} - {$review['author_name']} - Actif: " . ($review['is_active'] ? 'Oui' : 'Non') . "\n";
        echo "Photos: {$review['review_photos']}\n";
        echo "---\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "\n";
}

echo "\n🎉 Debug terminé !\n";
