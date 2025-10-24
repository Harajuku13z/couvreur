#!/bin/bash

# Script pour créer les répertoires nécessaires sur le serveur de production
# À exécuter sur le serveur de production

echo "Création des répertoires pour l'upload d'images..."

# Créer les répertoires avec les bonnes permissions
mkdir -p public/storage/uploads/services
mkdir -p public/storage/uploads/portfolio
mkdir -p public/storage/uploads/articles
mkdir -p public/storage/uploads/homepage

# Définir les permissions appropriées
chmod -R 755 public/storage/
chown -R www-data:www-data public/storage/ 2>/dev/null || echo "Impossible de changer le propriétaire (peut nécessiter sudo)"

echo "Répertoires créés avec succès !"
echo "Vérification des permissions :"
ls -la public/storage/
