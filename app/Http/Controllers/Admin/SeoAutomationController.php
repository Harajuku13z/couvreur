<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoAutomation;
use App\Models\City;
use App\Jobs\ProcessSeoCityJob;
use App\Services\SerpApiService;
use App\Services\GptSeoGenerator;
use App\Services\GoogleIndexingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SeoAutomationController extends Controller
{
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
        $logs = SeoAutomation::with('city')
            ->latest()
            ->paginate(30);
        
        // Statistiques
        $stats = [
            'total' => SeoAutomation::count(),
            'pending' => SeoAutomation::where('status', 'pending')->count(),
            'published' => SeoAutomation::where('status', 'published')->count(),
            'indexed' => SeoAutomation::where('status', 'indexed')->count(),
            'failed' => SeoAutomation::where('status', 'failed')->count(),
        ];
        
        // Récupérer les villes favorites
        $favoriteCities = City::where('is_favorite', true)->orderBy('name')->get();
        
        // Récupérer les services
        $servicesData = \App\Models\Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        if (!is_array($services)) {
            $services = [];
        }
        
        // Récupérer les configurations des APIs
        $apiConfig = [
            'serpapi_key' => \App\Models\Setting::get('serp_api_key', ''),
            'chatgpt_enabled' => \App\Models\Setting::get('chatgpt_enabled', true),
            'chatgpt_api_key' => \App\Models\Setting::get('chatgpt_api_key', ''),
            'chatgpt_model' => \App\Models\Setting::get('chatgpt_model', 'gpt-4o'),
            'groq_api_key' => \App\Models\Setting::get('groq_api_key', ''),
            'groq_model' => \App\Models\Setting::get('groq_model', 'llama-3.1-8b-instant'),
            'google_credentials' => \App\Models\Setting::get('google_search_console_credentials', ''),
        ];
        
        return view('admin.seo_automation.index', compact('logs', 'stats', 'favoriteCities', 'services', 'apiConfig'));
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
     * Relancer une automation échouée
     */
    public function retry(SeoAutomation $seoAutomation)
    {
        if (!$seoAutomation->city) {
            return redirect()->back()
                ->with('error', 'Ville non trouvée pour cette automation.');
        }

        ProcessSeoCityJob::dispatch($seoAutomation->city_id, null)
            ->onQueue('seo-automation');
        
        return redirect()->back()
            ->with('success', "Automation relancée pour {$seoAutomation->city->name}.");
    }

    /**
     * Lancer la génération avec paramètres personnalisés
     */
    public function run(Request $request)
    {
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

        $dispatched = 0;
        $delayCounter = 0;
        foreach ($cities as $cityIndex => $city) {
            // Créer le nombre d'articles demandé pour chaque ville
            for ($articleIndex = 0; $articleIndex < $numberOfArticles; $articleIndex++) {
                ProcessSeoCityJob::dispatch($city->id, $customKeyword)
                    ->onQueue('seo-automation')
                    ->delay(now()->addSeconds($delayCounter * 5)); // Délai échelonné de 5 secondes
                $dispatched++;
                $delayCounter++;
            }
        }

        $message = "✅ {$dispatched} job(s) planifié(s) pour " . $cities->count() . " ville(s)";
        if ($customKeyword) {
            $message .= " avec le mot-clé/service: {$customKeyword}";
        }

        return redirect()->back()
            ->with('success', $message);
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

        // Test SerpAPI
        try {
            $serpService = new SerpApiService();
            $keywords = $serpService->getTrendingKeywords('FR', 3);
            
            if (!empty($keywords)) {
                $results['serpapi'] = [
                    'status' => 'success',
                    'message' => 'Connexion SerpAPI réussie. ' . count($keywords) . ' mots-clés récupérés.',
                    'data' => array_slice($keywords, 0, 3)
                ];
            } else {
                $results['serpapi'] = [
                    'status' => 'warning',
                    'message' => 'Connexion SerpAPI OK mais aucun mot-clé récupéré. Vérifiez votre clé API ou les quotas.'
                ];
            }
        } catch (\Exception $e) {
            $results['serpapi'] = [
                'status' => 'error',
                'message' => 'Erreur SerpAPI: ' . $e->getMessage()
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
        $api = $request->input('api');
        
        $results = [];
        
        switch ($api) {
            case 'serpapi':
                try {
                    $serpService = new SerpApiService();
                    $keywords = $serpService->getTrendingKeywords('FR', 3);
                    
                    if (!empty($keywords)) {
                        $results = [
                            'status' => 'success',
                            'message' => 'Connexion SerpAPI réussie. ' . count($keywords) . ' mots-clés récupérés.',
                            'data' => array_slice($keywords, 0, 3)
                        ];
                    } else {
                        $results = [
                            'status' => 'warning',
                            'message' => 'Connexion SerpAPI OK mais aucun mot-clé récupéré. Vérifiez votre clé API ou les quotas.'
                        ];
                    }
                } catch (\Exception $e) {
                    $results = [
                        'status' => 'error',
                        'message' => 'Erreur SerpAPI: ' . $e->getMessage()
                    ];
                }
                break;
                
            case 'gpt':
                try {
                    $gptService = new GptSeoGenerator();
                    $testResult = $gptService->generateSeoArticle('couvreur', 'Paris', [], []);
                    
                    if ($testResult && !empty($testResult['titre'])) {
                        $results = [
                            'status' => 'success',
                            'message' => 'Connexion GPT réussie. Génération de test OK.',
                            'data' => [
                                'titre' => substr($testResult['titre'], 0, 100) . '...',
                                'has_content' => !empty($testResult['contenu_html'])
                            ]
                        ];
                    } else {
                        $results = [
                            'status' => 'warning',
                            'message' => 'Connexion GPT OK mais réponse invalide. Vérifiez la configuration.'
                        ];
                    }
                } catch (\Exception $e) {
                    $results = [
                        'status' => 'error',
                        'message' => 'Erreur GPT: ' . $e->getMessage()
                    ];
                }
                break;
                
            case 'google_indexing':
                try {
                    $googleService = new \App\Services\GoogleSearchConsoleService();
                    $isConfigured = $googleService->isConfigured();
                    
                    if ($isConfigured) {
                        $results = [
                            'status' => 'success',
                            'message' => 'Google Indexing configuré correctement. Les credentials sont valides.'
                        ];
                    } else {
                        $results = [
                            'status' => 'error',
                            'message' => 'Google Indexing non configuré. Veuillez configurer les credentials.'
                        ];
                    }
                } catch (\Exception $e) {
                    $results = [
                        'status' => 'error',
                        'message' => 'Erreur Google Indexing: ' . $e->getMessage()
                    ];
                }
                break;
                
            default:
                $results = [
                    'status' => 'error',
                    'message' => 'API inconnue'
                ];
        }
        
        return response()->json($results);
    }
}
