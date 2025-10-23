#!/bin/bash

# Script pour corriger les images des services sur le serveur de production
# À exécuter sur le serveur de production

echo "🔧 Correction des images des services sur le serveur de production"
echo "================================================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    echo "💡 Connectez-vous à votre serveur et exécutez ce script"
    exit 1
fi

echo "📁 Vérification de la structure des dossiers:"
echo ""

# Vérifier le dossier source (storage)
SOURCE_DIR="/public_html/storage/app/public/uploads/services"
if [ -d "$SOURCE_DIR" ]; then
    echo "✅ Dossier source trouvé: $SOURCE_DIR"
    STORAGE_COUNT=$(find "$SOURCE_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
    echo "   Images dans storage: $STORAGE_COUNT"
else
    echo "❌ Dossier source non trouvé: $SOURCE_DIR"
fi

# Vérifier le dossier de destination (public)
DEST_DIR="/public_html/uploads/services"
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
    PUBLIC_COUNT=$(find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
    echo "   Images dans public: $PUBLIC_COUNT"
fi

# Copier les images de storage vers public si nécessaire
if [ -d "$SOURCE_DIR" ] && [ -d "$DEST_DIR" ]; then
    echo ""
    echo "📋 Synchronisation des images..."
    
    # Copier toutes les images de storage vers public
    cp -r "$SOURCE_DIR"/* "$DEST_DIR/" 2>/dev/null
    
    if [ $? -eq 0 ]; then
        echo "✅ Images synchronisées avec succès"
    else
        echo "❌ Erreur lors de la synchronisation des images"
    fi
fi

# Vérifier les permissions
echo ""
echo "🔐 Configuration des permissions..."
chmod -R 755 "$DEST_DIR"
chown -R www-data:www-data "$DEST_DIR" 2>/dev/null || chown -R apache:apache "$DEST_DIR" 2>/dev/null

# Vérification finale
echo ""
echo "📊 Vérification finale:"
FINAL_COUNT=$(find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
echo "Images finales dans public/uploads/services/: $FINAL_COUNT"

if [ $FINAL_COUNT -gt 0 ]; then
    echo ""
    echo "✅ Images disponibles:"
    find "$DEST_DIR" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | head -10 | while read file; do
        echo "  - $(basename "$file")"
    done
    
    echo ""
    echo "🌐 Test des URLs:"
    echo "https://www.jd-renovation-service.fr/uploads/services/"
    echo "https://www.jd-renovation-service.fr/test-services-images.html"
    
    echo ""
    echo "🎉 Correction terminée avec succès!"
    echo "💡 Les nouvelles images devraient maintenant s'afficher correctement."
else
    echo "❌ Aucune image trouvée dans le dossier de destination"
    echo "💡 Vérifiez que les images existent dans $SOURCE_DIR"
fi
