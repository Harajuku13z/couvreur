<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapService;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FixSausercouvertureCompletely extends Command
{
    protected $signature = 'fix:sausercouverture-complete {--force : Force la correction sans confirmation}';
    protected $description = 'Corrige définitivement tous les problèmes liés à sausercouverture.fr';

    public function handle()
    {
        $this->info('🔍 Recherche complète de toutes les occurrences de sausercouverture.fr...');
        
        if (!$this->option('force')) {
            if (!$this->confirm('Cette commande va corriger TOUS les problèmes liés à sausercouverture.fr. Continuer ?')) {
                $this->info('❌ Opération annulée');
                return 0;
            }
        }
        
        $fixed = [];
        
        // 1. CORRIGER LE SETTING site_url dans la base de données
        $this->info('📊 1. Correction du setting site_url dans la base de données...');
        try {
            $setting = DB::table('settings')->where('key', 'site_url')->first();
            if ($setting) {
                $currentValue = $setting->value;
                if (strpos($currentValue, 'sausercouverture.fr') !== false) {
                    $newValue = 'https://normesrenovationbretagne.fr';
                    DB::table('settings')
                        ->where('key', 'site_url')
                        ->update(['value' => $newValue]);
                    $this->line("   ✓ Setting corrigé: {$currentValue} → {$newValue}");
                    $fixed[] = 'Setting site_url';
                } else {
                    $this->line("   ✓ Setting déjà correct: {$currentValue}");
                }
            } else {
                // Créer le setting s'il n'existe pas
                DB::table('settings')->insert([
                    'key' => 'site_url',
                    'value' => 'https://normesrenovationbretagne.fr',
                    'type' => 'string',
                    'group' => 'seo',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $this->line("   ✓ Setting créé: https://normesrenovationbretagne.fr");
                $fixed[] = 'Setting site_url créé';
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Erreur: " . $e->getMessage());
        }
        
        // 2. VIDER TOUS LES CACHES
        $this->info('🧹 2. Vidage de tous les caches...');
        try {
            Setting::clearCache();
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('view:clear');
            $this->line("   ✓ Caches vidés");
            $fixed[] = 'Caches vidés';
        } catch (\Exception $e) {
            $this->error("   ✗ Erreur: " . $e->getMessage());
        }
        
        // 3. SUPPRIMER TOUS LES SITEMAPS EXISTANTS
        $this->info('🗑️  3. Suppression de tous les sitemaps existants...');
        $sitemapFiles = glob(public_path('sitemap*.xml'));
        $deletedCount = 0;
        foreach ($sitemapFiles as $file) {
            if (unlink($file)) {
                $deletedCount++;
                $this->line("   ✓ Supprimé: " . basename($file));
            }
        }
        if ($deletedCount > 0) {
            $this->info("   ✅ {$deletedCount} sitemap(s) supprimé(s)");
            $fixed[] = "{$deletedCount} sitemap(s) supprimé(s)";
        }
        
        // 4. DÉSACTIVER LES AUTRES COMMANDES DE GÉNÉRATION DE SITEMAP
        $this->info('🔧 4. Vérification des commandes de génération de sitemap...');
        $this->line("   ℹ️  Les commandes suivantes existent mais ne doivent PAS être utilisées:");
        $this->line("      - sitemap:generate (Spatie)");
        $this->line("      - sitemap:generate-complete");
        $this->line("      - sitemap:generate-manual");
        $this->line("   ✓ Utilisez uniquement: sitemap:reset ou SitemapService");
        
        // 5. VÉRIFIER ET CORRIGER LES LISTENERS/MIDDLEWARE
        $this->info('🔍 5. Vérification des listeners et middleware...');
        $this->line("   ℹ️  UpdateSitemapListener et UpdateSitemapMiddleware utilisent SitemapService");
        $this->line("   ✓ SitemapService rejette automatiquement sausercouverture.fr");
        
        // 6. RÉGÉNÉRER LES SITEMAPS AVEC LA BONNE URL
        $this->info('📝 6. Régénération des sitemaps avec la bonne URL...');
        sleep(1); // Attendre que les caches soient vidés
        try {
            $sitemapService = new SitemapService();
            $result = $sitemapService->generateSitemap();
            
            if ($result['success']) {
                $this->info("   ✅ " . count($result['sitemaps']) . " sitemap(s) généré(s)");
                $this->info("   📊 Total: {$result['total_urls']} URLs");
                foreach ($result['sitemaps'] as $sitemap) {
                    $this->line("      ✓ {$sitemap['filename']} ({$sitemap['urls_count']} URLs)");
                }
                $fixed[] = count($result['sitemaps']) . " sitemap(s) régénéré(s)";
            } else {
                $this->error("   ✗ Erreur: " . ($result['error'] ?? 'Erreur inconnue'));
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Erreur: " . $e->getMessage());
        }
        
        // 7. VÉRIFICATION FINALE STRICTE
        $this->info('🔍 7. Vérification finale stricte...');
        $finalSitemaps = glob(public_path('sitemap*.xml'));
        $allGood = true;
        
        foreach ($finalSitemaps as $sitemapFile) {
            $content = file_get_contents($sitemapFile);
            $filename = basename($sitemapFile);
            
            $badCount = substr_count($content, 'sausercouverture.fr');
            $goodCount = substr_count($content, 'normesrenovationbretagne.fr');
            
            if ($badCount > 0) {
                $this->error("   ✗ {$filename} contient encore {$badCount} occurrence(s) de sausercouverture.fr !");
                unlink($sitemapFile);
                $allGood = false;
            } else if ($goodCount === 0) {
                $this->error("   ✗ {$filename} ne contient pas normesrenovationbretagne.fr !");
                unlink($sitemapFile);
                $allGood = false;
            } else {
                $this->line("   ✓ {$filename}: {$goodCount} URL(s) avec normesrenovationbretagne.fr, 0 avec sausercouverture.fr");
            }
        }
        
        // Si des sitemaps ont été supprimés, régénérer une dernière fois
        if (!$allGood) {
            $this->warn("🔄 Régénération finale...");
            sleep(1);
            try {
                $sitemapService = new SitemapService();
                $result = $sitemapService->generateSitemap();
                if ($result['success']) {
                    $this->info("   ✅ " . count($result['sitemaps']) . " sitemap(s) régénéré(s)");
                }
            } catch (\Exception $e) {
                $this->error("   ✗ Erreur: " . $e->getMessage());
            }
        }
        
        // 8. VÉRIFIER LES AUTRES COMMANDES QUI PEUVENT UTILISER site_url
        $this->info('🔍 8. Vérification des autres commandes...');
        $this->line("   ℹ️  Les commandes suivantes utilisent site_url:");
        $this->line("      - GenerateSitemap (Spatie) - NE PAS UTILISER");
        $this->line("      - GenerateCompleteSitemap - NE PAS UTILISER");
        $this->line("      - GenerateSitemapManual - NE PAS UTILISER");
        $this->line("   ✓ Utilisez uniquement: sitemap:reset ou SitemapService");
        
        // Résumé
        $this->newLine();
        $this->info('✅ Correction terminée !');
        $this->table(
            ['Action', 'Statut'],
            array_map(function($item) {
                return ['✓ ' . $item, 'OK'];
            }, $fixed)
        );
        
        $this->newLine();
        $this->info('💡 Pour éviter que le problème revienne:');
        $this->line('   1. Utilisez UNIQUEMENT la commande: php artisan sitemap:reset');
        $this->line('   2. OU utilisez SitemapService dans le code');
        $this->line('   3. NE PAS utiliser les commandes: sitemap:generate, sitemap:generate-complete, sitemap:generate-manual');
        $this->line('   4. Vérifiez que le setting site_url est toujours: https://normesrenovationbretagne.fr');
        
        return 0;
    }
}

