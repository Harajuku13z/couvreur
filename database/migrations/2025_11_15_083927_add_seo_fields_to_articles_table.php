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
        Schema::table('articles', function (Blueprint $table) {
            // Ajouter published_at et is_published si pas déjà présents
            if (!Schema::hasColumn('articles', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('articles', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('published_at');
            }
            if (!Schema::hasColumn('articles', 'author_id')) {
                $table->unsignedBigInteger('author_id')->nullable()->after('is_published');
                $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            }
            
            // Index pour les performances
            $table->index(['is_published', 'published_at']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'author_id')) {
                $table->dropForeign(['author_id']);
                $table->dropColumn('author_id');
            }
            if (Schema::hasColumn('articles', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('articles', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });
    }
};
