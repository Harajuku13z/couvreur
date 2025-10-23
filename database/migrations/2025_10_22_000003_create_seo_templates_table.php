<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('template_title');
            $table->string('template_meta');
            $table->json('content_blocks_json')->nullable();
            $table->json('config_json')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_templates');
    }
};





