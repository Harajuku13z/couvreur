#!/bin/bash

echo "🚀 Déploiement de la correction portfolio..."

# Instructions pour le serveur de production
echo "📋 Instructions pour le serveur de production :"
echo ""
echo "1. Connectez-vous au serveur de production"
echo "2. Allez dans le dossier du projet"
echo "3. Exécutez les commandes suivantes :"
echo ""
echo "   cd /path/to/your/project"
echo "   git pull origin main"
echo "   php artisan cache:clear"
echo "   php artisan view:clear"
echo "   php artisan config:clear"
echo ""
echo "4. Vérifiez que le fichier resources/views/admin/portfolio.blade.php contient :"
echo "   \$servicesData = setting('services', []);"
echo "   \$services = is_string(\$servicesData) ? json_decode(\$servicesData, true) : (\$servicesData ?? []);"
echo ""
echo "5. Testez la page /admin/portfolio"
echo ""
echo "✅ Déploiement terminé !"
