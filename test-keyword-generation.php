<?php

// Script de test simple pour diagnostiquer la génération keyword-cities
// À exécuter sur le serveur : php test-keyword-generation.php

echo "🔍 Test de génération keyword-cities\n";
echo "===================================\n\n";

// 1. Test de base - vérifier que les classes existent
echo "1. Vérification des classes...\n";
try {
    if (class_exists('App\Models\City')) {
        echo "✅ Classe City trouvée\n";
    } else {
        echo "❌ Classe City non trouvée\n";
    }
    
    if (class_exists('App\Models\Ad')) {
        echo "✅ Classe Ad trouvée\n";
    } else {
        echo "❌ Classe Ad non trouvée\n";
    }
    
    if (class_exists('App\Models\Setting')) {
        echo "✅ Classe Setting trouvée\n";
    } else {
        echo "❌ Classe Setting non trouvée\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

// 2. Test de connexion à la base de données
echo "\n2. Test de connexion à la base de données...\n";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=couvreur', 'root', '');
    echo "✅ Connexion DB réussie\n";
    
    // Compter les villes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Villes en base: " . $result['count'] . "\n";
    
    // Compter les villes favorites
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cities WHERE is_favorite = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Villes favorites: " . $result['count'] . "\n";
    
    // Compter les annonces
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM ads");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Annonces en base: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur DB: " . $e->getMessage() . "\n";
}

// 3. Test de la clé API
echo "\n3. Test de la clé API ChatGPT...\n";
try {
    $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'chatgpt_api_key'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && !empty($result['value'])) {
        echo "✅ Clé API trouvée (longueur: " . strlen($result['value']) . ")\n";
        
        // Test simple de l'API
        $apiKey = $result['value'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => 'Test - répondez "OK"']
            ],
            'max_tokens' => 10
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data && isset($data['choices'][0]['message']['content'])) {
                echo "✅ API ChatGPT fonctionne: " . trim($data['choices'][0]['message']['content']) . "\n";
            } else {
                echo "❌ Réponse API invalide\n";
            }
        } else {
            echo "❌ Erreur API (HTTP $httpCode): " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "❌ Clé API non trouvée ou vide\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

// 4. Test de génération d'une annonce
echo "\n4. Test de génération d'annonce...\n";
try {
    // Récupérer une ville favorite
    $stmt = $pdo->query("SELECT * FROM cities WHERE is_favorite = 1 LIMIT 1");
    $city = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($city) {
        echo "   Ville de test: " . $city['name'] . "\n";
        
        // Générer le contenu via API
        $keywords = "rénovation toiture";
        $prompt = "Génère une annonce SEO pour: {$keywords} à {$city['name']}. Réponds en JSON: {\"title\":\"Titre\",\"content\":\"Contenu HTML\",\"meta_title\":\"Meta titre\",\"meta_description\":\"Meta description\"}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => 500
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            
            echo "✅ Contenu généré (" . strlen($content) . " caractères)\n";
            
            // Parser le JSON
            $jsonStart = strpos($content, '{');
            $jsonEnd = strrpos($content, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $parsed = json_decode($jsonContent, true);
                
                if ($parsed && isset($parsed['title'])) {
                    echo "✅ JSON parsé avec succès\n";
                    echo "   Titre: " . $parsed['title'] . "\n";
                    
                    // Tenter de créer l'annonce
                    $stmt = $pdo->prepare("INSERT INTO ads (title, keyword, city_id, slug, status, meta_title, meta_description, content_html, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $slug = strtolower(str_replace(' ', '-', $parsed['title'] . '-' . $city['name']));
                    $result = $stmt->execute([
                        $parsed['title'],
                        $keywords . ' ' . $city['name'],
                        $city['id'],
                        $slug,
                        'published',
                        $parsed['meta_title'] ?? $parsed['title'],
                        $parsed['meta_description'] ?? '',
                        $parsed['content'] ?? ''
                    ]);
                    
                    if ($result) {
                        echo "✅ Annonce créée avec succès (ID: " . $pdo->lastInsertId() . ")\n";
                    } else {
                        echo "❌ Erreur lors de la création de l'annonce\n";
                    }
                } else {
                    echo "❌ JSON invalide\n";
                    echo "   Contenu reçu: " . substr($content, 0, 200) . "...\n";
                }
            } else {
                echo "❌ Aucun JSON trouvé dans la réponse\n";
                echo "   Contenu reçu: " . substr($content, 0, 200) . "...\n";
            }
        } else {
            echo "❌ Erreur API (HTTP $httpCode): " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "❌ Aucune ville favorite trouvée\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🏁 Test terminé\n";
echo "==============\n";
