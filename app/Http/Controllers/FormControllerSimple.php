<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Review;
use App\Models\Setting;
use App\Models\PhoneCall;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Mail\SubmissionReceived;
use App\Mail\SubmissionNotification;
use App\Services\IpGeolocationService;

/**
 * FormController ULTRA-SIMPLE
 * Pas de AJAX compliqué, navigation directe
 */
class FormControllerSimple extends Controller
{
    /** @var array<int,string> */
    private array $steps = [
        'propertyType',
        'surface',
        'workType',
        'roofWorkType',
        'facadeWorkType',
        'isolationWorkType',
        'ownershipStatus',
        'personalInfo',
        'postalCode',
        'phone',
        'email',
    ];

    public function index()
    {
        $sessionId = Session::getId();
        $submission = Submission::where('session_id', $sessionId)->first();
        
        // Afficher uniquement les 10 derniers avis 5 étoiles, triés par date (les plus récents d'abord)
        $reviews = Review::active()
            ->where('rating', 5)
            ->orderBy('review_date', 'desc')
            ->limit(10)
            ->get();
        
        return view('form.index', compact('submission', 'reviews'));
    }

    /**
     * Afficher tous les avis
     */
    public function allReviews()
    {
        // Tous les avis actifs, triés par note puis par date
        $reviews = Review::active()
            ->orderBy('rating', 'desc')
            ->orderBy('review_date', 'desc')
            ->paginate(20);
        
        $stats = [
            'total' => Review::active()->count(),
            'five_stars' => Review::active()->where('rating', 5)->count(),
            'four_stars' => Review::active()->where('rating', 4)->count(),
            'three_stars' => Review::active()->where('rating', 3)->count(),
            'average' => round(Review::active()->avg('rating'), 1),
        ];
        
        // Set current page for SEO
        $currentPage = 'reviews';
        
        return view('form.all-reviews', compact('reviews', 'stats', 'currentPage'));
    }

    /**
     * Afficher le formulaire de création d'avis
     */
    public function createReview()
    {
        return view('form.create-review');
    }

    /**
     * Soumettre un nouvel avis public
     */
    public function storeReview(Request $request)
    {
        try {
            // Validation avec messages personnalisés en français
                $request->validate([
                    'author_name' => 'required|string|max:255',
                    'rating' => 'required|integer|min:1|max:5',
                    'review_text' => 'required|string|min:5|max:1000',
                    'honeypot' => 'nullable|string|max:0', // Honeypot anti-spam
                    'timestamp' => 'required|integer'
                ], [
                    'author_name.required' => 'Le nom est obligatoire.',
                    'author_name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                    'rating.required' => 'La note est obligatoire.',
                    'rating.integer' => 'La note doit être un nombre entier.',
                    'rating.min' => 'La note doit être au minimum 1.',
                    'rating.max' => 'La note doit être au maximum 5.',
                    'review_text.required' => 'Le texte de l\'avis est obligatoire.',
                    'review_text.min' => 'Le texte de l\'avis doit contenir au minimum 5 caractères.',
                    'review_text.max' => 'Le texte de l\'avis ne peut pas dépasser 1000 caractères.',
                    'timestamp.required' => 'Erreur de session, veuillez réessayer.',
                    'timestamp.integer' => 'Erreur de session, veuillez réessayer.'
                ]);

            // Protection anti-spam personnalisée
            $honeypot = $request->input('honeypot');
            $timestamp = $request->input('timestamp');
            $currentTime = time();
            
            // Vérifier honeypot (doit être vide)
            if (!empty($honeypot)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Soumission détectée comme spam'
                ], 400);
            }
            
            // Vérifier timestamp (doit être récent, max 1 heure)
            if (($currentTime - $timestamp) > 3600) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expirée, veuillez réessayer'
                ], 400);
            }
            
            // Vérifier que le texte n'est pas trop répétitif (anti-spam)
            $reviewText = $request->review_text;
            $words = explode(' ', strtolower($reviewText));
            $wordCounts = array_count_values($words);
            $maxRepetition = max($wordCounts);
            
            if ($maxRepetition > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Texte détecté comme spam'
                ], 400);
            }

            // Créer l'avis
            $reviewData = [
                'author_name' => $request->author_name,
                'rating' => $request->rating,
                'review_text' => $request->review_text,
                'review_date' => now(),
                'source' => 'Site Web',
                'is_active' => false, // En attente de validation
                'is_verified' => false
            ];

            $review = Review::create($reviewData);

            // Système de photos supprimé

            return response()->json([
                'success' => true,
                'message' => 'Votre avis a été soumis avec succès ! Il sera publié après validation.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Gestion spécifique des erreurs de validation
            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Erreur de validation';
            
            return response()->json([
                'success' => false,
                'message' => $firstError
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistrer un clic sur un lien téléphone
     */
    public function trackPhoneCall(Request $request)
    {
        try {
            $sessionId = Session::getId();
            $submission = Submission::where('session_id', $sessionId)->first();

            PhoneCall::create([
                'submission_id' => $submission ? $submission->id : null,
                'session_id' => $sessionId,
                'phone_number' => $request->input('phone_number') ?? setting('company_phone_raw') ?? setting('company_phone'),
                'source_page' => $request->input('source_page') ?? request()->url(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'clicked_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Track form button clicks
     */
    public function trackFormClick(Request $request)
    {
        try {
            \Log::info('Form click tracked', [
                'source' => $request->source ?? 'unknown',
                'page' => $request->page ?? 'unknown',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Track service clicks
     */
    public function trackServiceClick(Request $request)
    {
        try {
            \Log::info('Service click tracked', [
                'service' => $request->service ?? 'unknown',
                'page' => $request->page ?? 'unknown',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function showStep(string $step)
    {
        if (!in_array($step, $this->steps, true)) {
            return redirect()->route('home');
        }

        $sessionId = Session::getId();
        $submission = Submission::where('session_id', $sessionId)->first();
        
        if (!$submission) {
            // Capturer toutes les informations de tracking dès la création
            $ipAddress = $this->getClientIp(request());
            $referrerUrl = request()->header('referer') ?? request()->input('ref') ?? null;
            $userAgent = request()->userAgent();
            
            // Géolocalisation
            $geoService = new IpGeolocationService();
            $location = $geoService->getLocationFromIp($ipAddress);
            
            $submission = Submission::create([
                'session_id' => $sessionId,
                'user_identifier' => $this->generateUserIdentifier(),
                'status' => 'IN_PROGRESS',
                'current_step' => $step,
                'ip_address' => $ipAddress,
                'city' => $location['city'],
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'referrer_url' => $referrerUrl,
                'user_agent' => $userAgent,
                'tracking_data' => [
                    'created_at' => now()->toDateTimeString(),
                    'first_visit' => true,
                ],
            ]);
        }

        // Métadonnées SEO pour la page propertyType (simulateur de devis)
        $pageTitle = null;
        $pageDescription = null;
        $pageKeywords = null;
        
        if ($step === 'propertyType') {
            $companyName = setting('company_name', 'Notre Entreprise');
            $pageTitle = 'Simulateur de devis gratuit - ' . $companyName;
            $pageDescription = 'Obtenez votre devis gratuit en quelques clics pour vos travaux de rénovation. ' . $companyName . ' vous accompagne dans tous vos projets de toiture, isolation, façade et plus encore.';
            $pageKeywords = 'devis gratuit, simulateur devis, estimation travaux, devis en ligne, rénovation, toiture, isolation, façade';
        }

        return view('form.steps.' . $step, compact('submission', 'pageTitle', 'pageDescription', 'pageKeywords'));
    }

    public function submitStep(Request $request, string $step)
    {
        $sessionId = Session::getId();
        $submission = Submission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return redirect()->route('form.step', 'propertyType');
        }

        // Vérifier reCAPTCHA pour toutes les étapes (dès la première étape)
        $recaptchaResult = $this->verifyRecaptcha($request);
        if (!$recaptchaResult['success']) {
            return back()->withErrors(['recaptcha' => 'Vérification anti-robot échouée. Veuillez réessayer.'])->withInput();
        }
        
        // Sauvegarder le score reCAPTCHA (mise à jour si meilleur score)
        $currentScore = $submission->recaptcha_score;
        $newScore = $recaptchaResult['score'] ?? null;
        if ($newScore !== null && ($currentScore === null || $newScore > $currentScore)) {
            $submission->update(['recaptcha_score' => $newScore]);
        }

        // Enregistrer les données de l'étape
        $this->saveStepData($submission, $request, $step);
        
        // Mettre à jour les données de tracking
        $trackingData = $submission->tracking_data ?? [];
        $trackingData['last_step'] = $step;
        $trackingData['last_update'] = now()->toDateTimeString();
        $trackingData['steps_completed'][] = [
            'step' => $step,
            'timestamp' => now()->toDateTimeString(),
        ];
        $submission->update(['tracking_data' => $trackingData]);

        $nextStep = $this->getNextStep($step, $request->all());

        if ($nextStep) {
            $submission->update(['current_step' => $nextStep]);
            return redirect()->route('form.step', $nextStep);
        }

            $submission->markAsCompleted();
            $this->sendEmails($submission);
            return redirect()->route('form.success');
    }

    public function previousStep(string $currentStep)
    {
        $previousStep = $this->getPreviousStep($currentStep);
        if ($previousStep) {
            return redirect()->route('form.step', $previousStep);
        }
        return redirect()->route('home');
    }

    public function success()
    {
        $sessionId = Session::getId();
        $submission = Submission::where('session_id', $sessionId)->completed()->first();
        if (!$submission) {
            return redirect()->route('home');
        }
        return view('form.success', compact('submission'));
    }

    private function saveStepData(Submission $submission, Request $request, string $step): void
    {
        switch ($step) {
            case 'propertyType':
                // Normaliser vers les valeurs attendues par la DB
                $propertyType = $this->normalizePropertyType($request->property_type);
                $submission->update(['property_type' => $propertyType]);
                break;
            case 'surface':
                $submission->update(['surface' => $request->surface]);
                break;
            case 'workType':
                $submission->update(['work_types' => $request->work_type]);
                break;
            case 'roofWorkType':
                $submission->update(['roof_work_types' => $request->roof_work_type]);
                break;
            case 'facadeWorkType':
                $submission->update(['facade_work_types' => $request->facade_work_type]);
                break;
            case 'isolationWorkType':
                $submission->update(['isolation_work_types' => $request->isolation_work_type]);
                break;
            case 'ownershipStatus':
                // Normaliser vers les valeurs attendues par la DB
                $ownershipStatus = $this->normalizeOwnershipStatus($request->ownership_status);
                $submission->update(['ownership_status' => $ownershipStatus]);
                break;
            case 'personalInfo':
                // Normaliser le genre
                $gender = $this->normalizeGender($request->gender);
                $submission->update([
                    'gender' => $gender,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                ]);
                break;
            case 'postalCode':
                $postalCode = $request->input('postal_code');
                if (empty($postalCode)) {
                    $postalCodeNumber = $request->input('postal_code_number');
                    $city = $request->input('city');
                    $postalCode = $postalCodeNumber . ', ' . $city;
                }
                $submission->update(['postal_code' => $postalCode]);
                break;
            case 'phone':
                $submission->update(['phone' => $request->phone]);
                break;
            case 'email':
                $submission->update(['email' => $request->email]);
                break;
        }
    }

    private function getNextStep(string $currentStep, array $data): ?string
    {
        $currentIndex = array_search($currentStep, $this->steps, true);
        if ($currentIndex === false) {
            return null;
        }

        // Gestion spéciale pour l'étape workType
        if ($currentStep === 'workType') {
            $workTypes = $data['work_type'] ?? [];
            
            // Retourner la première étape de travaux sélectionnée
            if (in_array('roof', $workTypes, true)) {
                return 'roofWorkType';
            }
            if (in_array('facade', $workTypes, true)) {
                return 'facadeWorkType';
            }
            if (in_array('isolation', $workTypes, true)) {
                return 'isolationWorkType';
            }
            
            // Si aucun travail sélectionné, passer à l'étape suivante
            return 'ownershipStatus';
        }

        // Gestion spéciale pour les étapes de travaux
        if (in_array($currentStep, ['roofWorkType', 'facadeWorkType', 'isolationWorkType'], true)) {
            $workTypes = $data['work_type'] ?? [];
            
            // Si on est sur roofWorkType et qu'il y a d'autres travaux sélectionnés
            if ($currentStep === 'roofWorkType') {
                if (in_array('facade', $workTypes, true)) {
                    return 'facadeWorkType';
                }
                if (in_array('isolation', $workTypes, true)) {
                    return 'isolationWorkType';
                }
            }
            
            // Si on est sur facadeWorkType et qu'il y a d'autres travaux sélectionnés
            if ($currentStep === 'facadeWorkType') {
                if (in_array('isolation', $workTypes, true)) {
                    return 'isolationWorkType';
                }
            }
            
            // Si on a fini tous les travaux sélectionnés, passer à ownershipStatus
            return 'ownershipStatus';
        }

        // Navigation normale pour les autres étapes
        return $this->steps[$currentIndex + 1] ?? null;
    }

    private function getPreviousStep(string $currentStep): ?string
    {
        $currentIndex = array_search($currentStep, $this->steps, true);
        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }
        return $this->steps[$currentIndex - 1];
    }

    private function generateUserIdentifier(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Obtenir l'adresse IP réelle du client
     */
    private function getClientIp($request = null): string
    {
        $request = $request ?? request();
        
        // Vérifier les headers de proxy
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_X_FORWARDED_FOR',       // Proxy standard
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
        ];
        
        foreach ($headers as $header) {
            $ip = $request->server($header);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
        
        return $request->ip();
    }

    /**
     * Vérifier le reCAPTCHA v3
     */
    private function verifyRecaptcha(Request $request): array
    {
        $recaptchaSecret = setting('recaptcha_secret_key');
        $recaptchaToken = $request->input('recaptcha_token') ?? $request->input('g-recaptcha-response');
        
        if (empty($recaptchaSecret) || empty($recaptchaToken)) {
            // Si pas configuré, accepter (mode développement)
            return ['success' => true, 'score' => 1.0];
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $recaptchaSecret,
                'response' => $recaptchaToken,
                'remoteip' => $this->getClientIp($request),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Score minimum: 0.5 (0.0 = bot, 1.0 = humain)
                $minScore = 0.5;
                $score = $data['score'] ?? 0;
                
                return [
                    'success' => $data['success'] && $score >= $minScore,
                    'score' => $score,
                    'message' => $data['success'] ? 'Vérification réussie' : 'Score trop faible'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Erreur vérification reCAPTCHA: ' . $e->getMessage());
        }

        return ['success' => false, 'score' => 0, 'message' => 'Erreur de vérification'];
    }

    private function sendEmails(Submission $submission): void
    {
        try {
            if (Setting::get('email_enabled', false)) {
                $emailService = new \App\Services\EmailService();
                
                // Email pour l'utilisateur
                if ($submission->email) {
                    $emailService->sendSubmissionReceived($submission);
                }
                
                // Notification interne
                $emailService->sendSubmissionNotification($submission);
            }
        } catch (\Throwable $e) {
            // Ne pas bloquer le flux si l'email échoue
            \Log::warning('Email sending failed for submission '.$submission->id.': '.$e->getMessage());
        }
    }

    /**
     * Normaliser le type de propriété vers les valeurs de la DB
     */
    private function normalizePropertyType(?string $value): ?string
    {
        if (!$value) return null;
        
        $map = [
            'maison' => 'HOUSE',
            'appartement' => 'APARTMENT',
            'immeuble' => 'APARTMENT',
            'local_commercial' => 'HOUSE', // Par défaut
        ];
        
        return $map[strtolower($value)] ?? strtoupper($value);
    }

    /**
     * Normaliser le statut de propriété vers les valeurs de la DB
     */
    private function normalizeOwnershipStatus(?string $value): ?string
    {
        if (!$value) return null;
        
        $map = [
            'owner' => 'OWNER',
            'proprietaire' => 'OWNER',
            'tenant' => 'TENANT',
            'locataire' => 'TENANT',
        ];
        
        return $map[strtolower($value)] ?? strtoupper($value);
    }

    /**
     * Normaliser le genre vers les valeurs de la DB
     */
    private function normalizeGender(?string $value): ?string
    {
        if (!$value) return null;
        
        $map = [
            'madame' => 'MADAME',
            'mme' => 'MADAME',
            'monsieur' => 'MONSIEUR',
            'mr' => 'MONSIEUR',
            'm' => 'MONSIEUR',
        ];
        
        return $map[strtolower($value)] ?? strtoupper($value);
    }
}











