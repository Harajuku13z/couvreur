<?php

echo "🔧 Correction de la configuration SEO...\n";

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
    
    // Vérifier les paramètres SEO actuels
    echo "\n📋 Paramètres SEO actuels :\n";
    $settings = $pdo->query("SELECT * FROM settings WHERE `key` LIKE '%seo%' OR `key` LIKE '%og_%' OR `key` LIKE '%meta_%' OR `key` LIKE '%company_%'")->fetchAll();
    
    foreach ($settings as $setting) {
        echo "- {$setting['key']}: {$setting['value']}\n";
    }
    
    // Créer les paramètres SEO par défaut s'ils n'existent pas
    $defaultSettings = [
        'meta_title' => 'Sauser Couverture - Expert en Travaux de Rénovation',
        'meta_description' => 'Expert en travaux de rénovation et couverture. Devis gratuit, intervention rapide, qualité garantie. Spécialiste toiture, façade, zinguerie.',
        'meta_keywords' => 'couverture, rénovation, toiture, façade, zinguerie, travaux, devis gratuit',
        'og_title' => 'Sauser Couverture - Expert en Travaux de Rénovation',
        'og_description' => 'Expert en travaux de rénovation et couverture. Devis gratuit, intervention rapide, qualité garantie.',
        'og_image' => 'images/og-accueil.jpg',
        'company_name' => 'Sauser Couverture',
        'company_logo' => 'logo/logo.png',
        'site_favicon' => 'favicon-1761020023.png'
    ];
    
    echo "\n🔧 Mise à jour des paramètres SEO...\n";
    
    foreach ($defaultSettings as $key => $value) {
        // Vérifier si le paramètre existe
        $existing = $pdo->prepare("SELECT id FROM settings WHERE `key` = ?");
        $existing->execute([$key]);
        
        if ($existing->rowCount() > 0) {
            // Mettre à jour
            $update = $pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
            $update->execute([$value, $key]);
            echo "✅ Mis à jour: $key = $value\n";
        } else {
            // Créer
            $insert = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
            $insert->execute([$key, $value]);
            echo "➕ Créé: $key = $value\n";
        }
    }
    
    echo "\n🎉 Configuration SEO mise à jour !\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎉 Script terminé !\n";
