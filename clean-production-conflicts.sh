#!/bin/bash

# Script pour nettoyer les conflits de fichiers sur le serveur de production
echo "🧹 Nettoyage des conflits de fichiers..."

# Vérifier d'abord l'état du lien symbolique storage
echo "🔍 Vérification du lien symbolique storage..."
if [ -L "public/storage" ]; then
    echo "✅ Lien symbolique storage détecté"
    if [ ! -e "public/storage" ]; then
        echo "⚠️  Lien symbolique cassé, suppression..."
        rm -f public/storage
    fi
elif [ -f "public/storage" ]; then
    echo "⚠️  Fichier storage détecté, suppression..."
    rm -f public/storage
fi

# Créer le répertoire storage/app/public s'il n'existe pas
echo "📁 Création du répertoire storage/app/public..."
mkdir -p storage/app/public

# Créer le lien symbolique s'il n'existe pas
if [ ! -e "public/storage" ]; then
    echo "🔗 Création du lien symbolique storage..."
    ln -sfn ../storage/app/public public/storage
fi

# Vérifier et supprimer les fichiers qui entrent en conflit avec les répertoires
CONFLICT_PATHS=(
    "storage/app/public/uploads/services"
    "storage/app/public/uploads/portfolio"
    "storage/app/public/uploads/articles"
    "storage/app/public/uploads/homepage"
)

for path in "${CONFLICT_PATHS[@]}"; do
    if [ -f "$path" ]; then
        echo "⚠️  Fichier trouvé au lieu d'un répertoire: $path"
        echo "🗑️  Suppression du fichier conflictuel..."
        rm -f "$path"
        echo "✅ Fichier supprimé: $path"
    elif [ -d "$path" ]; then
        echo "✅ Répertoire existe déjà: $path"
    else
        echo "📁 Création du répertoire: $path"
        mkdir -p "$path"
    fi
done

# Définir les permissions appropriées
echo "🔐 Configuration des permissions..."
chmod -R 755 storage/app/public/
chmod -R 755 public/storage/ 2>/dev/null || echo "⚠️  Impossible d'accéder à public/storage (lien symbolique)"

# Vérification finale
echo "✅ Vérification des répertoires:"
echo "📋 Contenu de storage/app/public/uploads:"
ls -la storage/app/public/uploads/ 2>/dev/null || echo "⚠️  Répertoire uploads non trouvé"
echo "📋 Lien symbolique public/storage:"
ls -la public/ | grep storage

echo "🎉 Nettoyage terminé !"
