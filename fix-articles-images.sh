#!/bin/bash

echo "🔧 Correction des images d'articles..."

# Vérifier si le lien symbolique existe
if [ -L "public/storage" ]; then
    echo "✅ Lien symbolique public/storage existe"
    echo "📁 Pointe vers: $(readlink public/storage)"
else
    echo "❌ Lien symbolique public/storage manquant"
    echo "🔗 Création du lien symbolique..."
    php artisan storage:link
fi

# Créer le dossier articles s'il n'existe pas
if [ ! -d "storage/app/public/articles" ]; then
    echo "📁 Création du dossier storage/app/public/articles"
    mkdir -p storage/app/public/articles
fi

# Vérifier les permissions
echo "🔐 Vérification des permissions..."
chmod -R 755 storage/app/public
chmod -R 755 public/storage

# Tester l'accès
echo "🧪 Test d'accès aux images..."
if [ -d "public/storage/articles" ]; then
    echo "✅ Dossier public/storage/articles accessible"
    ls -la public/storage/articles/ | head -5
else
    echo "❌ Dossier public/storage/articles non accessible"
fi

echo "✅ Correction terminée"
