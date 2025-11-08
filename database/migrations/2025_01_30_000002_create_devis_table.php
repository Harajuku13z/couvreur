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
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('numero')->unique();
            $table->enum('statut', ['Brouillon', 'En Attente', 'Accepté', 'Refusé'])->default('Brouillon');
            $table->date('date_emission');
            $table->date('date_validite')->nullable();
            $table->text('description_globale')->nullable();
            $table->string('superficie_totale')->nullable();
            $table->decimal('prix_final_estime', 10, 2)->nullable();
            $table->decimal('total_ht', 10, 2)->default(0);
            $table->decimal('taux_tva', 5, 2)->default(20.00);
            $table->decimal('total_ttc', 10, 2)->default(0);
            $table->text('conditions_particulieres')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            
            $table->index('numero');
            $table->index('statut');
            $table->index('date_emission');
            $table->index('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};

