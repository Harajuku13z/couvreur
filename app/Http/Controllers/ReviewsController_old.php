<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReviewsController extends Controller
{
    /**
     * Afficher la page de gestion des avis
     */
    public function index()
    {
        $reviews = Review::latest()->paginate(10);
        
        // Statistiques
        $stats = [
            'total' => Review::count(),
            'active' => Review::where('is_active', true)->count(),
            'inactive' => Review::where('is_active', false)->count(),
            'avg_rating' => Review::where('is_active', true)->avg('rating') ?? 0,
        ];
        
        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Afficher le formulaire de création d'avis
     */
    public function create()
    {
        return view('admin.reviews.create');
    }

    /**
     * Enregistrer un nouvel avis
     */
    public function store(Request $request)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'is_active' => 'boolean',
            'author_location' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        $review = Review::create([
            'author_name' => $request->author_name,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'is_active' => $request->boolean('is_active', true),
            'author_location' => $request->author_location,
            'source' => $request->source ?? 'manual',
            'review_date' => now(),
        ]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis créé avec succès !');
    }

    /**
     * Afficher un avis spécifique
     */
    public function show(Review $review)
    {
        return view('admin.reviews.show', compact('review'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Review $review)
    {
        return view('admin.reviews.edit', compact('review'));
    }

    /**
     * Mettre à jour un avis
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'is_active' => 'boolean',
            'author_location' => 'nullable|string|max:255',
        ]);

        $review->update([
            'author_name' => $request->author_name,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'is_active' => $request->boolean('is_active'),
            'author_location' => $request->author_location,
        ]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis mis à jour avec succès !');
    }

    /**
     * Supprimer un avis
     */
    public function destroy(Review $review)
    {
        $review->delete();
        
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis supprimé avec succès !');
    }

    /**
     * Activer/Désactiver un avis
     */
    public function toggle(Review $review)
    {
        $review->update(['is_active' => !$review->is_active]);
        
        $status = $review->is_active ? 'activé' : 'désactivé';
        
        return redirect()->back()
            ->with('success', "Avis {$status} avec succès !");
    }

    /**
     * Afficher la configuration Google
     */
    public function googleConfig()
    {
        $googlePlaceId = setting('google_place_id');
        $outscraperApiKey = setting('outscraper_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);
        
        return view('admin.reviews.google-config', compact('googlePlaceId', 'outscraperApiKey', 'autoApprove'));
    }

    /**
     * Sauvegarder la configuration Google
     */
    public function saveGoogleConfig(Request $request)
    {
        $request->validate([
            'google_place_id' => 'required|string',
            'outscraper_api_key' => 'required|string',
            'auto_approve_google' => 'boolean',
        ]);

        // Debug: Vérifier les données reçues
        Log::info('Données reçues dans saveGoogleConfig:', [
            'google_place_id' => $request->google_place_id,
            'outscraper_api_key' => $request->outscraper_api_key ? 'PRÉSENTE' : 'MANQUANTE',
            'auto_approve_google' => $request->boolean('auto_approve_google')
        ]);

        Setting::set('google_place_id', $request->google_place_id);
        Setting::set('outscraper_api_key', $request->outscraper_api_key);
        Setting::set('auto_approve_google_reviews', $request->boolean('auto_approve_google'));
        
        Setting::clearCache();

        // Debug: Vérifier que les données sont bien sauvegardées
        Log::info('Données sauvegardées:', [
            'google_place_id_saved' => setting('google_place_id'),
            'outscraper_api_key_saved' => setting('outscraper_api_key') ? 'PRÉSENTE' : 'MANQUANTE',
            'auto_approve_saved' => setting('auto_approve_google_reviews')
        ]);

        return redirect()->route('admin.reviews.google.config')
            ->with('success', 'Configuration Google sauvegardée avec succès !');
    }

    /**
     * Importer les avis Google - Version améliorée pour récupérer TOUS les avis
     */
    public function importGoogle()
    {
        $placeId = setting('google_place_id');
        $apiKey = setting('google_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$placeId || !$apiKey) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration Google manquante !');
        }

        try {
            // Utiliser l'API Google Places avec plus de champs pour récupérer TOUS les avis
            $response = Http::get("https://maps.googleapis.com/maps/api/place/details/json", [
                'place_id' => $placeId,
                'fields' => 'reviews,rating,user_ratings_total,name,formatted_address',
                'key' => $apiKey,
                'language' => 'fr',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['result']['reviews'])) {
                    $reviews = $data['result']['reviews'];
                    $imported = 0;
                    $updated = 0;
                    $skipped = 0;
                    
                    // Traiter TOUS les avis disponibles (pas seulement 5)
                    foreach ($reviews as $index => $googleReview) {
                        $reviewDate = date('Y-m-d H:i:s', $googleReview['time']);
                        
                        // Créer un identifiant unique pour éviter les doublons
                        $googleReviewId = md5($googleReview['author_name'] . $googleReview['time'] . $googleReview['text']);
                        
                        // Vérifier si l'avis existe déjà
                        $existingReview = Review::where('google_review_id', $googleReviewId)
                            ->orWhere(function($query) use ($googleReview, $reviewDate) {
                                $query->where('author_name', $googleReview['author_name'])
                                      ->where('source', 'google')
                                      ->whereDate('review_date', '=', date('Y-m-d', $googleReview['time']));
                            })
                            ->first();
                        
                        if (!$existingReview) {
                            // Créer un nouvel avis
                            Review::create([
                                'google_review_id' => $googleReviewId,
                                'author_name' => $googleReview['author_name'],
                                'author_location' => 'Google',
                                'author_photo_url' => $googleReview['profile_photo_url'] ?? null,
                                'rating' => $googleReview['rating'],
                                'review_text' => $googleReview['text'] ?? '',
                                'is_active' => $autoApprove,
                                'is_verified' => true,
                                'source' => 'google',
                                'display_order' => $index,
                                'review_date' => $reviewDate,
                            ]);
                            $imported++;
                        } else {
                            // Mettre à jour l'avis existant si nécessaire
                            if ($existingReview->review_text !== ($googleReview['text'] ?? '')) {
                                $existingReview->update([
                                    'review_text' => $googleReview['text'] ?? '',
                                    'rating' => $googleReview['rating'],
                                    'author_photo_url' => $googleReview['profile_photo_url'] ?? null,
                                ]);
                                $updated++;
                            } else {
                                $skipped++;
                            }
                        }
                    }
                    
                    $message = "Import terminé : {$imported} nouveaux avis";
                    if ($updated > 0) $message .= ", {$updated} avis mis à jour";
                    if ($skipped > 0) $message .= ", {$skipped} avis déjà existants";
                    
                    return redirect()->route('admin.reviews.index')
                        ->with('success', $message);
                } else {
                    return redirect()->route('admin.reviews.index')
                        ->with('error', 'Aucun avis trouvé pour ce Place ID.');
                }
            } else {
                return redirect()->route('admin.reviews.index')
                    ->with('error', 'Erreur lors de la récupération des avis Google : ' . $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Erreur import Google reviews: ' . $e->getMessage());
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Importer TOUS les avis Google avec Outscraper API (ultra-simple)
     */
    public function importGoogleAuto()
    {
        $placeId = setting('google_place_id');
        $outscraperApiKey = setting('outscraper_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        // Debug: Vérifier les valeurs récupérées
        Log::info('Configuration récupérée:', [
            'place_id' => $placeId,
            'outscraper_api_key' => $outscraperApiKey ? 'PRÉSENTE' : 'MANQUANTE',
            'auto_approve' => $autoApprove
        ]);

        if (!$placeId || !$outscraperApiKey) {
            Log::error('Configuration manquante:', [
                'place_id_present' => !empty($placeId),
                'outscraper_key_present' => !empty($outscraperApiKey)
            ]);
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration manquante ! Place ID: ' . ($placeId ? 'OK' : 'MANQUANT') . ', Clé Outscraper: ' . ($outscraperApiKey ? 'OK' : 'MANQUANTE'));
        }

        try {
            // Debug: Afficher les paramètres
            Log::info('Outscraper API Debug:', [
                'place_id' => $placeId,
                'api_key' => substr($outscraperApiKey, 0, 10) . '...',
                'url' => 'https://api.outscraper.com/maps/reviews-v3'
            ]);

            // Utilisation de l'API Outscraper pour récupérer TOUS les avis
            $response = Http::timeout(60)->post('https://api.outscraper.com/maps/reviews-v3', [
                'query' => $placeId,
                'limit' => 100, // Maximum d'avis à récupérer
                'language' => 'fr',
                'region' => 'fr',
                'sort' => 'newest',
                'reviewsLimit' => 100
            ], [
                'X-API-KEY' => $outscraperApiKey,
                'Content-Type' => 'application/json'
            ]);

            // Debug: Afficher la réponse complète
            Log::info('Outscraper API Response:', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Erreur Outscraper API: ' . $response->status() . ' - ' . $response->body());
                return redirect()->route('admin.reviews.index')
                    ->with('error', 'Erreur API Outscraper : ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();
            
            // Debug: Afficher la structure des données
            Log::info('Outscraper API Data Structure:', [
                'data_type' => gettype($data),
                'data_count' => is_array($data) ? count($data) : 'not_array',
                'data_content' => $data
            ]);
            
            if (!isset($data[0]['reviews']) || empty($data[0]['reviews'])) {
                Log::warning('Aucun avis trouvé dans la réponse Outscraper');
                return redirect()->route('admin.reviews.index')
                    ->with('error', 'Aucun avis trouvé pour ce Place ID via Outscraper. Réponse: ' . json_encode($data));
            }

            $allReviews = $data[0]['reviews'];
            
            // Debug: Afficher le premier avis pour voir la structure
            if (!empty($allReviews)) {
                Log::info('Premier avis Outscraper:', [
                    'review_structure' => $allReviews[0],
                    'total_reviews' => count($allReviews)
                ]);
            }
            
            $imported = 0;
            $skipped = 0;

            foreach ($allReviews as $index => $review) {
                // Debug: Afficher les champs disponibles dans l'avis
                if ($index === 0) {
                    Log::info('Champs disponibles dans l\'avis:', [
                        'available_fields' => array_keys($review),
                        'review_data' => $review
                    ]);
                }
                
                // Adapter le format Outscraper
                $reviewDate = isset($review['review_datetime_utc']) 
                    ? date('Y-m-d H:i:s', strtotime($review['review_datetime_utc']))
                    : date('Y-m-d H:i:s');
                
                $reviewId = md5(
                    ($review['reviewer_name'] ?? 'Anonyme') . 
                    $reviewDate . 
                    ($review['review_text'] ?? '')
                );
                
                $existingReview = Review::where('google_review_id', $reviewId)->first();
                
                if (!$existingReview) {
                    Review::create([
                        'google_review_id' => $reviewId,
                        'author_name' => $review['reviewer_name'] ?? 'Anonyme',
                        'author_location' => $review['reviewer_id'] ?? 'Google',
                        'author_photo_url' => $review['reviewer_photo'] ?? null,
                        'rating' => $review['review_rating'] ?? 5,
                        'review_text' => $review['review_text'] ?? '',
                        'is_active' => $autoApprove,
                        'is_verified' => true,
                        'source' => 'outscraper',
                        'display_order' => $index,
                        'review_date' => $reviewDate,
                    ]);
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            $message = "Import Outscraper terminé : {$imported} nouveaux avis";
            if ($skipped > 0) $message .= ", {$skipped} avis déjà existants";
            $message .= " (Total trouvé : " . count($allReviews) . " avis)";

            return redirect()->route('admin.reviews.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur import Outscraper: ' . $e->getMessage());
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import Outscraper : ' . $e->getMessage());
        }
    }

    /**
     * Tester la connexion avec Outscraper API (AJAX)
     */
    public function testOutscraperConnection()
    {
        $placeId = setting('google_place_id');
        $outscraperApiKey = setting('outscraper_api_key');

        if (!$placeId || !$outscraperApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration manquante ! Veuillez configurer le Place ID et la clé API Outscraper.'
            ]);
        }

        try {
            // Test simple avec l'API Outscraper
            $response = Http::timeout(30)->post('https://api.outscraper.com/maps/reviews-v3', [
                'query' => $placeId,
                'limit' => 5, // Juste 5 avis pour le test
                'language' => 'fr',
                'region' => 'fr'
            ], [
                'X-API-KEY' => $outscraperApiKey,
                'Content-Type' => 'application/json'
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion Outscraper : ' . $response->status() . ' - ' . $response->body()
                ]);
            }

            $data = $response->json();
            
            if (isset($data[0]['reviews']) && !empty($data[0]['reviews'])) {
                $reviewCount = count($data[0]['reviews']);
                return response()->json([
                    'success' => true,
                    'message' => "Connexion Outscraper réussie ! {$reviewCount} avis trouvés. L'API fonctionne correctement."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connexion Outscraper réussie mais aucun avis trouvé. Vérifiez votre Place ID.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test Outscraper : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Afficher la page d'import manuel d'avis
     */
    public function showManualImport()
    {
        return view('admin.reviews.manual-import');
    }

    /**
     * Importer des avis manuellement (format JSON)
     */
    public function importManual(Request $request)
    {
        $request->validate([
            'reviews_json' => 'required|string',
        ]);

        try {
            $reviewsData = json_decode($request->reviews_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->route('admin.reviews.manual-import')
                    ->with('error', 'Format JSON invalide : ' . json_last_error_msg());
            }

            if (!is_array($reviewsData)) {
                return redirect()->route('admin.reviews.manual-import')
                    ->with('error', 'Le JSON doit contenir un tableau d\'avis.');
            }

            $imported = 0;
            $skipped = 0;

            foreach ($reviewsData as $reviewData) {
                // Vérifier les champs requis
                if (!isset($reviewData['author_name']) || !isset($reviewData['rating']) || !isset($reviewData['review_text'])) {
                    continue; // Ignorer les avis incomplets
                }

                $reviewId = md5($reviewData['author_name'] . ($reviewData['review_date'] ?? time()) . $reviewData['review_text']);
                
                $existingReview = Review::where('google_review_id', $reviewId)->first();
                
                if (!$existingReview) {
                    Review::create([
                        'google_review_id' => $reviewId,
                        'author_name' => $reviewData['author_name'],
                        'author_location' => $reviewData['author_location'] ?? 'Manuel',
                        'author_photo_url' => $reviewData['author_photo_url'] ?? null,
                        'rating' => $reviewData['rating'],
                        'review_text' => $reviewData['review_text'],
                        'is_active' => $reviewData['is_active'] ?? true,
                        'is_verified' => $reviewData['is_verified'] ?? false,
                        'source' => 'manual',
                        'display_order' => $imported,
                        'review_date' => $reviewData['review_date'] ?? date('Y-m-d H:i:s'),
                    ]);
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            $message = "Import manuel terminé : {$imported} nouveaux avis";
            if ($skipped > 0) $message .= ", {$skipped} avis déjà existants";

            return redirect()->route('admin.reviews.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur import manuel avis: ' . $e->getMessage());
            return redirect()->route('admin.reviews.manual-import')
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer tous les avis
     */
    public function deleteAll()
    {
        try {
            $count = Review::count();
            Review::truncate(); // Supprime tous les avis
            
            return redirect()->route('admin.reviews.index')
                ->with('success', "Tous les avis ont été supprimés ({$count} avis supprimés).");
        } catch (\Exception $e) {
            Log::error('Erreur suppression avis: ' . $e->getMessage());
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * API: Obtenir un avis (pour AJAX)
     */
    public function get(Review $review)
    {
        return response()->json([
            'success' => true,
            'review' => $review
        ]);
    }

    /**
     * API: Mettre à jour un avis (pour AJAX)
     */
    public function updateAjax(Request $request, Review $review)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $review->update([
            'author_name' => $request->author_name,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Avis mis à jour avec succès'
        ]);
    }

    /**
     * API: Supprimer un avis (pour AJAX)
     */
    public function deleteAjax(Review $review)
    {
        $review->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Avis supprimé avec succès'
        ]);
    }
}








