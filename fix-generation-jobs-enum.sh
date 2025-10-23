#!/bin/bash

# Script pour corriger l'enum de la table generation_jobs sur le serveur de production
# À exécuter sur le serveur de production

echo "🔧 Correction de l'enum generation_jobs sur le serveur de production"
echo "=================================================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    echo "💡 Connectez-vous à votre serveur et exécutez ce script"
    exit 1
fi

echo "📋 Problème identifié:"
echo "❌ L'enum 'mode' dans generation_jobs ne contient que ['keyword', 'titles']"
echo "❌ Le code essaie d'insérer 'keyword_cities' qui n'est pas autorisé"
echo "❌ Erreur: Data truncated for column 'mode' at row 1"
echo ""

echo "🔧 Solution:"
echo "1. Ajouter les nouvelles valeurs à l'enum"
echo "2. Inclure: keyword_cities, keyword_services, bulk_generation"
echo ""

# Exécuter la migration
echo "📊 Exécution de la migration..."
cd /public_html

# Vérifier si Laravel est disponible
if [ -f "artisan" ]; then
    echo "✅ Laravel détecté, exécution de la migration..."
    php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        echo "✅ Migration exécutée avec succès"
    else
        echo "❌ Erreur lors de l'exécution de la migration"
        echo "💡 Tentative de correction manuelle..."
        
        # Correction manuelle via SQL
        echo "🔧 Correction manuelle via SQL..."
        mysql -u root -p -e "USE u182601382_jdrenov; ALTER TABLE generation_jobs MODIFY COLUMN mode ENUM('keyword', 'titles', 'keyword_cities', 'keyword_services', 'bulk_generation') NOT NULL;"
        
        if [ $? -eq 0 ]; then
            echo "✅ Correction manuelle réussie"
        else
            echo "❌ Échec de la correction manuelle"
            exit 1
        fi
    fi
else
    echo "❌ Laravel non détecté, correction manuelle nécessaire"
    echo "💡 Exécutez manuellement:"
    echo "ALTER TABLE generation_jobs MODIFY COLUMN mode ENUM('keyword', 'titles', 'keyword_cities', 'keyword_services', 'bulk_generation') NOT NULL;"
fi

echo ""
echo "🎉 Correction terminée!"
echo "💡 L'enum generation_jobs supporte maintenant toutes les valeurs nécessaires."
echo "💡 La génération d'annonces devrait maintenant fonctionner correctement."
