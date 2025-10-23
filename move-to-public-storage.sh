#!/bin/bash

# Script pour déplacer les images vers public/storage/uploads/services
# À exécuter sur le serveur de production

echo "🔧 Déplacement des images vers public/storage/uploads/services"
echo "============================================================"
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    exit 1
fi

# Dossier source (images existantes)
SOURCE_DIR="/public_html/storage/app/public/uploads/services"
echo "📁 Dossier source: $SOURCE_DIR"

# Dossier destination (nouveau emplacement)
DEST_DIR="/public_html/public/storage/uploads/services"
echo "📁 Dossier destination: $DEST_DIR"
echo ""

# Vérifier que le dossier source existe
if [ ! -d "$SOURCE_DIR" ]; then
    echo "❌ Dossier source non trouvé: $SOURCE_DIR"
    echo "💡 Vérifiez que les images sont bien dans ce dossier"
    exit 1
fi

# Compter les images dans le dossier source
SOURCE_COUNT=$(find "$SOURCE_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
echo "📊 Images trouvées dans storage: $SOURCE_COUNT"

if [ $SOURCE_COUNT -eq 0 ]; then
    echo "❌ Aucune image trouvée dans le dossier source"
    exit 1
fi

# Créer le dossier destination s'il n'existe pas
if [ ! -d "$DEST_DIR" ]; then
    echo "📁 Création du dossier destination..."
    mkdir -p "$DEST_DIR"
    if [ $? -eq 0 ]; then
        echo "✅ Dossier créé: $DEST_DIR"
    else
        echo "❌ Erreur lors de la création du dossier"
        exit 1
    fi
else
    echo "✅ Dossier destination existe déjà"
fi

# Déplacer toutes les images
echo ""
echo "📋 Déplacement des images..."
mv "$SOURCE_DIR"/* "$DEST_DIR/" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Images déplacées avec succès"
else
    echo "❌ Erreur lors du déplacement des images"
    exit 1
fi

# Vérifier le résultat
DEST_COUNT=$(find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
echo "📊 Images dans le dossier destination: $DEST_COUNT"

# Configurer les permissions
echo ""
echo "🔐 Configuration des permissions..."
chmod -R 755 "$DEST_DIR"
chown -R www-data:www-data "$DEST_DIR" 2>/dev/null || chown -R apache:apache "$DEST_DIR" 2>/dev/null

# Afficher les images déplacées
echo ""
echo "📋 Images déplacées:"
find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | head -10 | while read file; do
    echo "  ✅ $(basename "$file")"
done

if [ $DEST_COUNT -gt 10 ]; then
    echo "  ... et $((DEST_COUNT - 10)) autres images"
fi

echo ""
echo "🌐 URLs de test:"
echo "https://www.jd-renovation-service.fr/storage/uploads/services/"
echo "https://www.jd-renovation-service.fr/test-services-images.html"

echo ""
echo "🎉 Déplacement terminé avec succès!"
echo "💡 Les nouvelles images seront maintenant enregistrées dans public/storage/uploads/services/"
echo "💡 Les images sont accessibles directement via /storage/uploads/services/"
