<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SitemapService;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class ResetSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:reset {--force : Force la réinitialisation sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialise et régénère tous les sitemaps en supprimant les anciens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Réinitialisation des sitemaps...');
        
        // Demander confirmation si --force n'est pas utilisé
        if (!$this->option('force')) {
            if (!$this->confirm('Êtes-vous sûr de vouloir supprimer tous les sitemaps existants et les régénérer ?')) {
                $this->info('❌ Opération annulée');
                return 0;
            }
        }
        
        try {
            // 1. Supprimer tous les anciens sitemaps
            $this->info('🗑️  Suppression des anciens sitemaps...');
            $sitemapFiles = glob(public_path('sitemap*.xml'));
            $deletedCount = 0;
            
            foreach ($sitemapFiles as $file) {
                if (unlink($file)) {
                    $deletedCount++;
                    $this->line("   ✓ Supprimé: " . basename($file));
                    Log::info("🗑️ Sitemap supprimé: " . basename($file));
                } else {
                    $this->error("   ✗ Erreur lors de la suppression: " . basename($file));
                }
            }
            
            if ($deletedCount > 0) {
                $this->info("✅ {$deletedCount} sitemap(s) supprimé(s)");
            } else {
                $this->info("ℹ️  Aucun sitemap à supprimer");
            }
            
            // 2. FORCER la bonne URL (utiliser la commande site-url:fix)
            $this->info('🔗 Correction de l\'URL du site...');
            $this->call('site-url:fix');
            
            // Récupérer l'URL corrigée
            $siteUrl = Setting::get('site_url', 'https://normesrenovationbretagne.fr');
            if (strpos($siteUrl, 'sausercouverture.fr') !== false) {
                $this->error("❌ ERREUR: L'URL contient encore sausercouverture.fr après correction !");
                $siteUrl = 'https://normesrenovationbretagne.fr';
                Setting::set('site_url', $siteUrl, 'string', 'seo');
            }
            
            $this->line("   ✓ URL configurée: {$siteUrl}");
            Log::info("✅ site_url FORCÉ à: {$siteUrl}");
            
            // 3. Vider TOUS les caches
            $this->info('🧹 Vidage des caches...');
            Setting::clearCache();
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('view:clear');
            $this->info('✅ Caches vidés');
            
            // 4. Attendre un peu pour que les caches soient bien vidés
            sleep(1);
            
            // 5. Régénérer tous les sitemaps
            $this->info('📝 Génération des nouveaux sitemaps...');
            $sitemapService = new SitemapService();
            $result = $sitemapService->generateSitemap();
            
            if (!$result['success']) {
                $this->error('❌ Erreur lors de la génération: ' . ($result['error'] ?? 'Erreur inconnue'));
                return 1;
            }
            
            $this->info("✅ " . count($result['sitemaps']) . " sitemap(s) généré(s)");
            $this->info("📊 Total: {$result['total_urls']} URLs");
            
            foreach ($result['sitemaps'] as $sitemap) {
                $this->line("   ✓ {$sitemap['filename']} ({$sitemap['urls_count']} URLs)");
            }
            
            // 6. Vérifier que TOUS les sitemaps ont la bonne URL (vérification stricte)
            $this->info('🔍 Vérification stricte des URLs dans les sitemaps...');
            $allSitemaps = glob(public_path('sitemap*.xml'));
            $hasOldUrl = false;
            $deletedForOldUrl = 0;
            
            foreach ($allSitemaps as $sitemapFile) {
                $content = file_get_contents($sitemapFile);
                if (strpos($content, 'sausercouverture.fr') !== false) {
                    $this->warn("⚠️  Le sitemap " . basename($sitemapFile) . " contient encore l'ancienne URL sausercouverture.fr - SUPPRESSION");
                    unlink($sitemapFile);
                    $hasOldUrl = true;
                    $deletedForOldUrl++;
                    Log::warning("⚠️ Le sitemap " . basename($sitemapFile) . " contient encore l'ancienne URL sausercouverture.fr, suppression...");
                } else if (strpos($content, 'normesrenovationbretagne.fr') === false) {
                    // Vérifier aussi qu'il contient bien la bonne URL
                    $this->warn("⚠️  Le sitemap " . basename($sitemapFile) . " ne contient pas normesrenovationbretagne.fr - SUPPRESSION");
                    unlink($sitemapFile);
                    $hasOldUrl = true;
                    $deletedForOldUrl++;
                }
            }
            
            // Si des sitemaps avec l'ancienne URL ont été supprimés, régénérer
            if ($hasOldUrl) {
                $this->warn("🔄 Régénération des sitemaps avec la bonne URL ({$deletedForOldUrl} sitemap(s) supprimé(s))...");
                $result = $sitemapService->generateSitemap();
                if ($result['success']) {
                    $this->info("✅ " . count($result['sitemaps']) . " sitemap(s) régénéré(s)");
                } else {
                    $this->error("❌ Erreur lors de la régénération: " . ($result['error'] ?? 'Erreur inconnue'));
                }
            }
            
            // 7. Vérification finale STRICTE (vérifier chaque sitemap)
            $this->info('🔍 Vérification finale stricte...');
            $finalCheck = glob(public_path('sitemap*.xml'));
            $finalDeleted = 0;
            
            foreach ($finalCheck as $sitemapFile) {
                $content = file_get_contents($sitemapFile);
                $filename = basename($sitemapFile);
                
                if (strpos($content, 'sausercouverture.fr') !== false) {
                    $this->error("❌ ERREUR: Le sitemap {$filename} contient encore sausercouverture.fr !");
                    unlink($sitemapFile);
                    $finalDeleted++;
                    Log::error("❌ ERREUR: Le sitemap {$filename} contient encore sausercouverture.fr après régénération !");
                } else if (strpos($content, 'normesrenovationbretagne.fr') === false) {
                    $this->error("❌ ERREUR: Le sitemap {$filename} ne contient pas normesrenovationbretagne.fr !");
                    unlink($sitemapFile);
                    $finalDeleted++;
                    Log::error("❌ ERREUR: Le sitemap {$filename} ne contient pas normesrenovationbretagne.fr !");
                } else {
                    // Compter combien d'URLs contiennent la bonne URL
                    $goodUrlCount = substr_count($content, 'normesrenovationbretagne.fr');
                    $badUrlCount = substr_count($content, 'sausercouverture.fr');
                    if ($badUrlCount > 0) {
                        $this->error("❌ ERREUR: Le sitemap {$filename} contient {$badUrlCount} URL(s) avec sausercouverture.fr !");
                        unlink($sitemapFile);
                        $finalDeleted++;
                    } else {
                        $this->line("   ✓ {$filename}: {$goodUrlCount} URL(s) avec normesrenovationbretagne.fr");
                    }
                }
            }
            
            // Si des sitemaps ont été supprimés lors de la vérification finale, régénérer
            if ($finalDeleted > 0) {
                $this->warn("🔄 Régénération finale ({$finalDeleted} sitemap(s) supprimé(s))...");
                $result = $sitemapService->generateSitemap();
                if ($result['success']) {
                    $this->info("✅ " . count($result['sitemaps']) . " sitemap(s) généré(s)");
                } else {
                    $this->error("❌ Erreur lors de la régénération finale: " . ($result['error'] ?? 'Erreur inconnue'));
                    return 1;
                }
            }
            
            // Résumé final
            $finalSitemaps = glob(public_path('sitemap*.xml'));
            $this->newLine();
            $this->info('✅ Réinitialisation terminée avec succès !');
            $this->table(
                ['Fichier', 'Taille', 'URLs'],
                array_map(function($file) {
                    $filename = basename($file);
                    $size = filesize($file);
                    $urlsCount = 0;
                    try {
                        $xml = file_get_contents($file);
                        $xmlObj = simplexml_load_string($xml);
                        if ($xmlObj && isset($xmlObj->url)) {
                            $urlsCount = count($xmlObj->url);
                        }
                    } catch (\Exception $e) {
                        // Ignorer
                    }
                    return [
                        $filename,
                        number_format($size / 1024, 2) . ' KB',
                        $urlsCount
                    ];
                }, $finalSitemaps)
            );
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la réinitialisation: ' . $e->getMessage());
            Log::error('Erreur réinitialisation sitemaps: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}

