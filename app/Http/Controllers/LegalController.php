<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class LegalController extends Controller
{
    /**
     * Page Mentions Légales
     */
    public function mentionsLegales()
    {
        $companyName = setting('company_name', 'Sausser Couverture');
        $companyAddress = setting('company_address', '');
        $companyPhone = setting('company_phone', '');
        $companyEmail = setting('company_email', '');
        $companySiret = setting('company_siret', '');
        $companyRcs = setting('company_rcs', '');
        $companyCapital = setting('company_capital', '');
        $companyTva = setting('company_tva', '');
        $hostingProvider = setting('hosting_provider', '');
        $directorName = setting('director_name', '');
        
        return view('legal.mentions-legales', compact(
            'companyName',
            'companyAddress', 
            'companyPhone',
            'companyEmail',
            'companySiret',
            'companyRcs',
            'companyCapital',
            'companyTva',
            'hostingProvider',
            'directorName'
        ));
    }
    
    /**
     * Page Politique de Confidentialité
     */
    public function politiqueConfidentialite()
    {
        $companyName = setting('company_name', 'Sausser Couverture');
        $companyEmail = setting('company_email', '');
        $companyPhone = setting('company_phone', '');
        $companyAddress = setting('company_address', '');
        
        return view('legal.politique-confidentialite', compact(
            'companyName',
            'companyEmail',
            'companyPhone',
            'companyAddress'
        ));
    }
    
    /**
     * Page CGV (Conditions Générales de Vente)
     */
    public function cgv()
    {
        $companyName = setting('company_name', 'Sausser Couverture');
        $companyEmail = setting('company_email', '');
        $companyPhone = setting('company_phone', '');
        $companyAddress = setting('company_address', '');
        $companySiret = setting('company_siret', '');
        
        // Récupérer la liste des services depuis les paramètres
        $servicesData = setting('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // Filtrer les services actifs
        $activeServices = array_filter($services, function($service) {
            return is_array($service) && ($service['is_active'] ?? true);
        });
        
        // Récupérer les modalités de paiement depuis les paramètres
        $paymentTerms = setting('payment_terms', '');
        $latePaymentPenalties = setting('late_payment_penalties', '');
        
        return view('legal.cgv', compact(
            'companyName',
            'companyEmail',
            'companyPhone',
            'companyAddress',
            'companySiret',
            'activeServices',
            'paymentTerms',
            'latePaymentPenalties'
        ));
    }
}

