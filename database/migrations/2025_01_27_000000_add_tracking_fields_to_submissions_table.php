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
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('user_identifier');
            $table->string('city')->nullable()->after('ip_address');
            $table->string('country')->nullable()->after('city');
            $table->string('country_code', 2)->nullable()->after('country');
            $table->text('referrer_url')->nullable()->after('country_code');
            $table->text('user_agent')->nullable()->after('referrer_url');
            $table->decimal('recaptcha_score', 3, 2)->nullable()->after('user_agent');
            $table->json('tracking_data')->nullable()->after('recaptcha_score');
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

