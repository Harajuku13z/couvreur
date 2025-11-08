<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier l'enum pour inclure "Partiellement payée"
        // Note: MySQL ne permet pas de modifier directement un enum, il faut recréer la colonne
        DB::statement("ALTER TABLE factures MODIFY COLUMN statut ENUM('Impayée', 'Payée', 'Partiellement payée', 'Annulée') DEFAULT 'Impayée'");
    }

    public function down(): void
    {
        // Revenir à l'ancien enum sans "Partiellement payée"
        DB::statement("ALTER TABLE factures MODIFY COLUMN statut ENUM('Impayée', 'Payée', 'Annulée') DEFAULT 'Impayée'");
    }
};

