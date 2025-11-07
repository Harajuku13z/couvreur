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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->index();
            $table->string('path')->index();
            $table->string('method', 10)->default('GET');
            $table->text('referrer_url')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->integer('duration')->nullable(); // Durée en secondes
            $table->timestamp('visited_at');
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index('visited_at');
            $table->index(['path', 'visited_at']);
            $table->index(['session_id', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};

