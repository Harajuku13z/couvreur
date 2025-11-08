<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class LegalAdminController extends Controller
{
    /**
     * Afficher la page de configuration des informations légales
     */
    public function index()
    {
        $legalData = [
            'company_name' => setting('company_name', ''),
            'company_address' => setting('company_address', ''),
            'company_phone' => setting('company_phone', ''),
            'company_email' => setting('company_email', ''),
            'company_siret' => setting('company_siret', ''),
            'company_rcs' => setting('company_rcs', ''),
            'company_capital' => setting('company_capital', ''),
            'company_tva' => setting('company_tva', ''),
            'company_director' => setting('company_director', ''),
            'hosting_provider' => setting('hosting_provider', ''),
            'company_description' => setting('company_description', ''),
            'payment_terms' => setting('payment_terms', ''),
            'late_payment_penalties' => setting('late_payment_penalties', ''),
            'company_rib' => setting('company_rib', ''),
        ];
        
        return view('admin.legal-config', compact('legalData'));
    }
    
    /**
     * Mettre à jour les informations légales
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'company_phone' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'company_siret' => 'nullable|string|max:20',
            'company_rcs' => 'nullable|string|max:50',
            'company_capital' => 'nullable|string|max:50',
            'company_tva' => 'nullable|string|max:20',
            'company_director' => 'nullable|string|max:255',
            'hosting_provider' => 'nullable|string|max:255',
            'company_description' => 'nullable|string|max:1000',
            'payment_terms' => 'nullable|string|max:2000',
            'late_payment_penalties' => 'nullable|string|max:1000',
            'company_rib' => 'nullable|string|max:100',
        ]);
        
        // Sauvegarder toutes les informations
        Setting::set('company_name', $request->company_name, 'string', 'company');
        Setting::set('company_address', $request->company_address, 'string', 'company');
        Setting::set('company_phone', $request->company_phone, 'string', 'company');
        Setting::set('company_email', $request->company_email, 'string', 'company');
        Setting::set('company_siret', $request->company_siret, 'string', 'company');
        Setting::set('company_rcs', $request->company_rcs, 'string', 'company');
        Setting::set('company_capital', $request->company_capital, 'string', 'company');
        Setting::set('company_tva', $request->company_tva, 'string', 'company');
        Setting::set('company_director', $request->company_director, 'string', 'company');
        Setting::set('hosting_provider', $request->hosting_provider, 'string', 'company');
        Setting::set('company_description', $request->company_description, 'string', 'company');
        Setting::set('payment_terms', $request->payment_terms, 'string', 'company');
        Setting::set('late_payment_penalties', $request->late_payment_penalties, 'string', 'company');
        Setting::set('company_rib', $request->company_rib ?? '', 'string', 'company');
        
        return redirect()->route('admin.legal.config')->with('success', 'Informations légales mises à jour avec succès !');
    }
}

