#!/bin/bash

# Script pour corriger le problème de lien symbolique storage
echo "🔍 Diagnostic du problème de storage..."

# Vérifier l'état actuel
echo "📋 État actuel de public/storage:"
ls -la public/ | grep storage

echo "📋 État actuel de storage/app/public:"
ls -la storage/app/ | grep public

# Supprimer le lien symbolique existant s'il est cassé
if [ -L "public/storage" ]; then
    echo "🔗 Lien symbolique détecté, vérification..."
    if [ ! -e "public/storage" ]; then
        echo "⚠️  Lien symbolique cassé, suppression..."
        rm -f public/storage
    else
        echo "✅ Lien symbolique fonctionnel"
    fi
elif [ -d "public/storage" ]; then
    echo "📁 Répertoire storage existant, vérification des permissions..."
    ls -la public/storage
elif [ -f "public/storage" ]; then
    echo "📄 Fichier storage détecté, suppression..."
    rm -f public/storage
fi

# Créer le répertoire storage/app/public s'il n'existe pas
echo "📁 Création du répertoire storage/app/public..."
mkdir -p storage/app/public

# Créer le lien symbolique
echo "🔗 Création du lien symbolique..."
ln -sfn ../storage/app/public public/storage

# Créer les sous-répertoires d'upload
echo "📁 Création des répertoires d'upload..."
mkdir -p storage/app/public/uploads/services
mkdir -p storage/app/public/uploads/portfolio
mkdir -p storage/app/public/uploads/articles
mkdir -p storage/app/public/uploads/homepage

# Définir les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 storage/app/public/
chmod -R 755 public/storage/

# Vérification finale
echo "✅ Vérification finale:"
echo "📋 Lien symbolique:"
ls -la public/ | grep storage
echo "📋 Contenu de storage/app/public:"
ls -la storage/app/public/
echo "📋 Contenu de public/storage:"
ls -la public/storage/

echo "🎉 Correction terminée !"
