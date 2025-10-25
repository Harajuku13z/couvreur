<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Review;
use App\Models\Setting;
use App\Models\PhoneCall;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SubmissionReceived;
use App\Mail\SubmissionNotification;

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
        
        return view('form.all-reviews', compact('reviews', 'stats'));
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
                'review_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
                'review_photos.*.image' => 'Les fichiers doivent être des images.',
                'review_photos.*.mimes' => 'Les images doivent être au format JPEG, PNG, JPG ou GIF.',
                'review_photos.*.max' => 'Chaque image ne peut pas dépasser 2MB.',
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

            // Gérer les photos (avec vérification de l'existence de la colonne)
            if ($request->hasFile('review_photos')) {
                try {
                    $photos = [];
                    foreach ($request->file('review_photos') as $photo) {
                        if ($photo->isValid()) {
                            $filename = time() . '_' . $photo->getClientOriginalName();
                            $path = $photo->storeAs('reviews', $filename, 'public');
                            $photos[] = $path;
                        }
                    }
                    if (!empty($photos)) {
                        // Vérifier si la colonne existe avant de l'utiliser
                        $review->update(['review_photos' => $photos]);
                    }
                } catch (\Exception $e) {
                    // Si la colonne n'existe pas, on ignore l'erreur et on continue
                    \Log::info('Colonne review_photos non disponible: ' . $e->getMessage());
                }
            }

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
            $submission = Submission::create([
                'session_id' => $sessionId,
                'user_identifier' => $this->generateUserIdentifier(),
                'status' => 'IN_PROGRESS',
                'current_step' => $step,
            ]);
        }

        return view('form.steps.' . $step, compact('submission'));
    }

    public function submitStep(Request $request, string $step)
    {
        $sessionId = Session::getId();
        $submission = Submission::where('session_id', $sessionId)->first();

        if (!$submission) {
            return redirect()->route('form.step', 'propertyType');
        }

        $this->saveStepData($submission, $request, $step);

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











