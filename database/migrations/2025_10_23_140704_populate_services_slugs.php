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
        // Remplir les slugs pour les services existants
        $services = \DB::table('services')->get();
        
        foreach ($services as $service) {
            $slug = \Illuminate\Support\Str::slug($service->title);
            
            // Vérifier si le slug existe déjà
            $existingCount = \DB::table('services')
                ->where('slug', $slug)
                ->where('id', '!=', $service->id)
                ->count();
            
            // Si le slug existe, ajouter un suffixe
            if ($existingCount > 0) {
                $slug = $slug . '-' . $service->id;
            }
            
            \DB::table('services')
                ->where('id', $service->id)
                ->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vider les slugs (optionnel)
        \DB::table('services')->update(['slug' => null]);
    }
};
