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
        // Ajouter les paramètres de prompts par défaut
        \App\Models\Setting::create([
            'key' => 'ai_prompt_ads',
            'value' => 'Tu es un expert en marketing digital et SEO local. Génère une description optimisée pour une annonce de service local. Inclus des mots-clés locaux, des avantages clients, et un appel à l\'action. Le contenu doit être unique et engageant.',
            'type' => 'text'
        ]);
        
        \App\Models\Setting::create([
            'key' => 'ai_prompt_articles',
            'value' => 'Tu es un expert en rédaction web et SEO. Génère un titre d\'article optimisé pour le référencement, avec un focus sur les mots-clés pertinents. Le titre doit être accrocheur et informatif.',
            'type' => 'text'
        ]);
        
        \App\Models\Setting::create([
            'key' => 'ai_prompt_services',
            'value' => 'Tu es un expert en rédaction de contenu pour services. Génère une description de service professionnelle, incluant les avantages, la qualité, et l\'expertise. Le contenu doit inspirer confiance.',
            'type' => 'text'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les paramètres de prompts
        \App\Models\Setting::whereIn('key', [
            'ai_prompt_ads',
            'ai_prompt_articles', 
            'ai_prompt_services'
        ])->delete();
    }
};
