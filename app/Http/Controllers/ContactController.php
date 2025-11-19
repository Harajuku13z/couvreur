<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Mail\ContactConfirmation;
use App\Mail\ContactNotification;

class ContactController extends Controller
{
    /**
     * Afficher la page de contact avec FAQ
     */
    public function index()
    {
        // Vérifier blocage géographique si activé
        $blockNonFrance = Setting::get('block_non_france', false);
        
        if ($blockNonFrance) {
            // Géolocalisation
            $ipAddress = request()->ip();
            $geoService = new \App\Services\IpGeolocationService();
            $location = $geoService->getLocationFromIp($ipAddress);
            
            // Pays et territoires autorisés : France + Suisse + DOM-TOM
            $allowedCountries = [
                'FR', 'France',
                'CH', 'Switzerland', 'Suisse',
                'RE', 'Réunion', 'Reunion',
                'GP', 'Guadeloupe',
                'MQ', 'Martinique',
                'GF', 'Guyane', 'French Guiana',
                'YT', 'Mayotte',
                'NC', 'Nouvelle-Calédonie', 'New Caledonia',
                'PF', 'Polynésie française', 'French Polynesia',
                'PM', 'Saint-Pierre-et-Miquelon',
                'BL', 'Saint-Barthélemy',
                'MF', 'Saint-Martin',
                'WF', 'Wallis-et-Futuna'
            ];
            
            $countryCode = strtoupper($location['country_code'] ?? '');
            $countryName = $location['country'] ?? '';
            
            $isAllowed = in_array($countryCode, $allowedCountries) || 
                         in_array($countryName, $allowedCountries);
            
            if (!empty($countryCode) && !$isAllowed) {
                return view('form.blocked', [
                    'country' => $countryName ?: 'votre pays',
                    'countryCode' => $countryCode,
                    'ipAddress' => $ipAddress,
                    'allowedRegions' => 'France métropolitaine, Suisse et DOM-TOM',
                    'isContactForm' => true
                ]);
            }
        }
        
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
            'name' => 'required|string|min:6|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:6|max:20',
            'postal_code' => 'required|string|min:5|max:10',
            'city' => 'required|string|min:6|max:100',
            'callback_time' => 'required|string|max:50',
            'service_interest' => 'required|string|max:255',
            'subject' => 'required|string|min:6|max:255',
            'message' => 'required|string|min:6|max:2000',
            'recaptcha_token' => 'nullable|string',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins 6 caractères.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'phone.min' => 'Le téléphone doit contenir au moins 6 caractères.',
            'postal_code.required' => 'Le code postal est obligatoire.',
            'postal_code.min' => 'Le code postal doit contenir au moins 5 caractères.',
            'city.required' => 'La ville est obligatoire.',
            'city.min' => 'La ville doit contenir au moins 6 caractères.',
            'callback_time.required' => 'Veuillez sélectionner un créneau pour vous rappeler.',
            'service_interest.required' => 'Veuillez sélectionner un service.',
            'subject.required' => 'Le sujet est obligatoire.',
            'subject.min' => 'Le sujet doit contenir au moins 6 caractères.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 6 caractères.',
        ]);

        // Vérifier reCAPTCHA si activé (mais ne pas bloquer si le token est vide - peut être désactivé)
        if (setting('recaptcha_site_key') && setting('recaptcha_secret_key')) {
            $recaptchaToken = $request->input('recaptcha_token');
            if (!empty($recaptchaToken)) {
                $recaptchaSecret = setting('recaptcha_secret_key');
                $recaptchaResponse = @file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecret}&response={$recaptchaToken}");
                $recaptchaData = json_decode($recaptchaResponse, true);
                
                if (!isset($recaptchaData['success']) || !$recaptchaData['success']) {
                    \Log::warning('reCAPTCHA failed', ['response' => $recaptchaData]);
                    // Ne pas bloquer si le score est faible mais > 0.3 (plus permissif)
                    if (isset($recaptchaData['score']) && $recaptchaData['score'] < 0.3) {
                        return back()->with('error', 'Vérification anti-robot échouée. Veuillez réessayer.')->withInput();
                    }
                }
            }
        }

        try {
            $companyEmail = Setting::get('company_email');
            $companyName = Setting::get('company_name', 'Votre Entreprise');
            
            // Préparer les données pour les emails
            $callbackTimeLabels = [
                'matin' => 'Matin (9h - 12h)',
                'apres-midi' => 'Après-midi (14h - 17h)',
                'soir' => 'Soir (17h - 19h)',
                'flexible' => 'Flexible'
            ];
            
            $callbackTimeText = isset($validated['callback_time']) && isset($callbackTimeLabels[$validated['callback_time']]) 
                ? $callbackTimeLabels[$validated['callback_time']] 
                : ($validated['callback_time'] ?? 'Non spécifié');
            
            $emailData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? '',
                'postal_code' => $validated['postal_code'] ?? '',
                'city' => $validated['city'] ?? '',
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'callback_time' => $callbackTimeText,
                'service_interest' => $validated['service_interest'] ?? ''
            ];
            
            // Créer un lead/submission avec status "COMPLETED"
            try {
                // Extraire prénom et nom depuis le nom complet
                $nameParts = explode(' ', $validated['name'], 2);
                $firstName = $nameParts[0] ?? $validated['name'];
                $lastName = $nameParts[1] ?? '';
                
                // Préparer les données de soumission
                $submissionData = [
                    'session_id' => session()->getId(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'property_type' => null,
                    'surface' => null,
                    'postal_code' => $validated['postal_code'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'status' => 'COMPLETED',
                    'completed_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'referrer_url' => $request->header('referer'),
                ];
                
                // Ajouter form_data seulement si la colonne existe
                try {
                    // Vérifier si la colonne form_data existe
                    $columns = Schema::getColumnListing('submissions');
                    if (in_array('form_data', $columns)) {
                        $submissionData['form_data'] = [
                            'subject' => $validated['subject'],
                            'message' => $validated['message'],
                            'callback_time' => $validated['callback_time'] ?? null,
                            'service_interest' => $validated['service_interest'] ?? null,
                            'postal_code' => $validated['postal_code'] ?? null,
                            'city' => $validated['city'] ?? null,
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::warning('Impossible de vérifier la colonne form_data: ' . $e->getMessage());
                }
                
                \Log::info('Création d\'une soumission depuis le formulaire de contact', [
                    'email' => $validated['email'],
                    'name' => $validated['name']
                ]);
                
                $submission = Submission::create($submissionData);
                
                \Log::info('Soumission créée avec succès', [
                    'submission_id' => $submission->id,
                    'email' => $submission->email
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la création du submission depuis le formulaire de contact', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'email' => $validated['email'] ?? 'N/A',
                    'name' => $validated['name'] ?? 'N/A'
                ]);
            }
            
            // Envoyer l'email de confirmation à l'utilisateur
            try {
                Mail::to($validated['email'])->send(new ContactConfirmation($emailData));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email confirmation: ' . $e->getMessage());
            }
            
            // Envoyer l'email de notification à l'admin
            if ($companyEmail) {
                try {
                    Mail::to($companyEmail)->send(new ContactNotification($emailData));
                } catch (\Exception $e) {
                    \Log::error('Erreur envoi email notification admin: ' . $e->getMessage());
                }
            }
            
            return redirect()->route('contact.success')->with('contact_data', $emailData);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email contact: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer ou nous appeler directement.')->withInput();
        }
    }
    
    /**
     * Afficher la page de succès après envoi du formulaire
     */
    public function success()
    {
        $contactData = session('contact_data', []);
        
        if (empty($contactData)) {
            return redirect()->route('contact');
        }
        
        $companySettings = [
            'name' => Setting::get('company_name', 'Votre Entreprise'),
            'phone' => Setting::get('company_phone', ''),
            'phone_raw' => Setting::get('company_phone_raw', ''),
            'email' => Setting::get('company_email', ''),
        ];
        
        return view('contact.success', compact('contactData', 'companySettings'));
    }
}

