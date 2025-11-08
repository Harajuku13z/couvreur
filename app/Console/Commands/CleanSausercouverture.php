<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanSausercouverture extends Command
{
    protected $signature = 'clean:sausercouverture {--force : Force le nettoyage sans confirmation}';
    protected $description = 'Nettoie complètement toutes les références à sausercouverture.fr dans la base de données et les fichiers';

    public function handle()
    {
        $this->info('🧹 Nettoyage complet de sausercouverture.fr...');
        
        if (!$this->option('force')) {
            if (!$this->confirm('Êtes-vous sûr de vouloir nettoyer toutes les références à sausercouverture.fr ?')) {
                $this->info('❌ Opération annulée');
                return 0;
            }
        }
        
        $correctUrl = 'https://normesrenovationbretagne.fr';
        $changes = 0;
        
        try {
            // 1. Corriger le setting site_url
            $this->info('📝 Correction du setting site_url...');
            $currentUrl = Setting::get('site_url', null);
            if (!empty($currentUrl) && strpos($currentUrl, 'sausercouverture.fr') !== false) {
                Setting::set('site_url', $correctUrl, 'string', 'seo');
                Setting::clearCache();
                $this->line("   ✓ site_url corrigé: {$currentUrl} → {$correctUrl}");
                $changes++;
            }
            
            // 2. Chercher dans tous les settings
            $this->info('🔍 Recherche dans les settings...');
            $settings = DB::table('settings')->get();
            foreach ($settings as $setting) {
                $value = $setting->value;
                if (is_string($value) && strpos($value, 'sausercouverture.fr') !== false) {
                    $newValue = str_replace('sausercouverture.fr', 'normesrenovationbretagne.fr', $value);
                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(['value' => $newValue]);
                    $this->line("   ✓ Setting '{$setting->key}' corrigé");
                    $changes++;
                }
            }
            
            // 3. Chercher dans les articles
            $this->info('📰 Recherche dans les articles...');
            $articles = DB::table('articles')->get();
            foreach ($articles as $article) {
                $updated = false;
                $data = [];
                
                if (strpos($article->content ?? '', 'sausercouverture.fr') !== false) {
                    $data['content'] = str_replace('sausercouverture.fr', 'normesrenovationbretagne.fr', $article->content);
                    $updated = true;
                }
                if (strpos($article->meta_description ?? '', 'sausercouverture.fr') !== false) {
                    $data['meta_description'] = str_replace('sausercouverture.fr', 'normesrenovationbretagne.fr', $article->meta_description);
                    $updated = true;
                }
                
                if ($updated) {
                    DB::table('articles')->where('id', $article->id)->update($data);
                    $this->line("   ✓ Article '{$article->title}' corrigé");
                    $changes++;
                }
            }
            
            // 4. Chercher dans les annonces
            $this->info('📢 Recherche dans les annonces...');
            $ads = DB::table('ads')->get();
            foreach ($ads as $ad) {
                $updated = false;
                $data = [];
                
                if (strpos($ad->content ?? '', 'sausercouverture.fr') !== false) {
                    $data['content'] = str_replace('sausercouverture.fr', 'normesrenovationbretagne.fr', $ad->content);
                    $updated = true;
                }
                if (strpos($ad->meta_description ?? '', 'sausercouverture.fr') !== false) {
                    $data['meta_description'] = str_replace('sausercouverture.fr', 'normesrenovationbretagne.fr', $ad->meta_description);
                    $updated = true;
                }
                
                if ($updated) {
                    DB::table('ads')->where('id', $ad->id)->update($data);
                    $this->line("   ✓ Annonce '{$ad->title}' corrigée");
                    $changes++;
                }
            }
            
            // 5. Supprimer tous les sitemaps existants
            $this->info('🗑️  Suppression des sitemaps existants...');
            $sitemapFiles = glob(public_path('sitemap*.xml'));
            $deletedCount = 0;
            foreach ($sitemapFiles as $file) {
                if (unlink($file)) {
                    $deletedCount++;
                }
            }
            if ($deletedCount > 0) {
                $this->line("   ✓ {$deletedCount} sitemap(s) supprimé(s)");
            }
            
            // 6. Vider tous les caches
            $this->info('🧹 Vidage des caches...');
            Setting::clearCache();
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('view:clear');
            
            // 7. Régénérer les sitemaps avec la bonne URL
            $this->info('📝 Régénération des sitemaps...');
            $this->call('sitemap:reset', ['--force' => true]);
            
            $this->newLine();
            $this->info("✅ Nettoyage terminé ! {$changes} modification(s) effectuée(s)");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors du nettoyage: ' . $e->getMessage());
            Log::error('Erreur nettoyage sausercouverture: ' . $e->getMessage());
            return 1;
        }
    }
}

