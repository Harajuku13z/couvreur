<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class Deploy extends Command
{
    protected $signature = 'deploy
        {--branch=main : Branche Git a deployer}
        {--release-name= : Valeur de APP_RELEASE_NAME a enregistrer}
        {--release-version= : Valeur de APP_VERSION a enregistrer}
        {--release-date= : Valeur de APP_RELEASE_DATE a enregistrer}
        {--skip-git : Ne pas executer les commandes git}
        {--skip-composer : Ne pas executer composer install}
        {--skip-migrate : Ne pas executer les migrations}
        {--skip-sitemap : Ne pas regenerer les sitemaps}
        {--skip-domain-sync : Ne pas corriger automatiquement APP_URL/site_url depuis le domaine Hostinger}
        {--skip-backup-env : Ne pas creer de sauvegarde du .env}
        {--allow-dirty : Autoriser des modifications locales git suivies}
        {--no-maintenance : Ne pas activer le mode maintenance}';

    protected $description = 'Deploie le site courant sans toucher aux images ni au .env existant';

    public function handle(): int
    {
        $branch = (string) $this->option('branch');
        $maintenanceEnabled = !$this->option('no-maintenance');
        $maintenanceActivated = false;
        $skipGit = (bool) $this->option('skip-git');
        $siteAssetsBackupPath = null;
        $releaseContext = [
            'branch' => $branch,
            'commit' => null,
            'date' => now()->format('Y-m-d H:i:s'),
        ];

        $this->components->info('Demarrage du deploy pour le site courant');
        $this->line('Racine projet: ' . base_path());
        $this->line('Branche cible: ' . $branch);
        $this->newLine();

        try {
            if (!$this->option('skip-backup-env')) {
                $this->backupEnvFile();
            }

            $this->reportUploadsDirectory();

            if (!$skipGit) {
                $skipGit = $this->shouldSkipGitAutomatically($branch);
            }

            if ($maintenanceEnabled) {
                $this->line('Activation du mode maintenance...');
                Artisan::call('down', ['--render' => 'errors::503']);
                $maintenanceActivated = true;
            }

            if (!$skipGit) {
                $siteAssetsBackupPath = $this->backupSiteAssets();
                $this->runProcess(['git', 'fetch', 'origin', $branch], 'Recuperation des changements Git');
                $this->runProcess(['git', 'checkout', $branch], 'Bascule sur la branche ' . $branch);
                $this->runProcess(['git', 'pull', '--ff-only', 'origin', $branch], 'Mise a jour du code');
                $this->restoreSiteAssets($siteAssetsBackupPath);
                $siteAssetsBackupPath = null;
                $releaseContext['commit'] = $this->captureProcess(['git', 'rev-parse', '--short', 'HEAD']);
            } else {
                $this->comment('Etape Git ignoree.');
                $releaseContext['commit'] = $this->captureProcess(['git', 'rev-parse', '--short', 'HEAD']);
            }

            $this->updateReleaseMetadata($releaseContext);
            $this->syncCurrentDomainConfiguration();

            if (!$this->option('skip-composer')) {
                $this->runProcess(
                    ['composer', 'install', '--no-dev', '--optimize-autoloader'],
                    'Installation/optimisation des dependances'
                );
            } else {
                $this->comment('Etape Composer ignoree.');
            }

            if (!$this->option('skip-migrate')) {
                $this->line('Execution des migrations...');
                $this->call('migrate', ['--force' => true]);
            } else {
                $this->comment('Etape migration ignoree.');
            }

            $this->repairPublicStorageLink();

            $this->line('Nettoyage des caches...');
            $this->callSilent('config:clear');
            $this->callSilent('cache:clear');
            $this->callSilent('route:clear');
            $this->callSilent('view:clear');

            $this->line('Reconstruction des caches...');
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:cache');

            if (!$this->option('skip-sitemap')) {
                $this->line('Regeneration des sitemaps...');
                $this->call('sitemap:generate-daily');
            } else {
                $this->comment('Etape sitemap ignoree.');
            }

            if ($maintenanceActivated) {
                $this->line('Desactivation du mode maintenance...');
                $this->callSilent('up');
                $maintenanceActivated = false;
            }

            $this->newLine();
            $this->components->info('Deploy termine avec succes.');
            $this->line('Les fichiers de medias locaux proteges ont ete preserves.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if ($siteAssetsBackupPath !== null) {
                try {
                    $this->restoreSiteAssets($siteAssetsBackupPath);
                } catch (\Throwable $restoreException) {
                    $this->warn('Restauration des assets locaux impossible: ' . $restoreException->getMessage());
                }
            }

            if ($maintenanceActivated) {
                $this->callSilent('up');
            }

            $this->newLine();
            $this->components->error('Deploy interrompu: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function backupEnvFile(): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->warn('Aucun fichier .env trouve, sauvegarde ignoree.');
            return;
        }

        $backupPath = base_path('.env.backup.' . now()->format('Ymd-His'));

        if (!copy($envPath, $backupPath)) {
            throw new \RuntimeException('Impossible de sauvegarder le fichier .env');
        }

        $this->line('Sauvegarde .env creee: ' . basename($backupPath));
    }

    private function reportUploadsDirectory(): void
    {
        $uploadsPath = public_path('uploads');

        if (is_dir($uploadsPath)) {
            $this->line('Dossier images detecte: public/uploads');
            $this->line('Assets locaux proteges: ' . implode(', ', $this->siteAssetPaths()));
            return;
        }

        $this->warn('Dossier public/uploads absent. Aucun media local a proteger dans ce chemin.');
    }

    private function siteAssetPaths(): array
    {
        return [
            'public/logo',
            'public/logoTop.png',
            'public/favicon.ico',
            'public/favicons',
            'public/images',
            'public/uploads',
        ];
    }

    private function backupSiteAssets(): ?string
    {
        $paths = array_values(array_filter($this->siteAssetPaths(), function (string $relativePath) {
            return file_exists(base_path($relativePath));
        }));

        if (empty($paths)) {
            $this->warn('Aucun asset local a sauvegarder avant Git.');
            return null;
        }

        $backupPath = storage_path('app/deploy-asset-backups/' . now()->format('Ymd-His'));

        if (! is_dir($backupPath) && ! mkdir($backupPath, 0755, true) && ! is_dir($backupPath)) {
            throw new \RuntimeException('Impossible de creer le dossier de sauvegarde des assets locaux');
        }

        foreach ($paths as $relativePath) {
            $this->copyPath(base_path($relativePath), $backupPath . '/' . $relativePath);
        }

        $this->line('Sauvegarde assets locaux creee: storage/app/deploy-asset-backups/' . basename($backupPath));

        return $backupPath;
    }

    private function restoreSiteAssets(?string $backupPath): void
    {
        if ($backupPath === null || ! is_dir($backupPath)) {
            return;
        }

        foreach ($this->siteAssetPaths() as $relativePath) {
            $source = $backupPath . '/' . $relativePath;

            if (! file_exists($source)) {
                continue;
            }

            $this->removePath(base_path($relativePath));
            $this->copyPath($source, base_path($relativePath));
        }

        $this->line('Assets locaux restaures apres Git.');
    }

    private function copyPath(string $source, string $destination): void
    {
        if (is_dir($source)) {
            if (! is_dir($destination) && ! mkdir($destination, 0755, true) && ! is_dir($destination)) {
                throw new \RuntimeException('Impossible de creer le dossier: ' . $destination);
            }

            $items = scandir($source);

            if ($items === false) {
                throw new \RuntimeException('Impossible de lire le dossier: ' . $source);
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $this->copyPath($source . '/' . $item, $destination . '/' . $item);
            }

            return;
        }

        $destinationDirectory = dirname($destination);

        if (! is_dir($destinationDirectory) && ! mkdir($destinationDirectory, 0755, true) && ! is_dir($destinationDirectory)) {
            throw new \RuntimeException('Impossible de creer le dossier: ' . $destinationDirectory);
        }

        if (! copy($source, $destination)) {
            throw new \RuntimeException('Impossible de copier: ' . $source);
        }
    }

    private function removePath(string $path): void
    {
        if (! file_exists($path) && ! is_link($path)) {
            return;
        }

        if (is_dir($path) && ! is_link($path)) {
            $items = scandir($path);

            if ($items === false) {
                throw new \RuntimeException('Impossible de lire le dossier: ' . $path);
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $this->removePath($path . '/' . $item);
            }

            if (! rmdir($path)) {
                throw new \RuntimeException('Impossible de supprimer le dossier: ' . $path);
            }

            return;
        }

        if (! unlink($path)) {
            throw new \RuntimeException('Impossible de supprimer le fichier: ' . $path);
        }
    }

    private function repairPublicStorageLink(): void
    {
        $linkPath = public_path('storage');
        $targetPath = storage_path('app/public');

        if (! is_dir($targetPath) && ! mkdir($targetPath, 0755, true) && ! is_dir($targetPath)) {
            throw new \RuntimeException('Impossible de creer storage/app/public');
        }

        if (is_link($linkPath)) {
            $currentTarget = readlink($linkPath);

            if ($currentTarget === $targetPath) {
                $this->line('Lien public/storage valide.');
                return;
            }

            if (! unlink($linkPath)) {
                throw new \RuntimeException('Impossible de remplacer le lien public/storage');
            }
        } elseif (file_exists($linkPath)) {
            if (is_dir($linkPath) && count(array_diff(scandir($linkPath) ?: [], ['.', '..'])) === 0) {
                if (! rmdir($linkPath)) {
                    throw new \RuntimeException('Impossible de remplacer le dossier public/storage vide');
                }
            } else {
                $this->warn('public/storage existe deja et n est pas un lien vide. Verification manuelle recommandee.');
                return;
            }
        }

        if (! symlink($targetPath, $linkPath)) {
            throw new \RuntimeException('Impossible de creer le lien public/storage');
        }

        $this->line('Lien public/storage repare.');
    }

    private function guardAgainstTrackedGitChanges(): void
    {
        $trackedChanges = $this->trackedGitChanges();

        if ($this->canAutoCleanVendorChanges($trackedChanges)) {
            $this->warn('Des modifications locales ont ete detectees uniquement dans vendor/. Nettoyage automatique en cours...');
            $this->runProcess(['git', 'restore', '--worktree', '--staged', 'vendor'], 'Nettoyage automatique de vendor');
            $trackedChanges = $this->trackedGitChanges();
        }

        if (empty($trackedChanges) || $this->option('allow-dirty')) {
            if (!empty($trackedChanges)) {
                $this->warn('Des modifications git suivies existent, mais --allow-dirty autorise le deploy.');
            }

            return;
        }

        throw new \RuntimeException(
            "Le depot contient des modifications locales suivies. Lancez d'abord 'git status'. ".
            "Utilisez --allow-dirty uniquement si vous savez exactement ce que vous faites."
        );
    }

    private function shouldSkipGitAutomatically(string $branch): bool
    {
        $trackedChanges = $this->trackedGitChanges();

        if (empty($trackedChanges)) {
            return false;
        }

        if ($this->option('allow-dirty')) {
            $this->warn('Des modifications git suivies existent, mais --allow-dirty autorise le deploy.');
            return false;
        }

        if ($this->canAutoCleanVendorChanges($trackedChanges)) {
            $this->warn('Des modifications locales ont ete detectees uniquement dans vendor/. Nettoyage automatique en cours...');
            $this->runProcess(['git', 'restore', '--worktree', '--staged', 'vendor'], 'Nettoyage automatique de vendor');
            return false;
        }

        $this->runProcess(['git', 'fetch', 'origin', $branch], 'Verification de l etat distant');

        $localHead = $this->captureProcess(['git', 'rev-parse', 'HEAD']);
        $remoteHead = $this->captureProcess(['git', 'rev-parse', 'origin/' . $branch]);

        if ($localHead === $remoteHead) {
            $this->warn(
                "Le depot contient des modifications locales suivies, mais le site est deja a jour sur origin/{$branch}. ".
                'Le deploy continue sans etape Git.'
            );
            return true;
        }

        $this->guardAgainstTrackedGitChanges();

        return false;
    }

    private function trackedGitChanges(): array
    {
        $output = $this->captureProcess(['git', 'status', '--porcelain']);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $output) ?: [])));

        return array_values(array_filter($lines, function (string $line) {
            if (str_starts_with($line, '?? ')) {
                return false;
            }

            $path = $this->extractPathFromGitStatusLine($line);

            return $path !== 'public/robots.txt';
        }));
    }

    private function canAutoCleanVendorChanges(array $trackedChanges): bool
    {
        if (empty($trackedChanges)) {
            return false;
        }

        foreach ($trackedChanges as $line) {
            $path = $this->extractPathFromGitStatusLine($line);

            if ($path === null || !str_starts_with($path, 'vendor/')) {
                return false;
            }
        }

        return true;
    }

    private function extractPathFromGitStatusLine(string $line): ?string
    {
        $path = trim(substr($line, 3));

        if ($path === '') {
            return null;
        }

        if (str_contains($path, ' -> ')) {
            $parts = explode(' -> ', $path);
            $path = trim((string) end($parts));
        }

        return $path !== '' ? $path : null;
    }

    private function updateReleaseMetadata(array $releaseContext): void
    {
        $defaultReleaseName = 'Deploy ' . strtoupper((string) ($releaseContext['branch'] ?? 'main'));
        $defaultReleaseVersion = trim((string) ($releaseContext['branch'] ?? 'main')) .
            (($releaseContext['commit'] ?? null) ? '-' . $releaseContext['commit'] : '');
        $defaultReleaseDate = (string) ($releaseContext['date'] ?? now()->format('Y-m-d H:i:s'));

        $updates = array_filter([
            'APP_RELEASE_NAME' => $this->option('release-name') ?: $defaultReleaseName,
            'APP_VERSION' => $this->option('release-version') ?: $defaultReleaseVersion,
            'APP_RELEASE_DATE' => $this->option('release-date') ?: $defaultReleaseDate,
        ], fn ($value) => is_string($value) && trim($value) !== '');

        $this->writeEnvValues($updates);

        if (! empty($updates)) {
            $this->line('Variables de release mises a jour dans le .env');
        }
    }

    private function syncCurrentDomainConfiguration(): void
    {
        if ($this->option('skip-domain-sync')) {
            $this->comment('Synchronisation domaine ignoree.');
            return;
        }

        $siteUrl = $this->inferCurrentSiteUrl();

        if ($siteUrl === null) {
            $this->warn('Domaine courant non detecte depuis le chemin du projet. APP_URL/site_url non modifies.');
            return;
        }

        $oldUrls = array_filter(array_unique([
            $this->readEnvValue('APP_URL'),
            (string) config('app.url'),
            $this->readSettingValue('site_url'),
        ]));

        $this->writeEnvValues(['APP_URL' => $siteUrl]);
        config(['app.url' => $siteUrl]);
        $this->line('APP_URL synchronise: ' . $siteUrl);

        $this->syncSettingsSiteUrl($siteUrl, $oldUrls);
        $this->syncRobotsTxt($siteUrl);
    }

    private function inferCurrentSiteUrl(): ?string
    {
        $path = str_replace('\\', '/', base_path());

        if (! preg_match('#/domains/([^/]+)/public_html(?:/|$)#', $path, $matches)) {
            return null;
        }

        $domain = strtolower(trim($matches[1]));

        if ($domain === '' || ! str_contains($domain, '.')) {
            return null;
        }

        return 'https://' . $domain;
    }

    private function readEnvValue(string $key): ?string
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return null;
        }

        $content = (string) file_get_contents($envPath);

        if (! preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
            return null;
        }

        return trim(trim((string) $matches[1]), "\"'");
    }

    private function readSettingValue(string $key): ?string
    {
        try {
            if (! class_exists(\App\Models\Setting::class)) {
                return null;
            }

            $value = \App\Models\Setting::get($key, null);

            return is_string($value) ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function syncSettingsSiteUrl(string $siteUrl, array $oldUrls): void
    {
        try {
            if (! class_exists(\App\Models\Setting::class)) {
                return;
            }

            \App\Models\Setting::set('site_url', $siteUrl, 'string', 'seo');

            $oldHosts = array_values(array_filter(array_unique(array_map(function (string $url) use ($siteUrl) {
                $host = parse_url($url, PHP_URL_HOST);
                $currentHost = parse_url($siteUrl, PHP_URL_HOST);

                return $host && $host !== $currentHost ? $host : null;
            }, $oldUrls))));

            if (empty($oldHosts)) {
                $this->line('site_url synchronise en base.');
                return;
            }

            $settings = \App\Models\Setting::query()
                ->where(function ($query) use ($oldHosts) {
                    foreach ($oldHosts as $host) {
                        $query->orWhere('value', 'like', '%' . $host . '%');
                    }
                })
                ->get();

            foreach ($settings as $setting) {
                $value = (string) $setting->value;

                foreach ($oldHosts as $host) {
                    $value = str_replace('https://' . $host, $siteUrl, $value);
                    $value = str_replace('http://' . $host, $siteUrl, $value);
                    $value = str_replace('https:\\/\\/' . $host, str_replace('/', '\\/', $siteUrl), $value);
                    $value = str_replace('http:\\/\\/' . $host, str_replace('/', '\\/', $siteUrl), $value);
                }

                if ($value !== $setting->value) {
                    $setting->value = $value;
                    $setting->save();
                }
            }

            $this->line('site_url et anciennes URLs synchronises en base.');
        } catch (\Throwable $e) {
            $this->warn('Synchronisation settings domaine impossible: ' . $e->getMessage());
        }
    }

    private function syncRobotsTxt(string $siteUrl): void
    {
        $robotsPath = public_path('robots.txt');
        $updatedAt = now()->format('Y-m-d');
        $content = <<<ROBOTS
# Robots.txt SEO
# Domaine synchronise automatiquement par php artisan deploy
# Derniere mise a jour : {$updatedAt}

User-agent: *
Allow: /

# Zones privees
Disallow: /admin/
Disallow: /admin
Disallow: /config/
Disallow: /setup
Disallow: /api/
Disallow: /schedule/run
Disallow: /cron/run

# Ressources
Allow: /*.css$
Allow: /*.js$
Allow: /*.jpg$
Allow: /*.jpeg$
Allow: /*.png$
Allow: /*.webp$
Allow: /*.svg$
Allow: /*.woff$
Allow: /*.woff2$
Allow: /*.ttf$

# Fichiers sensibles
Disallow: /*.log$
Disallow: /*.sql$
Disallow: /*.env$
Disallow: /*.git
Disallow: /storage/
Disallow: /vendor/

# Sitemaps
Sitemap: {$siteUrl}/sitemap.xml

ROBOTS;

        if (file_put_contents($robotsPath, $content) === false) {
            throw new \RuntimeException('Impossible de synchroniser public/robots.txt');
        }

        $this->line('robots.txt synchronise: ' . $siteUrl . '/sitemap.xml');
    }

    private function writeEnvValues(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        $envPath = base_path('.env');
        $content = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        foreach ($updates as $key => $value) {
            $escaped = str_replace('"', '\\"', trim((string) $value));
            $line = $key . '="' . $escaped . '"';

            if (preg_match('/^' . preg_quote($key, '/') . '=.*$/m', $content)) {
                $content = preg_replace('/^' . preg_quote($key, '/') . '=.*$/m', $line, $content) ?? $content;
            } else {
                $content .= ($content === '' ? '' : PHP_EOL) . $line;
            }
        }

        file_put_contents($envPath, $content);
    }

    private function runProcess(array $command, string $label): void
    {
        $this->line($label . '...');

        $process = new Process($command, base_path(), null, null, null);
        $process->setTty(Process::isTtySupported());
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Echec de l'etape: {$label}");
        }
    }

    private function captureProcess(array $command): string
    {
        $process = new Process($command, base_path(), null, null, null);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Commande externe en echec');
        }

        return trim($process->getOutput());
    }
}
