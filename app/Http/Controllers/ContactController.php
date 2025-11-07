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
    
    /**
     * Envoyer un message de contact
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // Envoyer l'email
            $companyEmail = Setting::get('company_email');
            $companyName = Setting::get('company_name', 'Votre Entreprise');
            
            if ($companyEmail) {
                Mail::send([], [], function($message) use ($validated, $companyEmail, $companyName) {
                    $message->to($companyEmail)
                            ->subject('Nouveau message de contact : ' . $validated['subject'])
                            ->from($validated['email'], $validated['name'])
                            ->replyTo($validated['email'], $validated['name'])
                            ->html("
                                <h2>Nouveau message de contact</h2>
                                <p><strong>Nom :</strong> {$validated['name']}</p>
                                <p><strong>Email :</strong> {$validated['email']}</p>
                                " . ($validated['phone'] ? "<p><strong>Téléphone :</strong> {$validated['phone']}</p>" : "") . "
                                <p><strong>Sujet :</strong> {$validated['subject']}</p>
                                <p><strong>Message :</strong></p>
                                <p>" . nl2br(e($validated['message'])) . "</p>
                            ");
                });
            }
            
            return back()->with('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email contact: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer ou nous appeler directement.')->withInput();
        }
    }
}

