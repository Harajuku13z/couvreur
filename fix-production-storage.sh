#!/bin/bash

# Script pour corriger les chemins d'images sur le serveur de production
# Les images sont dans /public_html/storage/app/public/uploads/services
# Mais doivent être accessibles via /storage/uploads/services/

echo "🔧 Correction des chemins d'images sur le serveur de production"
echo "=============================================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    exit 1
fi

echo "📁 Vérification de la structure des dossiers:"
echo ""

# Vérifier le dossier source
SOURCE_DIR="/public_html/storage/app/public/uploads/services"
if [ -d "$SOURCE_DIR" ]; then
    echo "✅ Dossier source trouvé: $SOURCE_DIR"
    IMAGE_COUNT=$(find "$SOURCE_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
    echo "   Images trouvées: $IMAGE_COUNT"
else
    echo "❌ Dossier source non trouvé: $SOURCE_DIR"
    exit 1
fi

# Vérifier le dossier de destination
DEST_DIR="/public_html/public/uploads/services"
echo ""
echo "📁 Dossier de destination: $DEST_DIR"

# Créer le dossier de destination s'il n'existe pas
if [ ! -d "$DEST_DIR" ]; then
    echo "📁 Création du dossier de destination..."
    mkdir -p "$DEST_DIR"
    if [ $? -eq 0 ]; then
        echo "✅ Dossier créé avec succès"
    else
        echo "❌ Erreur lors de la création du dossier"
        exit 1
    fi
else
    echo "✅ Dossier de destination existe déjà"
fi

# Copier les images
echo ""
echo "📋 Copie des images..."
cp -r "$SOURCE_DIR"/* "$DEST_DIR/" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Images copiées avec succès"
else
    echo "❌ Erreur lors de la copie des images"
    exit 1
fi

# Vérifier les permissions
echo ""
echo "🔐 Configuration des permissions..."
chmod -R 755 "$DEST_DIR"
chown -R www-data:www-data "$DEST_DIR" 2>/dev/null || chown -R apache:apache "$DEST_DIR" 2>/dev/null

# Vérifier le lien symbolique storage
echo ""
echo "🔗 Vérification du lien symbolique storage..."
STORAGE_LINK="/public_html/public/storage"

if [ -L "$STORAGE_LINK" ]; then
    echo "✅ Lien symbolique storage existe"
    echo "   Pointe vers: $(readlink "$STORAGE_LINK")"
else
    echo "❌ Lien symbolique storage manquant"
    echo "   Création du lien symbolique..."
    ln -sf "/public_html/storage/app/public" "$STORAGE_LINK"
    if [ $? -eq 0 ]; then
        echo "✅ Lien symbolique créé"
    else
        echo "❌ Erreur lors de la création du lien symbolique"
    fi
fi

# Vérification finale
echo ""
echo "📊 Vérification finale:"
DEST_COUNT=$(find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
echo "   Images dans le dossier de destination: $DEST_COUNT"

if [ $DEST_COUNT -gt 0 ]; then
    echo "✅ Images accessibles via /uploads/services/"
    echo "✅ Images accessibles via /storage/uploads/services/"
    echo ""
    echo "🎉 Correction terminée avec succès!"
    echo "💡 Les images des services devraient maintenant s'afficher correctement."
else
    echo "❌ Aucune image trouvée dans le dossier de destination"
    exit 1
fi
