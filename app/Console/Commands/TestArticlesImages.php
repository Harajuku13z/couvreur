<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestArticlesImages extends Command
{
    protected $signature = 'test:articles-images';
    protected $description = 'Test des images d\'articles';

    public function handle()
    {
        $this->info('🔍 Test des images d\'articles...');

        // Vérifier si le lien symbolique existe
        $storageLink = public_path('storage');
        if (is_link($storageLink)) {
            $this->info('✅ Lien symbolique public/storage existe');
            $this->info('📁 Pointe vers: ' . readlink($storageLink));
        } else {
            $this->error('❌ Lien symbolique public/storage manquant');
        }

        // Vérifier le dossier storage/app/public/articles
        $articlesDir = storage_path('app/public/articles');
        if (is_dir($articlesDir)) {
            $this->info('✅ Dossier storage/app/public/articles existe');
            $files = scandir($articlesDir);
            $imageFiles = array_filter($files, function($file) {
                return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            });
            
            if (count($imageFiles) > 0) {
                $this->info('📸 Images trouvées: ' . count($imageFiles));
                foreach (array_slice($imageFiles, 0, 3) as $file) {
                    $this->info('  - ' . $file);
                }
            } else {
                $this->error('❌ Aucune image trouvée dans storage/app/public/articles');
            }
        } else {
            $this->error('❌ Dossier storage/app/public/articles n\'existe pas');
        }

        // Vérifier les permissions
        $storagePath = storage_path('app/public');
        if (is_dir($storagePath)) {
            $perms = substr(sprintf('%o', fileperms($storagePath)), -4);
            $this->info('🔐 Permissions storage/app/public: ' . $perms);
        }

        $publicStoragePath = public_path('storage');
        if (is_dir($publicStoragePath)) {
            $perms = substr(sprintf('%o', fileperms($publicStoragePath)), -4);
            $this->info('🔐 Permissions public/storage: ' . $perms);
        }

        // Test d'URL
        $this->info('');
        $this->info('🌐 Test d\'URLs:');
        $testImage = 'articles/test.jpg';
        $url1 = url('storage/' . $testImage);
        $url2 = request()->getSchemeAndHttpHost() . '/storage/' . $testImage;

        $this->info('URL avec url(): ' . $url1);
        $this->info('URL manuelle: ' . $url2);

        $this->info('');
        $this->info('✅ Test terminé');
    }
}