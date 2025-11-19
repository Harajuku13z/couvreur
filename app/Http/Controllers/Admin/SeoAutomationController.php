<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoAutomation;
use App\Models\City;
use App\Jobs\ProcessSeoCityJob;
use App\Services\SerpApiService;
use App\Services\GptSeoGenerator;
use App\Services\GoogleIndexingService;
use App\Services\AiService;
use App\Models\KeywordImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeoAutomationController extends Controller
{
    /**
     * Rediriger proprement les accès GET à /seo-automation/run
     */
    public function redirectRunGet(Request $request)
    {
        return redirect()->route('admin.seo-automation.index')
            ->with('error', 'Cette action doit être appelée en POST. Vous avez été redirigé vers la page d\'automatisation.');
    }

    /**
     * Afficher le formulaire de mot de passe
     */
    public function passwordForm()
    {
        return view('admin.seo_automation.password');
    }

    /**
     * Vérifier le mot de passe
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($request->password === 'elizo') {
            $request->session()->put('seo_automation_password_verified', true);
            $request->session()->put('seo_automation_password_verified_at', now());
            
            $redirectTo = $request->session()->get('redirect_to', route('admin.seo-automation.index'));
            $request->session()->forget('redirect_to');
            
            return redirect($redirectTo)
                ->with('success', 'Accès autorisé pour 1 heure.');
        }

        return redirect()->back()
            ->with('error', 'Mot de passe incorrect.')
            ->withInput();
    }

    /**
     * Afficher la liste des automations SEO
     */
    public function index()
    {
        // Convertir uniquement les logs "pending" anciens (> 10 minutes) en "failed" (nettoyage automatique)
        // Les logs "pending" récents (< 10 minutes) sont probablement en cours d'exécution
        $oldPendingLogs = SeoAutomation::where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(10))
            ->get();
        
        if ($oldPendingLogs->count() > 0) {
            foreach ($oldPendingLogs as $pendingLog) {
                $pendingLog->update([
                    'status' => 'failed',
                    'error_message' => $pendingLog->error_message ?? 'Traitement interrompu - statut en attente trop ancien (> 10 min)'
                ]);
            }
            Log::info('SeoAutomationController: Logs "pending" anciens convertis en "failed"', [
                'count' => $oldPendingLogs->count()
            ]);
        }
        
        // Récupérer tous les logs, même ceux sans ville
        // Utiliser with() avec une closure pour gérer les villes manquantes
        $logs = SeoAutomation::with(['city' => function($query) {
            // Ne pas exclure les logs si la ville n'existe plus
        }])
            ->orderBy('created_at', 'desc')
            ->paginate(30);
        
        // Log pour debug
        Log::info('SeoAutomationController: Logs récupérés', [
            'total_count' => SeoAutomation::count(),
            'logs_count' => $logs->count(),
            'failed_count' => SeoAutomation::where('status', 'failed')->count(),
            'pending_count' => SeoAutomation::where('status', 'pending')->count(),
            'with_city' => SeoAutomation::whereNotNull('city_id')->count(),
            'without_city' => SeoAutomation::whereNull('city_id')->count()
        ]);
        
        // Statistiques (inclure les "pending" récents qui sont en cours d'exécution)
        $stats = [
            'total' => SeoAutomation::count(),
            'pending' => SeoAutomation::where('status', 'pending')->count(),
            'published' => SeoAutomation::where('status', 'published')->count(),
            'indexed' => SeoAutomation::where('status', 'indexed')->count(),
            'failed' => SeoAutomation::where('status', 'failed')->count(),
        ];
        
        // Récupérer les jobs en attente dans la queue
        $pendingJobs = [];
        try {
            // Vérifier les jobs dans la queue 'seo-automation'
            $queueConnection = config('queue.default');
            if ($queueConnection === 'database') {
                $pendingJobs = \DB::table('jobs')
                    ->where('queue', 'seo-automation')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($job) {
                        $payload = json_decode($job->payload, true);
                        $jobClass = $payload['displayName'] ?? 'Unknown';
                        return [
                            'id' => $job->id,
                            'class' => $jobClass,
                            'created_at' => $job->created_at,
                            'attempts' => $job->attempts,
                        ];
                    });
            }
        } catch (\Exception $e) {
            Log::warning('Impossible de récupérer les jobs en attente: ' . $e->getMessage());
        }
        
        // Récupérer l'heure configurée et le fuseau horaire
        $automationTime = \App\Models\Setting::where('key', 'seo_automation_time')->value('value') ?? '04:00';
        $timezone = config('app.timezone', 'Europe/Paris');
        $currentTime = now()->format('H:i');
        $nextExecution = null;
        
        // Calculer la prochaine exécution
        if ($currentTime < $automationTime) {
            // Aujourd'hui
            $nextExecution = now()->setTimeFromTimeString($automationTime);
        } else {
            // Demain
            $nextExecution = now()->addDay()->setTimeFromTimeString($automationTime);
        }
        
        // Récupérer les villes favorites
        $favoriteCities = City::where('is_favorite', true)->orderBy('name')->get();
        
        // Vérifier si l'automatisation est activée
        $automationEnabled = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
        $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
        // Par défaut, activé si non défini
        if ($automationEnabled === false && $automationEnabled !== true) {
            $automationEnabled = true;
        }
        
        // Récupérer les services
        $servicesData = \App\Models\Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        if (!is_array($services)) {
            $services = [];
        }
        
        // Récupérer les configurations des APIs
        // Forcer la récupération directe depuis la base pour éviter les problèmes de cache
        $serpApiKey = \App\Models\Setting::where('key', 'serp_api_key')->value('value') ?? '';
        $chatgptApiKey = \App\Models\Setting::where('key', 'chatgpt_api_key')->value('value') ?? '';
        $chatgptEnabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
        $chatgptEnabled = filter_var($chatgptEnabled, FILTER_VALIDATE_BOOLEAN);
        if ($chatgptEnabled === null) {
            $chatgptEnabled = true; // Valeur par défaut
        }
        $chatgptModel = \App\Models\Setting::where('key', 'chatgpt_model')->value('value') ?? 'gpt-4o';
        $groqApiKey = \App\Models\Setting::where('key', 'groq_api_key')->value('value') ?? '';
        $groqModel = \App\Models\Setting::where('key', 'groq_model')->value('value') ?? 'llama-3.1-8b-instant';
        
        $googleCredentials = \App\Models\Setting::where('key', 'google_search_console_credentials')->value('value') ?? '';
        
        // Si google_credentials est un tableau (décodé automatiquement), le convertir en JSON pour l'affichage
        if (is_array($googleCredentials)) {
            $googleCredentials = json_encode($googleCredentials, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } elseif (!empty($googleCredentials)) {
            // Vérifier si c'est du JSON valide
            $decoded = json_decode($googleCredentials, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $googleCredentials = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }
        }
        
        $apiConfig = [
            'serpapi_key' => $serpApiKey,
            'chatgpt_enabled' => $chatgptEnabled,
            'chatgpt_api_key' => $chatgptApiKey,
            'chatgpt_model' => $chatgptModel,
            'groq_api_key' => $groqApiKey,
            'groq_model' => $groqModel,
            'google_credentials' => $googleCredentials,
        ];
        
        // Récupérer les mots-clés personnalisés
        $customKeywordsData = \App\Models\Setting::where('key', 'seo_custom_keywords')->value('value') ?? '[]';
        $customKeywords = json_decode($customKeywordsData, true) ?? [];
        if (!is_array($customKeywords)) {
            $customKeywords = [];
        }
        
                // Récupérer la description de l'entreprise
                $companyDescription = \App\Models\Setting::where('key', 'company_description')->value('value') ?? '';
                
                // Récupérer les images de mots-clés
                $keywordImages = KeywordImage::orderBy('keyword')->orderBy('display_order')->get();
                
                // Récupérer les horaires planifiés
                $scheduler = app(\App\Services\SeoArticleScheduler::class);
                $scheduledTimes = $scheduler->getScheduledTimes();
                $scheduleStats = $scheduler->getScheduleStats();
                
                return view('admin.seo_automation.index', compact('logs', 'stats', 'favoriteCities', 'services', 'apiConfig', 'automationEnabled', 'customKeywords', 'companyDescription', 'keywordImages', 'pendingJobs', 'automationTime', 'timezone', 'currentTime', 'nextExecution', 'scheduledTimes', 'scheduleStats'));
    }

    /**
     * Forcer l'exécution pour une ville
     */
    public function runForCity(City $city)
    {
        // Dispatcher le job immédiatement
        ProcessSeoCityJob::dispatch($city->id)
            ->onQueue('seo-automation');
        
        return redirect()->back()
            ->with('success', "Tâche planifiée pour {$city->name}. Le traitement est en cours.");
    }

    /**
     * Relancer une automation échouée ou en attente
     */
    public function retry(SeoAutomation $seoAutomation)
    {
        if (!$seoAutomation->city) {
            return redirect()->back()
                ->with('error', 'Ville non trouvée pour cette automation.');
        }

        // Utiliser le mot-clé du log s'il existe
        $keyword = $seoAutomation->keyword;
        
        ProcessSeoCityJob::dispatch($seoAutomation->city_id, $keyword)
            ->onQueue('seo-automation');
        
        return redirect()->back()
            ->with('success', "Automation relancée pour {$seoAutomation->city->name}.");
    }

    /**
     * Supprimer un log d'automation et son article associé
     */
    public function destroy(SeoAutomation $seoAutomation)
    {
        try {
            $articleId = $seoAutomation->article_id;
            $cityName = $seoAutomation->city->name ?? 'N/A';
            
            // Supprimer l'article associé s'il existe
            if ($articleId) {
                $article = \App\Models\Article::find($articleId);
                if ($article) {
                    $article->delete();
                    Log::info('Article supprimé avec le log SEO automation', [
                        'article_id' => $articleId,
                        'log_id' => $seoAutomation->id
                    ]);
                }
            }
            
            // Supprimer le log
            $seoAutomation->delete();
            
            return redirect()->back()
                ->with('success', "Log et article supprimés avec succès pour {$cityName}.");
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du log SEO automation', [
                'log_id' => $seoAutomation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Relancer tous les articles en attente ou en échec
     */
    public function retryPendingAndFailed(Request $request)
    {
        try {
            // Récupérer tous les logs en attente ou en échec
            $pendingLogs = SeoAutomation::where('status', 'pending')
                ->with('city')
                ->get();
            
            $failedLogs = SeoAutomation::where('status', 'failed')
                ->with('city')
                ->get();
            
            $totalLogs = $pendingLogs->count() + $failedLogs->count();
            
            if ($totalLogs === 0) {
                return redirect()->back()
                    ->with('info', 'Aucun article en attente ou en échec à relancer.');
            }
            
            $relaunchedCount = 0;
            $errors = [];
            
            // Relancer les logs en attente
            foreach ($pendingLogs as $log) {
                if ($log->city) {
                    try {
                        ProcessSeoCityJob::dispatch($log->city_id, $log->keyword)
                            ->onQueue('seo-automation');
                        $relaunchedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Erreur pour {$log->city->name}: " . $e->getMessage();
                        Log::error('Erreur relance automation pending', [
                            'log_id' => $log->id,
                            'city_id' => $log->city_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Relancer les logs en échec
            foreach ($failedLogs as $log) {
                if ($log->city) {
                    try {
                        ProcessSeoCityJob::dispatch($log->city_id, $log->keyword)
                            ->onQueue('seo-automation');
                        $relaunchedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Erreur pour {$log->city->name}: " . $e->getMessage();
                        Log::error('Erreur relance automation failed', [
                            'log_id' => $log->id,
                            'city_id' => $log->city_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $message = "✅ {$relaunchedCount} article(s) relancé(s) avec succès";
            if (count($errors) > 0) {
                $message .= ". ⚠️ " . count($errors) . " erreur(s) lors de la relance.";
            }
            
            Log::info('Relance manuelle des articles pending/failed', [
                'pending_count' => $pendingLogs->count(),
                'failed_count' => $failedLogs->count(),
                'relaunched_count' => $relaunchedCount,
                'errors_count' => count($errors)
            ]);
            
            return redirect()->back()
                ->with('success', $message)
                ->with('relaunch_errors', $errors);
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la relance des articles pending/failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de la relance: ' . $e->getMessage());
        }
    }

    /**
     * Lancer la génération avec paramètres personnalisés
     */
    public function run(Request $request)
    {
        // Si la requête est en GET, rediriger vers la page d'index
        if ($request->isMethod('GET')) {
            return redirect()->route('admin.seo-automation.index')
                ->with('error', 'Cette action nécessite un formulaire POST. Veuillez utiliser le formulaire de la page.');
        }
        
        $validated = $request->validate([
            'number_of_articles' => 'required|integer|min:1|max:50',
            'keyword' => 'nullable|string|max:255',
            'service_id' => 'nullable|string',
            'city_ids' => 'nullable|array',
            'city_ids.*' => 'exists:cities,id',
        ]);

        $numberOfArticles = $validated['number_of_articles'];
        $customKeyword = $validated['keyword'] ?? null;
        $serviceId = $validated['service_id'] ?? null;
        $cityIds = $validated['city_ids'] ?? [];

        // Si un service est sélectionné, récupérer son nom comme mot-clé
        if ($serviceId && !$customKeyword) {
            $servicesData = \App\Models\Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            foreach ($services as $service) {
                if (isset($service['id']) && $service['id'] === $serviceId) {
                    $customKeyword = $service['name'] ?? null;
                    break;
                }
            }
        }

        // Si aucune ville spécifiée, utiliser toutes les villes favorites
        if (empty($cityIds)) {
            $cities = City::where('is_favorite', true)->get();
        } else {
            $cities = City::whereIn('id', $cityIds)->get();
        }

        if ($cities->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Aucune ville sélectionnée ou favorite trouvée.');
        }

        // Exécuter les générations de manière synchrone pour afficher les résultats
        $results = [];
        $successCount = 0;
        $failedCount = 0;
        
        $manager = app(\App\Services\SeoAutomationManager::class);
        
        foreach ($cities as $city) {
            // Créer le nombre d'articles demandé pour chaque ville
            for ($articleIndex = 0; $articleIndex < $numberOfArticles; $articleIndex++) {
                try {
                    $citySteps = [];
                    $log = $manager->runForCity($city, $customKeyword, function($steps) use (&$citySteps) {
                        $citySteps = $steps;
                    });
                    
                    if ($log->status === 'indexed' || $log->status === 'published') {
                        $successCount++;
                        $results[] = [
                            'city' => $city->name,
                            'keyword' => $log->keyword,
                            'status' => 'success',
                            'indexed' => $log->status === 'indexed',
                            'url' => $log->article_url,
                            'article_id' => $log->article_id,
                            'steps' => $citySteps,
                        ];
                    } else {
                        $failedCount++;
                        $results[] = [
                            'city' => $city->name,
                            'keyword' => $log->keyword ?? 'N/A',
                            'status' => 'failed',
                            'indexed' => false,
                            'error' => $log->error_message ?? 'Erreur inconnue',
                            'steps' => $citySteps,
                        ];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'city' => $city->name,
                        'keyword' => $customKeyword ?? 'N/A',
                        'status' => 'error',
                        'error' => $e->getMessage(),
                        'steps' => [],
                    ];
                    Log::error('Erreur génération article SEO', [
                        'city_id' => $city->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        $message = "✅ {$successCount} article(s) généré(s) avec succès";
        if ($failedCount > 0) {
            $message .= ", ⚠️ {$failedCount} échec(s)";
        }
        if ($customKeyword) {
            $message .= " avec le mot-clé/service: {$customKeyword}";
        }

        return redirect()->back()
            ->with('success', $message)
            ->with('seo_results', $results);
    }

    /**
     * Activer/Désactiver l'automatisation globale
     */
    public function toggle()
    {
        $currentStatus = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
        $currentStatus = filter_var($currentStatus, FILTER_VALIDATE_BOOLEAN);
        
        // Si non défini, considérer comme activé par défaut
        if ($currentStatus === false && $currentStatus !== true) {
            $currentStatus = true;
        }
        
        $newStatus = !$currentStatus;
        \App\Models\Setting::set('seo_automation_enabled', $newStatus ? '1' : '0', 'boolean', 'seo');
        
        // Vider le cache pour que le changement soit immédiat
        \App\Models\Setting::clearCache();
        
        $automationTime = \App\Models\Setting::where('key', 'seo_automation_time')->value('value') ?? '04:00';
        
        $message = $newStatus 
            ? "✅ Automatisation SEO activée. Les articles seront générés automatiquement chaque jour à {$automationTime}."
            : '⏸️ Automatisation SEO mise en pause. Les générations automatiques sont désactivées.';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Forcer l'exécution manuelle du scheduler (test)
     */
    public function forceRun(Request $request)
    {
        try {
            // Vérifier si l'automatisation est activée
            $automationEnabled = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
            $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
            
            if ($automationEnabled === false && $automationEnabled !== true) {
                $automationEnabled = true;
            }
            
            if (!$automationEnabled) {
                return redirect()->back()
                    ->with('error', '⚠️ L\'automatisation est désactivée. Activez-la d\'abord pour tester.');
            }
            
            // Exécuter la commande seo:run-automations
            $exitCode = \Artisan::call('seo:run-automations');
            $output = \Artisan::output();
            
            Log::info('SeoAutomationController: Exécution forcée du scheduler', [
                'exit_code' => $exitCode,
                'output' => $output
            ]);
            
            // Parser la sortie pour extraire les informations
            $citiesCount = 0;
            $jobsCount = 0;
            
            if (preg_match('/Traitement de (\d+) ville\(s\) favorite\(s\)\.\.\./', $output, $matches)) {
                $citiesCount = (int)$matches[1];
            }
            if (preg_match('/(\d+) job\(s\) planifié\(s\)/', $output, $matches)) {
                $jobsCount = (int)$matches[1];
            }
            
            if ($exitCode === 0 && $jobsCount > 0) {
                $message = "✅ Scheduler exécuté avec succès ! {$jobsCount} job(s) planifié(s) pour {$citiesCount} ville(s).";
                $message .= "\n💡 Exécutez maintenant: php artisan queue:work --queue=seo-automation";
                
                return redirect()->back()
                    ->with('success', $message)
                    ->with('scheduler_output', $output);
            } else {
                return redirect()->back()
                    ->with('warning', "⚠️ Scheduler exécuté mais aucun job n'a été planifié. Vérifiez que vous avez des villes favorites configurées.")
                    ->with('scheduler_output', $output);
            }
        } catch (\Exception $e) {
            Log::error('SeoAutomationController: Erreur lors de l\'exécution forcée du scheduler', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de l\'exécution du scheduler: ' . $e->getMessage());
        }
    }

    /**
     * Exécuter immédiatement (sans respecter l'heure configurée)
     */
    public function executeNow(Request $request)
    {
        try {
            // Vérifier si l'automatisation est activée
            $automationEnabled = \App\Models\Setting::where('key', 'seo_automation_enabled')->value('value');
            $automationEnabled = filter_var($automationEnabled, FILTER_VALIDATE_BOOLEAN);
            
            if ($automationEnabled === false && $automationEnabled !== true) {
                $automationEnabled = true;
            }
            
            if (!$automationEnabled) {
                return redirect()->back()
                    ->with('error', '⚠️ L\'automatisation est désactivée. Activez-la d\'abord.');
            }
            
            // Exécuter la commande seo:run-automations immédiatement
            $exitCode = \Artisan::call('seo:run-automations');
            $output = \Artisan::output();
            
            Log::info('SeoAutomationController: Exécution immédiate du scheduler', [
                'exit_code' => $exitCode,
                'output' => $output
            ]);
            
            // Parser la sortie
            $citiesCount = 0;
            $jobsCount = 0;
            
            if (preg_match('/Traitement de (\d+) ville\(s\) favorite\(s\)\.\.\./', $output, $matches)) {
                $citiesCount = (int)$matches[1];
            }
            if (preg_match('/(\d+) job\(s\) planifié\(s\)/', $output, $matches)) {
                $jobsCount = (int)$matches[1];
            }
            
            if ($exitCode === 0 && $jobsCount > 0) {
                $message = "✅ Exécution immédiate réussie ! {$jobsCount} job(s) planifié(s) pour {$citiesCount} ville(s).";
                $message .= "\n💡 Les jobs sont en attente dans la queue. Exécutez: php artisan queue:work --queue=seo-automation";
                
                return redirect()->back()
                    ->with('success', $message)
                    ->with('scheduler_output', $output);
            } else {
                return redirect()->back()
                    ->with('warning', "⚠️ Aucun job n'a été planifié. Vérifiez que vous avez des villes favorites configurées.")
                    ->with('scheduler_output', $output);
            }
        } catch (\Exception $e) {
            Log::error('SeoAutomationController: Erreur lors de l\'exécution immédiate', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir ou générer le token pour la route HTTP schedule/run
     */
    public function getScheduleToken(Request $request)
    {
        $token = \App\Models\Setting::where('key', 'schedule_run_token')->value('value');
        
        if (empty($token)) {
            // Générer un nouveau token
            $token = \Illuminate\Support\Str::random(32);
            \App\Models\Setting::set('schedule_run_token', $token, 'string', 'seo');
        }
        
        $url = url('/schedule/run?token=' . $token);
        
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'url' => $url,
            'message' => 'Token récupéré avec succès'
        ]);
    }
    
    /**
     * Régénérer le token pour la route HTTP schedule/run
     */
    public function regenerateScheduleToken(Request $request)
    {
        $newToken = \Illuminate\Support\Str::random(32);
        \App\Models\Setting::set('schedule_run_token', $newToken, 'string', 'seo');
        
        $url = url('/schedule/run?token=' . $newToken);
        
        return response()->json([
            'status' => 'success',
            'token' => $newToken,
            'url' => $url,
            'message' => 'Token régénéré avec succès. N\'oubliez pas de mettre à jour votre service externe (cron-job.org, etc.) avec le nouveau token.'
        ]);
    }
    
    /**
     * Tester la route HTTP schedule/run
     */
    public function testScheduleHttp(Request $request)
    {
        $token = \App\Models\Setting::where('key', 'schedule_run_token')->value('value');
        
        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun token configuré. Générez d\'abord un token.'
            ], 400);
        }
        
        $url = url('/schedule/run?token=' . $token);
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->get($url);
            $data = $response->json();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Route HTTP testée avec succès',
                'url' => $url,
                'response' => $data,
                'http_status' => $response->status()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du test : ' . $e->getMessage(),
                'url' => $url
            ], 500);
        }
    }

    /**
     * Tester le scheduler manuellement
     */
    public function testScheduler(Request $request)
    {
        try {
            // Exécuter le scheduler manuellement
            $exitCode = \Artisan::call('schedule:run');
            $output = \Artisan::output();
            
            Log::info('SeoAutomationController: Test du scheduler', [
                'exit_code' => $exitCode,
                'output' => $output
            ]);
            
            // Vérifier l'heure actuelle et configurée
            $automationTime = \App\Models\Setting::where('key', 'seo_automation_time')->value('value') ?? '04:00';
            $currentTime = now()->format('H:i');
            $timezone = config('app.timezone', 'Europe/Paris');
            
            // Vérifier si le cron est configuré (test basique)
            $cronConfigured = false;
            try {
                // Tenter de détecter si le cron s'exécute (vérifier les logs récents)
                $logFile = storage_path('logs/laravel.log');
                if (file_exists($logFile)) {
                    $logContent = file_get_contents($logFile);
                    // Chercher des traces d'exécution du scheduler dans les dernières 24h
                    $cronConfigured = (strpos($logContent, 'Running scheduled command') !== false || 
                                     strpos($logContent, 'schedule:run') !== false);
                }
            } catch (\Exception $e) {
                // Ignorer les erreurs
            }
            
            // Analyser la sortie pour voir si des commandes sont prêtes
            $hasReadyCommands = (strpos($output, 'Running scheduled command') !== false);
            $noCommandsReady = (strpos($output, 'No scheduled commands are ready to run') !== false);
            
            $info = [
                'scheduler_executed' => $exitCode === 0,
                'current_time' => $currentTime,
                'automation_time' => $automationTime,
                'timezone' => $timezone,
                'will_trigger' => $currentTime === $automationTime,
                'output' => $output,
                'cron_configured' => $cronConfigured,
                'has_ready_commands' => $hasReadyCommands,
                'no_commands_ready' => $noCommandsReady,
                'explanation' => $noCommandsReady 
                    ? ($currentTime === $automationTime 
                        ? 'L\'heure est arrivée mais aucune commande n\'est prête. Vérifiez que l\'automatisation est activée.'
                        : "L'heure configurée ({$automationTime}) n'est pas encore arrivée. Le scheduler attendra jusqu'à {$automationTime}.")
                    : ($hasReadyCommands ? 'Des commandes sont prêtes et seront exécutées.' : 'Aucune commande planifiée pour le moment.')
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'Scheduler testé avec succès',
                'info' => $info
            ]);
            
        } catch (\Exception $e) {
            Log::error('SeoAutomationController: Erreur test scheduler', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réinitialiser toutes les automations (supprimer les logs et jobs)
     */
    public function resetAll(Request $request)
    {
        try {
            // Supprimer tous les logs SeoAutomation
            $deletedLogs = SeoAutomation::count();
            SeoAutomation::truncate();
            
            // Supprimer les jobs en attente dans la queue
            $deletedJobs = 0;
            try {
                if (config('queue.default') === 'database') {
                    $deletedJobs = \DB::table('jobs')
                        ->where('queue', 'seo-automation')
                        ->delete();
                }
            } catch (\Exception $e) {
                Log::warning('Impossible de supprimer les jobs: ' . $e->getMessage());
            }
            
            // Supprimer les jobs échoués
            $deletedFailed = 0;
            try {
                if (config('queue.default') === 'database') {
                    $deletedFailed = \DB::table('failed_jobs')
                        ->where('queue', 'seo-automation')
                        ->orWhere('payload', 'like', '%ProcessSeoCityJob%')
                        ->delete();
                }
            } catch (\Exception $e) {
                Log::warning('Impossible de supprimer les jobs échoués: ' . $e->getMessage());
            }
            
            Log::info('SeoAutomationController: Réinitialisation complète', [
                'deleted_logs' => $deletedLogs,
                'deleted_jobs' => $deletedJobs,
                'deleted_failed' => $deletedFailed
            ]);
            
            $message = "✅ Réinitialisation complète réussie !\n";
            $message .= "• {$deletedLogs} log(s) d'automation supprimé(s)\n";
            $message .= "• {$deletedJobs} job(s) en attente supprimé(s)\n";
            if ($deletedFailed > 0) {
                $message .= "• {$deletedFailed} job(s) échoué(s) supprimé(s)";
            }
            
            return redirect()->back()
                ->with('success', $message);
                
        } catch (\Exception $e) {
            Log::error('SeoAutomationController: Erreur lors de la réinitialisation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de la réinitialisation: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder l'heure de publication automatique et le nombre d'articles
     */
    public function saveTime(Request $request)
    {
        $validated = $request->validate([
            'time' => ['required', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
            'cron_interval' => 'nullable|integer|min:1|max:60',
            'articles_per_day' => 'nullable|integer|min:1|max:50',
            'articles_per_city' => 'nullable|integer|min:1|max:10', // Gardé pour compatibilité
            'direct_execution' => 'nullable|boolean',
            'ignore_quota' => 'nullable|boolean',
        ]);
        
        \App\Models\Setting::set('seo_automation_time', $validated['time'], 'string', 'seo');
        
        // Sauvegarder l'intervalle d'exécution du cron (en minutes)
        $cronInterval = $validated['cron_interval'] ?? 1;
        \App\Models\Setting::set('seo_automation_cron_interval', (string)$cronInterval, 'string', 'seo');
        
        // Nouveau système : articles par jour (répartis sur toutes les villes)
        if (isset($validated['articles_per_day'])) {
            \App\Models\Setting::set('seo_automation_articles_per_day', (string)$validated['articles_per_day'], 'string', 'seo');
        }
        
        // Ancien système : articles par ville (gardé pour compatibilité)
        if (isset($validated['articles_per_city'])) {
            \App\Models\Setting::set('seo_automation_articles_per_city', (string)$validated['articles_per_city'], 'string', 'seo');
        }
        
        // Sauvegarder le mode d'exécution (direct ou queue)
        $directExecution = $request->has('direct_execution') && $request->boolean('direct_execution');
        \App\Models\Setting::set('seo_automation_direct_execution', $directExecution ? '1' : '0', 'boolean', 'seo');
        
        // Sauvegarder l'option pour ignorer le quota (mode test)
        $ignoreQuota = $request->has('ignore_quota') && $request->boolean('ignore_quota');
        \App\Models\Setting::set('seo_automation_ignore_quota', $ignoreQuota ? '1' : '0', 'boolean', 'seo');
        
        $articlesPerDay = $validated['articles_per_day'] ?? 5;
        $citiesCount = \App\Models\City::where('is_favorite', true)->count();
        $totalArticlesPerDay = $articlesPerDay * $citiesCount;
        $executionMode = $directExecution ? 'directe (sans queue)' : 'via queue (nécessite worker)';
        
        return redirect()->back()
            ->with('success', "✅ Configuration mise à jour : Heure {$validated['time']}, intervalle cron {$cronInterval} min, {$articlesPerDay} article(s) par jour par ville ({$totalArticlesPerDay} total pour {$citiesCount} ville(s)), exécution {$executionMode}");
    }

    /**
     * Uploader et redimensionner l'image OG Blog
     */
    public function uploadOgImage(Request $request)
    {
        $validated = $request->validate([
            'og_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);
        
        try {
            $image = $request->file('og_image');
            
            // Créer le dossier s'il n'existe pas
            $uploadDir = public_path('images');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Supprimer l'ancienne image si elle existe
            $oldImagePath = \App\Models\Setting::where('key', 'default_blog_og_image')->value('value');
            if ($oldImagePath && file_exists(public_path($oldImagePath))) {
                // Ne supprimer que si c'est le fichier og-blog.jpg (pour éviter de supprimer d'autres images)
                if (basename($oldImagePath) === 'og-blog.jpg') {
                    @unlink(public_path($oldImagePath));
                    Log::info('Ancienne image OG Blog supprimée', ['path' => $oldImagePath]);
                }
            }
            
            // Nom du fichier avec timestamp pour éviter les conflits de cache
            $filename = 'og-blog-' . time() . '.jpg';
            $imagePath = 'images/' . $filename;
            $fullPath = public_path($imagePath);
            
            // Charger l'image avec GD ou Intervention Image si disponible
            $sourceImage = null;
            $imageType = $image->getMimeType();
            
            if ($imageType === 'image/jpeg' || $imageType === 'image/jpg') {
                $sourceImage = imagecreatefromjpeg($image->getRealPath());
            } elseif ($imageType === 'image/png') {
                $sourceImage = imagecreatefrompng($image->getRealPath());
            } elseif ($imageType === 'image/webp') {
                $sourceImage = imagecreatefromwebp($image->getRealPath());
            }
            
            if (!$sourceImage) {
                return redirect()->back()
                    ->with('error', '❌ Format d\'image non supporté. Utilisez JPG, PNG ou WebP.');
            }
            
            // Dimensions cibles pour OG (1200x630px)
            $targetWidth = 1200;
            $targetHeight = 630;
            
            // Obtenir les dimensions de l'image source
            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            
            // Calculer les dimensions pour conserver le ratio
            $sourceRatio = $sourceWidth / $sourceHeight;
            $targetRatio = $targetWidth / $targetHeight;
            
            if ($sourceRatio > $targetRatio) {
                // Image plus large, ajuster la hauteur
                $newHeight = $targetHeight;
                $newWidth = (int)($targetHeight * $sourceRatio);
            } else {
                // Image plus haute, ajuster la largeur
                $newWidth = $targetWidth;
                $newHeight = (int)($targetWidth / $sourceRatio);
            }
            
            // Créer une nouvelle image avec les dimensions cibles
            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
            
            // Remplir avec une couleur blanche (pour les zones vides)
            $white = imagecolorallocate($targetImage, 255, 255, 255);
            imagefill($targetImage, 0, 0, $white);
            
            // Calculer la position pour centrer l'image
            $x = (int)(($targetWidth - $newWidth) / 2);
            $y = (int)(($targetHeight - $newHeight) / 2);
            
            // Redimensionner et copier l'image
            imagecopyresampled(
                $targetImage, $sourceImage,
                $x, $y, 0, 0,
                $newWidth, $newHeight,
                $sourceWidth, $sourceHeight
            );
            
            // Sauvegarder l'image redimensionnée
            imagejpeg($targetImage, $fullPath, 90); // Qualité 90%
            
            // Libérer la mémoire
            imagedestroy($sourceImage);
            imagedestroy($targetImage);
            
            // Supprimer les anciennes images og-blog-*.jpg (garder seulement la dernière)
            $imagesDir = public_path('images');
            if (is_dir($imagesDir)) {
                $files = glob($imagesDir . '/og-blog-*.jpg');
                // Trier par date de modification (plus récent en dernier)
                usort($files, function($a, $b) {
                    return filemtime($a) - filemtime($b);
                });
                // Supprimer toutes sauf la dernière (celle qu'on vient de créer)
                if (count($files) > 1) {
                    foreach (array_slice($files, 0, -1) as $oldFile) {
                        @unlink($oldFile);
                        Log::info('Ancienne image OG Blog supprimée', ['file' => basename($oldFile)]);
                    }
                }
            }
            
            // Mettre à jour le setting avec le nouveau chemin
            \App\Models\Setting::set('default_blog_og_image', $imagePath, 'string', 'seo');
            
            // Vider le cache si nécessaire
            \App\Models\Setting::clearCache();
            
            return redirect()->back()
                ->with('success', "✅ Image Open Graph uploadée et redimensionnée à 1200x630px : {$imagePath}");
                
        } catch (\Exception $e) {
            Log::error('Erreur upload image OG Blog', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder le chemin de l'image OG Blog par défaut
     */
    public function saveOgImage(Request $request)
    {
        $validated = $request->validate([
            'image_path' => 'required|string|max:255',
        ]);
        
        $imagePath = trim($validated['image_path']);
        
        // Vérifier que le chemin commence par "images/"
        if (!str_starts_with($imagePath, 'images/')) {
            return redirect()->back()
                ->with('error', '❌ Le chemin doit commencer par "images/" (ex: images/og-blog.jpg)');
        }
        
        // Vérifier que le fichier existe
        if (!file_exists(public_path($imagePath))) {
            return redirect()->back()
                ->with('error', "❌ Le fichier {$imagePath} n'existe pas dans public/. Veuillez d'abord uploader l'image.");
        }
        
        \App\Models\Setting::set('default_blog_og_image', $imagePath, 'string', 'seo');
        
        return redirect()->back()
            ->with('success', "✅ Image Open Graph par défaut mise à jour : {$imagePath}");
    }

    /**
     * Générer des mots-clés depuis la description de l'entreprise en utilisant SerpAPI
     */
    public function generateKeywords(Request $request)
    {
        try {
            $companyDescription = \App\Models\Setting::where('key', 'company_description')->value('value') ?? '';
            
            if (empty($companyDescription)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucune description d\'entreprise trouvée. Veuillez d\'abord configurer la description de votre entreprise.'
                ], 400);
            }
            
            // Vérifier que ChatGPT est configuré
            $chatgptApiKey = \App\Models\Setting::where('key', 'chatgpt_api_key')->value('value');
            $chatgptEnabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
            $chatgptEnabled = filter_var($chatgptEnabled, FILTER_VALIDATE_BOOLEAN);
            
            if (empty($chatgptApiKey) || !$chatgptEnabled) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ChatGPT n\'est pas configuré ou désactivé. Veuillez configurer votre clé API ChatGPT dans la section "Configuration des APIs".'
                ], 400);
            }
            
            Log::info('Génération mots-clés via ChatGPT', [
                'description_length' => strlen($companyDescription)
            ]);
            
            // Construire le prompt pour ChatGPT
            $prompt = "À partir de la description suivante d'une entreprise de couvreur/rénovation, génère une liste de 20 à 30 mots-clés SEO pertinents et spécifiques pour le secteur du bâtiment et de la rénovation.

Description de l'entreprise :
{$companyDescription}

**Instructions :**
- Génère des mots-clés spécifiques au secteur (ex: 'rénovation de toiture', 'couverture en tuiles', 'isolation thermique', etc.)
- Inclus des mots-clés avec localisation (ex: 'couvreur à [ville]', 'rénovation toiture [ville]')
- Inclus des mots-clés de services (ex: 'réparation toiture', 'charpente traditionnelle', 'isolation combles')
- Inclus des mots-clés de matériaux (ex: 'tuiles ardoise', 'zinc', 'isolation laine de verre')
- Les mots-clés doivent être pertinents, recherchés et adaptés au secteur
- Évite les mots-clés trop génériques ou hors sujet
- Retourne UNIQUEMENT une liste de mots-clés, un par ligne, sans numérotation, sans puces, sans formatage

Format de sortie :
mot-clé 1
mot-clé 2
mot-clé 3
...";

            $systemMessage = 'Tu es un expert SEO spécialisé dans le secteur du bâtiment et de la rénovation.';
            
            $result = AiService::callAI($prompt, $systemMessage, [
                'max_tokens' => 1000,
                'temperature' => 0.3,
                'timeout' => 60
            ]);
            
            if (!$result || !isset($result['content']) || empty($result['content'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur lors de la génération des mots-clés par ChatGPT. Vérifiez votre clé API et vos quotas.'
                ], 500);
            }
            
            // Parser les mots-clés (un par ligne)
            $content = trim($result['content']);
            $keywords = [];
            
            // Séparer par lignes
            $lines = preg_split('/\r?\n/', $content);
            foreach ($lines as $line) {
                $line = trim($line);
                // Enlever les numéros, puces, tirets en début de ligne
                $line = preg_replace('/^[\d\.\-\*\+\s]+/', '', $line);
                $line = trim($line);
                
                if (!empty($line) && strlen($line) >= 3 && strlen($line) <= 100) {
                    // Enlever les guillemets si présents
                    $line = trim($line, '"\'');
                    if (!empty($line) && !in_array($line, $keywords)) {
                        $keywords[] = $line;
                    }
                }
            }
            
            // Si pas assez de mots-clés, essayer de parser différemment
            if (count($keywords) < 10) {
                // Essayer de trouver des mots-clés séparés par virgules
                $commaSeparated = preg_split('/[,;]/', $content);
                foreach ($commaSeparated as $item) {
                    $item = trim($item);
                    if (!empty($item) && strlen($item) >= 3 && strlen($item) <= 100 && !in_array($item, $keywords)) {
                        $keywords[] = $item;
                    }
                }
            }
            
            // Nettoyer et limiter
            $keywords = array_slice(array_unique($keywords), 0, 30);
            
            if (empty($keywords)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun mot-clé généré. ChatGPT n\'a pas retourné de mots-clés valides. Essayez de reformuler votre description d\'entreprise.'
                ], 500);
            }
            
            Log::info('Mots-clés générés via ChatGPT', [
                'count' => count($keywords),
                'keywords_preview' => array_slice($keywords, 0, 5)
            ]);
            
            return response()->json([
                'status' => 'success',
                'keywords' => $keywords,
                'message' => count($keywords) . ' mots-clés générés avec succès via ChatGPT.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération mots-clés SerpAPI', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sauvegarder les mots-clés personnalisés avec leurs images associées
     */
    public function saveKeywords(Request $request)
    {
        try {
            $validated = $request->validate([
                'keywords' => 'required|array',
                'keywords.*' => 'string|max:255',
                'keyword_images' => 'nullable|array',
                'keyword_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);
            
            $keywords = array_filter(array_map('trim', $validated['keywords']));
            $keywords = array_values(array_unique($keywords)); // Supprimer les doublons
            
            // Vérifier qu'il y a au moins un mot-clé
            if (empty($keywords)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun mot-clé valide à sauvegarder. Veuillez ajouter au moins un mot-clé.'
                ], 400);
            }
            
            // Créer le dossier pour les images de mots-clés s'il n'existe pas
            $uploadDir = public_path('images/keywords');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Traiter les images associées aux mots-clés
            $keywordImages = $validated['keyword_images'] ?? [];
            $savedImages = [];
            
            foreach ($keywords as $index => $keyword) {
                // Si une image est fournie pour ce mot-clé
                if (isset($keywordImages[$index]) && $keywordImages[$index]->isValid()) {
                    $image = $keywordImages[$index];
                    $filename = 'keyword-' . Str::slug($keyword) . '-' . time() . '-' . $index . '.' . $image->getClientOriginalExtension();
                    $imagePath = 'images/keywords/' . $filename;
                    
                    // Déplacer l'image
                    $image->move($uploadDir, $filename);
                    
                    // Créer ou mettre à jour l'entrée dans keyword_images
                    $keywordImageModel = KeywordImage::updateOrCreate(
                        ['keyword' => $keyword],
                        [
                            'image_path' => $imagePath,
                            'title' => $keyword,
                            'is_active' => true,
                            'display_order' => $index,
                        ]
                    );
                    
                    $savedImages[] = $keywordImageModel->id;
                } else {
                    // Vérifier si une image existe déjà pour ce mot-clé
                    $existingImage = KeywordImage::where('keyword', $keyword)->first();
                    if ($existingImage) {
                        // Mettre à jour l'ordre d'affichage
                        $existingImage->update(['display_order' => $index]);
                    }
                }
            }
            
            // Sauvegarder la liste des mots-clés
            \App\Models\Setting::set('seo_custom_keywords', json_encode($keywords), 'json', 'seo');
            
            $message = count($keywords) . ' mots-clés sauvegardés avec succès.';
            if (count($savedImages) > 0) {
                $message .= ' ' . count($savedImages) . ' image(s) associée(s).';
            }
            
            Log::info('Mots-clés sauvegardés', [
                'count' => count($keywords),
                'keywords' => $keywords
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'keywords' => $keywords,
                'images_saved' => count($savedImages)
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation sauvegarde mots-clés', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors()))
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde mots-clés', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester toutes les connexions (SerpAPI, GPT, Google Indexing)
     */
    public function testConnections()
    {
        $results = [
            'serpapi' => ['status' => 'pending', 'message' => ''],
            'gpt' => ['status' => 'pending', 'message' => ''],
            'google_indexing' => ['status' => 'pending', 'message' => ''],
        ];

        // Test SerpAPI - Test simple de connexion
        try {
            $apiKey = \App\Models\Setting::where('key', 'serp_api_key')->value('value');
            if (empty($apiKey)) {
                $results['serpapi'] = [
                    'status' => 'error',
                    'message' => 'Clé API SerpAPI non configurée.'
                ];
            } else {
                // Test simple de connexion avec une requête Google Search basique
                $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://serpapi.com/search.json', [
                    'engine' => 'google',
                    'q' => 'test',
                    'api_key' => $apiKey,
                    'num' => 1, // Juste 1 résultat pour économiser les quotas
                ]);
                
                if ($response->successful()) {
                    $json = $response->json();
                    if (isset($json['search_metadata']) || isset($json['organic_results'])) {
                        $results['serpapi'] = [
                            'status' => 'success',
                            'message' => 'Connexion SerpAPI réussie. L\'API répond correctement.'
                        ];
                    } else {
                        $results['serpapi'] = [
                            'status' => 'warning',
                            'message' => 'Connexion SerpAPI OK mais réponse inattendue.'
                        ];
                    }
                } else {
                    $errorBody = $response->json();
                    $errorMessage = $errorBody['error'] ?? 'Erreur inconnue';
                    $results['serpapi'] = [
                        'status' => 'error',
                        'message' => 'Erreur SerpAPI: ' . $errorMessage
                    ];
                }
            }
        } catch (\Exception $e) {
            $results['serpapi'] = [
                'status' => 'error',
                'message' => 'Erreur de connexion SerpAPI: ' . $e->getMessage()
            ];
        }

        // Test GPT
        try {
            $gptService = new GptSeoGenerator();
            $testKeyword = 'couvreur';
            $testCity = 'Paris';
            
            // Test avec un prompt minimal pour vérifier la connexion
            $testResult = $gptService->generateSeoArticle($testKeyword, $testCity, [], []);
            
            if ($testResult && !empty($testResult['titre'])) {
                $results['gpt'] = [
                    'status' => 'success',
                    'message' => 'Connexion GPT réussie. Génération de test OK.',
                    'data' => [
                        'titre' => substr($testResult['titre'], 0, 100) . '...',
                        'has_content' => !empty($testResult['contenu_html'])
                    ]
                ];
            } else {
                $results['gpt'] = [
                    'status' => 'warning',
                    'message' => 'Connexion GPT OK mais réponse invalide. Vérifiez la configuration.'
                ];
            }
        } catch (\Exception $e) {
            $results['gpt'] = [
                'status' => 'error',
                'message' => 'Erreur GPT: ' . $e->getMessage()
            ];
        }

        // Test Google Indexing
        try {
            $indexingService = new GoogleIndexingService();
            
            // Test avec une URL factice (ne sera pas réellement indexée mais teste la connexion)
            $testUrl = config('app.url', 'https://example.com') . '/test-seo-automation';
            
            // On ne peut pas vraiment tester sans une vraie URL, donc on vérifie juste la configuration
            $googleService = new \App\Services\GoogleSearchConsoleService();
            $isConfigured = $googleService->isConfigured();
            
            if ($isConfigured) {
                $results['google_indexing'] = [
                    'status' => 'success',
                    'message' => 'Google Indexing configuré correctement. Les credentials sont valides.'
                ];
            } else {
                $results['google_indexing'] = [
                    'status' => 'error',
                    'message' => 'Google Indexing non configuré. Veuillez configurer les credentials dans Indexation.'
                ];
            }
        } catch (\Exception $e) {
            $results['google_indexing'] = [
                'status' => 'error',
                'message' => 'Erreur Google Indexing: ' . $e->getMessage()
            ];
        }

        // Résumé global
        $allSuccess = collect($results)->every(function ($result) {
            return $result['status'] === 'success';
        });

        $hasError = collect($results)->contains(function ($result) {
            return $result['status'] === 'error';
        });

        return response()->json([
            'success' => $allSuccess,
            'has_error' => $hasError,
            'results' => $results,
            'summary' => [
                'total' => count($results),
                'success' => collect($results)->where('status', 'success')->count(),
                'warning' => collect($results)->where('status', 'warning')->count(),
                'error' => collect($results)->where('status', 'error')->count(),
            ]
        ]);
    }

    /**
     * Sauvegarder les configurations des APIs
     */
    public function saveApiConfig(Request $request)
    {
        $validated = $request->validate([
            'serpapi_key' => 'nullable|string',
            'seo_automation_serpapi_enabled' => 'nullable|boolean',
            'chatgpt_enabled' => 'nullable|boolean',
            'chatgpt_api_key' => 'nullable|string',
            'chatgpt_model' => 'nullable|string|in:gpt-3.5-turbo,gpt-4,gpt-4-turbo,gpt-4o',
            'groq_api_key' => 'nullable|string',
            'groq_model' => 'nullable|string|in:llama-3.1-8b-instant,llama-3.1-70b-versatile,mixtral-8x7b-32768',
            'google_credentials' => 'nullable|string',
        ]);

        // Sauvegarder SerpAPI (seulement si une valeur est fournie)
        if ($request->filled('serpapi_key')) {
            \App\Models\Setting::set('serp_api_key', $validated['serpapi_key'], 'string', 'seo');
        }
        
        // Sauvegarder le toggle SerpAPI pour l'automatisation
        if ($request->has('seo_automation_serpapi_enabled')) {
            $serpapiEnabled = $request->boolean('seo_automation_serpapi_enabled');
            \App\Models\Setting::set('seo_automation_serpapi_enabled', $serpapiEnabled ? '1' : '0', 'boolean', 'seo');
            Log::info('SeoAutomationController: SerpAPI pour automatisation', [
                'enabled' => $serpapiEnabled
            ]);
        }

        // Sauvegarder ChatGPT
        if ($request->has('chatgpt_enabled')) {
            \App\Models\Setting::set('chatgpt_enabled', $request->boolean('chatgpt_enabled', true), 'boolean', 'ai');
        }
        if ($request->filled('chatgpt_api_key')) {
            \App\Models\Setting::set('chatgpt_api_key', $validated['chatgpt_api_key'], 'string', 'ai');
        }
        if ($request->has('chatgpt_model')) {
            \App\Models\Setting::set('chatgpt_model', $validated['chatgpt_model'] ?? 'gpt-4o', 'string', 'ai');
        }

        // Sauvegarder Groq (seulement si une valeur est fournie)
        if ($request->filled('groq_api_key')) {
            \App\Models\Setting::set('groq_api_key', $validated['groq_api_key'], 'string', 'ai');
        }
        if ($request->has('groq_model')) {
            \App\Models\Setting::set('groq_model', $validated['groq_model'] ?? 'llama-3.1-8b-instant', 'string', 'ai');
        }

        // Sauvegarder Google Search Console
        if ($request->has('google_credentials')) {
            $credentials = $validated['google_credentials'] ?? '';
            
            if (!empty($credentials)) {
                // Valider que c'est un JSON valide
                $decoded = json_decode($credentials, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return redirect()->back()
                        ->with('error', 'Le JSON des credentials Google Search Console est invalide: ' . json_last_error_msg())
                        ->withInput();
                }
                
                // Vérifier que c'est bien un service account
                if (!isset($decoded['type']) || $decoded['type'] !== 'service_account') {
                    return redirect()->back()
                        ->with('error', 'Les credentials doivent être de type "service_account"')
                        ->withInput();
                }
            }
            
            \App\Models\Setting::set('google_search_console_credentials', $credentials, 'json', 'seo');
        }

        \App\Models\Setting::clearCache();

        return redirect()->back()
            ->with('success', 'Configurations des APIs sauvegardées avec succès !');
    }

    /**
     * Tester une API spécifique
     */
    public function testApi(Request $request)
    {
        try {
            $api = $request->input('api');
            
            if (empty($api)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nom de l\'API non fourni'
                ], 400);
            }
            
            $results = [];
            
            switch ($api) {
                case 'serpapi':
                    try {
                        // Vérifier d'abord si la clé est configurée
                        $apiKey = \App\Models\Setting::where('key', 'serp_api_key')->value('value');
                        if (empty($apiKey)) {
                            $results = [
                                'status' => 'error',
                                'message' => 'Clé API SerpAPI non configurée. Veuillez configurer votre clé API d\'abord.'
                            ];
                            break;
                        }
                        
                        Log::info('Test SerpAPI - Test de connexion simple', ['key_length' => strlen($apiKey)]);
                        
                        // Test simple de connexion avec une requête Google Search basique
                        $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://serpapi.com/search.json', [
                            'engine' => 'google',
                            'q' => 'test',
                            'api_key' => $apiKey,
                            'num' => 1, // Juste 1 résultat pour économiser les quotas
                        ]);
                        
                        if ($response->successful()) {
                            $json = $response->json();
                            
                            // Vérifier que la réponse contient des données valides
                            if (isset($json['search_metadata']) || isset($json['organic_results'])) {
                                $results = [
                                    'status' => 'success',
                                    'message' => 'Connexion SerpAPI réussie. L\'API répond correctement.'
                                ];
                            } else {
                                $results = [
                                    'status' => 'warning',
                                    'message' => 'Connexion SerpAPI OK mais réponse inattendue. Vérifiez votre clé API.'
                                ];
                            }
                        } else {
                            $errorBody = $response->json();
                            $errorMessage = $errorBody['error'] ?? 'Erreur inconnue';
                            
                            // Détecter les erreurs spécifiques
                            if (str_contains($errorMessage, 'Invalid API key') || str_contains($errorMessage, 'Invalid api_key')) {
                                $results = [
                                    'status' => 'error',
                                    'message' => 'Clé API SerpAPI invalide. Vérifiez votre clé API.'
                                ];
                            } elseif (str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'limit')) {
                                $results = [
                                    'status' => 'warning',
                                    'message' => 'Quota SerpAPI dépassé. Vérifiez votre plan et vos limites.'
                                ];
                            } else {
                                $results = [
                                    'status' => 'error',
                                    'message' => 'Erreur SerpAPI: ' . $errorMessage
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Test SerpAPI failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $results = [
                            'status' => 'error',
                            'message' => 'Erreur de connexion SerpAPI: ' . $e->getMessage()
                        ];
                    }
                    break;
                    
                case 'gpt':
                    try {
                        // Vérifier d'abord si la clé est configurée
                        $apiKey = \App\Models\Setting::where('key', 'chatgpt_api_key')->value('value');
                        $enabled = \App\Models\Setting::where('key', 'chatgpt_enabled')->value('value');
                        $enabled = filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
                        
                        if (empty($apiKey)) {
                            $results = [
                                'status' => 'error',
                                'message' => 'Clé API ChatGPT non configurée. Veuillez configurer votre clé API d\'abord.'
                            ];
                            break;
                        }
                        
                        if (!$enabled) {
                            $results = [
                                'status' => 'warning',
                                'message' => 'ChatGPT est désactivé. Activez-le dans la configuration.'
                            ];
                            break;
                        }
                        
                        // Test simple avec AiService directement
                        $aiService = new \App\Services\AiService();
                        $testResult = $aiService->callAI('Réponds simplement "OK" si tu reçois ce message.', 'Tu es un assistant.', [
                            'max_tokens' => 10,
                            'temperature' => 0.1,
                            'timeout' => 30
                        ]);
                        
                        if ($testResult && isset($testResult['content']) && !empty($testResult['content'])) {
                            $results = [
                                'status' => 'success',
                                'message' => 'Connexion ChatGPT réussie. L\'API répond correctement.',
                                'data' => [
                                    'response_preview' => substr($testResult['content'], 0, 50) . '...'
                                ]
                            ];
                        } else {
                            $results = [
                                'status' => 'warning',
                                'message' => 'Connexion ChatGPT OK mais réponse invalide. Vérifiez la configuration.'
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error('Test ChatGPT failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $results = [
                            'status' => 'error',
                            'message' => 'Erreur ChatGPT: ' . $e->getMessage()
                        ];
                    }
                    break;
                    
                case 'google_indexing':
                    try {
                        // Vérifier d'abord si les credentials sont configurés
                        $credentials = \App\Models\Setting::where('key', 'google_search_console_credentials')->value('value');
                        if (empty($credentials)) {
                            $results = [
                                'status' => 'error',
                                'message' => 'Google Indexing non configuré. Veuillez configurer les credentials JSON.'
                            ];
                            break;
                        }
                        
                        // Vérifier que c'est du JSON valide
                        $decoded = json_decode($credentials, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $results = [
                                'status' => 'error',
                                'message' => 'Les credentials JSON sont invalides: ' . json_last_error_msg()
                            ];
                            break;
                        }
                        
                        if (!isset($decoded['type']) || $decoded['type'] !== 'service_account') {
                            $results = [
                                'status' => 'error',
                                'message' => 'Les credentials doivent être de type "service_account"'
                            ];
                            break;
                        }
                        
                        // Tester la connexion réelle
                        $googleService = new \App\Services\GoogleSearchConsoleService();
                        
                        // Vérifier d'abord si le service est configuré
                        $isConfigured = $googleService->isConfigured();
                        if (!$isConfigured) {
                            $results = [
                                'status' => 'error',
                                'message' => 'Les credentials sont présents mais le service ne peut pas être initialisé. Vérifiez le format des credentials et que toutes les clés requises sont présentes.'
                            ];
                            break;
                        }
                        
                        // Tester la connexion avec l'API Google
                        $testResult = $googleService->testConnection();
                        
                        // Tester différents protocoles et domaines
                        // Utiliser le domaine depuis les settings ou la requête actuelle
                        $siteUrl = \App\Models\Setting::get('site_url', null);
                        if (empty($siteUrl)) {
                            $siteUrl = config('app.url', request()->getSchemeAndHttpHost());
                        }
                        // S'assurer que l'URL a un protocole
                        if (!preg_match('/^https?:\/\//', $siteUrl)) {
                            $siteUrl = 'https://' . $siteUrl;
                        }
                        $domain = parse_url($siteUrl, PHP_URL_HOST) ?: parse_url(request()->getSchemeAndHttpHost(), PHP_URL_HOST) ?: request()->getHost();
                        
                        $testUrls = [
                            $domain, // Domaine nu sans protocole
                            'https://' . $domain,
                            'http://' . $domain,
                            'https://' . $domain . '/',
                            'http://' . $domain . '/',
                            'sc-domain:' . $domain,
                        ];
                        
                        $urlTests = [];
                        Log::info('Début tests URL Google Indexing', ['count' => count($testUrls)]);
                        
                        foreach ($testUrls as $testUrl) {
                            try {
                                Log::info('Test URL:', ['url' => $testUrl]);
                                $indexResult = $googleService->indexUrl($testUrl);
                                $urlTests[] = [
                                    'url' => $testUrl,
                                    'success' => $indexResult['success'] ?? false,
                                    'message' => $indexResult['message'] ?? 'Aucun message',
                                    'error_code' => $indexResult['error_code'] ?? null
                                ];
                                Log::info('Résultat test URL:', ['url' => $testUrl, 'success' => $indexResult['success'] ?? false]);
                            } catch (\Exception $e) {
                                Log::error('Exception test URL:', ['url' => $testUrl, 'error' => $e->getMessage()]);
                                $urlTests[] = [
                                    'url' => $testUrl,
                                    'success' => false,
                                    'message' => 'Exception: ' . $e->getMessage(),
                                    'error_code' => 'EXCEPTION'
                                ];
                            }
                        }
                        
                        Log::info('Tests URL terminés', ['count' => count($urlTests), 'tests' => $urlTests]);
                        
                        // S'assurer que url_tests est toujours présent, même s'il est vide
                        $responseData = [
                            'sites_count' => $testResult['sites_count'] ?? 0,
                            'site_found' => $testResult['site_found'] ?? false,
                            'site_permission' => $testResult['site_permission'] ?? null,
                            'site_url' => $testResult['site_url'] ?? null,
                            'url_tests' => $urlTests // Toujours inclure, même si vide
                        ];
                        
                        Log::info('Données de réponse préparées', ['url_tests_count' => count($urlTests)]);
                        
                        if ($testResult['success'] ?? false) {
                            $message = 'Connexion Google Indexing réussie.';
                            if (isset($testResult['warning']) && !empty($testResult['warning'])) {
                                $message .= ' ' . $testResult['warning'];
                                $results = [
                                    'status' => 'warning',
                                    'message' => $message,
                                    'data' => $responseData
                                ];
                            } else {
                                $results = [
                                    'status' => 'success',
                                    'message' => $message,
                                    'data' => $responseData
                                ];
                            }
                        } else {
                            $results = [
                                'status' => 'error',
                                'message' => 'Erreur de connexion: ' . ($testResult['message'] ?? 'Erreur inconnue'),
                                'data' => [
                                    'url_tests' => $urlTests // Toujours inclure
                                ]
                            ];
                        }
                        
                        Log::info('Réponse finale préparée', ['has_url_tests' => isset($results['data']['url_tests']), 'url_tests_count' => count($results['data']['url_tests'] ?? [])]);
                    } catch (\Exception $e) {
                        Log::error('Test Google Indexing failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $results = [
                            'status' => 'error',
                            'message' => 'Erreur Google Indexing: ' . $e->getMessage()
                        ];
                    }
                    break;
                    
                default:
                    $results = [
                        'status' => 'error',
                        'message' => 'API inconnue: ' . $api
                    ];
            }
            
            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Test API general error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur générale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stocker une image pour un mot-clé
     */
    public function storeKeywordImage(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $image = $request->file('image');
            $keyword = trim($validated['keyword']);
            
            // Créer le dossier s'il n'existe pas
            $uploadDir = public_path('images/keywords');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Nom du fichier
            $filename = 'keyword-' . Str::slug($keyword) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = 'images/keywords/' . $filename;
            
            // Déplacer l'image
            $image->move($uploadDir, $filename);
            
            // Créer l'entrée dans la base de données
            $keywordImage = KeywordImage::create([
                'keyword' => $keyword,
                'image_path' => $imagePath,
                'title' => $validated['title'] ?? null,
                'is_active' => true,
                'display_order' => 0,
            ]);
            
            return redirect()->back()
                ->with('success', "✅ Image ajoutée avec succès pour le mot-clé \"{$keyword}\"");
                
        } catch (\Exception $e) {
            Log::error('Erreur ajout image mot-clé', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de l\'ajout de l\'image : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une image de mot-clé
     */
    public function destroyKeywordImage(KeywordImage $keywordImage)
    {
        try {
            // Supprimer le fichier physique
            $imagePath = public_path($keywordImage->image_path);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Supprimer l'entrée de la base de données
            $keywordImage->delete();
            
            return redirect()->back()
                ->with('success', "✅ Image supprimée avec succès");
                
        } catch (\Exception $e) {
            Log::error('Erreur suppression image mot-clé', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de la suppression de l\'image : ' . $e->getMessage());
        }
    }
    
    /**
     * Indexer un article manuellement
     */
    public function indexArticle(Request $request)
    {
        try {
            // Valider manuellement (pas avec validate() qui cause l'erreur pattern)
            $url = $request->input('url');
            
            if (empty($url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL manquante'
                ], 400);
            }
            
            // Vérifier que c'est une URL valide
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL invalide : ' . $url
                ], 400);
            }
            
            Log::info('Tentative indexation manuelle article', [
                'url' => $url,
                'user' => auth()->user()->name ?? 'admin'
            ]);
            
            // Vérifier Google configuré
            $googleService = new \App\Services\GoogleSearchConsoleService();
            
            if (!$googleService->isConfigured()) {
                Log::warning('Google Search Console non configuré pour indexation manuelle');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Google Search Console non configuré. Allez dans /admin/indexation pour configurer les credentials JSON.'
                ], 400);
            }
            
            // Indexer l'URL via GoogleIndexingService (pas GoogleSearchConsoleService)
            $indexingService = new \App\Services\GoogleIndexingService();
            $result = $indexingService->indexUrl($url);
            
            if ($result) {
                // Succès
                Log::info('Article indexé manuellement avec succès', [
                    'url' => $url,
                    'user' => auth()->user()->name ?? 'admin'
                ]);
                
                // Enregistrer dans UrlIndexationStatus
                try {
                    \App\Models\UrlIndexationStatus::recordSubmission($url);
                } catch (\Exception $e) {
                    Log::warning('Erreur enregistrement soumission', ['error' => $e->getMessage()]);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Demande d\'indexation envoyée avec succès à Google',
                    'url' => $url
                ]);
            }
            
            Log::warning('Échec indexation manuelle article', [
                'url' => $url,
                'result' => $result
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la demande d\'indexation. Vérifiez les logs Laravel.'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Exception indexation manuelle article', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->input('url')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique : ' . $e->getMessage()
            ], 500);
        }
    }
}
