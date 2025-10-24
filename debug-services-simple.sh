#!/bin/bash

# Script de diagnostic simple pour les services
echo "🔍 Diagnostic des services (version simple)"
echo "=========================================="

# Vérifier la connexion à la base de données
echo "📊 Vérification de la base de données..."
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;
try {
    DB::connection()->getPdo();
    echo '✅ Connexion à la base de données : OK\n';
} catch (Exception \$e) {
    echo '❌ Connexion à la base de données : ÉCHEC\n';
    echo 'Erreur : ' . \$e->getMessage() . '\n';
    exit(1);
}
"

# Vérifier les services
echo "📋 Vérification des services..."
php artisan tinker --execute="
use App\Models\Setting;
\$servicesData = Setting::get('services', '[]');
if (is_string(\$servicesData)) {
    \$services = json_decode(\$servicesData, true);
    if (is_array(\$services)) {
        echo '✅ Services trouvés : ' . count(\$services) . ' services\n';
        foreach (\$services as \$index => \$service) {
            echo '  - Service ' . \$index . ': ' . (\$service['name'] ?? 'Sans nom') . ' (ID: ' . (\$service['id'] ?? 'N/A') . ')\n';
        }
    } else {
        echo '❌ Erreur de décodage JSON des services\n';
    }
} elseif (is_array(\$servicesData)) {
    echo '✅ Services trouvés : ' . count(\$servicesData) . ' services\n';
    foreach (\$servicesData as \$index => \$service) {
        echo '  - Service ' . \$index . ': ' . (\$service['name'] ?? 'Sans nom') . ' (ID: ' . (\$service['id'] ?? 'N/A') . ')\n';
    }
} else {
    echo '❌ Aucun service trouvé\n';
}
"

# Vérifier les répertoires d'images
echo "📁 Vérification des répertoires d'images..."
if [ -d "storage/app/public/uploads/services" ]; then
    file_count=$(find storage/app/public/uploads/services -type f | wc -l)
    echo "✅ Répertoire services : $file_count fichiers"
else
    echo "❌ Répertoire services manquant"
fi

if [ -L "public/storage" ]; then
    echo "✅ Lien symbolique public/storage : OK"
else
    echo "❌ Lien symbolique public/storage : Manquant"
fi

echo "🎯 Recommandations :"
echo "- Si les services ne s'affichent pas, vérifiez la clé 'services' dans la table settings"
echo "- Si les images ne s'affichent pas, exécutez : php artisan storage:link"
echo "- Si la suppression ne fonctionne pas, vérifiez les permissions de la base de données"
