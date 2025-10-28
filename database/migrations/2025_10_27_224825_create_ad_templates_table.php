<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ad_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du template (ex: "Rénovation toiture")
            $table->string('service_name'); // Nom du service
            $table->string('service_slug'); // Slug du service
            $table->text('content_html'); // Contenu HTML généré par l'IA
            $table->text('short_description'); // Description courte
            $table->text('long_description'); // Description longue
            $table->string('icon', 50)->default('fas fa-tools'); // Icône Font Awesome
            $table->string('meta_title', 160); // Titre SEO
            $table->text('meta_description'); // Description SEO
            $table->text('meta_keywords'); // Mots-clés SEO
            $table->string('og_title', 160); // Titre Open Graph
            $table->text('og_description'); // Description Open Graph
            $table->string('twitter_title', 160); // Titre Twitter
            $table->text('twitter_description'); // Description Twitter
            $table->json('ai_prompt_used')->nullable(); // Prompt utilisé pour générer le template
            $table->json('ai_response_data')->nullable(); // Données complètes de la réponse IA
            $table->boolean('is_active')->default(true); // Template actif/inactif
            $table->integer('usage_count')->default(0); // Nombre d'annonces utilisant ce template
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['service_slug', 'is_active']);
            $table->index('service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_templates');
    }
};