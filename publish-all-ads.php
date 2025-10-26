<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;

echo "=== Publication de toutes les annonces ===\n\n";

// Compter les annonces en brouillon
$draftAds = Ad::where('status', 'draft')->count();
echo "Annonces en brouillon: {$draftAds}\n";

if ($draftAds > 0) {
    // Publier toutes les annonces en brouillon
    $updated = Ad::where('status', 'draft')->update([
        'status' => 'published',
        'published_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "Annonces publiées: {$updated}\n";
} else {
    echo "Aucune annonce en brouillon à publier.\n";
}

// Vérifier le résultat
$publishedAds = Ad::where('status', 'published')->count();
echo "Total des annonces publiées: {$publishedAds}\n";

echo "\n✅ Toutes les annonces sont maintenant publiées !\n";
echo "Relancez: php artisan sitemap:generate\n";
