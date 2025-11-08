<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Listeners\UpdateSitemapListener;
use App\Events\AdCreated;
use App\Events\AdUpdated;
use App\Events\ArticleCreated;
use App\Events\ServiceUpdated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Vérifier si la table sessions existe, sinon utiliser les sessions fichiers
        try {
            if (config('session.driver') === 'database') {
                $connection = DB::connection(config('session.connection'));
                $table = config('session.table', 'sessions');
                
                // Vérifier si la table existe
                $tables = $connection->select("SHOW TABLES LIKE '{$table}'");
                if (empty($tables)) {
                    Log::warning('Table sessions n\'existe pas, basculement vers sessions fichiers');
                    config(['session.driver' => 'file']);
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur de connexion DB, utiliser les sessions fichiers
            Log::warning('Erreur lors de la vérification de la table sessions, basculement vers sessions fichiers: ' . $e->getMessage());
            config(['session.driver' => 'file']);
        }
        
        // Enregistrer les événements pour la mise à jour automatique du sitemap
        Event::listen(AdCreated::class, UpdateSitemapListener::class);
        Event::listen(AdUpdated::class, UpdateSitemapListener::class);
        Event::listen(ArticleCreated::class, UpdateSitemapListener::class);
        Event::listen(ServiceUpdated::class, UpdateSitemapListener::class);
        
        // S'assurer que MySQL est toujours utilisé comme connexion par défaut
        // Note: On vérifie seulement la configuration, pas la connexion active
        // pour éviter les erreurs si la DB n'est pas encore disponible
        try {
            $defaultConnection = config('database.default');
            
            if ($defaultConnection !== 'mysql') {
                Log::warning("La connexion par défaut n'est pas MySQL (driver: {$defaultConnection}), forçage vers MySQL...");
                config(['database.default' => 'mysql']);
                DB::purge();
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la configuration MySQL: ' . $e->getMessage());
        }

        // Configurer dynamiquement le mailer SMTP depuis les settings
        try {
            if (class_exists('\App\Models\Setting')) {
                $mailHost = \App\Models\Setting::get('mail_host');
                $mailPort = \App\Models\Setting::get('mail_port', 587);
                $mailEncryption = \App\Models\Setting::get('mail_encryption', 'tls');
                $mailUsername = \App\Models\Setting::get('mail_username');
                $mailPassword = \App\Models\Setting::get('mail_password');
                $mailFromAddress = \App\Models\Setting::get('mail_from_address');
                $mailFromName = \App\Models\Setting::get('mail_from_name');
                $emailEnabled = \App\Models\Setting::get('email_enabled', false);

                // Si la configuration email existe et est activée, utiliser SMTP
                if ($emailEnabled && $mailHost && $mailUsername && $mailPassword) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $mailHost,
                        'mail.mailers.smtp.port' => $mailPort,
                        'mail.mailers.smtp.encryption' => $mailEncryption,
                        'mail.mailers.smtp.username' => $mailUsername,
                        'mail.mailers.smtp.password' => $mailPassword,
                        'mail.from.address' => $mailFromAddress ?: env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                        'mail.from.name' => $mailFromName ?: env('MAIL_FROM_NAME', 'Example'),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la configuration email: ' . $e->getMessage());
        }
    }
}
