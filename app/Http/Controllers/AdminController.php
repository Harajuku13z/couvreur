<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\AbandonedSubmission;
use App\Models\PhoneCall;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class AdminController extends Controller
{
    // Pas de constructeur, le middleware sera appliqué via les routes
    
    /**
     * Show admin login form
     */
    public function showLogin()
    {
        // Si déjà connecté, rediriger vers dashboard
        if (session()->has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
    }
    
    /**
     * Authenticate admin
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        // Identifiants par défaut (SUPER SIMPLE)
        // Vous pouvez les modifier ici directement
        $adminUsername = 'admin';
        $adminPassword = 'admin';
        
        // Ou depuis la configuration si elle existe
        try {
            if (class_exists('\App\Models\Setting')) {
                $configUsername = \App\Models\Setting::get('admin_username', null);
                $configPassword = \App\Models\Setting::get('admin_password', null);
                
                if ($configUsername && $configPassword) {
                    $adminUsername = $configUsername;
                    $adminPassword = $configPassword;
                }
            }
        } catch (\Exception $e) {
            // Ignorer si la table settings n'existe pas encore
        }
        
        // Log pour debug
        \Log::info('Tentative de connexion', [
            'username_provided' => $request->username,
            'username_expected' => $adminUsername,
            'password_match' => $request->password === $adminPassword
        ]);
        
        // Vérification des identifiants
        $passwordMatch = false;
        
        // Si c'est le mot de passe par défaut (non hashé)
        if ($adminPassword === 'admin' && $request->password === 'admin') {
            $passwordMatch = true;
        }
        // Si c'est un mot de passe hashé (bcrypt)
        elseif (Hash::check($request->password, $adminPassword)) {
            $passwordMatch = true;
        }
        
        if ($request->username === $adminUsername && $passwordMatch) {
            session([
                'admin_logged_in' => true,
                'admin_username' => $request->username,
                'admin_login_time' => now(),
            ]);
            
            return redirect()->route('admin.dashboard')->with('success', 'Connexion réussie !');
        }
        
        return back()->withInput()->with('error', 'Identifiants incorrects. Utilisez admin/admin par défaut.');
    }
    
    /**
     * Logout admin
     */
    public function logout()
    {
        session()->forget(['admin_logged_in', 'admin_username', 'admin_login_time']);
        return redirect()->route('admin.login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    public function dashboard()
    {
        // Marquer automatiquement les submissions en cours depuis plus de 3h comme abandonnées
        $this->markOldSubmissionsAsAbandoned();

        // Statistiques générales
        $totalSubmissions = Submission::count();
        $completedSubmissions = Submission::completed()->count();
        $abandonedSubmissions = Submission::abandoned()->count();
        $inProgressSubmissions = Submission::inProgress()->count();

        // Statistiques des services
        $servicesData = \App\Models\Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        $totalServices = count($services);
        $activeServices = collect($services)->filter(function($service) {
            return isset($service['is_visible']) ? $service['is_visible'] : true;
        })->count();

        // Statistiques du portfolio
        $portfolioData = \App\Models\Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        $totalPortfolioItems = count($portfolioItems);

        // Statistiques des avis
        $totalReviews = \App\Models\Review::count();
        $activeReviews = \App\Models\Review::where('is_active', true)->count();
        $avgRating = \App\Models\Review::where('is_active', true)->avg('rating') ?? 0;

        // Statistiques des appels téléphoniques
        $totalPhoneCalls = \App\Models\PhoneCall::count();
        $todayPhoneCalls = \App\Models\PhoneCall::whereDate('clicked_at', today())->count();

        // Statistiques des articles
        $totalArticles = \App\Models\Article::count();
        $publishedArticles = \App\Models\Article::where('status', 'published')->count();
        $draftArticles = \App\Models\Article::where('status', 'draft')->count();

        // Statistiques des annonces
        $totalAds = \App\Models\Ad::count();
        $publishedAds = \App\Models\Ad::where('status', 'published')->count();
        $draftAds = \App\Models\Ad::where('status', 'draft')->count();

        // Taux d'abandon par étape
        $abandonmentByStep = AbandonedSubmission::select('abandoned_at_step', DB::raw('count(*) as count'))
            ->groupBy('abandoned_at_step')
            ->orderBy('count', 'desc')
            ->get();

        // Statistiques temporelles
        $submissionsByDay = Submission::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $abandonmentsByDay = AbandonedSubmission::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Temps moyen de complétion
        $completedSubmissionsForAvg = Submission::completed()
            ->whereNotNull('completed_at')
            ->get();
        
        $totalSeconds = 0;
        $countForAvg = 0;
        foreach ($completedSubmissionsForAvg as $submission) {
            if ($submission->completed_at && $submission->created_at) {
                $totalSeconds += $submission->completed_at->diffInSeconds($submission->created_at);
                $countForAvg++;
            }
        }
        
        $avgCompletionTime = $countForAvg > 0 ? (object)['avg_seconds' => $totalSeconds / $countForAvg] : (object)['avg_seconds' => 0];

        // Taux de conversion
        $conversionRate = $totalSubmissions > 0 ? round(($completedSubmissions / $totalSubmissions) * 100, 2) : 0;

        return view('admin.dashboard', compact(
            'totalSubmissions',
            'completedSubmissions',
            'abandonedSubmissions',
            'inProgressSubmissions',
            'totalServices',
            'activeServices',
            'totalPortfolioItems',
            'totalReviews',
            'activeReviews',
            'avgRating',
            'totalPhoneCalls',
            'todayPhoneCalls',
            'totalArticles',
            'publishedArticles',
            'draftArticles',
            'totalAds',
            'publishedAds',
            'draftAds',
            'abandonmentByStep',
            'submissionsByDay',
            'abandonmentsByDay',
            'avgCompletionTime',
            'conversionRate'
        ));
    }

    public function submissions(Request $request)
    {
        try {
            // Marquer automatiquement les submissions en cours depuis plus de 3h comme abandonnées
            $this->markOldSubmissionsAsAbandoned();

            $query = Submission::query();

            // Filtres
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $submissions = $query->orderBy('created_at', 'desc')->paginate(20);
            
            // Compter les leads abandonnés pour afficher dans la page
            $abandonedCount = Submission::abandoned()->count();
            
            // Log pour debug
            \Log::info('Récupération des soumissions', [
                'total' => $submissions->total(),
                'count' => $submissions->count(),
                'status_filter' => $request->status ?? 'all'
            ]);

            return view('admin.submissions', compact('submissions', 'abandonedCount'));
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des soumissions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retourner une vue vide en cas d'erreur
            $submissions = collect([])->paginate(20);
            $abandonedCount = 0;
            return view('admin.submissions', compact('submissions', 'abandonedCount'))
                ->with('error', 'Erreur lors du chargement des soumissions. Vérifiez les logs pour plus de détails.');
        }
    }

    public function abandonedSubmissions(Request $request)
    {
        // Marquer automatiquement les submissions en cours depuis plus de 3h comme abandonnées
        $this->markOldSubmissionsAsAbandoned();

        $query = Submission::abandoned();

        // Filtres
        if ($request->filled('step')) {
            $query->where('current_step', $request->step);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $abandonedSubmissions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.abandoned-submissions', compact('abandonedSubmissions'));
    }

    /**
     * Marquer automatiquement les submissions en cours depuis plus de 3h comme abandonnées
     */
    private function markOldSubmissionsAsAbandoned()
    {
        $cutoffTime = now()->subHours(3);
        
        // Trouver les soumissions en cours qui n'ont pas été mises à jour depuis 3h
        $abandonedSubmissions = Submission::where('status', 'IN_PROGRESS')
            ->where('updated_at', '<', $cutoffTime)
            ->get();
        
        if ($abandonedSubmissions->isNotEmpty()) {
            foreach ($abandonedSubmissions as $submission) {
                $submission->markAsAbandoned();
            }
            
            \Log::info('Auto-marked submissions as abandoned', [
                'count' => $abandonedSubmissions->count(),
                'cutoff_time' => $cutoffTime->format('Y-m-d H:i:s')
            ]);
        }
    }

    public function showSubmission($id)
    {
        $submission = Submission::findOrFail($id);
        return view('admin.submission-detail', compact('submission'));
    }

    /**
     * Marquer une soumission comme abandonnée manuellement
     */
    public function markSubmissionAsAbandoned($id)
    {
        $submission = Submission::findOrFail($id);
        
        if ($submission->status === 'IN_PROGRESS') {
            $submission->markAsAbandoned();
            
            return redirect()->route('admin.submissions')
                ->with('success', 'La soumission a été marquée comme abandonnée.');
        }
        
        return redirect()->route('admin.submissions')
            ->with('error', 'Cette soumission ne peut pas être marquée comme abandonnée.');
    }

    /**
     * Supprimer toutes les soumissions (avec vérification du mot de passe)
     */
    public function deleteAllSubmissions(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Vérifier le mot de passe
        $correctPassword = 'elizo';
        
        if ($request->password !== $correctPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect.'
            ], 403);
        }

        try {
            // Compter avant suppression pour le message
            $count = Submission::count();
            
            // Supprimer toutes les soumissions
            Submission::query()->delete();
            
            \Log::warning('Toutes les soumissions ont été supprimées', [
                'count' => $count,
                'admin' => session()->get('admin_username', 'unknown'),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Toutes les soumissions ({$count}) ont été supprimées avec succès."
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de toutes les soumissions: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Supprimer tous les appels téléphoniques (avec vérification du mot de passe)
     */
    public function deleteAllPhoneCalls(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Vérifier le mot de passe
        $correctPassword = 'elizo';
        
        if ($request->password !== $correctPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect.'
            ], 403);
        }

        try {
            // Compter avant suppression pour le message
            $count = PhoneCall::count();
            
            // Supprimer tous les appels
            PhoneCall::query()->delete();
            
            \Log::warning('Tous les appels téléphoniques ont été supprimés', [
                'count' => $count,
                'admin' => session()->get('admin_username', 'unknown'),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Tous les appels téléphoniques ({$count}) ont été supprimés avec succès."
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de tous les appels: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showAbandonedSubmission($id)
    {
        $abandonedSubmission = Submission::abandoned()->findOrFail($id);
        return view('admin.abandoned-submission-detail', compact('abandonedSubmission'));
    }

    /**
     * Créer un client depuis une soumission et rediriger vers la création de devis
     */
    public function createClientFromSubmission($id)
    {
        try {
            $submission = Submission::findOrFail($id);
            
            if ($submission->status !== 'COMPLETED') {
                return back()->with('error', 'Seules les soumissions complétées peuvent être converties en client.');
            }
            
            if (!$submission->email) {
                return back()->with('error', 'La soumission doit avoir un email pour créer un client.');
            }
            
            // Vérifier si un client existe déjà avec cet email
            $existingClient = Client::where('email', $submission->email)->first();
            
            if ($existingClient) {
                // Client existe déjà, rediriger vers la création de devis avec ce client
                \Log::info('Client existant trouvé pour la soumission', [
                    'submission_id' => $submission->id,
                    'client_id' => $existingClient->id,
                    'email' => $submission->email
                ]);
                
                return redirect()->route('admin.devis.create', ['client_id' => $existingClient->id])
                    ->with('success', 'Client existant trouvé. Création d\'un nouveau devis.');
            }
            
            // Créer un nouveau client
            $client = Client::create([
                'nom' => $submission->last_name ?: 'N/A',
                'prenom' => $submission->first_name,
                'email' => $submission->email,
                'telephone' => $submission->phone,
                'adresse' => null, // Pas d'adresse dans submission
                'code_postal' => $submission->postal_code,
                'ville' => $submission->city,
                'pays' => $submission->country ?? 'France',
                'notes' => 'Créé depuis la soumission #' . $submission->id . ' le ' . now()->format('d/m/Y H:i'),
            ]);
            
            \Log::info('Client créé depuis une soumission', [
                'submission_id' => $submission->id,
                'client_id' => $client->id,
                'email' => $client->email
            ]);
            
            // Rediriger vers la création de devis avec le client pré-rempli
            return redirect()->route('admin.devis.create', ['client_id' => $client->id])
                ->with('success', 'Client créé avec succès. Vous pouvez maintenant créer un devis.');
                
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du client depuis la soumission', [
                'submission_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la création du client : ' . $e->getMessage());
        }
    }

    public function exportSubmissions(Request $request)
    {
        $query = Submission::query();

        // Appliquer les mêmes filtres que dans la liste
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $submissions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'submissions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($submissions) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Session ID',
                'User Identifier',
                'Property Type',
                'Surface',
                'Work Types',
                'Roof Work Types',
                'Facade Work Types',
                'Isolation Work Types',
                'Ownership Status',
                'Gender',
                'First Name',
                'Last Name',
                'Postal Code',
                'Phone',
                'Email',
                'Status',
                'Current Step',
                'Created At',
                'Completed At',
                'Abandoned At'
            ]);

            foreach ($submissions as $submission) {
                fputcsv($file, [
                    $submission->id,
                    $submission->session_id,
                    $submission->user_identifier,
                    $submission->property_type,
                    $submission->surface,
                    implode(', ', $submission->work_types ?? []),
                    implode(', ', $submission->roof_work_types ?? []),
                    implode(', ', $submission->facade_work_types ?? []),
                    implode(', ', $submission->isolation_work_types ?? []),
                    $submission->ownership_status,
                    $submission->gender,
                    $submission->first_name,
                    $submission->last_name,
                    $submission->postal_code,
                    $submission->phone,
                    $submission->email,
                    $submission->status,
                    $submission->current_step,
                    $submission->created_at->format('Y-m-d H:i:s'),
                    $submission->completed_at ? $submission->completed_at->format('Y-m-d H:i:s') : '',
                    $submission->abandoned_at ? $submission->abandoned_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportAbandonedSubmissions(Request $request)
    {
        $query = AbandonedSubmission::query();

        // Appliquer les mêmes filtres que dans la liste
        if ($request->filled('step')) {
            $query->where('abandoned_at_step', $request->step);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $abandonedSubmissions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'abandoned_submissions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($abandonedSubmissions) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Session ID',
                'User Identifier',
                'Abandoned At Step',
                'Step Number',
                'Time Spent (seconds)',
                'Time Spent (formatted)',
                'Abandon Reason',
                'Form Data',
                'Created At'
            ]);

            foreach ($abandonedSubmissions as $abandonedSubmission) {
                fputcsv($file, [
                    $abandonedSubmission->id,
                    $abandonedSubmission->session_id,
                    $abandonedSubmission->user_identifier,
                    $abandonedSubmission->abandoned_at_step,
                    $abandonedSubmission->step_number,
                    $abandonedSubmission->time_spent_seconds,
                    $abandonedSubmission->getTimeSpentFormatted(),
                    $abandonedSubmission->abandon_reason,
                    json_encode($abandonedSubmission->form_data),
                    $abandonedSubmission->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function statistics()
    {
        // Statistiques générales
        $totalSubmissions = Submission::count();
        $completedSubmissions = Submission::completed()->count();
        $abandonedSubmissions = Submission::abandoned()->count();
        $conversionRate = $totalSubmissions > 0 ? round(($completedSubmissions / $totalSubmissions) * 100, 2) : 0;

        // Statistiques des services
        $servicesData = \App\Models\Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        $totalServices = count($services);
        $featuredServices = collect($services)->filter(function($service) {
            return isset($service['is_featured']) && $service['is_featured'];
        })->count();

        // Statistiques du portfolio
        $portfolioData = \App\Models\Setting::get('portfolio_items', '[]');
        $portfolioItems = is_string($portfolioData) ? json_decode($portfolioData, true) : ($portfolioData ?? []);
        $totalPortfolioItems = count($portfolioItems);
        
        // Répartition par type de travail
        $workTypes = collect($portfolioItems)->groupBy('work_type')->map(function($items) {
            return $items->count();
        })->toArray();

        // Statistiques des avis
        $totalReviews = \App\Models\Review::count();
        $activeReviews = \App\Models\Review::where('is_active', true)->count();
        $avgRating = \App\Models\Review::where('is_active', true)->avg('rating') ?? 0;
        
        // Répartition des notes
        $ratingDistribution = \App\Models\Review::where('is_active', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        // Statistiques des appels téléphoniques
        $totalPhoneCalls = \App\Models\PhoneCall::count();
        $callsByPage = \App\Models\PhoneCall::selectRaw('source_page, COUNT(*) as count')
            ->groupBy('source_page')
            ->pluck('count', 'source_page')
            ->toArray();

        // Statistiques des articles
        $totalArticles = \App\Models\Article::count();
        $publishedArticles = \App\Models\Article::where('status', 'published')->count();
        $draftArticles = \App\Models\Article::where('status', 'draft')->count();

        // Statistiques des annonces
        $totalAds = \App\Models\Ad::count();
        $publishedAds = \App\Models\Ad::where('status', 'published')->count();
        $draftAds = \App\Models\Ad::where('status', 'draft')->count();

        // Statistiques des villes
        $totalCities = \App\Models\City::count();
        $favoriteCities = \App\Models\City::where('is_favorite', true)->count();

        // Tendance des appels (7 derniers jours)
        $callsTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $callsTrend[$date->format('d/m')] = \App\Models\PhoneCall::whereDate('clicked_at', $date->toDateString())->count();
        }

        // Statistiques détaillées des soumissions
        $stepStatistics = AbandonedSubmission::select('abandoned_at_step', 'step_number')
            ->selectRaw('count(*) as abandonment_count')
            ->selectRaw('AVG(time_spent_seconds) as avg_time_spent')
            ->groupBy('abandoned_at_step', 'step_number')
            ->orderBy('step_number')
            ->get();

        $completionRateByStep = [];
        foreach ($stepStatistics as $stat) {
            $completionRateByStep[$stat->abandoned_at_step] = [
                'step_number' => $stat->step_number,
                'abandonment_count' => $stat->abandonment_count,
                'completion_rate' => $totalSubmissions > 0 ? round((($totalSubmissions - $stat->abandonment_count) / $totalSubmissions) * 100, 2) : 0,
                'avg_time_spent' => round($stat->avg_time_spent, 2)
            ];
        }

        return view('admin.statistics', compact(
            'totalSubmissions',
            'completedSubmissions',
            'abandonedSubmissions',
            'conversionRate',
            'totalServices',
            'featuredServices',
            'totalPortfolioItems',
            'workTypes',
            'totalReviews',
            'activeReviews',
            'avgRating',
            'ratingDistribution',
            'totalPhoneCalls',
            'callsByPage',
            'callsTrend',
            'totalArticles',
            'publishedArticles',
            'draftArticles',
            'totalAds',
            'publishedAds',
            'draftAds',
            'totalCities',
            'favoriteCities',
            'stepStatistics',
            'completionRateByStep'
        ));
    }

    /**
     * Simple phone calls page placeholder to satisfy route
     */
    public function phoneCalls()
    {
        $phoneCalls = PhoneCall::with('submission')
            ->orderBy('clicked_at', 'desc')
            ->paginate(20);

        $stats = [
            'today' => PhoneCall::today()->count(),
            'this_week' => PhoneCall::thisWeek()->count(),
            'this_month' => PhoneCall::thisMonth()->count(),
            'total' => PhoneCall::count(),
        ];

        // Appels par page
        $callsByPage = PhoneCall::selectRaw('source_page, COUNT(*) as count')
            ->groupBy('source_page')
            ->pluck('count', 'source_page')
            ->toArray();

        // Tendance 7 derniers jours
        $callsTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $callsTrend[$date->format('d/m')] = PhoneCall::whereDate('clicked_at', $date->toDateString())->count();
        }

        return view('admin.phone-calls', compact('phoneCalls', 'stats', 'callsByPage', 'callsTrend'));
    }

    /**
     * Afficher les détails d'un appel téléphonique
     */
    public function showPhoneCall($id)
    {
        $phoneCall = PhoneCall::with('submission')->findOrFail($id);
        
        return view('admin.phone-calls-show', compact('phoneCall'));
    }

    /**
     * Mettre à jour la ville d'un appel téléphonique
     */
    public function updatePhoneCallCity(Request $request, $id)
    {
        $request->validate([
            'city' => 'required|string|max:255',
        ]);

        try {
            $phoneCall = PhoneCall::findOrFail($id);
            $phoneCall->city = $request->city;
            $phoneCall->save();

            \Log::info('Ville corrigée pour un appel téléphonique', [
                'call_id' => $id,
                'old_city' => $phoneCall->getOriginal('city'),
                'new_city' => $request->city,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ville corrigée avec succès',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la correction de la ville', [
                'call_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la correction : ' . $e->getMessage(),
            ], 500);
        }
    }
}
