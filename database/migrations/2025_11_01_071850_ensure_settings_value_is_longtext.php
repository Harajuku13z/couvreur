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
        // Utiliser DB::statement pour modifier directement la colonne
        // Cela évite les problèmes avec doctrine/dbal qui pourrait ne pas être installé
        \DB::statement('ALTER TABLE `settings` MODIFY `value` LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à TEXT en cas de rollback
        \DB::statement('ALTER TABLE `settings` MODIFY `value` TEXT NULL');
    }
};
