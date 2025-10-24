#!/bin/bash

# Script de déploiement pour corriger le problème d'upload
echo "🚀 Déploiement des corrections d'upload d'images..."

# 1. Récupérer les dernières modifications
echo "📥 Récupération des modifications depuis Git..."
git pull origin main

# 2. Créer les répertoires nécessaires
echo "📁 Création des répertoires d'upload..."
mkdir -p public/storage/uploads/services
mkdir -p public/storage/uploads/portfolio
mkdir -p public/storage/uploads/articles
mkdir -p public/storage/uploads/homepage

# 3. Définir les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 public/storage/
chown -R www-data:www-data public/storage/ 2>/dev/null || echo "⚠️  Impossible de changer le propriétaire (peut nécessiter sudo)"

# 4. Vérifier les permissions
echo "✅ Vérification des répertoires créés :"
ls -la public/storage/

# 5. Nettoyer le cache Laravel
echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "✅ Déploiement terminé !"
echo "🔗 Testez l'upload d'images sur : https://artisan-elfrick.osmoseconsulting.fr/admin/services"
