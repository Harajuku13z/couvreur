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
        Schema::create('abandoned_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->string('user_identifier')->nullable();
            
            // Étape où l'abandon a eu lieu
            $table->string('abandoned_at_step');
            $table->integer('step_number'); // Numéro de l'étape (1, 2, 3, etc.)
            
            // Données saisies jusqu'à l'abandon
            $table->json('form_data');
            
            // Temps passé sur le formulaire
            $table->integer('time_spent_seconds')->nullable();
            
            // Raison de l'abandon (si disponible)
            $table->string('abandon_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['abandoned_at_step', 'created_at']);
            $table->index('user_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_submissions');
    }
};
