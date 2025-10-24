#!/bin/bash

# Script pour nettoyer les conflits de fichiers sur le serveur de production
echo "🧹 Nettoyage des conflits de fichiers..."

# Vérifier et supprimer les fichiers qui entrent en conflit avec les répertoires
CONFLICT_PATHS=(
    "public/storage/uploads/services"
    "public/storage/uploads/portfolio"
    "public/storage/uploads/articles"
    "public/storage/uploads/homepage"
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
chmod -R 755 public/storage/
chown -R www-data:www-data public/storage/ 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (peut nécessiter sudo)"

# Vérification finale
echo "✅ Vérification des répertoires:"
ls -la public/storage/uploads/

echo "🎉 Nettoyage terminé !"
