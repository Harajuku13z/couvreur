#!/bin/bash

echo "🚀 Déploiement de la correction des images d'articles..."

# Se connecter au serveur et exécuter les corrections
ssh root@jd-renovation-service.fr << 'EOF'
cd /public_html

echo "🔧 Diagnostic des images d'articles sur le serveur..."

# Vérifier si le lien symbolique existe
echo "📁 Vérification du lien symbolique public/storage:"
if [ -L "public/storage" ]; then
    echo "✅ Lien symbolique public/storage existe"
    echo "📁 Pointe vers: $(readlink public/storage)"
else
    echo "❌ Lien symbolique public/storage manquant"
fi

# Vérifier le dossier storage/app/public
echo "📁 Vérification du dossier storage/app/public:"
if [ -d "storage/app/public" ]; then
    echo "✅ Dossier storage/app/public existe"
    ls -la storage/app/public/ | head -5
else
    echo "❌ Dossier storage/app/public n'existe pas"
fi

# Créer le dossier articles s'il n'existe pas
echo "📁 Création du dossier articles:"
if [ ! -d "storage/app/public/articles" ]; then
    echo "📁 Création du dossier storage/app/public/articles"
    mkdir -p storage/app/public/articles
    echo "✅ Dossier créé"
else
    echo "✅ Dossier storage/app/public/articles existe déjà"
fi

# Vérifier les permissions
echo "🔐 Vérification des permissions:"
chmod -R 755 storage/app/public
chmod -R 755 public/storage
echo "✅ Permissions mises à jour"

# Recréer le lien symbolique
echo "🔗 Recréation du lien symbolique:"
rm -rf public/storage
php artisan storage:link
echo "✅ Lien symbolique recréé"

# Tester l'accès
echo "🧪 Test d'accès aux images:"
if [ -d "public/storage/articles" ]; then
    echo "✅ Dossier public/storage/articles accessible"
    ls -la public/storage/articles/ | head -5
else
    echo "❌ Dossier public/storage/articles non accessible"
fi

# Vérifier les images existantes
echo "📸 Images existantes dans storage/app/public:"
find storage/app/public -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" | head -10

echo "✅ Diagnostic terminé sur le serveur"
EOF

echo "✅ Déploiement terminé"