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
        Schema::create('phone_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->string('phone_number');
            $table->string('source_page'); // Page où le clic a eu lieu (home, success, etc.)
            $table->string('ip_address')->nullable();
            
            // Colonnes ajoutées directement ici pour éviter les migrations qui échouent
            // Note: ->after() ne fonctionne pas dans Schema::create(), seulement dans Schema::table()
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->text('referrer_url')->nullable();
            
            $table->text('user_agent')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();
            
            $table->index(['session_id', 'clicked_at']);
            $table->index('clicked_at');
            $table->index('source_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_calls');
    }
};
