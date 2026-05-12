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
        {--skip-backup-env : Ne pas creer de sauvegarde du .env}
        {--allow-dirty : Autoriser des modifications locales git suivies}
        {--no-maintenance : Ne pas activer le mode maintenance}';

    protected $description = 'Deploie le site courant sans toucher aux images ni au .env existant';

    public function handle(): int
    {
        $branch = (string) $this->option('branch');
        $maintenanceEnabled = !$this->option('no-maintenance');
        $maintenanceActivated = false;
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

            if (!$this->option('skip-git')) {
                $this->guardAgainstTrackedGitChanges();
            }

            if ($maintenanceEnabled) {
                $this->line('Activation du mode maintenance...');
                Artisan::call('down', ['--render' => 'errors::503']);
                $maintenanceActivated = true;
            }

            if (!$this->option('skip-git')) {
                $this->runProcess(['git', 'fetch', 'origin', $branch], 'Recuperation des changements Git');
                $this->runProcess(['git', 'checkout', $branch], 'Bascule sur la branche ' . $branch);
                $this->runProcess(['git', 'pull', '--ff-only', 'origin', $branch], 'Mise a jour du code');
                $releaseContext['commit'] = $this->captureProcess(['git', 'rev-parse', '--short', 'HEAD']);
            } else {
                $this->comment('Etape Git ignoree.');
            }

            $this->updateReleaseMetadata($releaseContext);

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
            $this->line('Les fichiers de medias dans public/uploads n\'ont pas ete modifies.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
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
            return;
        }

        $this->warn('Dossier public/uploads absent. Aucun media local a proteger dans ce chemin.');
    }

    private function guardAgainstTrackedGitChanges(): void
    {
        $output = $this->captureProcess(['git', 'status', '--porcelain']);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $output) ?: [])));

        $trackedChanges = array_values(array_filter($lines, fn (string $line) => !str_starts_with($line, '?? ')));

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
        $this->line('Variables de release mises a jour dans le .env');
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
