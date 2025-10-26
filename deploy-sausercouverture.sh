#!/bin/bash

# Script de déploiement pour Sauser Couverture
# À exécuter sur le serveur de production

echo "🚀 Déploiement sur Sauser Couverture"
echo "===================================="

# Vérifier que nous sommes sur le bon serveur
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté dans le répertoire racine du projet Laravel"
    exit 1
fi

# Mettre à jour le code depuis Git
echo "📥 Mise à jour du code depuis Git..."
git pull origin main

# Installer/ mettre à jour les dépendances
echo "📦 Mise à jour des dépendances..."
composer install --no-dev --optimize-autoloader

# Générer la clé d'application si nécessaire
echo "🔑 Vérification de la clé d'application..."
php artisan key:generate --force

# Exécuter les migrations
echo "🗄️ Exécution des migrations..."
php artisan migrate --force

# Créer les liens symboliques pour le stockage
echo "🔗 Création des liens symboliques..."
php artisan storage:link

# Optimiser l'application pour la production
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear

# Définir les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 public/uploads/

echo "✅ Déploiement terminé avec succès!"
echo ""
echo "🌐 Site: https://sausercouverture.fr"
echo "🔧 Admin: https://sausercouverture.fr/admin"
echo "🤖 IA Services: https://sausercouverture.fr/admin/services/ai"
echo ""
echo "📋 Vérifications:"
echo "1. Testez la page admin des services"
echo "2. Vérifiez que le bouton 'Génération IA' est visible"
echo "3. Testez la génération de services par IA"
