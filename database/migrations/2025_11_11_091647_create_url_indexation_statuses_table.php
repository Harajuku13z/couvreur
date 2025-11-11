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
        Schema::create('url_indexation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->boolean('indexed')->default(false);
            $table->string('coverage_state')->nullable(); // Indexed, NotIndexed, Discovered, etc.
            $table->string('indexing_state')->nullable(); // IndexingAllowed, BlockedByRobotsTxt, etc.
            $table->string('page_fetch_state')->nullable(); // Success, Soft404, etc.
            $table->string('verdict')->nullable(); // Pass, Partial, Fail, etc.
            $table->timestamp('last_crawl_time')->nullable();
            $table->timestamp('last_submission_time')->nullable(); // Quand on a envoyé via API
            $table->timestamp('last_verification_time')->nullable(); // Quand on a vérifié via Inspection API
            $table->json('details')->nullable(); // Détails supplémentaires
            $table->json('errors')->nullable(); // Erreurs éventuelles
            $table->json('warnings')->nullable(); // Avertissements
            $table->boolean('mobile_usable')->nullable();
            $table->integer('submission_count')->default(0); // Nombre de fois qu'on a soumis
            $table->timestamps();

            $table->index('url');
            $table->index('indexed');
            $table->index('coverage_state');
            $table->index('last_verification_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_indexation_statuses');
    }
};
