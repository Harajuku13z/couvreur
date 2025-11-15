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
        Schema::table('services', function (Blueprint $table) {
            // Ajouter slug si pas déjà présent
            if (!Schema::hasColumn('services', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('title');
            }
            
            // Champs SEO
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('og_image')->nullable()->after('meta_description');
            $table->decimal('price_from', 10, 2)->nullable()->after('og_image');
            $table->integer('order')->default(0)->after('price_from');
            
            // Index pour les performances
            if (!Schema::hasColumn('services', 'slug')) {
                $table->index('slug');
            }
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'meta_title',
                'meta_description',
                'og_image',
                'price_from',
                'order'
            ]);
        });
    }
};
