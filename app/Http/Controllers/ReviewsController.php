<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        Setting::set('google_place_id', $request->google_place_id);
        Setting::set('outscraper_api_key', $request->outscraper_api_key);
        Setting::set('auto_approve_google_reviews', $request->boolean('auto_approve_google'));
        
        Setting::clearCache();

        return redirect()->route('admin.reviews.google.config')
            ->with('success', 'Configuration sauvegardée avec succès !');
    }

    /**
     * Tester la connexion avec Outscraper API
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
            $response = Http::timeout(30)->get('https://api.outscraper.cloud/google-maps-reviews', [
                'query' => $placeId,
                'reviewsLimit' => 5,
                'language' => 'fr',
                'region' => 'FR',
                'async' => 'false'
            ], [
                'X-API-KEY' => $outscraperApiKey
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion : ' . $response->status() . ' - ' . $response->body()
                ]);
            }

            $data = $response->json();
            
            if (isset($data['data']) && is_array($data['data']) && !empty($data['data'])) {
                $place = $data['data'][0];
                if (isset($place['reviews']) && !empty($place['reviews'])) {
                    $reviewCount = count($place['reviews']);
                    return response()->json([
                        'success' => true,
                        'message' => "Connexion réussie ! {$reviewCount} avis trouvés."
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Connexion réussie mais aucun avis trouvé. Vérifiez votre Place ID.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Importer les avis avec Outscraper API
     */
    public function importGoogleAuto()
    {
        $placeId = setting('google_place_id');
        $outscraperApiKey = setting('outscraper_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$placeId || !$outscraperApiKey) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration manquante !');
        }

        try {
            $response = Http::timeout(60)->get('https://api.outscraper.cloud/google-maps-reviews', [
                'query' => $placeId,
                'reviewsLimit' => 100,
                'language' => 'fr',
                'region' => 'FR',
                'async' => 'false'
            ], [
                'X-API-KEY' => $outscraperApiKey
            ]);

            if (!$response->successful()) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Erreur API : ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['data']) || !is_array($data['data']) || empty($data['data'])) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Aucun avis trouvé. Vérifiez votre Place ID.');
            }

            $place = $data['data'][0];
            if (!isset($place['reviews']) || empty($place['reviews'])) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Aucun avis trouvé. Vérifiez votre Place ID.');
            }

            $reviews = $place['reviews'];
            $importedCount = 0;
            $updatedCount = 0;

            foreach ($reviews as $review) {
                $googleReviewId = $review['review_id'] ?? md5($review['author_name'] . $review['review_datetime']);
                
                $existingReview = Review::where('google_review_id', $googleReviewId)->first();

                $reviewData = [
                    'google_review_id' => $googleReviewId,
                    'author_name' => $review['author_name'] ?? 'Auteur inconnu',
                    'rating' => $review['review_rating'] ?? 5,
                    'review_text' => $review['review_text'] ?? '',
                    'review_date' => $review['review_datetime'] ?? now(),
                    'source' => 'Google (Outscraper)',
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