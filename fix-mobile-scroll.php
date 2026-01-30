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

// Styles CSS à ajouter pour éviter le scroll horizontal
$mobileScrollFixStyles = <<<'CSS'

<style>
    /* Limiter le scroll horizontal et forcer les retours à la ligne, surtout en mobile */
    .service-page-content,
    .article-content,
    .ad-page-content {
        overflow-x: hidden;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
    }

    .service-page-content img,
    .service-page-content table,
    .article-content img,
    .article-content table,
    .ad-page-content img,
    .ad-page-content table {
        max-width: 100%;
        height: auto;
    }

    @media (max-width: 768px) {
        .service-hero-title,
        .article-hero-title,
        .ad-hero-title {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        /* Force le conteneur principal à ne pas dépasser */
        body {
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        /* Assure que tous les conteneurs respectent la largeur */
        .container,
        .container-fluid {
            max-width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
        }
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
