<?php

// =====================================================
// SCRIPT DE CORRECTION RAPIDE - SCROLL HORIZONTAL MOBILE
// =====================================================
// À exécuter en production pour corriger le scroll X en mobile
// sur les pages de services et autres pages de contenu

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\File;

echo "🔧 Correction du scroll horizontal en mobile...\n\n";

$fixesApplied = 0;
$filesChecked = 0;
$errors = [];

// Styles CSS à ajouter pour éviter le scroll horizontal (même approche que les annonces)
$mobileScrollFixStyles = <<<'CSS'

<style>
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
    
    /* Contenus HTML - forcer les retours à la ligne */
    .service-page-content,
    .article-content,
    .ad-page-content,
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
    .article-content p,
    .article-content div,
    .article-content span,
    .article-content li,
    .article-content td,
    .article-content th,
    .article-content a,
    .article-content h1,
    .article-content h2,
    .article-content h3,
    .article-content h4,
    .article-content h5,
    .article-content h6,
    .ad-page-content p,
    .ad-page-content div,
    .ad-page-content span,
    .ad-page-content li,
    .ad-page-content td,
    .ad-page-content th,
    .ad-page-content a,
    .ad-page-content h1,
    .ad-page-content h2,
    .ad-page-content h3,
    .ad-page-content h4,
    .ad-page-content h5,
    .ad-page-content h6,
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
    .article-content a,
    .ad-page-content a,
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
    .article-content img,
    .article-content iframe,
    .article-content video,
    .article-content embed,
    .article-content object,
    .ad-page-content img,
    .ad-page-content iframe,
    .ad-page-content video,
    .ad-page-content embed,
    .ad-page-content object,
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
    .article-content table,
    .ad-page-content table,
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
    .article-content td,
    .article-content th,
    .ad-page-content td,
    .ad-page-content th,
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
    .article-content pre,
    .article-content code,
    .ad-page-content pre,
    .ad-page-content code,
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
    .article-content ul,
    .article-content ol,
    .ad-page-content ul,
    .ad-page-content ol,
    .bg-white.rounded-2xl ul,
    .bg-white.rounded-2xl ol {
        overflow-x: hidden;
        word-wrap: break-word;
    }
    
    /* Assurer que tous les éléments enfants respectent la largeur */
    .service-page-content *,
    .article-content *,
    .ad-page-content *,
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
</style>
CSS;

// Classes à ajouter aux éléments de contenu
$contentWrapperClass = 'service-page-content';
$heroTitleClass = 'service-hero-title';

// Fichiers à vérifier et corriger
$filesToFix = [
    'resources/views/services/show.blade.php' => [
        'content_class' => 'service-page-content',
        'hero_class' => 'service-hero-title',
        'section_end' => '@endsection'
    ],
    'resources/views/articles/show.blade.php' => [
        'content_class' => 'article-content',
        'hero_class' => 'article-hero-title',
        'section_end' => '@endsection'
    ],
    'resources/views/articles/show_new.blade.php' => [
        'content_class' => 'article-content',
        'hero_class' => 'article-hero-title',
        'section_end' => '@endsection'
    ],
    'resources/views/ads/show.blade.php' => [
        'content_class' => 'ad-page-content',
        'hero_class' => 'ad-hero-title',
        'section_end' => '@endsection'
    ],
];

try {
    foreach ($filesToFix as $filePath => $config) {
        $filesChecked++;
        $fullPath = base_path($filePath);
        
        echo "📋 Vérification de {$filePath}...\n";
        
        if (!File::exists($fullPath)) {
            echo "   ⚠️  Fichier non trouvé, ignoré\n\n";
            continue;
        }
        
        $content = File::get($fullPath);
        $originalContent = $content;
        $needsFix = false;
        
        // 1. Vérifier si les styles anti-scroll sont déjà présents
        $hasStyles = strpos($content, 'overflow-x: hidden') !== false 
                  || strpos($content, 'service-page-content') !== false
                  || strpos($content, 'article-content') !== false
                  || strpos($content, 'ad-page-content') !== false;
        
        // 2. Vérifier si la classe de contenu est appliquée
        $hasContentClass = strpos($content, "class=\"{$config['content_class']}") !== false
                        || strpos($content, "class='{$config['content_class']}") !== false;
        
        // 3. Vérifier si la classe hero est appliquée
        $hasHeroClass = strpos($content, "class=\"{$config['hero_class']}") !== false
                     || strpos($content, "class='{$config['hero_class']}") !== false;
        
        echo "   - Styles anti-scroll: " . ($hasStyles ? "✅ Présents" : "❌ Manquants") . "\n";
        echo "   - Classe contenu ({$config['content_class']}): " . ($hasContentClass ? "✅ Présente" : "❌ Manquante") . "\n";
        echo "   - Classe hero ({$config['hero_class']}): " . ($hasHeroClass ? "✅ Présente" : "❌ Manquante") . "\n";
        
        // Appliquer les corrections si nécessaire
        if (!$hasStyles || !$hasContentClass || !$hasHeroClass) {
            $needsFix = true;
            
            // Ajouter la classe au conteneur de contenu principal
            if (!$hasContentClass) {
                // Chercher le div principal de contenu (généralement avec bg-white ou similaire)
                // Pattern plus flexible pour capturer les divs avec plusieurs classes
                $patterns = [
                    '/(<div\s+class=")([^"]*bg-white[^"]*rounded[^"]*shadow[^"]*p-\d+[^"]*)(">)/',
                    '/(<div\s+class=\')([^\']*bg-white[^\']*rounded[^\']*shadow[^\']*p-\d+[^\']*)(\'>)/',
                ];
                
                $found = false;
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content, $matches)) {
                        $newClass = trim($matches[2] . ' ' . $config['content_class']);
                        $replacement = $matches[1] . $newClass . $matches[3];
                        $content = preg_replace($pattern, $replacement, $content, 1);
                        echo "   ✅ Classe de contenu ajoutée\n";
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    // Fallback: chercher le premier div après @else
                    if (preg_match('/(@else\s*\n\s*)(<div\s+class=")([^"]*)(">)/', $content, $matches)) {
                        $newClass = trim($matches[3] . ' ' . $config['content_class']);
                        $replacement = $matches[1] . $matches[2] . $newClass . $matches[4];
                        $content = preg_replace('/(@else\s*\n\s*)(<div\s+class=")([^"]*)(">)/', $replacement, $content, 1);
                        echo "   ✅ Classe de contenu ajoutée (fallback)\n";
                    }
                }
            }
            
            // Ajouter la classe au titre hero
            if (!$hasHeroClass) {
                // Chercher le h1 principal avec plusieurs patterns
                $patterns = [
                    '/(<h1\s+class=")([^"]*text-\d+xl[^"]*font-bold[^"]*mb-\d+[^"]*)(">)/',
                    '/(<h1\s+class=\')([^\']*text-\d+xl[^\']*font-bold[^\']*mb-\d+[^\']*)(\'>)/',
                    '/(<h1\s+class=")([^"]*text-\d+xl[^"]*mb-\d+[^"]*)(">)/',
                ];
                
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content, $matches)) {
                        $newClass = trim($matches[2] . ' ' . $config['hero_class']);
                        $replacement = $matches[1] . $newClass . $matches[3];
                        $content = preg_replace($pattern, $replacement, $content, 1);
                        echo "   ✅ Classe hero ajoutée\n";
                        break;
                    }
                }
            }
            
            // Ajouter les styles CSS avant @endsection
            if (!$hasStyles) {
                $sectionEnd = $config['section_end'];
                if (strpos($content, $sectionEnd) !== false) {
                    // Vérifier si un <style> avec overflow-x existe déjà avant @endsection
                    $beforeEnd = substr($content, 0, strpos($content, $sectionEnd));
                    $hasOverflowStyle = strpos($beforeEnd, 'overflow-x: hidden') !== false;
                    
                    if (!$hasOverflowStyle) {
                        // Chercher la dernière balise </style> ou ajouter avant @endsection
                        if (preg_match('/(<\/style>\s*)(\n\s*' . preg_quote($sectionEnd, '/') . ')/', $content, $matches)) {
                            // Ajouter après le dernier </style>
                            $content = preg_replace('/(<\/style>\s*)(\n\s*' . preg_quote($sectionEnd, '/') . ')/', '$1' . "\n" . $mobileScrollFixStyles . '$2', $content, 1);
                        } else {
                            // Ajouter juste avant @endsection
                            $content = str_replace($sectionEnd, $mobileScrollFixStyles . "\n" . $sectionEnd, $content);
                        }
                        echo "   ✅ Styles CSS ajoutés\n";
                    } else {
                        echo "   ⚠️  Styles overflow-x déjà présents, ignorés\n";
                    }
                } else {
                    // Ajouter à la fin du fichier
                    $content .= "\n" . $mobileScrollFixStyles;
                    echo "   ✅ Styles CSS ajoutés (fin de fichier)\n";
                }
            }
            
            // Sauvegarder le fichier modifié
            if ($content !== $originalContent) {
                File::put($fullPath, $content);
                $fixesApplied++;
                echo "   ✅ Fichier corrigé et sauvegardé\n\n";
            } else {
                echo "   ⚠️  Aucune modification nécessaire\n\n";
            }
        } else {
            echo "   ✅ Fichier déjà corrigé\n\n";
        }
    }
    
    // Vérification finale
    echo "🔍 Résumé de la correction...\n";
    echo "   - Fichiers vérifiés: {$filesChecked}\n";
    echo "   - Corrections appliquées: {$fixesApplied}\n";
    
    if ($fixesApplied > 0) {
        echo "\n🧹 Nettoyage du cache...\n";
        \Artisan::call('view:clear');
        \Artisan::call('cache:clear');
        echo "✅ Cache nettoyé\n";
        
        echo "\n🎉 Correction terminée avec succès!\n";
        echo "📱 Les pages devraient maintenant être sans scroll horizontal en mobile\n";
    } else {
        echo "\n✅ Tous les fichiers sont déjà corrigés!\n";
    }
    
    if (!empty($errors)) {
        echo "\n⚠️  Erreurs rencontrées:\n";
        foreach ($errors as $error) {
            echo "   - {$error}\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ Erreur lors de la correction: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
