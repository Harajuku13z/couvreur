<?php

// =====================================================
// SCRIPT DE DÉPLOIEMENT AUTOMATIQUE - TEMPLATES
// =====================================================
// Exécuter avec: php deploy-templates.php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🚀 Déploiement automatique du système de templates d'annonces...\n\n";

try {
    // 1. Vérifier si la table ad_templates existe
    echo "📋 Vérification de l'état de la base de données...\n";
    
    $templatesExists = Schema::hasTable('ad_templates');
    $templateIdExists = Schema::hasColumn('ads', 'template_id');
    
    echo "   - Table ad_templates: " . ($templatesExists ? "✅ Existe" : "❌ Manquante") . "\n";
    echo "   - Colonne template_id: " . ($templateIdExists ? "✅ Existe" : "❌ Manquante") . "\n\n";

    // 2. Créer la table ad_templates si elle n'existe pas
    if (!$templatesExists) {
        echo "📦 Création de la table ad_templates...\n";
        
        Schema::create('ad_templates', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('service_name');
            $table->string('service_slug');
            $table->longText('content_html');
            $table->text('short_description');
            $table->text('long_description');
            $table->string('icon', 50)->default('fas fa-tools');
            $table->string('meta_title', 160);
            $table->text('meta_description');
            $table->text('meta_keywords');
            $table->string('og_title', 160);
            $table->text('og_description');
            $table->string('twitter_title', 160);
            $table->text('twitter_description');
            $table->json('ai_prompt_used')->nullable();
            $table->json('ai_response_data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->index(['service_slug', 'is_active']);
            $table->index('service_name');
        });
        
        echo "✅ Table ad_templates créée avec succès\n\n";
    } else {
        echo "✅ Table ad_templates existe déjà\n\n";
    }

    // 3. Ajouter la colonne template_id si elle n'existe pas
    if (!$templateIdExists) {
        echo "📦 Ajout de la colonne template_id...\n";
        
        Schema::table('ads', function ($table) {
            $table->foreignId('template_id')->nullable()->after('city_id')->constrained('ad_templates')->onDelete('set null');
            $table->index('template_id');
        });
        
        echo "✅ Colonne template_id ajoutée avec succès\n\n";
    } else {
        echo "✅ Colonne template_id existe déjà\n\n";
    }

    // 4. Marquer les migrations comme exécutées
    echo "📝 Marquage des migrations comme exécutées...\n";
    
    $batch = DB::table('migrations')->max('batch') + 1;
    
    // Vérifier si les migrations ne sont pas déjà marquées
    $migration1Exists = DB::table('migrations')->where('migration', '2025_10_27_224825_create_ad_templates_table')->exists();
    $migration2Exists = DB::table('migrations')->where('migration', '2025_10_27_224854_add_template_id_to_ads_table')->exists();
    
    if (!$migration1Exists) {
        DB::table('migrations')->insert([
            'migration' => '2025_10_27_224825_create_ad_templates_table',
            'batch' => $batch
        ]);
        echo "✅ Migration 1 marquée comme exécutée\n";
    }
    
    if (!$migration2Exists) {
        DB::table('migrations')->insert([
            'migration' => '2025_10_27_224854_add_template_id_to_ads_table',
            'batch' => $batch + 1
        ]);
        echo "✅ Migration 2 marquée comme exécutée\n";
    }
    
    echo "\n";

    // 5. Vérifier le déploiement
    echo "🔍 Vérification du déploiement...\n";
    
    $finalTemplatesExists = Schema::hasTable('ad_templates');
    $finalTemplateIdExists = Schema::hasColumn('ads', 'template_id');
    
    echo "   - Table ad_templates: " . ($finalTemplatesExists ? "✅ OK" : "❌ ERREUR") . "\n";
    echo "   - Colonne template_id: " . ($finalTemplateIdExists ? "✅ OK" : "❌ ERREUR") . "\n";
    
    if ($finalTemplatesExists && $finalTemplateIdExists) {
        echo "\n🎉 Déploiement terminé avec succès!\n";
        echo "🌐 Vous pouvez maintenant accéder à /admin/ads/templates\n";
        echo "\n📋 Prochaines étapes:\n";
        echo "   1. Aller sur /admin/ads/templates\n";
        echo "   2. Cliquer sur 'Créer un Template'\n";
        echo "   3. Sélectionner un service\n";
        echo "   4. Tester la génération d'annonces\n";
    } else {
        echo "\n❌ Erreur lors du déploiement. Vérifiez les logs.\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n❌ Erreur lors du déploiement: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
