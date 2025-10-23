#!/bin/bash

# Script pour corriger le lien symbolique public/storage
# À exécuter sur le serveur de production

echo "🔗 Correction du lien symbolique public/storage"
echo "==============================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    echo "💡 Connectez-vous à votre serveur et exécutez ce script"
    exit 1
fi

# Chemin du lien symbolique
STORAGE_LINK="/public_html/public/storage"
STORAGE_TARGET="/public_html/storage/app/public"

echo "📁 Vérification du lien symbolique:"
echo "Lien: $STORAGE_LINK"
echo "Cible: $STORAGE_TARGET"
echo ""

# Vérifier si le lien existe
if [ -L "$STORAGE_LINK" ]; then
    echo "✅ Lien symbolique existe"
    CURRENT_TARGET=$(readlink "$STORAGE_LINK")
    echo "   Pointe vers: $CURRENT_TARGET"
    
    if [ "$CURRENT_TARGET" = "$STORAGE_TARGET" ]; then
        echo "✅ Lien symbolique correct"
    else
        echo "❌ Lien symbolique incorrect"
        echo "   Suppression de l'ancien lien..."
        rm "$STORAGE_LINK"
        echo "   Création du nouveau lien..."
        ln -sf "$STORAGE_TARGET" "$STORAGE_LINK"
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
    ln -sf "$STORAGE_TARGET" "$STORAGE_LINK"
    if [ $? -eq 0 ]; then
        echo "✅ Lien symbolique créé"
    else
        echo "❌ Erreur lors de la création du lien"
    fi
else
    echo "❌ Lien symbolique manquant"
    echo "   Création du lien symbolique..."
    ln -sf "$STORAGE_TARGET" "$STORAGE_LINK"
    if [ $? -eq 0 ]; then
        echo "✅ Lien symbolique créé"
    else
        echo "❌ Erreur lors de la création du lien"
    fi
fi

# Vérifier que le lien fonctionne
echo ""
echo "🔍 Vérification du lien:"
if [ -L "$STORAGE_LINK" ] && [ -d "$STORAGE_LINK" ]; then
    echo "✅ Lien symbolique fonctionnel"
    
    # Vérifier les images accessibles via le lien
    SERVICES_LINK="$STORAGE_LINK/uploads/services"
    if [ -d "$SERVICES_LINK" ]; then
        LINK_COUNT=$(find "$SERVICES_LINK" -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" -o -name "*.webp" \) | wc -l)
        echo "✅ Images accessibles via /storage/uploads/services/: $LINK_COUNT"
    else
        echo "❌ Dossier /storage/uploads/services/ non accessible"
    fi
else
    echo "❌ Lien symbolique non fonctionnel"
fi

echo ""
echo "🌐 URLs de test:"
echo "https://www.jd-renovation-service.fr/storage/uploads/services/"
echo "https://www.jd-renovation-service.fr/uploads/services/"

echo ""
echo "🎉 Correction du lien symbolique terminée!"
