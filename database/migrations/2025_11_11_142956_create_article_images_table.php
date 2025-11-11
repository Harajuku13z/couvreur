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
        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->nullable()->constrained('articles')->onDelete('cascade');
            $table->string('image_path'); // Chemin de l'image (depuis public/)
            $table->string('alt_text')->nullable(); // Texte alternatif pour SEO
            $table->text('keywords')->nullable(); // Mots-clés associés à l'image
            $table->string('title')->nullable(); // Titre optionnel
            $table->text('description')->nullable(); // Description optionnelle
            $table->integer('width')->nullable(); // Largeur de l'image
            $table->integer('height')->nullable(); // Hauteur de l'image
            $table->integer('file_size')->nullable(); // Taille du fichier en bytes
            $table->string('mime_type')->nullable(); // Type MIME
            $table->timestamps();
            
            $table->index('article_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_images');
    }
};
