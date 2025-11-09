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
        Schema::table('devis', function (Blueprint $table) {
            $table->string('public_token', 32)->nullable()->after('pdf_path');
        });
        
        // Générer un token pour les devis existants
        \DB::statement('UPDATE devis SET public_token = SUBSTRING(MD5(CONCAT(id, RAND())), 1, 32) WHERE public_token IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};
