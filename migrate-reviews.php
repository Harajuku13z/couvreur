<?php

echo "🔄 Migration des avis vers la nouvelle base de données...\n";

// Configuration de l'ancienne base de données
$oldHost = 'localhost';
$oldDbname = 'u570136219_sauser';
$oldUsername = 'u570136219_sauser';
$oldPassword = 'Harajuku1993@';

// Configuration de la nouvelle base de données
$newHost = 'localhost';
$newDbname = 'u182601382_jdrenov';
$newUsername = 'u182601382_jdrenov';
$newPassword = 'Harajuku1993@';

try {
    // Connexion à l'ancienne base
    echo "📡 Connexion à l'ancienne base de données...\n";
    $oldPdo = new PDO(
        "mysql:host=$oldHost;dbname=$oldDbname;charset=utf8mb4",
        $oldUsername,
        $oldPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✅ Connexion à l'ancienne base réussie\n";
    
    // Connexion à la nouvelle base
    echo "📡 Connexion à la nouvelle base de données...\n";
    $newPdo = new PDO(
        "mysql:host=$newHost;dbname=$newDbname;charset=utf8mb4",
        $newUsername,
        $newPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✅ Connexion à la nouvelle base réussie\n";
    
    // Récupérer tous les avis de l'ancienne base
    echo "📋 Récupération des avis de l'ancienne base...\n";
    $reviews = $oldPdo->query("SELECT * FROM reviews ORDER BY created_at DESC")->fetchAll();
    echo "📊 " . count($reviews) . " avis trouvés\n";
    
    if (empty($reviews)) {
        echo "⚠️  Aucun avis à migrer\n";
        exit;
    }
    
    // Vérifier la structure de la nouvelle table
    echo "🔍 Vérification de la structure de la nouvelle table...\n";
    $newColumns = $newPdo->query("DESCRIBE reviews")->fetchAll();
    $newColumnNames = array_column($newColumns, 'Field');
    echo "📋 Colonnes disponibles: " . implode(', ', $newColumnNames) . "\n";
    
    // Migrer chaque avis
    $migrated = 0;
    $skipped = 0;
    
    foreach ($reviews as $review) {
        try {
            // Préparer les données pour l'insertion
            $insertData = [];
            $insertColumns = [];
            
            foreach ($review as $column => $value) {
                if (in_array($column, $newColumnNames)) {
                    $insertColumns[] = $column;
                    $insertData[] = $value;
                }
            }
            
            // Construire la requête d'insertion
            $placeholders = str_repeat('?,', count($insertData) - 1) . '?';
            $columns = implode(',', $insertColumns);
            $sql = "INSERT INTO reviews ($columns) VALUES ($placeholders)";
            
            $stmt = $newPdo->prepare($sql);
            $stmt->execute($insertData);
            
            echo "✅ Avis migré: {$review['author_name']} (ID: {$review['id']})\n";
            $migrated++;
            
        } catch (PDOException $e) {
            echo "❌ Erreur lors de la migration de l'avis {$review['id']}: " . $e->getMessage() . "\n";
            $skipped++;
        }
    }
    
    echo "\n🎉 Migration terminée !\n";
    echo "✅ Avis migrés: $migrated\n";
    echo "❌ Avis ignorés: $skipped\n";
    
    // Vérifier les avis dans la nouvelle base
    echo "\n📊 Vérification des avis dans la nouvelle base...\n";
    $newReviews = $newPdo->query("SELECT COUNT(*) as total FROM reviews")->fetch();
    $activeReviews = $newPdo->query("SELECT COUNT(*) as active FROM reviews WHERE is_active = 1")->fetch();
    $reviewsWithPhotos = $newPdo->query("SELECT COUNT(*) as with_photos FROM reviews WHERE review_photos IS NOT NULL AND review_photos != '' AND review_photos != 'null'")->fetch();
    
    echo "📈 Total avis: {$newReviews['total']}\n";
    echo "✅ Avis actifs: {$activeReviews['active']}\n";
    echo "🖼️ Avis avec photos: {$reviewsWithPhotos['with_photos']}\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
}

echo "\n🎉 Script terminé !\n";
