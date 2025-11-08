<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('montant_paye', 10, 2)->default(0)->after('prix_total_ttc');
            $table->integer('nombre_relances')->default(0)->after('montant_paye');
            $table->date('derniere_relance')->nullable()->after('nombre_relances');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['montant_paye', 'nombre_relances', 'derniere_relance']);
        });
    }
};

