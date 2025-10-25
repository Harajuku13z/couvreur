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
            'approved' => Review::where('approved', 1)->count(),
            'pending' => Review::where('approved', 0)->count(),
            'average_rating' => Review::where('approved', 1)->avg('rating') ?? 0
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
            ->with('success', 'Configuration Google sauvegardée avec succès !');
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
     * Récupérer et importer les avis avec Outscraper API
     */
    public function importGoogleAuto()
    {
        $placeId = setting('google_place_id');
        $outscraperApiKey = setting('outscraper_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$placeId || !$outscraperApiKey) {
            return redirect()->route('admin.reviews.google.config')
                ->with('error', 'Configuration manquante ! Veuillez configurer le Place ID et la clé API Outscraper.');
        }

        try {
            // Appel à l'API Outscraper
            $response = Http::timeout(60)->post('https://api.outscraper.com/maps/reviews-v3', [
                'query' => $placeId,
                'limit' => 100,
                'language' => 'fr',
                'region' => 'fr'
            ], [
                'X-API-KEY' => $outscraperApiKey,
                'Content-Type' => 'application/json'
            ]);

            if (!$response->successful()) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Erreur API Outscraper : ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data[0]['reviews']) || empty($data[0]['reviews'])) {
                return redirect()->route('admin.reviews.google.config')
                    ->with('error', 'Aucun avis trouvé. Vérifiez votre Place ID.');
            }

            $reviews = $data[0]['reviews'];
            $importedCount = 0;
            $updatedCount = 0;

            foreach ($reviews as $review) {
                // Créer un ID unique pour l'avis
                $reviewId = md5($review['review_id'] ?? $review['author_name'] . $review['review_datetime']);
                
                // Extraire les données
                $authorName = $review['author_name'] ?? 'Auteur inconnu';
                $rating = $review['review_rating'] ?? 5;
                $text = $review['review_text'] ?? '';
                $date = $review['review_datetime'] ?? now();
                
                // Vérifier si l'avis existe déjà
                $existingReview = Review::where('review_id', $reviewId)->first();

                if ($existingReview) {
                    // Mettre à jour
                    $existingReview->update([
                        'author_name' => $authorName,
                        'rating' => $rating,
                        'text' => $text,
                        'date' => $date,
                        'source' => 'Google (Outscraper)',
                        'approved' => $autoApprove ? 1 : 0
                    ]);
                    $updatedCount++;
                } else {
                    // Créer nouveau
                    Review::create([
                        'review_id' => $reviewId,
                        'author_name' => $authorName,
                        'rating' => $rating,
                        'text' => $text,
                        'date' => $date,
                        'source' => 'Google (Outscraper)',
                        'approved' => $autoApprove ? 1 : 0
                    ]);
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
}
