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
        $googleApiKey = setting('google_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);
        
        return view('admin.reviews.google-config', compact('googlePlaceId', 'googleApiKey', 'autoApprove'));
    }

    /**
     * Sauvegarder la configuration Google
     */
    public function saveGoogleConfig(Request $request)
    {
        $request->validate([
            'google_place_id' => 'required|string',
            'google_api_key' => 'required|string',
            'auto_approve_google' => 'boolean',
        ]);

        Setting::set('google_place_id', $request->google_place_id);
        Setting::set('google_api_key', $request->google_api_key);
        Setting::set('auto_approve_google_reviews', $request->boolean('auto_approve_google'));
        Setting::clearCache();

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
     * Importer les avis Google avec pagination (pour récupérer plus d'avis)
     */
    public function importGoogleAdvanced()
    {
        $placeId = setting('google_place_id');
        $apiKey = setting('google_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$placeId || !$apiKey) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration Google manquante !');
        }

        try {
            $allReviews = [];
            $nextPageToken = null;
            $maxPages = 3; // Limiter à 3 pages pour éviter les quotas
            $page = 0;

            do {
                $params = [
                    'place_id' => $placeId,
                    'fields' => 'reviews,rating,user_ratings_total,name,formatted_address',
                    'key' => $apiKey,
                    'language' => 'fr',
                ];

                if ($nextPageToken) {
                    $params['pagetoken'] = $nextPageToken;
                }

                $response = Http::get("https://maps.googleapis.com/maps/api/place/details/json", $params);

                if (!$response->successful()) {
                    return redirect()->route('admin.reviews.index')
                        ->with('error', 'Erreur API Google : ' . $response->status());
                }

                $data = $response->json();

                if (isset($data['error_message'])) {
                    return redirect()->route('admin.reviews.index')
                        ->with('error', 'Erreur Google API : ' . $data['error_message']);
                }

                if (isset($data['result']['reviews'])) {
                    $allReviews = array_merge($allReviews, $data['result']['reviews']);
                }

                $nextPageToken = $data['next_page_token'] ?? null;
                $page++;

                // Attendre 2 secondes entre les requêtes (requis par Google)
                if ($nextPageToken && $page < $maxPages) {
                    sleep(2);
                }

            } while ($nextPageToken && $page < $maxPages);

            if (empty($allReviews)) {
                return redirect()->route('admin.reviews.index')
                    ->with('error', 'Aucun avis trouvé pour ce Place ID.');
            }

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($allReviews as $index => $googleReview) {
                $reviewDate = date('Y-m-d H:i:s', $googleReview['time']);
                $googleReviewId = md5($googleReview['author_name'] . $googleReview['time'] . $googleReview['text']);
                
                $existingReview = Review::where('google_review_id', $googleReviewId)->first();
                
                if (!$existingReview) {
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
                    $skipped++;
                }
            }

            $message = "Import avancé terminé : {$imported} nouveaux avis";
            if ($skipped > 0) $message .= ", {$skipped} avis déjà existants";
            $message .= " (Total traité : " . count($allReviews) . " avis)";

            return redirect()->route('admin.reviews.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erreur import Google reviews avancé: ' . $e->getMessage());
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import avancé : ' . $e->getMessage());
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








