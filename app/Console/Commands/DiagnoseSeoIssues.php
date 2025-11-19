<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use App\Models\Ad;
use App\Models\Setting;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiagnoseSeoIssues extends Command
{
    protected $signature = 'seo:diagnose {--fix : Corriger automatiquement les problèmes détectés}';
    protected $description = 'Diagnostiquer les problèmes SEO critiques du site';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTIC SEO COMPLET');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        $fix = $this->option('fix');
        $issues = [];
        $warnings = [];
        
        // 1. Vérifier la configuration du domaine
        $this->info('1️⃣  Vérification configuration domaine...');
        $siteUrl = Setting::get('site_url', null);
        $appUrl = config('app.url', null);
        
        if (empty($siteUrl)) {
            $issues[] = "❌ CRITIQUE : 'site_url' n'est pas configuré dans Settings";
            if ($fix) {
                Setting::set('site_url', $appUrl ?? 'https://couvreur-chevigny-saint-sauveur.fr', 'string', 'system');
                $this->warn("   → CORRIGÉ : site_url défini à " . ($appUrl ?? 'https://couvreur-chevigny-saint-sauveur.fr'));
            }
        } else {
            $this->info("   ✅ site_url configuré : {$siteUrl}");
            
            // Vérifier cohérence avec APP_URL
            if ($appUrl && $siteUrl !== $appUrl) {
                $warnings[] = "⚠️  site_url ({$siteUrl}) différent de APP_URL ({$appUrl})";
            }
        }
        
        // 2. Vérifier le sitemap
        $this->info('2️⃣  Vérification sitemap...');
        $sitemapPath = public_path('sitemap.xml');
        
        if (!file_exists($sitemapPath)) {
            $issues[] = "❌ CRITIQUE : sitemap.xml n'existe pas";
            if ($fix) {
                \Artisan::call('sitemap:generate-daily');
                $this->warn("   → CORRIGÉ : Sitemap régénéré");
            }
        } else {
            $sitemapContent = file_get_contents($sitemapPath);
            $urlCount = substr_count($sitemapContent, '<loc>');
            $this->info("   ✅ sitemap.xml existe ({$urlCount} URLs)");
            
            // Vérifier que le sitemap pointe vers le bon domaine
            if ($siteUrl && strpos($sitemapContent, $siteUrl) === false) {
                $issues[] = "❌ CRITIQUE : Le sitemap ne contient PAS d'URLs vers {$siteUrl}";
                if ($fix) {
                    \Artisan::call('sitemap:generate-daily');
                    $this->warn("   → CORRIGÉ : Sitemap régénéré avec bon domaine");
                }
            } else if ($siteUrl) {
                $this->info("   ✅ Sitemap pointe vers le bon domaine");
            }
        }
        
        // 3. Vérifier robots.txt
        $this->info('3️⃣  Vérification robots.txt...');
        $robotsPath = public_path('robots.txt');
        
        if (!file_exists($robotsPath)) {
            $issues[] = "❌ robots.txt n'existe pas";
            $this->warn("   ⚠️  Créez robots.txt manuellement (voir PLAN_RECUPERATION_SEO.md)");
        } else {
            $robotsContent = file_get_contents($robotsPath);
            $this->info("   ✅ robots.txt existe");
            
            // Vérifier présence Sitemap
            if (strpos($robotsContent, 'Sitemap:') === false) {
                $warnings[] = "⚠️  robots.txt ne déclare pas de sitemap";
            }
            
            // Vérifier Disallow critiques
            if (strpos($robotsContent, 'Disallow: /admin') === false) {
                $warnings[] = "⚠️  robots.txt ne bloque pas /admin (recommandé)";
            }
        }
        
        // 4. Analyser la qualité du contenu
        $this->info('4️⃣  Analyse qualité contenu...');
        
        try {
            $totalArticles = Article::count();
            $publishedArticles = Article::where('status', 'published')->count();
            $totalAds = Ad::count();
            
            $this->info("   Articles : {$publishedArticles} publiés / {$totalArticles} total");
            $this->info("   Annonces : {$totalAds}");
            
            // Détecter contenus potentiellement dupliqués (même titre)
            $duplicateTitles = Article::select('title', DB::raw('COUNT(*) as count'))
                ->groupBy('title')
                ->having('count', '>', 1)
                ->get();
            
            if ($duplicateTitles->count() > 0) {
                $issues[] = "❌ {$duplicateTitles->count()} titres d'articles dupliqués détectés";
                $this->warn("   ⚠️  Titres dupliqués : " . $duplicateTitles->pluck('title')->take(5)->implode(', '));
            } else {
                $this->info("   ✅ Aucun titre dupliqué");
            }
            
            // Analyser longueur moyenne
            $avgLength = Article::where('status', 'published')
                ->selectRaw('AVG(LENGTH(content_html)) as avg_length')
                ->value('avg_length');
            
            $avgWords = round($avgLength / 6); // Approximation : 1 mot = 6 caractères
            $this->info("   Longueur moyenne : ~{$avgWords} mots");
            
            if ($avgWords < 800) {
                $warnings[] = "⚠️  Contenu court (< 800 mots en moyenne) - risque de thin content";
            } else if ($avgWords >= 1500) {
                $this->info("   ✅ Contenu riche (> 1500 mots)");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur analyse : " . $e->getMessage());
        }
        
        // 5. Vérifier configuration Google Indexing
        $this->info('5️⃣  Vérification Google Indexing API...');
        $googleCreds = Setting::get('google_search_console_credentials', null);
        
        if (empty($googleCreds)) {
            $issues[] = "❌ Google Search Console credentials non configurés";
            $this->warn("   ⚠️  Configurer dans /admin/indexation");
        } else {
            $this->info("   ✅ Credentials configurés");
            
            // Tester validité
            try {
                $decoded = json_decode($googleCreds, true);
                if ($decoded && isset($decoded['type']) && $decoded['type'] === 'service_account') {
                    $this->info("   ✅ Format JSON valide (service_account)");
                } else {
                    $warnings[] = "⚠️  Credentials JSON format invalide ou type incorrect";
                }
            } catch (\Exception $e) {
                $warnings[] = "⚠️  Erreur parsing credentials JSON";
            }
        }
        
        // 6. Vérifier l'automatisation SEO
        $this->info('6️⃣  Vérification automatisation SEO...');
        $autoEnabled = Setting::get('seo_automation_enabled', false);
        $autoEnabled = filter_var($autoEnabled, FILTER_VALIDATE_BOOLEAN);
        
        if (!$autoEnabled) {
            $warnings[] = "⚠️  Automatisation SEO désactivée";
            $this->warn("   ⚠️  Activer dans /admin/seo-automation");
        } else {
            $this->info("   ✅ Automatisation SEO activée");
        }
        
        // Vérifier personnalisation IA
        $aiPersonalization = Setting::get('ad_template_ai_personalization', true);
        $aiPersonalization = filter_var($aiPersonalization, FILTER_VALIDATE_BOOLEAN);
        
        if (!$aiPersonalization) {
            $warnings[] = "⚠️  Personnalisation IA des templates désactivée";
            $this->warn("   ⚠️  Activer pour éviter contenus dupliqués");
        } else {
            $this->info("   ✅ Personnalisation IA activée");
        }
        
        // 7. Vérifier villes favorites
        $this->info('7️⃣  Vérification villes favorites...');
        $favoriteCities = City::where('is_favorite', true)->count();
        
        if ($favoriteCities === 0) {
            $issues[] = "❌ Aucune ville favorite configurée";
            $this->warn("   ⚠️  Configurer dans /admin/cities");
        } else {
            $this->info("   ✅ {$favoriteCities} villes favorites");
        }
        
        // 8. Vérifier mots-clés personnalisés
        $this->info('8️⃣  Vérification mots-clés SEO...');
        $customKeywords = Setting::get('seo_custom_keywords', '[]');
        $keywords = json_decode($customKeywords, true) ?? [];
        
        if (empty($keywords)) {
            $warnings[] = "⚠️  Aucun mot-clé personnalisé configuré";
            $this->warn("   ⚠️  Configurer dans /admin/keywords");
        } else {
            $this->info("   ✅ " . count($keywords) . " mots-clés configurés");
        }
        
        // 9. Vérifier clés API
        $this->info('9️⃣  Vérification clés API...');
        $chatgptKey = Setting::get('chatgpt_api_key', null);
        $serpKey = Setting::get('serp_api_key', null);
        
        if (empty($chatgptKey)) {
            $issues[] = "❌ CRITIQUE : Clé API ChatGPT manquante";
        } else {
            $this->info("   ✅ ChatGPT configuré");
        }
        
        if (empty($serpKey)) {
            $warnings[] = "⚠️  Clé API SerpAPI manquante (optionnel)";
        } else {
            $this->info("   ✅ SerpAPI configuré");
        }
        
        // 10. Analyser la distribution des pages par ville
        $this->info('🔟 Analyse distribution pages/ville...');
        
        try {
            $adsByCity = Ad::select('city_id', DB::raw('COUNT(*) as count'))
                ->whereNotNull('city_id')
                ->groupBy('city_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();
            
            if ($adsByCity->isNotEmpty()) {
                $max = $adsByCity->first()->count;
                $min = $adsByCity->last()->count;
                $this->info("   Distribution : {$max} max, {$min} min par ville (top 10)");
                
                if ($max / $min > 5) {
                    $warnings[] = "⚠️  Distribution déséquilibrée (facteur " . round($max / $min, 1) . "x)";
                }
            }
        } catch (\Exception $e) {
            // Ignorer si pas de données
        }
        
        // RÉSUMÉ
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 RÉSUMÉ DU DIAGNOSTIC');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        if (empty($issues) && empty($warnings)) {
            $this->info('🎉 Aucun problème détecté ! Configuration optimale.');
            return 0;
        }
        
        if (!empty($issues)) {
            $this->error('🚨 PROBLÈMES CRITIQUES (' . count($issues) . ') :');
            foreach ($issues as $issue) {
                $this->error($issue);
            }
            $this->newLine();
        }
        
        if (!empty($warnings)) {
            $this->warn('⚠️  AVERTISSEMENTS (' . count($warnings) . ') :');
            foreach ($warnings as $warning) {
                $this->warn($warning);
            }
            $this->newLine();
        }
        
        // Recommandations
        $this->info('💡 ACTIONS RECOMMANDÉES :');
        $this->newLine();
        
        if (!empty($issues)) {
            $this->warn('1. Corriger immédiatement les problèmes critiques ci-dessus');
            if (!$fix) {
                $this->warn('   → Relancer avec --fix pour auto-correction');
            }
        }
        
        $this->info('2. Régénérer le sitemap : php artisan sitemap:generate-daily');
        $this->info('3. Soumettre sitemap à Google Search Console');
        $this->info('4. Demander réindexation des pages clés via GSC');
        $this->info('5. Consulter PLAN_RECUPERATION_SEO.md pour plan détaillé');
        $this->newLine();
        
        // Logging
        Log::info('Diagnostic SEO effectué', [
            'issues_count' => count($issues),
            'warnings_count' => count($warnings),
            'auto_fix' => $fix
        ]);
        
        return count($issues) > 0 ? 1 : 0;
    }
}

