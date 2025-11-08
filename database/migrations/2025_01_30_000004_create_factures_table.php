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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->nullable()->constrained('devis')->onDelete('set null');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('numero')->unique();
            $table->enum('statut', ['Impayée', 'Payée', 'Partiellement payée', 'Annulée'])->default('Impayée');
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->date('date_paiement')->nullable();
            $table->decimal('prix_total_ht', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('prix_total_ttc', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            
            $table->index('numero');
            $table->index('statut');
            $table->index('date_emission');
            $table->index('client_id');
            $table->index('devis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};

