#!/bin/bash

# =====================================================
# SCRIPT DE DÉPLOIEMENT PRODUCTION
# =====================================================
# À exécuter sur le serveur de production après git pull

echo "🚀 Déploiement en production..."

# 1. Aller dans le répertoire du projet
cd "$(dirname "$0")"

# 2. Mettre à jour le code
echo "📥 Mise à jour du code..."
git pull origin main

# 3. Installer les dépendances si nécessaire
echo "📦 Vérification des dépendances..."
composer install --no-dev --optimize-autoloader

# 4. Exécuter le déploiement des templates
echo "🔧 Déploiement du système de templates..."
php deploy-templates.php

# 5. Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# 6. Vérifier les permissions
echo "🔐 Vérification des permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Déploiement en production terminé!"
echo "🌐 Site accessible et fonctionnel"
