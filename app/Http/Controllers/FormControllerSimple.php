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











