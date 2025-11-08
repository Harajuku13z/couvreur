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
        // Vérifier si la table existe avant de la modifier
        if (!Schema::hasTable('phone_calls')) {
            // Si la table n'existe pas, les colonnes seront créées dans la migration de création
            return;
        }
        
        Schema::table('phone_calls', function (Blueprint $table) {
            // Vérifier si les colonnes existent déjà avant de les ajouter
            if (!Schema::hasColumn('phone_calls', 'city')) {
                $table->string('city')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('phone_calls', 'country')) {
                $table->string('country')->nullable()->after('city');
            }
            if (!Schema::hasColumn('phone_calls', 'country_code')) {
                $table->string('country_code', 10)->nullable()->after('country');
            }
            if (!Schema::hasColumn('phone_calls', 'referrer_url')) {
                $table->text('referrer_url')->nullable()->after('country_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('phone_calls', function (Blueprint $table) {
            $table->dropColumn(['city', 'country', 'country_code', 'referrer_url']);
        });
    }
};

