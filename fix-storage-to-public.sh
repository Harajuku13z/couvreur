#!/bin/bash

# Script pour copier les images de storage/app/public/uploads/services vers public/uploads/services
# À exécuter sur le serveur de production

echo "🔧 Copie des images de storage vers public"
echo "=========================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    echo "💡 Connectez-vous à votre serveur et exécutez ce script"
    exit 1
fi

# Dossier source (où sont actuellement les images)
SOURCE_DIR="/public_html/storage/app/public/uploads/services"
echo "📁 Dossier source: $SOURCE_DIR"

# Dossier destination (où les images doivent être accessibles)
DEST_DIR="/public_html/uploads/services"
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

# Copier toutes les images
echo ""
echo "📋 Copie des images..."
cp -r "$SOURCE_DIR"/* "$DEST_DIR/" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ Images copiées avec succès"
else
    echo "❌ Erreur lors de la copie des images"
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

# Afficher les images copiées
echo ""
echo "📋 Images copiées:"
find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | head -10 | while read file; do
    echo "  ✅ $(basename "$file")"
done

if [ $DEST_COUNT -gt 10 ]; then
    echo "  ... et $((DEST_COUNT - 10)) autres images"
fi

echo ""
echo "🌐 Test des URLs:"
echo "https://www.jd-renovation-service.fr/uploads/services/"
echo "https://www.jd-renovation-service.fr/test-services-images.html"

echo ""
echo "🎉 Copie terminée avec succès!"
echo "💡 Vos nouvelles images devraient maintenant être visibles sur le site."
