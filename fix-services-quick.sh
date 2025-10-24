#!/bin/bash

# Script de correction rapide pour les services
echo "🚀 Correction rapide des services"
echo "================================="

# 1. Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear

# 2. Créer le lien symbolique de stockage
echo "🔗 Création du lien symbolique de stockage..."
php artisan storage:link

# 3. Créer les répertoires nécessaires
echo "📁 Création des répertoires..."
mkdir -p storage/app/public/uploads/services
mkdir -p storage/app/public/uploads/articles

# 4. Définir les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 storage/app/public/

# 5. Vérifier l'état des services
echo "📊 Vérification des services..."
php artisan tinker --execute="
use App\Models\Setting;
\$servicesData = Setting::get('services', '[]');
if (is_string(\$servicesData)) {
    \$services = json_decode(\$servicesData, true);
    if (is_array(\$services)) {
        echo '✅ Services trouvés : ' . count(\$services) . ' services\n';
    } else {
        echo '❌ Erreur de décodage JSON\n';
    }
} elseif (is_array(\$servicesData)) {
    echo '✅ Services trouvés : ' . count(\$servicesData) . ' services\n';
} else {
    echo '❌ Aucun service trouvé\n';
}
"

echo "✅ Correction terminée !"
echo "🔗 Testez maintenant :"
echo "- Suppression de services : https://artisan-elfrick.osmoseconsulting.fr/admin/services"
echo "- Upload d'images : https://artisan-elfrick.osmoseconsulting.fr/admin/services"
