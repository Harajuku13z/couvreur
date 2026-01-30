<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\File;

$filePath = base_path('resources/views/services/show.blade.php');
$content = File::get($filePath);

// Styles à insérer (même que les annonces mais avec .service-page-content aussi)
$stylesToInsert = <<<'STYLES'
    
    /* Empêcher le scroll horizontal sur mobile */
    html, body {
        overflow-x: hidden;
        max-width: 100%;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: none;
    }
    
    /* Assurer que tous les conteneurs respectent la largeur */
    .container, [class*="max-w"] {
        max-width: 100%;
        overflow-x: hidden;
    }
    
    /* Contenus HTML dans les services - forcer les retours à la ligne */
    .service-page-content,
    .bg-white.rounded-2xl {
        overflow-x: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: none;
    }
    
    /* Forcer les retours à la ligne pour tous les éléments de texte */
    .service-page-content p,
    .service-page-content div,
    .service-page-content span,
    .service-page-content li,
    .service-page-content td,
    .service-page-content th,
    .service-page-content a,
    .service-page-content h1,
    .service-page-content h2,
    .service-page-content h3,
    .service-page-content h4,
    .service-page-content h5,
    .service-page-content h6,
    .bg-white.rounded-2xl p,
    .bg-white.rounded-2xl div,
    .bg-white.rounded-2xl span,
    .bg-white.rounded-2xl li,
    .bg-white.rounded-2xl td,
    .bg-white.rounded-2xl th,
    .bg-white.rounded-2xl a,
    .bg-white.rounded-2xl h1,
    .bg-white.rounded-2xl h2,
    .bg-white.rounded-2xl h3,
    .bg-white.rounded-2xl h4,
    .bg-white.rounded-2xl h5,
    .bg-white.rounded-2xl h6 {
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        word-break: break-word !important;
        hyphens: none !important;
        max-width: 100%;
        overflow-x: hidden;
    }
    
    /* URLs et mots longs */
    .service-page-content a,
    .bg-white.rounded-2xl a {
        word-break: break-all;
        overflow-wrap: anywhere;
    }
    
    /* Images et médias */
    .service-page-content img,
    .service-page-content iframe,
    .service-page-content video,
    .service-page-content embed,
    .service-page-content object,
    .bg-white.rounded-2xl img,
    .bg-white.rounded-2xl iframe,
    .bg-white.rounded-2xl video,
    .bg-white.rounded-2xl embed,
    .bg-white.rounded-2xl object {
        max-width: 100% !important;
        height: auto;
        display: block;
    }
    
    /* Tableaux - forcer les retours à la ligne au lieu du scroll */
    .service-page-content table,
    .bg-white.rounded-2xl table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed;
        word-wrap: break-word;
        overflow-wrap: break-word;
        border-collapse: collapse;
    }
    
    .service-page-content td,
    .service-page-content th,
    .bg-white.rounded-2xl td,
    .bg-white.rounded-2xl th {
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        overflow-x: hidden;
        max-width: 0;
    }
    
    /* Code et pre - retour à la ligne */
    .service-page-content pre,
    .service-page-content code,
    .bg-white.rounded-2xl pre,
    .bg-white.rounded-2xl code {
        max-width: 100%;
        word-wrap: break-word !important;
        overflow-wrap: break-word !important;
        white-space: pre-wrap !important;
        overflow-x: hidden;
    }
    
    /* Listes */
    .service-page-content ul,
    .service-page-content ol,
    .bg-white.rounded-2xl ul,
    .bg-white.rounded-2xl ol {
        overflow-x: hidden;
        word-wrap: break-word;
    }
    
    /* Assurer que tous les éléments enfants respectent la largeur */
    .service-page-content *,
    .bg-white.rounded-2xl * {
        max-width: 100%;
        box-sizing: border-box;
    }
    
    /* Sections avec overflow */
    section {
        overflow-x: hidden;
        width: 100%;
        word-wrap: break-word;
        hyphens: none;
    }
STYLES;

// Remplacer la section @push('head')
if (preg_match('/(@push\(\'head\'\)\s*<style>\s*\/\* Variables.*?--accent-color.*?;\s*)(<\/style>@endpush)/s', $content, $matches)) {
    $newContent = str_replace($matches[0], $matches[1] . $stylesToInsert . "\n    " . $matches[2], $content);
    File::put($filePath, $newContent);
    echo "✅ Styles appliqués dans @push('head')!\n";
} else {
    echo "❌ Impossible de trouver la section @push('head')\n";
    exit(1);
}

// Supprimer l'ancienne section style avant @endsection
$content = File::get($filePath);
if (preg_match('/(<style>\s*\/\* Limiter le scroll.*?<\/style>)(@endsection)/s', $content, $matches)) {
    $newContent = str_replace($matches[0], $matches[2], $content);
    File::put($filePath, $newContent);
    echo "✅ Ancienne section style supprimée!\n";
}

echo "🧹 Nettoyage du cache...\n";
\Artisan::call('view:clear');
\Artisan::call('cache:clear');
echo "✅ Cache nettoyé\n";
echo "✅ Modification terminée!\n";
