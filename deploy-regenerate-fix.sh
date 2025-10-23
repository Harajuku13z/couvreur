#!/bin/bash

echo "🚀 Déploiement de la correction regenerate() sur le serveur de production..."

# Se connecter au serveur et mettre à jour le code
ssh root@jd-renovation-service.fr << 'EOF'
    echo "📁 Changement vers le répertoire du projet..."
    cd /public_html
    
    echo "🔄 Sauvegarde de la base de données..."
    mysqldump -u root -p'$MYSQL_ROOT_PASSWORD' couvreur > backup_$(date +%Y%m%d_%H%M%S).sql
    
    echo "📥 Mise à jour du code depuis GitHub..."
    git pull origin main
    
    echo "🔧 Vérification de la méthode regenerate()..."
    if grep -q "public function regenerate" app/Http/Controllers/ServicesController.php; then
        echo "✅ Méthode regenerate() trouvée dans le contrôleur"
    else
        echo "❌ Méthode regenerate() non trouvée - problème de déploiement"
        exit 1
    fi
    
    echo "🧹 Nettoyage du cache..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    echo "🔗 Vérification des liens de stockage..."
    if [ ! -L public/storage ]; then
        echo "🔗 Création du lien symbolique public/storage..."
        php artisan storage:link
    fi
    
    echo "📁 Vérification des permissions..."
    chmod -R 755 storage/
    chmod -R 755 public/storage/
    chmod -R 755 public/uploads/
    
    echo "✅ Déploiement terminé avec succès !"
    echo "🎯 La méthode regenerate() est maintenant disponible"
EOF

echo "🎉 Déploiement terminé !"
echo "🔗 Testez maintenant: https://www.jd-renovation-service.fr/admin/services"
