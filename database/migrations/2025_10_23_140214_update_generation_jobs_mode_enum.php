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
        Schema::table('generation_jobs', function (Blueprint $table) {
            // Modifier l'enum pour inclure les nouvelles valeurs
            $table->enum('mode', ['keyword', 'titles', 'keyword_cities', 'keyword_services', 'service_cities', 'bulk_generation'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generation_jobs', function (Blueprint $table) {
            // Revenir à l'enum original
            $table->enum('mode', ['keyword', 'titles'])->change();
        });
    }
};
