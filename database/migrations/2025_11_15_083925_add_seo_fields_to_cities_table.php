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
        Schema::table('cities', function (Blueprint $table) {
            // Ajouter slug si pas déjà présent
            if (!Schema::hasColumn('cities', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name');
            }
            
            // Champs SEO et localisation
            $table->text('description')->nullable()->after('region');
            $table->decimal('latitude', 10, 8)->nullable()->after('description');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('phone')->nullable()->after('longitude');
            $table->string('email')->nullable()->after('phone');
            $table->string('meta_title')->nullable()->after('email');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->boolean('is_active')->default(true)->after('meta_description');
            
            // Index pour les performances
            if (!Schema::hasColumn('cities', 'slug')) {
                $table->index('slug');
            }
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'description',
                'latitude',
                'longitude',
                'phone',
                'email',
                'meta_title',
                'meta_description',
                'is_active'
            ]);
        });
    }
};
