<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ServiceCitiesController extends Controller
{
    /**
     * Afficher la page Service + Villes
     */
    public function index()
    {
        // Récupérer les services depuis les settings
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        // Récupérer les villes favorites
        $favoriteCities = City::where('is_favorite', true)
            ->orderBy('name')
            ->get();
        
        // Récupérer toutes les régions pour le filtrage
        $regions = City::distinct()
            ->pluck('region')
            ->filter()
            ->sort()
            ->values();
        
        // Statistiques
        $totalCities = City::count();
        $favoriteCount = $favoriteCities->count();
        $totalAds = Ad::count();
        
        return view('admin.ads.service-cities', compact(
            'services', 
            'favoriteCities', 
            'regions', 
            'totalCities', 
            'favoriteCount', 
            'totalAds'
        ));
    }
    
    /**
     * Générer des annonces Service + Villes
     */
    public function generate(Request $request)
    {
        $request->validate([
            'service_slug' => 'required|string',
            'cities' => 'required|array|min:1',
            'cities.*' => 'exists:cities,id'
        ]);
        
        try {
            $serviceSlug = $request->service_slug;
            $cityIds = $request->cities;
            
            // Récupérer les villes
            $cities = City::whereIn('id', $cityIds)->get();
            
            // Récupérer le service
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            $service = collect($services)->firstWhere('slug', $serviceSlug);
            
            if (!$service) {
                return back()->with('error', 'Service non trouvé');
            }
            
            $generatedCount = 0;
            $errors = [];
            
            // Log des paramètres de génération
            Log::info('Starting service cities generation', [
                'service' => $serviceSlug,
                'cities_count' => $cities->count(),
                'total_expected' => $cities->count()
            ]);
            
            foreach ($cities as $city) {
                try {
                    Log::info('Generating ad for city', [
                        'city' => $city->name,
                        'service' => $serviceSlug
                    ]);
                    
                    // Générer le contenu via IA
                    $aiContent = $this->generateAIContent($service, $city);
                    
                    Log::info('AI content generated', [
                        'city' => $city->name,
                        'title' => $aiContent['title'] ?? 'N/A',
                        'content_length' => strlen($aiContent['content'] ?? '')
                    ]);
                    
                    // Créer l'annonce
                    $ad = Ad::create([
                        'title' => $aiContent['title'],
                        'content' => $aiContent['content'],
                        'meta_title' => $aiContent['meta_title'],
                        'meta_description' => $aiContent['meta_description'],
                        'meta_keywords' => $aiContent['meta_keywords'],
                        'city_id' => $city->id,
                        'service_slug' => $serviceSlug,
                        'generation_type' => 'service_cities',
                        'status' => 'draft',
                        'featured_image' => $service['image'] ?? null,
                    ]);
                    
                    $generatedCount++;
                    
                    Log::info('Ad created successfully', [
                        'ad_id' => $ad->id,
                        'city' => $city->name,
                        'title' => $ad->title
                    ]);
                    
                    // Pause pour éviter de surcharger l'API
                    usleep(500000); // 0.5 seconde
                    
                } catch (\Exception $e) {
                    $errorMsg = "Erreur pour {$city->name}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    
                    Log::error('Service cities generation error', [
                        'city' => $city->name,
                        'service' => $serviceSlug,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            $message = "Génération terminée : {$generatedCount} annonces créées";
            if (!empty($errors)) {
                $message .= " avec " . count($errors) . " erreurs";
            }
            
            return back()->with('success', $message)
                        ->with('errors', $errors);
                        
        } catch (\Exception $e) {
            Log::error('Service cities generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }
    
    /**
     * Générer le contenu via IA pour Service + Villes
     */
    private function generateAIContent($service, $city)
    {
        $apiKey = Setting::get('chatgpt_api_key');
        
        if (!$apiKey) {
            Log::error('ChatGPT API key not configured');
            throw new \Exception('Clé API ChatGPT non configurée');
        }
        
        Log::info('Generating AI content', [
            'service' => $service['name'] ?? 'N/A',
            'city' => $city->name,
            'api_key_length' => strlen($apiKey)
        ]);
        
        $prompt = "Génère une annonce SEO optimisée pour {$service['name']} à {$city->name} ({$city->postal_code}). 
        
        Focus sur le service {$service['name']} dans la région {$city->region}.
        
        Format de réponse JSON :
        {
            \"title\": \"Titre SEO optimisé (max 60 caractères)\",
            \"content\": \"Contenu HTML complet de l'annonce\",
            \"meta_title\": \"Titre meta SEO (max 60 caractères)\",
            \"meta_description\": \"Description meta SEO (max 160 caractères)\",
            \"meta_keywords\": \"Mots-clés SEO séparés par virgules\"
        }
        
        Le contenu doit être en HTML avec des balises appropriées (h1, h2, p, ul, li, etc.).
        Inclut des informations spécifiques à {$city->name} et {$city->region}.
        Optimise pour les mots-clés : {$service['name']}, {$city->name}, {$city->region}.
        ";
        
        $response = Http::timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Tu es un expert en marketing digital et SEO pour les entreprises de rénovation. Tu génères du contenu optimisé pour les moteurs de recherche.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7
        ], [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ]);
        
        if (!$response->successful()) {
            Log::error('ChatGPT API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'service' => $service['name'] ?? 'N/A',
                'city' => $city->name
            ]);
            throw new \Exception('Erreur API ChatGPT : ' . $response->body());
        }
        
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        Log::info('ChatGPT API response', [
            'service' => $service['name'] ?? 'N/A',
            'city' => $city->name,
            'content_length' => strlen($content),
            'response_status' => $response->status()
        ]);
        
        // Parser le contenu généré
        return $this->parseGeneratedContent($content, $service, $city);
    }
    
    /**
     * Parser le contenu généré par l'IA
     */
    private function parseGeneratedContent($content, $service, $city)
    {
        // Essayer de parser le JSON
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed = json_decode($jsonContent, true);
            
            if ($parsed && isset($parsed['title'])) {
                return $parsed;
            }
        }
        
        // Fallback si le JSON n'est pas valide
        return [
            'title' => "{$service['name']} à {$city->name} - Expert en rénovation",
            'content' => "<h1>{$service['name']} à {$city->name}</h1><p>Service professionnel de {$service['name']} à {$city->name}, {$city->region}.</p>",
            'meta_title' => "{$service['name']} à {$city->name} - Devis gratuit",
            'meta_description' => "Expert en {$service['name']} à {$city->name}. Devis gratuit et intervention rapide.",
            'meta_keywords' => "{$service['name']}, {$city->name}, {$city->region}, rénovation, devis"
        ];
    }
    
    /**
     * Récupérer les villes par région (AJAX)
     */
    public function getCitiesByRegion(Request $request)
    {
        $region = $request->get('region');
        
        if (!$region) {
            return response()->json(['cities' => []]);
        }
        
        $cities = City::where('region', $region)
            ->orderBy('name')
            ->get(['id', 'name', 'postal_code', 'department', 'is_favorite']);
            
        return response()->json(['cities' => $cities]);
    }
    
    /**
     * Récupérer les villes favorites (AJAX)
     */
    public function getFavoriteCities()
    {
        $cities = City::where('is_favorite', true)
            ->orderBy('name')
            ->get(['id', 'name', 'postal_code', 'department', 'region']);
            
        return response()->json(['cities' => $cities]);
    }
}