#!/bin/bash

# Script de diagnostic et correction pour le problème de storage
# À exécuter sur le serveur de production

echo "🔍 Diagnostic du problème de storage"
echo "=================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    exit 1
fi

echo "📁 Vérification de la structure des dossiers:"
echo ""

# Vérifier le dossier storage
STORAGE_DIR="/public_html/storage/app/public"
if [ -d "$STORAGE_DIR" ]; then
    echo "✅ Dossier storage existe: $STORAGE_DIR"
else
    echo "❌ Dossier storage manquant: $STORAGE_DIR"
    exit 1
fi

# Vérifier le dossier uploads dans storage
STORAGE_UPLOADS="/public_html/storage/app/public/uploads"
if [ -d "$STORAGE_UPLOADS" ]; then
    echo "✅ Dossier uploads dans storage existe: $STORAGE_UPLOADS"
else
    echo "❌ Dossier uploads dans storage manquant: $STORAGE_UPLOADS"
    echo "   Création du dossier..."
    mkdir -p "$STORAGE_UPLOADS"
fi

# Vérifier le dossier services dans storage
STORAGE_SERVICES="/public_html/storage/app/public/uploads/services"
if [ -d "$STORAGE_SERVICES" ]; then
    echo "✅ Dossier services dans storage existe: $STORAGE_SERVICES"
    STORAGE_COUNT=$(find "$STORAGE_SERVICES" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
    echo "   Images dans storage: $STORAGE_COUNT"
    
    if [ $STORAGE_COUNT -gt 0 ]; then
        echo "   Exemples d'images:"
        find "$STORAGE_SERVICES" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | head -5 | while read file; do
            echo "     - $(basename "$file")"
        done
    fi
else
    echo "❌ Dossier services dans storage manquant: $STORAGE_SERVICES"
    echo "   Création du dossier..."
    mkdir -p "$STORAGE_SERVICES"
fi

echo ""
echo "🔗 Vérification du lien symbolique public/storage:"

# Vérifier le lien symbolique
STORAGE_LINK="/public_html/public/storage"
if [ -L "$STORAGE_LINK" ]; then
    echo "✅ Lien symbolique existe: $STORAGE_LINK"
    TARGET=$(readlink "$STORAGE_LINK")
    echo "   Pointe vers: $TARGET"
    
    if [ "$TARGET" = "/public_html/storage/app/public" ]; then
        echo "✅ Lien symbolique correct"
    else
        echo "❌ Lien symbolique incorrect"
        echo "   Suppression de l'ancien lien..."
        rm "$STORAGE_LINK"
        echo "   Création du nouveau lien..."
        ln -sf "/public_html/storage/app/public" "$STORAGE_LINK"
        if [ $? -eq 0 ]; then
            echo "✅ Nouveau lien créé"
        else
            echo "❌ Erreur lors de la création du lien"
        fi
    fi
elif [ -d "$STORAGE_LINK" ]; then
    echo "❌ Un dossier existe à la place du lien symbolique"
    echo "   Suppression du dossier..."
    rm -rf "$STORAGE_LINK"
    echo "   Création du lien symbolique..."
    ln -sf "/public_html/storage/app/public" "$STORAGE_LINK"
    if [ $? -eq 0 ]; then
        echo "✅ Lien symbolique créé"
    else
        echo "❌ Erreur lors de la création du lien"
    fi
else
    echo "❌ Lien symbolique manquant"
    echo "   Création du lien symbolique..."
    ln -sf "/public_html/storage/app/public" "$STORAGE_LINK"
    if [ $? -eq 0 ]; then
        echo "✅ Lien symbolique créé"
    else
        echo "❌ Erreur lors de la création du lien"
    fi
fi

# Vérifier que le lien fonctionne
echo ""
echo "🔍 Test du lien symbolique:"
if [ -L "$STORAGE_LINK" ] && [ -d "$STORAGE_LINK" ]; then
    echo "✅ Lien symbolique fonctionnel"
    
    # Test spécifique pour l'image mentionnée
    TEST_IMAGE="$STORAGE_LINK/uploads/services/service_1761216270_travaux-de-toiture.jpg"
    if [ -f "$TEST_IMAGE" ]; then
        echo "✅ Image de test trouvée: service_1761216270_travaux-de-toiture.jpg"
    else
        echo "❌ Image de test non trouvée: service_1761216270_travaux-de-toiture.jpg"
        echo "   Images disponibles dans le dossier:"
        find "$STORAGE_LINK/uploads/services" -name "*travaux*" -o -name "*toiture*" 2>/dev/null | head -5
    fi
else
    echo "❌ Lien symbolique non fonctionnel"
fi

# Créer aussi une copie dans public/uploads/services pour l'accès direct
echo ""
echo "📋 Création d'une copie dans public/uploads/services pour l'accès direct:"
PUBLIC_SERVICES="/public_html/uploads/services"
if [ ! -d "$PUBLIC_SERVICES" ]; then
    echo "   Création du dossier public/uploads/services..."
    mkdir -p "$PUBLIC_SERVICES"
fi

if [ -d "$STORAGE_SERVICES" ] && [ -d "$PUBLIC_SERVICES" ]; then
    echo "   Copie des images de storage vers public..."
    cp -r "$STORAGE_SERVICES"/* "$PUBLIC_SERVICES/" 2>/dev/null
    if [ $? -eq 0 ]; then
        PUBLIC_COUNT=$(find "$PUBLIC_SERVICES" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
        echo "✅ Images copiées dans public/uploads/services: $PUBLIC_COUNT"
    else
        echo "❌ Erreur lors de la copie"
    fi
fi

# Configurer les permissions
echo ""
echo "🔐 Configuration des permissions..."
chmod -R 755 "$STORAGE_DIR"
chmod -R 755 "$PUBLIC_SERVICES"
chown -R www-data:www-data "$STORAGE_DIR" 2>/dev/null || chown -R apache:apache "$STORAGE_DIR" 2>/dev/null
chown -R www-data:www-data "$PUBLIC_SERVICES" 2>/dev/null || chown -R apache:apache "$PUBLIC_SERVICES" 2>/dev/null

echo ""
echo "🌐 URLs de test:"
echo "https://www.jd-renovation-service.fr/storage/uploads/services/service_1761216270_travaux-de-toiture.jpg"
echo "https://www.jd-renovation-service.fr/uploads/services/service_1761216270_travaux-de-toiture.jpg"

echo ""
echo "🎉 Diagnostic et correction terminés!"
echo "💡 Testez les URLs ci-dessus pour vérifier que les images sont accessibles."
