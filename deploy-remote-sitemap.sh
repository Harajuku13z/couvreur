#!/usr/bin/env bash

# Script de mise à jour distante + régénération du sitemap
# Usage :
#   ./deploy-remote-sitemap.sh /chemin/vers/le/projet [branche]
#
# Exemple (sur le serveur de davidelagage.fr) :
#   ./deploy-remote-sitemap.sh /home/USER/www/davidelagage main

set -euo pipefail

if [ $# -lt 1 ]; then
  echo "Usage : $0 /chemin/vers/le/projet [branche]"
  exit 1
fi

APP_DIR="$1"
BRANCH="${2:-main}"

echo "➡️  Dossier projet : $APP_DIR"
echo "➡️  Branche        : $BRANCH"

cd "$APP_DIR"

echo "🔄 Récupération du code depuis git..."
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

# Optionnel : décommenter si besoin de mettre à jour les dépendances PHP
# echo \"📦 Installation des dépendances (composer install --no-dev --optimize-autoloader)...\"
# composer install --no-dev --optimize-autoloader

echo \"⚙️  Optimisation du cache Laravel...\"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo \"🗺  Régénération du sitemap...\"
# On tente d'abord la commande recommandée, puis des fallbacks si elle n'existe pas
if php artisan sitemap:reset --force 2>/dev/null; then
  echo \"✅ sitemap:reset exécuté\"
elif php artisan sitemap:update 2>/dev/null; then
  echo \"✅ sitemap:update exécuté\"
elif php artisan sitemap:generate-daily 2>/dev/null; then
  echo \"✅ sitemap:generate-daily exécuté\"
else
  echo \"⚠️ Aucune commande sitemap:* connue n'a pu être exécutée. Vérifie les commandes disponibles avec 'php artisan list'.\"
fi

echo \"✅ Déploiement + sitemap terminés.\"

