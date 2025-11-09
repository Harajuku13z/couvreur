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
                        
                        Log::info('Test SerpAPI - Clé API trouvée', ['key_length' => strlen($apiKey)]);
                        
                        $serpService = new SerpApiService();
                        $keywords = $serpService->getTrendingKeywords('FR', 3);
                        
                        Log::info('Test SerpAPI - Résultats', ['keywords_count' => count($keywords)]);
                        
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
                        Log::error('Test SerpAPI failed', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $results = [
                            'status' => 'error',
                            'message' => 'Erreur SerpAPI: ' . $e->getMessage()
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
                        $siteUrl = config('app.url', 'https://couvreur-chevigny-saint-sauveur.fr');
                        $domain = parse_url($siteUrl, PHP_URL_HOST) ?: 'couvreur-chevigny-saint-sauveur.fr';
                        
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
}
