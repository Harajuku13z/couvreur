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
        Schema::create('keyword_images', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->index(); // Mot-clé associé
            $table->string('image_path'); // Chemin de l'image (depuis public/)
            $table->string('title')->nullable(); // Titre optionnel
            $table->text('description')->nullable(); // Description optionnelle
            $table->integer('display_order')->default(0); // Ordre d'affichage
            $table->boolean('is_active')->default(true); // Actif/inactif
            $table->timestamps();
            
            $table->index('keyword');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keyword_images');
    }
};
