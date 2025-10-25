<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Setting;
use App\Helpers\GoogleMyBusinessHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use AdnanHussainTurki\GoogleMyBusiness\GoogleMyBusiness;

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
     * Afficher la configuration Google My Business
     */
    public function googleConfig()
    {
        $googleApiKey = setting('google_api_key');
        $googlePlaceId = setting('google_place_id');
        $googleAccountId = setting('google_account_id');
        $googleLocationId = setting('google_location_id');
        $googleAccessToken = setting('google_access_token');
        $autoApprove = setting('auto_approve_google_reviews', false);

        return view('admin.reviews.google-config', compact(
            'googleApiKey', 'googlePlaceId', 'googleAccountId', 
            'googleLocationId', 'googleAccessToken', 'autoApprove'
        ));
    }

    /**
     * Sauvegarder la configuration Google My Business
     */
    public function saveGoogleConfig(Request $request)
    {
        $request->validate([
            'google_api_key' => 'required|string',
            'google_place_id' => 'required|string',
            'google_account_id' => 'required|string',
            'google_location_id' => 'required|string',
            'google_access_token' => 'required|string',
            'auto_approve_google' => 'boolean',
        ]);

        Setting::set('google_api_key', $request->google_api_key);
        Setting::set('google_place_id', $request->google_place_id);
        Setting::set('google_account_id', $request->google_account_id);
        Setting::set('google_location_id', $request->google_location_id);
        Setting::set('google_access_token', $request->google_access_token);
        Setting::set('auto_approve_google_reviews', $request->boolean('auto_approve_google'));
        
        Setting::clearCache();

        return redirect()->route('admin.reviews.google.config')
            ->with('success', 'Configuration Google My Business sauvegardée avec succès !');
    }

    /**
     * Tester la connexion avec Google My Business API
     */
    public function testGoogleConnection()
    {
        $accountId = setting('google_account_id');
        $locationId = setting('google_location_id');
        $accessToken = setting('google_access_token');

        if (!$accountId || !$locationId || !$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration manquante ! Veuillez configurer Account ID, Location ID et Access Token.'
            ]);
        }

        try {
            $gmb = new GoogleMyBusiness($accessToken);
            $reviews = $gmb->getReviews($accountId, $locationId);
            
            if (isset($reviews['reviews']) && !empty($reviews['reviews'])) {
                $reviewCount = count($reviews['reviews']);
                return response()->json([
                    'success' => true,
                    'message' => "Connexion Google My Business réussie ! {$reviewCount} avis trouvés."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connexion Google My Business réussie mais aucun avis trouvé.'
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
     * Importer les avis avec Google Places API (5 avis max)
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
                ->with('success', "Import terminé ! {$importedCount} nouveaux avis, {$updatedCount} mis à jour. (Limite Google Places: 5 avis max)");

        } catch (\Exception $e) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Importer TOUS les avis avec Google My Business API
     */
    public function importAllGoogleReviews()
    {
        $accountId = setting('google_account_id');
        $locationId = setting('google_location_id');
        $accessToken = setting('google_access_token');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$accountId || !$locationId || !$accessToken) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration Google My Business manquante !');
        }

        try {
            $gmb = new GoogleMyBusiness($accessToken);
            $reviews = $gmb->getReviews($accountId, $locationId);
            
            if (!isset($reviews['reviews']) || empty($reviews['reviews'])) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Aucun avis trouvé via Google My Business.');
            }

            $reviewsData = $reviews['reviews'];
            $importedCount = 0;
            $updatedCount = 0;

            foreach ($reviewsData as $review) {
                $googleReviewId = $review['reviewId'] ?? md5($review['reviewer']['displayName'] . $review['createTime']);
                
                $existingReview = Review::where('google_review_id', $googleReviewId)->first();

                $reviewData = [
                    'google_review_id' => $googleReviewId,
                    'author_name' => $review['reviewer']['displayName'] ?? 'Auteur inconnu',
                    'rating' => $review['starRating'] ?? 5,
                    'review_text' => $review['comment'] ?? '',
                    'review_date' => isset($review['createTime']) ? 
                        date('Y-m-d H:i:s', strtotime($review['createTime'])) : now(),
                    'source' => 'Google My Business',
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
                ->with('success', "Import Google My Business terminé ! {$importedCount} nouveaux avis, {$updatedCount} mis à jour. (Total: " . count($reviewsData) . " avis)");

        } catch (\Exception $e) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import Google My Business : ' . $e->getMessage());
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

    /**
     * Initier le processus OAuth2 Google My Business
     */
    public function googleOAuth()
    {
        $helper = new GoogleMyBusinessHelper();
        $authUrl = $helper->getAuthUrl();
        
        return redirect($authUrl);
    }

    /**
     * Callback OAuth2 Google My Business
     */
    public function googleOAuthCallback(Request $request)
    {
        $code = $request->get('code');
        $error = $request->get('error');

        if ($error) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Erreur d\'autorisation: ' . $error);
        }

        if (!$code) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Code d\'autorisation manquant');
        }

        $helper = new GoogleMyBusinessHelper();
        $tokenData = $helper->exchangeCodeForToken($code);

        if (!$tokenData) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Impossible d\'obtenir le token d\'accès');
        }

        // Sauvegarder le token d'accès
        Setting::set('google_access_token', $tokenData['access_token']);
        if (isset($tokenData['refresh_token'])) {
            Setting::set('google_refresh_token', $tokenData['refresh_token']);
        }
        Setting::clearCache();

        // Récupérer les comptes et établissements
        $accounts = $helper->getAccounts($tokenData['access_token']);
        
        if ($accounts && isset($accounts['accounts']) && !empty($accounts['accounts'])) {
            $account = $accounts['accounts'][0]; // Prendre le premier compte
            $accountId = $account['name'];
            
            Setting::set('google_account_id', $accountId);
            
            // Récupérer les établissements
            $locations = $helper->getLocations($tokenData['access_token'], $accountId);
            
            if ($locations && isset($locations['locations']) && !empty($locations['locations'])) {
                $location = $locations['locations'][0]; // Prendre le premier établissement
                $locationId = $location['name'];
                
                Setting::set('google_location_id', $locationId);
                Setting::clearCache();
                
                return redirect()->route('admin.reviews.google.config')
                    ->with('success', 'Connexion Google My Business réussie ! Account ID et Location ID configurés automatiquement.');
            }
        }

        return redirect()->route('admin.reviews.google.config')
            ->with('success', 'Token d\'accès sauvegardé. Veuillez configurer manuellement Account ID et Location ID.');
    }
}