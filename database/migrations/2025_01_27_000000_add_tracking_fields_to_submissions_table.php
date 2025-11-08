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
        if (!Schema::hasTable('submissions')) {
            // Si la table n'existe pas, créer les colonnes directement dans la migration de création
            // Cette migration sera ignorée si la table n'existe pas encore
            return;
        }
        
        Schema::table('submissions', function (Blueprint $table) {
            // Vérifier si les colonnes existent déjà avant de les ajouter
            if (!Schema::hasColumn('submissions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('user_identifier');
            }
            if (!Schema::hasColumn('submissions', 'city')) {
                $table->string('city')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('submissions', 'country')) {
                $table->string('country')->nullable()->after('city');
            }
            if (!Schema::hasColumn('submissions', 'country_code')) {
                $table->string('country_code', 2)->nullable()->after('country');
            }
            if (!Schema::hasColumn('submissions', 'referrer_url')) {
                $table->text('referrer_url')->nullable()->after('country_code');
            }
            if (!Schema::hasColumn('submissions', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('referrer_url');
            }
            if (!Schema::hasColumn('submissions', 'recaptcha_score')) {
                $table->decimal('recaptcha_score', 3, 2)->nullable()->after('user_agent');
            }
            if (!Schema::hasColumn('submissions', 'tracking_data')) {
                $table->json('tracking_data')->nullable()->after('recaptcha_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'city',
                'country',
                'country_code',
                'referrer_url',
                'user_agent',
                'recaptcha_score',
                'tracking_data'
            ]);
        });
    }
};

