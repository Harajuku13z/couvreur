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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->string('user_identifier')->nullable();
            
            // Tracking fields (ajoutés directement ici pour éviter les migrations qui échouent)
            // Note: ->after() ne fonctionne pas dans Schema::create(), seulement dans Schema::table()
            $table->string('ip_address', 45)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->text('referrer_url')->nullable();
            $table->text('user_agent')->nullable();
            $table->decimal('recaptcha_score', 3, 2)->nullable();
            $table->json('tracking_data')->nullable();
            
            $table->enum('status', ['IN_PROGRESS', 'COMPLETED', 'ABANDONED'])->default('IN_PROGRESS');
            $table->string('current_step')->nullable();
            
            // Form data
            $table->enum('property_type', ['HOUSE', 'APARTMENT'])->nullable();
            $table->integer('surface')->nullable();
            $table->json('work_types')->nullable();
            $table->json('roof_work_types')->nullable();
            $table->json('facade_work_types')->nullable();
            $table->json('isolation_work_types')->nullable();
            $table->enum('ownership_status', ['OWNER', 'TENANT'])->nullable();
            $table->enum('gender', ['MADAME', 'MONSIEUR'])->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            $table->timestamps();
            
            $table->index(['session_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
