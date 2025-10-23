#!/bin/bash

# Script de correction immédiate pour l'enum generation_jobs
# À exécuter directement sur le serveur de production

echo "🚨 CORRECTION IMMÉDIATE - Enum generation_jobs"
echo "=============================================="
echo ""

# Vérifier si nous sommes sur le serveur de production
if [ ! -d "/public_html" ]; then
    echo "❌ Ce script doit être exécuté sur le serveur de production"
    echo "💡 Connectez-vous à votre serveur et exécutez ce script"
    exit 1
fi

echo "📋 Problème actuel:"
echo "❌ L'enum 'mode' ne contient que ['keyword', 'titles']"
echo "❌ Le code essaie d'insérer 'keyword_cities'"
echo "❌ Erreur: Data truncated for column 'mode'"
echo ""

echo "🔧 Correction immédiate en cours..."

# Aller dans le répertoire Laravel
cd /public_html

# Vérifier la structure actuelle de la table
echo "📊 Vérification de la structure actuelle..."
mysql -u root -p -e "USE u182601382_jdrenov; SHOW COLUMNS FROM generation_jobs WHERE Field = 'mode';" 2>/dev/null

echo ""
echo "🔧 Application de la correction SQL..."

# Correction directe via SQL
mysql -u root -p -e "
USE u182601382_jdrenov;
ALTER TABLE generation_jobs 
MODIFY COLUMN mode ENUM('keyword', 'titles', 'keyword_cities', 'keyword_services', 'bulk_generation') NOT NULL;
"

if [ $? -eq 0 ]; then
    echo "✅ Correction SQL réussie !"
    
    # Vérifier la nouvelle structure
    echo ""
    echo "📊 Vérification de la nouvelle structure..."
    mysql -u root -p -e "USE u182601382_jdrenov; SHOW COLUMNS FROM generation_jobs WHERE Field = 'mode';" 2>/dev/null
    
    echo ""
    echo "🎉 CORRECTION TERMINÉE !"
    echo "💡 L'enum generation_jobs supporte maintenant 'keyword_cities'"
    echo "💡 La génération d'annonces devrait fonctionner"
    
else
    echo "❌ Erreur lors de la correction SQL"
    echo "💡 Vérifiez les identifiants de connexion à la base de données"
    exit 1
fi

echo ""
echo "🧪 Test de la correction..."
echo "💡 Essayez maintenant de générer des annonces depuis l'interface admin"
