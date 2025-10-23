#!/bin/bash

# Script de déploiement pour Hostinger
# À exécuter après avoir uploadé les fichiers via FTP

echo "🚀 Déploiement sur Hostinger - JD Renovation Service"
echo "=================================================="

# Générer la clé d'application
echo "📝 Génération de la clé d'application..."
php artisan key:generate --force

# Optimiser l'application pour la production
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exécuter les migrations
echo "🗄️ Exécution des migrations..."
php artisan migrate --force

# Créer les liens symboliques pour le stockage
echo "🔗 Création des liens symboliques..."
php artisan storage:link

# Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Définir les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

echo "✅ Déploiement terminé avec succès!"
echo ""
echo "📋 Étapes suivantes:"
echo "1. Vérifiez que votre base de données MySQL est créée"
echo "2. Configurez les paramètres d'email dans le fichier .env"
echo "3. Testez l'application sur https://jd-renovation-service.fr"
echo ""
echo "🔧 Configuration requise:"
echo "- Base de données: u182601382_jdrenov"
echo "- Utilisateur: u182601382_jdrenov"
echo "- Mot de passe: Harajuku1993@"
echo "- Domaine: jd-renovation-service.fr"
