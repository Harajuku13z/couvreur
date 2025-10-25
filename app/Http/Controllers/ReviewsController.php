<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReviewsController extends Controller
{
    /**
     * Afficher la liste des avis
     */
    public function index()
    {
        $reviews = Review::orderBy('created_at', 'desc')->paginate(20);
        
        // Statistiques
        $stats = [
            'total' => Review::count(),
            'active' => Review::where('is_active', true)->count(),
            'inactive' => Review::where('is_active', false)->count(),
            'average_rating' => Review::where('is_active', true)->avg('rating') ?? 0
        ];
        
        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Afficher la configuration Google
     */
    public function googleConfig()
    {
        $googleApiKey = setting('google_api_key');
        $googlePlaceId = setting('google_place_id');
        $autoApprove = setting('auto_approve_google_reviews', false);

        return view('admin.reviews.google-config', compact('googleApiKey', 'googlePlaceId', 'autoApprove'));
    }

    /**
     * Sauvegarder la configuration Google
     */
    public function saveGoogleConfig(Request $request)
    {
        $request->validate([
            'google_api_key' => 'required|string',
            'google_place_id' => 'required|string',
            'auto_approve_google' => 'boolean',
        ]);

        Setting::set('google_api_key', $request->google_api_key);
        Setting::set('google_place_id', $request->google_place_id);
        Setting::set('auto_approve_google_reviews', $request->boolean('auto_approve_google'));
        
        Setting::clearCache();

        return redirect()->route('admin.reviews.google.config')
            ->with('success', 'Configuration Google sauvegardée avec succès !');
    }

    /**
     * Tester la connexion avec Google Places API
     */
    public function testGoogleConnection()
    {
        $placeId = setting('google_place_id');
        $apiKey = setting('google_api_key');

        if (!$placeId || !$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration manquante ! Veuillez configurer le Place ID et la clé API Google.'
            ]);
        }

        try {
            $response = Http::timeout(30)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'name,rating,reviews',
                'key' => $apiKey
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion : ' . $response->status() . ' - ' . $response->body()
                ]);
            }

            $data = $response->json();
            
            if (isset($data['result']['reviews']) && !empty($data['result']['reviews'])) {
                $reviewCount = count($data['result']['reviews']);
                return response()->json([
                    'success' => true,
                    'message' => "Connexion Google réussie ! {$reviewCount} avis trouvés."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connexion Google réussie mais aucun avis trouvé. Vérifiez votre Place ID.'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Importer les avis avec Google Places API
     */
    public function importGoogleReviews()
    {
        $placeId = setting('google_place_id');
        $apiKey = setting('google_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$placeId || !$apiKey) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration manquante !');
        }

        try {
            $response = Http::timeout(60)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'name,rating,reviews',
                'key' => $apiKey
            ]);

            if (!$response->successful()) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Erreur API Google : ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['result']['reviews']) || empty($data['result']['reviews'])) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Aucun avis trouvé. Vérifiez votre Place ID.');
            }

            $reviews = $data['result']['reviews'];
            $importedCount = 0;
            $updatedCount = 0;

            foreach ($reviews as $review) {
                $googleReviewId = $review['author_url'] ?? md5($review['author_name'] . $review['time']);
                
                $existingReview = Review::where('google_review_id', $googleReviewId)->first();

                $reviewData = [
                    'google_review_id' => $googleReviewId,
                    'author_name' => $review['author_name'] ?? 'Auteur inconnu',
                    'rating' => $review['rating'] ?? 5,
                    'review_text' => $review['text'] ?? '',
                    'review_date' => isset($review['time']) ? date('Y-m-d H:i:s', $review['time']) : now(),
                    'source' => 'Google Places',
                    'is_active' => $autoApprove ? 1 : 0,
                    'is_verified' => true
                ];

                if ($existingReview) {
                    $existingReview->update($reviewData);
                    $updatedCount++;
                } else {
                    Review::create($reviewData);
                    $importedCount++;
                }
            }

            return redirect()->route('admin.reviews.index')
                ->with('success', "Import terminé ! {$importedCount} nouveaux avis, {$updatedCount} mis à jour.");

        } catch (\Exception $e) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer tous les avis
     */
    public function deleteAll()
    {
        Review::truncate();
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Tous les avis ont été supprimés.');
    }

    /**
     * Basculer le statut d'un avis
     */
    public function toggleStatus($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_active' => !$review->is_active]);
        
        $status = $review->is_active ? 'activé' : 'désactivé';
        return redirect()->route('admin.reviews.index')
            ->with('success', "Avis {$status} avec succès.");
    }

    /**
     * Supprimer un avis
     */
    public function delete($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();
        
        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis supprimé avec succès.');
    }
}