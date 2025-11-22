<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class ResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset-password 
                            {--username=admin : Nom d\'utilisateur admin}
                            {--password= : Nouveau mot de passe (optionnel, généré si vide)}
                            {--show : Afficher le mot de passe après création}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réinitialiser le mot de passe administrateur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->option('username');
        
        // Générer un mot de passe si non fourni
        if (!$this->option('password')) {
            $password = $this->generateSecurePassword();
            $this->info('🔑 Mot de passe généré automatiquement');
        } else {
            $password = $this->option('password');
        }
        
        // Sauvegarder dans Settings
        Setting::set('admin_username', $username);
        Setting::set('admin_password', $password);
        
        $this->info('✅ Mot de passe admin réinitialisé avec succès !');
        $this->newLine();
        
        $this->line('📋 Identifiants :');
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['Nom d\'utilisateur', $username],
                ['Mot de passe', $this->option('show') ? $password : str_repeat('*', strlen($password))],
            ]
        );
        
        if (!$this->option('show')) {
            $this->warn('💡 Pour voir le mot de passe, utilisez : --show');
        }
        
        $this->newLine();
        $this->info('🔗 URL de connexion : /admin/login');
        $this->info('📝 Utilisez ces identifiants pour vous connecter.');
        
        return 0;
    }
    
    /**
     * Générer un mot de passe sécurisé
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $max = strlen($characters) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }
        
        return $password;
    }
}


