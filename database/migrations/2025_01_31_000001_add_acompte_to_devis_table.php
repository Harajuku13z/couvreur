<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->decimal('acompte_pourcentage', 5, 2)->nullable()->after('total_ttc');
            $table->decimal('acompte_montant', 10, 2)->nullable()->after('acompte_pourcentage');
            $table->decimal('reste_a_payer', 10, 2)->nullable()->after('acompte_montant');
        });
    }

    public function down(): void
    {
        Schema::table('devis', function (Blueprint $table) {
            $table->dropColumn(['acompte_pourcentage', 'acompte_montant', 'reste_a_payer']);
        });
    }
};

