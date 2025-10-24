#!/bin/bash

# Script de correction complète pour la production
echo "🚀 Correction complète de la production"
echo "========================================"

# 1. Récupérer les dernières modifications
echo "📥 Récupération des modifications..."
git pull origin main

# 2. Exécuter les migrations
echo "🗄️  Exécution des migrations..."
php artisan migrate --force

# 3. Créer le lien symbolique de stockage
echo "🔗 Création du lien symbolique de stockage..."
php artisan storage:link

# 4. Créer les répertoires nécessaires
echo "📁 Création des répertoires de stockage..."
mkdir -p storage/app/public/uploads/services
mkdir -p storage/app/public/uploads/articles
mkdir -p storage/app/public/uploads/portfolio
mkdir -p storage/app/public/uploads/homepage

# 5. Définir les permissions
echo "🔐 Configuration des permissions..."
chmod -R 755 storage/
chmod -R 755 public/storage/ 2>/dev/null || echo "⚠️  Lien symbolique non accessible"

# 6. Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 7. Vérifier l'état
echo "✅ Vérification de l'état..."
echo "📋 Tables de base de données :"
php artisan tinker --execute="
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

\$tables = ['articles', 'settings', 'submissions', 'cities', 'ads'];
foreach (\$tables as \$table) {
    if (Schema::hasTable(\$table)) {
        \$count = DB::table(\$table)->count();
        echo \"✅ \$table : \$count enregistrements\n\";
    } else {
        echo \"❌ \$table : Table manquante\n\";
    }
}
"

echo "📋 Services dans les paramètres :"
php artisan tinker --execute="
use App\Models\Setting;
\$servicesData = Setting::get('services', '[]');
\$services = json_decode(\$servicesData, true);
if (is_array(\$services)) {
    echo '✅ Services : ' . count(\$services) . ' services trouvés\n';
} else {
    echo '❌ Aucun service trouvé\n';
}
"

echo "📁 Répertoires de stockage :"
ls -la storage/app/public/uploads/ 2>/dev/null || echo "⚠️  Répertoires d'upload non trouvés"

echo "🔗 Lien symbolique :"
ls -la public/ | grep storage

echo "🎉 Correction terminée !"
echo "🔗 Testez maintenant :"
echo "- Upload d'images : https://artisan-elfrick.osmoseconsulting.fr/admin/services"
echo "- Articles : https://artisan-elfrick.osmoseconsulting.fr/admin/articles"
