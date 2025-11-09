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
        Schema::create('seo_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->string('keyword')->nullable();
            $table->enum('status', ['pending', 'generated', 'published', 'indexed', 'failed'])->default('pending');
            $table->string('article_id')->nullable(); // id ou uuid de l'article créé
            $table->string('article_url')->nullable();
            $table->json('metadata')->nullable(); // ce que renvoie GPT / serpapi
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('city_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_automations');
    }
};
