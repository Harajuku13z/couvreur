#!/bin/bash

# =====================================================
# SCRIPT DE DÉPLOIEMENT AUTOMATIQUE - TEMPLATES
# =====================================================
# Ce script s'exécute automatiquement après git pull en production

echo "🚀 Déploiement automatique du système de templates d'annonces..."

# 1. Vérifier que nous sommes en production
if [ "$APP_ENV" != "production" ]; then
    echo "⚠️  Ce script doit être exécuté en production uniquement"
    exit 1
fi

# 2. Aller dans le répertoire du projet
cd "$(dirname "$0")"

# 3. Vérifier si les tables existent déjà
echo "📋 Vérification de l'état de la base de données..."

# Vérifier si la table ad_templates existe
TEMPLATES_EXISTS=$(php artisan tinker --execute="echo Schema::hasTable('ad_templates') ? 'true' : 'false';" 2>/dev/null)

# Vérifier si la colonne template_id existe dans ads
TEMPLATE_ID_EXISTS=$(php artisan tinker --execute="echo Schema::hasColumn('ads', 'template_id') ? 'true' : 'false';" 2>/dev/null)

echo "   - Table ad_templates: $TEMPLATES_EXISTS"
echo "   - Colonne template_id: $TEMPLATE_ID_EXISTS"

# 4. Créer la table ad_templates si elle n'existe pas
if [ "$TEMPLATES_EXISTS" = "false" ]; then
    echo "📦 Création de la table ad_templates..."
    php artisan tinker --execute="
    Schema::create('ad_templates', function (\$table) {
        \$table->id();
        \$table->string('name');
        \$table->string('service_name');
        \$table->string('service_slug');
        \$table->longText('content_html');
        \$table->text('short_description');
        \$table->text('long_description');
        \$table->string('icon', 50)->default('fas fa-tools');
        \$table->string('meta_title', 160);
        \$table->text('meta_description');
        \$table->text('meta_keywords');
        \$table->string('og_title', 160);
        \$table->text('og_description');
        \$table->string('twitter_title', 160);
        \$table->text('twitter_description');
        \$table->json('ai_prompt_used')->nullable();
        \$table->json('ai_response_data')->nullable();
        \$table->boolean('is_active')->default(true);
        \$table->integer('usage_count')->default(0);
        \$table->timestamps();
        \$table->index(['service_slug', 'is_active']);
        \$table->index('service_name');
    });
    echo 'Table ad_templates créée avec succès';
    "
else
    echo "✅ Table ad_templates existe déjà"
fi

# 5. Ajouter la colonne template_id si elle n'existe pas
if [ "$TEMPLATE_ID_EXISTS" = "false" ]; then
    echo "📦 Ajout de la colonne template_id..."
    php artisan tinker --execute="
    Schema::table('ads', function (\$table) {
        \$table->foreignId('template_id')->nullable()->after('city_id')->constrained('ad_templates')->onDelete('set null');
        \$table->index('template_id');
    });
    echo 'Colonne template_id ajoutée avec succès';
    "
else
    echo "✅ Colonne template_id existe déjà"
fi

# 6. Marquer les migrations comme exécutées
echo "📝 Marquage des migrations comme exécutées..."
php artisan tinker --execute="
\$batch = DB::table('migrations')->max('batch') + 1;
DB::table('migrations')->insert([
    ['migration' => '2025_10_27_224825_create_ad_templates_table', 'batch' => \$batch],
    ['migration' => '2025_10_27_224854_add_template_id_to_ads_table', 'batch' => \$batch + 1]
]);
echo 'Migrations marquées comme exécutées';
"

# 7. Vérifier le déploiement
echo "🔍 Vérification du déploiement..."
php artisan tinker --execute="
echo 'Vérification des tables:';
echo 'ad_templates: ' . (Schema::hasTable('ad_templates') ? 'OK' : 'ERREUR');
echo 'template_id: ' . (Schema::hasColumn('ads', 'template_id') ? 'OK' : 'ERREUR');
echo 'Déploiement terminé avec succès!';
"

# 8. Nettoyer le cache
echo "🧹 Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Déploiement automatique terminé avec succès!"
echo "🌐 Vous pouvez maintenant accéder à /admin/ads/templates"
