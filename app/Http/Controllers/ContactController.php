<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Afficher la page de contact avec FAQ
     */
    public function index()
    {
        // Récupérer les informations de l'entreprise
        $companySettings = [
            'name' => Setting::get('company_name', 'Votre Entreprise'),
            'phone' => Setting::get('company_phone', ''),
            'phone_raw' => Setting::get('company_phone_raw', ''),
            'email' => Setting::get('company_email', ''),
            'address' => Setting::get('company_address', ''),
            'city' => Setting::get('company_city', ''),
            'postal_code' => Setting::get('company_postal_code', ''),
            'country' => Setting::get('company_country', 'France'),
        ];
        
        // Récupérer les FAQ
        $faqsData = Setting::get('faqs', '[]');
        $faqs = is_string($faqsData) ? json_decode($faqsData, true) : ($faqsData ?? []);
        if (!is_array($faqs)) {
            $faqs = [];
        }
        
        // Breadcrumbs
        $breadcrumbs = [
            ['name' => 'Accueil', 'url' => route('home')],
            ['name' => 'Contact', 'url' => route('contact')]
        ];
        
        // SEO
        $pageTitle = 'Contact - ' . $companySettings['name'];
        $pageDescription = 'Contactez-nous pour vos projets de rénovation. Devis gratuit, intervention rapide.';
        $currentPage = 'contact';
        
        return view('contact.index', compact(
            'companySettings',
            'faqs',
            'breadcrumbs',
            'pageTitle',
            'pageDescription',
            'currentPage'
        ));
    }
}

