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
        Schema::table('phone_calls', function (Blueprint $table) {
            $table->string('city')->nullable()->after('ip_address');
            $table->string('country')->nullable()->after('city');
            $table->string('country_code', 10)->nullable()->after('country');
            $table->text('referrer_url')->nullable()->after('country_code');
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

