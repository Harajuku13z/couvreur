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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_location')->nullable();
            $table->integer('rating'); // 1-5 étoiles
            $table->text('review_text');
            $table->string('google_review_id')->unique()->nullable();
            $table->string('author_photo_url')->nullable();
            $table->timestamp('review_date')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->string('source')->default('manual'); // 'manual', 'google', 'facebook', etc.
            $table->timestamps();
            
            $table->index(['is_active', 'display_order']);
            $table->index('rating');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
