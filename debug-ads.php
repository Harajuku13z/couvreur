<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ad;

echo "=== Diagnostic des annonces ===\n\n";

// Compter toutes les annonces
$totalAds = Ad::count();
echo "Total des annonces: {$totalAds}\n";

// Compter par statut
$publishedAds = Ad::where('status', 'published')->count();
$draftAds = Ad::where('status', 'draft')->count();
$otherStatus = Ad::whereNotIn('status', ['published', 'draft'])->count();

echo "Annonces publiées: {$publishedAds}\n";
echo "Annonces brouillon: {$draftAds}\n";
echo "Autres statuts: {$otherStatus}\n\n";

// Vérifier les statuts uniques
echo "Statuts uniques dans la base:\n";
$statuses = Ad::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach ($statuses as $status) {
    echo "- '{$status->status}': {$status->count} annonces\n";
}

echo "\n=== Exemples d'annonces ===\n";

// Afficher quelques exemples
$sampleAds = Ad::limit(5)->get(['id', 'slug', 'status', 'created_at', 'updated_at']);

foreach ($sampleAds as $ad) {
    echo "ID: {$ad->id} | Slug: {$ad->slug} | Status: '{$ad->status}' | Créé: {$ad->created_at}\n";
}

echo "\n=== Test de génération sitemap ===\n";

// Tester la requête utilisée dans le sitemap
$adsForSitemap = Ad::where('status', 'published')
    ->orderBy('updated_at', 'desc')
    ->limit(10)
    ->get(['slug', 'updated_at']);

echo "Annonces pour le sitemap (premières 10):\n";
foreach ($adsForSitemap as $ad) {
    echo "- /annonces/{$ad->slug}\n";
}

echo "\n=== Instructions ===\n";
echo "1. Si vous avez 2304 annonces mais seulement {$publishedAds} publiées, vérifiez les statuts\n";
echo "2. Si les annonces sont en 'draft', changez-les en 'published'\n";
echo "3. Si les annonces ont un autre statut, mettez à jour la requête du sitemap\n";
