#!/bin/bash

# Script pour exécuter l'automatisation SEO via cron
# Usage: Ajoutez ce script dans votre cron Hostinger
# Exemple: */1 * * * * /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html/seo-automation-cron.sh

# Définir le répertoire du projet
PROJECT_DIR="/home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html"
cd "$PROJECT_DIR" || exit 1

# Chemin vers PHP
PHP_BIN="/usr/bin/php"

# Fichier de log
LOG_FILE="$PROJECT_DIR/storage/logs/seo-automation-cron.log"

# Fonction de logging
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Vérifier que PHP est disponible
if [ ! -f "$PHP_BIN" ]; then
    log "ERREUR: PHP non trouvé à $PHP_BIN"
    exit 1
fi

# Vérifier que le répertoire du projet existe
if [ ! -d "$PROJECT_DIR" ]; then
    log "ERREUR: Répertoire du projet non trouvé: $PROJECT_DIR"
    exit 1
fi

# Vérifier que artisan existe
if [ ! -f "$PROJECT_DIR/artisan" ]; then
    log "ERREUR: Fichier artisan non trouvé"
    exit 1
fi

# Exécuter la commande seo:run-automations
log "Démarrage de l'automatisation SEO..."

# Exécuter la commande et capturer la sortie
OUTPUT=$($PHP_BIN artisan seo:run-automations 2>&1)
EXIT_CODE=$?

# Logger le résultat
if [ $EXIT_CODE -eq 0 ]; then
    log "SUCCÈS: Automatisation SEO exécutée"
    log "Sortie: $OUTPUT"
else
    log "ERREUR: Code de sortie $EXIT_CODE"
    log "Sortie: $OUTPUT"
fi

# Limiter la taille du fichier de log (garder les 1000 dernières lignes)
if [ -f "$LOG_FILE" ]; then
    tail -n 1000 "$LOG_FILE" > "$LOG_FILE.tmp" && mv "$LOG_FILE.tmp" "$LOG_FILE"
fi

exit $EXIT_CODE

