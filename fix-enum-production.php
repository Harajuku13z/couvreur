<?php
/**
 * Script de correction immédiate pour l'enum generation_jobs
 * À exécuter sur le serveur de production
 */

echo "🚨 CORRECTION IMMÉDIATE - Enum generation_jobs\n";
echo "=============================================\n\n";

// Configuration de la base de données (à adapter selon votre serveur)
$host = 'localhost';
$dbname = 'u182601382_jdrenov';
$username = 'root'; // À adapter selon votre configuration
$password = ''; // À adapter selon votre configuration

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connexion à la base de données réussie\n";
    
    // Vérifier la structure actuelle
    echo "📊 Vérification de la structure actuelle...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM generation_jobs WHERE Field = 'mode'");
    $currentStructure = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Structure actuelle: " . $currentStructure['Type'] . "\n\n";
    
    // Appliquer la correction
    echo "🔧 Application de la correction SQL...\n";
    $sql = "ALTER TABLE generation_jobs 
            MODIFY COLUMN mode ENUM('keyword', 'titles', 'keyword_cities', 'keyword_services', 'service_cities', 'bulk_generation') NOT NULL";
    
    $pdo->exec($sql);
    
    echo "✅ Correction SQL réussie !\n\n";
    
    // Vérifier la nouvelle structure
    echo "📊 Vérification de la nouvelle structure...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM generation_jobs WHERE Field = 'mode'");
    $newStructure = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Nouvelle structure: " . $newStructure['Type'] . "\n\n";
    
    echo "🎉 CORRECTION TERMINÉE !\n";
    echo "💡 L'enum generation_jobs supporte maintenant 'service_cities'\n";
    echo "💡 La génération d'annonces devrait fonctionner\n\n";
    
    echo "🧪 Test de la correction...\n";
    echo "💡 Essayez maintenant de générer des annonces depuis l'interface admin\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    echo "💡 Vérifiez les paramètres de connexion dans le script\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Erreur lors de la correction: " . $e->getMessage() . "\n";
    exit(1);
}
?>
