<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('generation_jobs', function (Blueprint $table) {
            $table->id();
            $table->enum('mode', ['keyword', 'titles']);
            $table->json('payload_json');
            $table->enum('status', ['queued', 'running', 'completed', 'failed'])->default('queued')->index();
            $table->json('stats_json')->nullable();
            $table->timestamps();
            $table->timestamp('finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generation_jobs');
    }
};





