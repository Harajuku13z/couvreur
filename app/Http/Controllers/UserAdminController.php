<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    /**
     * Affiche la page de gestion des identifiants admin.
     */
    public function showForm()
    {
        $adminUsername = Setting::get('admin_username', 'admin');

        // Email admin affiché : notification ou email société, ou username par défaut
        $adminEmail = Setting::get('admin_notification_email', null)
            ?? Setting::get('company_email', null)
            ?? $adminUsername;

        // Par sécurité, on ne peut pas afficher le vrai mot de passe (stocké hashé)
        $passwordDisplay = 'Non affiché (mot de passe sécurisé / hashé)';

        return view('useradmin.index', [
            'adminUsername' => $adminUsername,
            'adminEmail' => $adminEmail,
            'passwordDisplay' => $passwordDisplay,
        ]);
    }

    /**
     * Met à jour l'email (username) et le mot de passe admin.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Le "username" admin peut être un email
        Setting::set('admin_username', $data['email'], 'string', 'admin');
        // Stockage hashé du mot de passe pour le login admin
        Setting::set('admin_password', bcrypt($data['password']), 'string', 'admin');

        // Email de notification admin aligné sur cet email
        Setting::set('admin_notification_email', $data['email'], 'string', 'email');

        Setting::clearCache();

        return redirect()
            ->route('useradmin.form')
            ->with('success', 'Identifiants admin mis à jour avec succès.');
    }
}

