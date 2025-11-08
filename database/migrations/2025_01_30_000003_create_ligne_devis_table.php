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
        Schema::create('ligne_devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devis_id')->constrained('devis')->onDelete('cascade');
            $table->integer('ordre')->default(0);
            $table->text('description');
            $table->decimal('quantite', 10, 2);
            $table->string('unite')->default('unité');
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('total_ligne', 10, 2);
            $table->timestamps();
            
            $table->index('devis_id');
            $table->index('ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_devis');
    }
};

