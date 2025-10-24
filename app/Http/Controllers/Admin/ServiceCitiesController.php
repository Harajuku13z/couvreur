<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            'selected_cities_json' => 'required|string'
        ]);
        
        try {
            $serviceSlug = $request->service_slug;
            
            // Décoder le JSON des villes sélectionnées
            $cityIds = json_decode($request->selected_cities_json, true);
            
            if (!is_array($cityIds) || empty($cityIds)) {
                return back()->with('error', 'Aucune ville sélectionnée');
            }
            
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
                        'keyword' => $service['name'] . ' ' . $city->name, // Mot-clé basé sur le service et la ville
                        'city_id' => $city->id,
                        'slug' => \Str::slug($aiContent['title'] . '-' . $city->name),
                        'status' => 'published',
                        'meta_title' => $aiContent['meta_title'],
                        'meta_description' => $aiContent['meta_description'],
                        'content_html' => $aiContent['content'],
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
            
            return redirect()->route('admin.admin.ads.index')
                        ->with('success', $message)
                        ->with('errors', $errors);
                        
        } catch (\Exception $e) {
            Log::error('Service cities generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return redirect()->route('admin.admin.ads.index')
                        ->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }
    
    /**
     * Générer le contenu via IA pour Service + Villes
     */
    private function generateAIContent($service, $city)
    {
        // Récupérer la clé API depuis la base de données
        $apiKey = Setting::get('chatgpt_api_key');
        
        // Si pas trouvée, essayer directement en base
        if (!$apiKey) {
            $setting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
            $apiKey = $setting ? $setting->value : null;
        }
        
        if (!$apiKey) {
            Log::error('ChatGPT API key not configured');
            throw new \Exception('Clé API ChatGPT non configurée. Veuillez la configurer dans /config');
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
            \"content\": \"Contenu HTML complet avec structure en 2 colonnes\",
            \"meta_title\": \"Titre meta SEO (max 60 caractères)\",
            \"meta_description\": \"Description meta SEO (max 160 caractères)\",
            \"meta_keywords\": \"Mots-clés SEO séparés par virgules\"
        }
        
        Le contenu HTML doit suivre EXACTEMENT cette structure :
        <div class=\"grid md:grid-cols-2 gap-8\">
          <div class=\"space-y-6\">
            <div class=\"space-y-4\">
              <p class=\"text-lg leading-relaxed\">Paragraphe d'introduction avec {$service['name']} à {$city->name}</p>
              <p class=\"text-lg leading-relaxed\">Paragraphe sur l'expertise et la qualité</p>
              <p class=\"text-lg leading-relaxed\">Paragraphe sur l'approche personnalisée</p>
            </div>
            
            <div class=\"bg-blue-50 p-6 rounded-lg\">
              <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Notre Engagement Qualité</h3>
              <p class=\"leading-relaxed mb-3\">Chez JD RENOVATION SERVICE, nous garantissons la satisfaction totale.</p>
              <p class=\"leading-relaxed\">Description des matériaux et techniques utilisés pour {$service['name']}.</p>
            </div>
            
            <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$service['name']}</h3>
            <ul class=\"space-y-3\">
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">🔧</span><span><strong>Prestation 1</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">🏠</span><span><strong>Prestation 2</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">🛠️</span><span><strong>Prestation 3</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">⚡</span><span><strong>Prestation 4</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">🎨</span><span><strong>Prestation 5</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">🔒</span><span><strong>Prestation 6</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">✨</span><span><strong>Prestation 7</strong></span></li>
              <li class=\"flex items-start\"><span class=\"text-blue-600 mr-3\">🛡️</span><span><strong>Prestation 8</strong></span></li>
            </ul>
            
            <div class=\"bg-green-50 p-6 rounded-lg\">
              <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi Choisir Notre Entreprise</h3>
              <p class=\"leading-relaxed\">Reconnus localement pour notre expertise en {$service['name']} à {$city->name}.</p>
            </div>
          </div>
          
          <div class=\"space-y-6\">
            <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise Locale</h3>
            <p class=\"leading-relaxed\">Avec une connaissance approfondie de {$city->name} et du {$city->region}, nous sommes votre partenaire de confiance pour {$service['name']}.</p>
            
            <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
              <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un Devis ?</h4>
              <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour vos {$service['name']}.</p>
              <a href=\"https://www.jd-renovation-service.fr/form/propertyType\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
            </div>
            
            <div class=\"bg-gray-50 p-6 rounded-lg\">
              <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
              <ul class=\"space-y-2 text-sm\">
                <li class=\"flex items-center\"><span>Service disponible à {$city->name} et dans toute la région du {$city->region}</span></li>
                <li class=\"flex items-center\"><span>Délais d'intervention rapides pour vos {$service['name']}</span></li>
                <li class=\"flex items-center\"><span>Équipe qualifiée et expérimentée</span></li>
                <li class=\"flex items-center\"><span>Conseils personnalisés pour le choix des matériaux</span></li>
                <li class=\"flex items-center\"><span>Respect des normes et réglementations en vigueur</span></li>
                <li class=\"flex items-center\"><span>Devis clair et détaillé, adapté à vos besoins</span></li>
              </ul>
            </div>
          </div>
        </div>
        
        IMPORTANT : 
        - Utilise le service {$service['name']} naturellement dans le contenu
        - Adapte le contenu à {$city->name} et {$city->region}
        - Génère 8 prestations spécifiques au service {$service['name']}
        - Garde la structure HTML exacte avec les classes CSS
        - Inclut des informations locales spécifiques à {$city->name}
        ";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
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