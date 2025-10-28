<?php

// =====================================================
// SCRIPT DE CORRECTION RAPIDE - COLONNE TEMPLATE_ID
// =====================================================
// À exécuter en production pour corriger la colonne manquante

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "🔧 Correction de la colonne template_id manquante...\n\n";

try {
    // 1. Vérifier si la colonne template_id existe
    echo "📋 Vérification de l'état de la base de données...\n";
    
    $templateIdExists = Schema::hasColumn('ads', 'template_id');
    $templatesTableExists = Schema::hasTable('ad_templates');
    
    echo "   - Table ad_templates: " . ($templatesTableExists ? "✅ Existe" : "❌ Manquante") . "\n";
    echo "   - Colonne template_id: " . ($templateIdExists ? "✅ Existe" : "❌ Manquante") . "\n\n";

    // 2. Créer la table ad_templates si elle n'existe pas
    if (!$templatesTableExists) {
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
        
        // Vérifier d'abord si la table ads existe
        if (Schema::hasTable('ads')) {
            Schema::table('ads', function ($table) {
                $table->foreignId('template_id')->nullable()->after('city_id')->constrained('ad_templates')->onDelete('set null');
                $table->index('template_id');
            });
            
            echo "✅ Colonne template_id ajoutée avec succès\n\n";
        } else {
            echo "❌ Table ads n'existe pas. Impossible d'ajouter la colonne.\n";
            exit(1);
        }
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
    } else {
        echo "ℹ️  Migration 1 déjà marquée\n";
    }
    
    if (!$migration2Exists) {
        DB::table('migrations')->insert([
            'migration' => '2025_10_27_224854_add_template_id_to_ads_table',
            'batch' => $batch + 1
        ]);
        echo "✅ Migration 2 marquée comme exécutée\n";
    } else {
        echo "ℹ️  Migration 2 déjà marquée\n";
    }
    
    echo "\n";

    // 5. Vérifier le déploiement
    echo "🔍 Vérification du déploiement...\n";
    
    $finalTemplatesExists = Schema::hasTable('ad_templates');
    $finalTemplateIdExists = Schema::hasColumn('ads', 'template_id');
    
    echo "   - Table ad_templates: " . ($finalTemplatesExists ? "✅ OK" : "❌ ERREUR") . "\n";
    echo "   - Colonne template_id: " . ($finalTemplateIdExists ? "✅ OK" : "❌ ERREUR") . "\n";
    
    if ($finalTemplatesExists && $finalTemplateIdExists) {
        echo "\n🎉 Correction terminée avec succès!\n";
        echo "🌐 Vous pouvez maintenant accéder à /admin/ads/templates\n";
        
        // Nettoyer le cache
        echo "\n🧹 Nettoyage du cache...\n";
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('view:clear');
        echo "✅ Cache nettoyé\n";
        
    } else {
        echo "\n❌ Erreur lors de la correction. Vérifiez les logs.\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n❌ Erreur lors de la correction: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
