<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Ad;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KeywordCitiesController extends Controller
{
    /**
     * Afficher la page Mot-clé + Villes
     */
    public function index()
    {
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
        
        return view('admin.ads.keyword-cities', compact(
            'favoriteCities', 
            'regions', 
            'totalCities', 
            'favoriteCount', 
            'totalAds'
        ));
    }
    
    /**
     * Générer des annonces Mot-clé + Villes
     */
    public function generate(Request $request)
    {
        $request->validate([
            'keywords' => 'required|string|max:500',
            'cities' => 'required|array|min:1',
            'cities.*' => 'exists:cities,id',
            'count_per_city' => 'integer|min:1|max:10'
        ]);
        
        try {
            $keywords = $request->keywords;
            $cityIds = $request->cities;
            $countPerCity = $request->count_per_city ?? 1;
            
            // Récupérer les villes
            $cities = City::whereIn('id', $cityIds)->get();
            
            $generatedCount = 0;
            $errors = [];
            
            foreach ($cities as $city) {
                for ($i = 0; $i < $countPerCity; $i++) {
                    try {
                        // Générer le contenu via IA
                        $aiContent = $this->generateAIContent($keywords, $city);
                        
                        // Créer l'annonce
                        $ad = Ad::create([
                            'title' => $aiContent['title'],
                            'content' => $aiContent['content'],
                            'meta_title' => $aiContent['meta_title'],
                            'meta_description' => $aiContent['meta_description'],
                            'meta_keywords' => $aiContent['meta_keywords'],
                            'city_id' => $city->id,
                            'service_slug' => null,
                            'generation_type' => 'keyword_cities',
                            'status' => 'draft',
                            'featured_image' => null,
                        ]);
                        
                        $generatedCount++;
                        
                        // Pause pour éviter de surcharger l'API
                        usleep(500000); // 0.5 seconde
                        
                    } catch (\Exception $e) {
                        $errors[] = "Erreur pour {$city->name}: " . $e->getMessage();
                        Log::error('Keyword cities generation error', [
                            'city' => $city->name,
                            'keywords' => $keywords,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $message = "Génération terminée : {$generatedCount} annonces créées";
            if (!empty($errors)) {
                $message .= " avec " . count($errors) . " erreurs";
            }
            
            return back()->with('success', $message)
                        ->with('errors', $errors);
                        
        } catch (\Exception $e) {
            Log::error('Keyword cities generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }
    
    /**
     * Générer le contenu via IA pour Mot-clé + Villes
     */
    private function generateAIContent($keywords, $city)
    {
        $apiKey = Setting::get('chatgpt_api_key');
        
        if (!$apiKey) {
            throw new \Exception('Clé API ChatGPT non configurée');
        }
        
        $prompt = "Génère une annonce SEO optimisée pour les mots-clés : {$keywords} à {$city->name} ({$city->postal_code}). 
        
        Utilise ces mots-clés de manière naturelle dans le contenu.
        Focus sur la localisation à {$city->name} dans la région {$city->region}.
        
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
        Optimise pour les mots-clés : {$keywords}, {$city->name}, {$city->region}.
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
            throw new \Exception('Erreur API ChatGPT : ' . $response->body());
        }
        
        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        // Parser le contenu généré
        return $this->parseGeneratedContent($content, $keywords, $city);
    }
    
    /**
     * Parser le contenu généré par l'IA
     */
    private function parseGeneratedContent($content, $keywords, $city)
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
            'title' => "{$keywords} à {$city->name} - Expert en rénovation",
            'content' => "<h1>{$keywords} à {$city->name}</h1><p>Service professionnel de {$keywords} à {$city->name}, {$city->region}.</p>",
            'meta_title' => "{$keywords} à {$city->name} - Devis gratuit",
            'meta_description' => "Expert en {$keywords} à {$city->name}. Devis gratuit et intervention rapide.",
            'meta_keywords' => "{$keywords}, {$city->name}, {$city->region}, rénovation, devis"
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