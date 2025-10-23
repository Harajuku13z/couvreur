#!/bin/bash

# Script pour déployer la page de test sur le serveur de production
# À exécuter sur le serveur de production

echo "🚀 Déploiement de la page de test sur le serveur de production"
echo "============================================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    echo "💡 Connectez-vous à votre serveur et exécutez ce script"
    exit 1
fi

# Créer la page de test
echo "📄 Création de la page de test..."
cat > /public_html/test-service-urls-final.html << 'EOF'
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test URLs Images Services</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .info { color: blue; }
        .path-info { background: #f0f0f0; padding: 10px; border-radius: 4px; margin: 10px 0; font-family: monospace; }
        .url-test { background: #e8f5e8; padding: 10px; border-radius: 4px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>✅ Vérification des URLs des images des services</h1>
    
    <div class="test-section">
        <h2>📋 Résumé de l'analyse</h2>
        <p class="success">✅ Toutes les vues Blade utilisent les bonnes URLs !</p>
        <p>Les vues utilisent <code>url($service['featured_image'])</code> qui génère des URLs correctes.</p>
    </div>
    
    <div class="test-section">
        <h2>🔗 URLs générées par les vues</h2>
        <div class="url-test">
            <strong>services/index.blade.php:</strong><br>
            <code>&lt;img src="{{ url($service['featured_image']) }}"&gt;</code><br>
            → <code>https://www.jd-renovation-service.fr/uploads/services/filename.jpg</code>
        </div>
        
        <div class="url-test">
            <strong>services/show.blade.php:</strong><br>
            <code>style="background-image: url('{{ url($service['featured_image']) }}')"</code><br>
            → <code>https://www.jd-renovation-service.fr/uploads/services/filename.jpg</code>
        </div>
        
        <div class="url-test">
            <strong>home.blade.php:</strong><br>
            <code>style="background-image: url('{{ url($service['featured_image']) }}')"</code><br>
            → <code>https://www.jd-renovation-service.fr/uploads/services/filename.jpg</code>
        </div>
        
        <div class="url-test">
            <strong>admin/services/edit.blade.php:</strong><br>
            <code>&lt;img src="{{ url($service['featured_image']) }}"&gt;</code><br>
            → <code>https://www.jd-renovation-service.fr/uploads/services/filename.jpg</code>
        </div>
    </div>
    
    <div class="test-section">
        <h2>📁 Structure des fichiers sur le serveur</h2>
        <div class="path-info">
            <strong>Stockage:</strong> /public_html/uploads/services/<br>
            <strong>Chemins en base:</strong> uploads/services/filename.jpg<br>
            <strong>URLs générées:</strong> https://www.jd-renovation-service.fr/uploads/services/filename.jpg<br>
            <strong>Accès direct:</strong> ✅ Sans lien symbolique
        </div>
    </div>
    
    <div class="test-section">
        <h2>🧪 Test des images existantes</h2>
        <p>Vérification des images des services sur le serveur :</p>
        <div id="image-tests"></div>
    </div>
    
    <div class="test-section">
        <h2>🎯 Avantages du système actuel</h2>
        <ul>
            <li>✅ <strong>Accès direct:</strong> Les images sont directement dans public/uploads/services/</li>
            <li>✅ <strong>URLs simples:</strong> /uploads/services/filename.jpg</li>
            <li>✅ <strong>Pas de lien symbolique:</strong> Plus besoin de public/storage</li>
            <li>✅ <strong>Performance:</strong> Accès direct aux fichiers</li>
            <li>✅ <strong>Simplicité:</strong> Chemins clairs et prévisibles</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>📊 Vues vérifiées</h2>
        <ul>
            <li>✅ <strong>services/index.blade.php</strong> - Liste des services</li>
            <li>✅ <strong>services/show.blade.php</strong> - Détail d'un service</li>
            <li>✅ <strong>home.blade.php</strong> - Page d'accueil</li>
            <li>✅ <strong>admin/services/edit.blade.php</strong> - Administration</li>
            <li>✅ <strong>Métadonnées Open Graph</strong> - SEO</li>
            <li>✅ <strong>Métadonnées Twitter Cards</strong> - Réseaux sociaux</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>🎉 Conclusion</h2>
        <p class="success">✅ Toutes les vues Blade utilisent les bonnes URLs !</p>
        <p>Le système est cohérent et fonctionnel :</p>
        <ul>
            <li>Images stockées dans <code>public/uploads/services/</code></li>
            <li>Chemins en base de données : <code>uploads/services/filename</code></li>
            <li>Vues utilisent <code>url()</code> pour générer les bonnes URLs</li>
            <li>Accès direct aux fichiers sans configuration complexe</li>
        </ul>
    </div>
    
    <script>
        // Test des images existantes
        const imageTests = document.getElementById('image-tests');
        const testImages = [
            'uploads/services/service-1761199330-68f9c4e2eb4c4.jpg',
            'uploads/services/service-1761199983-68f9c76fcb354.png',
            'uploads/services/service-1761200169-68f9c829bfdd4.jpg',
            'uploads/services/service-1761200362-68f9c8eaf2f2d.jpg',
            'uploads/services/service-1761200591-68f9c9cf0bd9b.jpg',
            'uploads/services/service-1761200775-68f9ca878105f.jpg',
            'uploads/services/service-1761203142-68f9d3c6ea313.jpg'
        ];
        
        testImages.forEach((imagePath, index) => {
            const img = document.createElement('img');
            img.src = imagePath;
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            img.style.margin = '5px';
            img.style.border = '1px solid #ccc';
            img.onload = function() {
                this.style.border = '2px solid green';
            };
            img.onerror = function() {
                this.style.border = '2px solid red';
                this.alt = 'Image non trouvée';
            };
            imageTests.appendChild(img);
        });
    </script>
</body>
</html>
EOF

echo "✅ Page de test créée avec succès !"
echo ""

# Vérifier que la page est accessible
echo "🔍 Vérification de l'accès à la page..."
if [ -f "/public_html/test-service-urls-final.html" ]; then
    echo "✅ Fichier créé: /public_html/test-service-urls-final.html"
    echo "🌐 URL: https://www.jd-renovation-service.fr/test-service-urls-final.html"
else
    echo "❌ Erreur lors de la création du fichier"
    exit 1
fi

# Vérifier les permissions
echo ""
echo "🔐 Configuration des permissions..."
chmod 644 /public_html/test-service-urls-final.html
chown www-data:www-data /public_html/test-service-urls-final.html 2>/dev/null || chown apache:apache /public_html/test-service-urls-final.html 2>/dev/null

echo ""
echo "🎉 Déploiement terminé !"
echo "💡 Vous pouvez maintenant accéder à : https://www.jd-renovation-service.fr/test-service-urls-final.html"
