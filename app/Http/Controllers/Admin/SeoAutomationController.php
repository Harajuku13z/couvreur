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
        
        return view('admin.seo_automation.index', compact('logs', 'stats', 'favoriteCities', 'services', 'apiConfig', 'automationEnabled', 'customKeywords', 'companyDescription'));
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
        
        $automationTime = \App\Models\Setting::where('key', 'seo_automation_time')->value('value') ?? '04:00';
        
        $message = $newStatus 
            ? "✅ Automatisation SEO activée. Les articles seront générés automatiquement chaque jour à {$automationTime}."
            : '⏸️ Automatisation SEO mise en pause. Les générations automatiques sont désactivées.';
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Sauvegarder l'heure de publication automatique
     */
    public function saveTime(Request $request)
    {
        $validated = $request->validate([
            'time' => ['required', 'regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/'],
        ]);
        
        \App\Models\Setting::set('seo_automation_time', $validated['time'], 'string', 'seo');
        
        return redirect()->back()
            ->with('success', "✅ Heure de publication automatique mise à jour : {$validated['time']}");
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
            
            // Nom du fichier
            $filename = 'og-blog.jpg';
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
            
            // Mettre à jour le setting
            \App\Models\Setting::set('default_blog_og_image', $imagePath, 'string', 'seo');
            
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
            
            // Vérifier que SerpAPI est configuré
            $serpApiKey = \App\Models\Setting::where('key', 'serp_api_key')->value('value');
            if (empty($serpApiKey)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'SerpAPI n\'est pas configuré. Veuillez configurer votre clé API SerpAPI dans la section "Configuration des APIs".'
                ], 400);
            }
            
            Log::info('Génération mots-clés via SerpAPI', [
                'description_length' => strlen($companyDescription)
            ]);
            
            $serpService = new \App\Services\SerpApiService();
            $keywords = [];
            
            // Extraire les mots-clés principaux de la description
            // Prendre les 3-5 premiers mots significatifs
            $words = preg_split('/\s+/', $companyDescription);
            $mainKeywords = array_filter($words, function($word) {
                return strlen($word) > 3 && !in_array(strtolower($word), ['pour', 'avec', 'dans', 'sont', 'cette', 'notre', 'votre', 'leurs', 'leurs']);
            });
            $mainKeywords = array_slice(array_values($mainKeywords), 0, 5);
            
            // Méthode 1: Google Autocomplete pour chaque mot-clé principal
            foreach ($mainKeywords as $mainKeyword) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://serpapi.com/search.json', [
                        'engine' => 'google_autocomplete',
                        'q' => $mainKeyword,
                        'hl' => 'fr',
                        'api_key' => $serpApiKey,
                    ]);
                    
                    if ($response->successful()) {
                        $json = $response->json();
                        if (isset($json['suggestions']) && is_array($json['suggestions'])) {
                            foreach ($json['suggestions'] as $suggestion) {
                                $keyword = $suggestion['value'] ?? null;
                                if ($keyword && !in_array($keyword, $keywords)) {
                                    $keywords[] = $keyword;
                                }
                                if (count($keywords) >= 30) break 2; // Limiter à 30 mots-clés
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Erreur SerpAPI Autocomplete', [
                        'keyword' => $mainKeyword,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Méthode 2: Google Search avec related queries pour les mots-clés principaux
            if (count($keywords) < 20) {
                foreach (array_slice($mainKeywords, 0, 3) as $mainKeyword) {
                    try {
                        $related = $serpService->getRelatedQueries($mainKeyword, 10);
                        foreach ($related as $query) {
                            if (!in_array($query, $keywords)) {
                                $keywords[] = $query;
                            }
                            if (count($keywords) >= 30) break 2;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erreur SerpAPI Related Queries', [
                            'keyword' => $mainKeyword,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Méthode 3: Recherche Google standard pour obtenir related_searches et people_also_ask
            if (count($keywords) < 20 && !empty($mainKeywords)) {
                $searchQuery = implode(' ', array_slice($mainKeywords, 0, 3));
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://serpapi.com/search.json', [
                        'engine' => 'google',
                        'q' => $searchQuery,
                        'hl' => 'fr',
                        'api_key' => $serpApiKey,
                        'num' => 5, // Juste pour récupérer les sections related
                    ]);
                    
                    if ($response->successful()) {
                        $json = $response->json();
                        
                        // Récupérer depuis related_searches
                        if (isset($json['related_searches']) && is_array($json['related_searches'])) {
                            foreach ($json['related_searches'] as $search) {
                                $query = $search['query'] ?? null;
                                if ($query && !in_array($query, $keywords)) {
                                    $keywords[] = $query;
                                }
                                if (count($keywords) >= 30) break;
                            }
                        }
                        
                        // Récupérer depuis people_also_ask
                        if (count($keywords) < 30 && isset($json['people_also_ask']) && is_array($json['people_also_ask'])) {
                            foreach ($json['people_also_ask'] as $ask) {
                                $question = $ask['question'] ?? null;
                                if ($question && !in_array($question, $keywords)) {
                                    // Extraire le mot-clé principal de la question
                                    $keyword = preg_replace('/\?$/', '', $question);
                                    $keyword = preg_replace('/^(comment|quand|où|pourquoi|combien|quel|quelle|quels|quelles)\s+/i', '', $keyword);
                                    if ($keyword && !in_array($keyword, $keywords)) {
                                        $keywords[] = $keyword;
                                    }
                                }
                                if (count($keywords) >= 30) break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Erreur SerpAPI Google Search', [
                        'query' => $searchQuery,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Nettoyer et formater les mots-clés
            $keywords = array_map(function($keyword) {
                // Enlever les caractères spéciaux en début/fin
                $keyword = trim($keyword);
                // Limiter la longueur
                if (strlen($keyword) > 80) {
                    $keyword = substr($keyword, 0, 77) . '...';
                }
                return $keyword;
            }, $keywords);
            
            // Supprimer les doublons et les mots-clés trop courts
            $keywords = array_filter($keywords, function($keyword) {
                return strlen($keyword) >= 3 && strlen($keyword) <= 80;
            });
            $keywords = array_values(array_unique($keywords));
            
            // Limiter à 30 mots-clés maximum
            $keywords = array_slice($keywords, 0, 30);
            
            if (empty($keywords)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun mot-clé généré. Vérifiez votre clé API SerpAPI et que la description de l\'entreprise contient des mots-clés pertinents.'
                ], 500);
            }
            
            Log::info('Mots-clés générés via SerpAPI', [
                'count' => count($keywords),
                'keywords_preview' => array_slice($keywords, 0, 5)
            ]);
            
            return response()->json([
                'status' => 'success',
                'keywords' => $keywords,
                'message' => count($keywords) . ' mots-clés générés avec succès via SerpAPI.'
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
     * Sauvegarder les mots-clés personnalisés
     */
    public function saveKeywords(Request $request)
    {
        $validated = $request->validate([
            'keywords' => 'required|array',
            'keywords.*' => 'string|max:255',
        ]);
        
        $keywords = array_filter(array_map('trim', $validated['keywords']));
        $keywords = array_values(array_unique($keywords)); // Supprimer les doublons
        
        \App\Models\Setting::set('seo_custom_keywords', json_encode($keywords), 'json', 'seo');
        
        return response()->json([
            'status' => 'success',
            'message' => count($keywords) . ' mots-clés sauvegardés avec succès.',
            'keywords' => $keywords
        ]);
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
