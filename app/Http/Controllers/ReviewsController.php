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
        $reviews = Review::orderBy('review_date', 'desc')->paginate(20);
        
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
     * Afficher la configuration SerpAPI
     */
    public function serpConfig()
    {
        $serpApiKey = setting('serp_api_key', '1e60ebcc99eb3f99ad054a7710846558e3b12b3c71fdc56fec72c4e495e63370');
        $googlePlaceId = setting('google_place_id');
        $autoApprove = setting('auto_approve_google_reviews', false);

        return view('admin.reviews.serp-config', compact('serpApiKey', 'googlePlaceId', 'autoApprove'));
    }

    /**
     * Sauvegarder la configuration SerpAPI
     */
    public function saveSerpConfig(Request $request)
    {
        $request->validate([
            'serp_api_key' => 'required|string',
            'google_place_id' => 'required|string',
            'auto_approve_google' => 'boolean',
        ]);

        Setting::set('serp_api_key', $request->serp_api_key);
        Setting::set('google_place_id', $request->google_place_id);
        Setting::set('auto_approve_google_reviews', $request->boolean('auto_approve_google'));
        
        Setting::clearCache();

        return redirect()->route('admin.reviews.serp.config')
            ->with('success', 'Configuration SerpAPI sauvegardée avec succès !');
    }

    /**
     * Tester la connexion avec SerpAPI
     */
    public function testSerpConnection()
    {
        $placeId = setting('google_place_id');
        $serpApiKey = setting('serp_api_key');

        if (!$placeId || !$serpApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration manquante ! Veuillez configurer Place ID et SerpAPI Key.'
            ]);
        }

        try {
            $response = Http::timeout(30)->get('https://serpapi.com/search', [
                'engine' => 'google_maps_reviews',
                'place_id' => $placeId,
                'api_key' => $serpApiKey
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur SerpAPI : ' . $response->status() . ' - ' . $response->body()
                ]);
            }

            $data = $response->json();
            
            if (isset($data['reviews']) && !empty($data['reviews'])) {
                $reviewCount = count($data['reviews']);
                return response()->json([
                    'success' => true,
                    'message' => "Connexion SerpAPI réussie ! {$reviewCount} avis trouvés."
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connexion SerpAPI réussie mais aucun avis trouvé. Vérifiez votre Place ID.'
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
     * Importer les avis avec SerpAPI (avec pagination)
     */
    public function importSerpReviews()
    {
        $placeId = setting('google_place_id');
        $serpApiKey = setting('serp_api_key');
        $autoApprove = setting('auto_approve_google_reviews', false);

        if (!$placeId || !$serpApiKey) {
            return redirect()->route('admin.reviews.serp.config')
                ->with('error', 'Configuration SerpAPI manquante !');
        }

        try {
            $allReviews = [];
            $nextPageToken = null;
            $pageCount = 0;
            $maxPages = 5; // Limite à 5 pages pour éviter les timeouts

            do {
                $params = [
                    'engine' => 'google_maps_reviews',
                    'place_id' => $placeId,
                    'api_key' => $serpApiKey
                ];

                if ($nextPageToken) {
                    $params['next_page_token'] = $nextPageToken;
                }

                $response = Http::timeout(60)->get('https://serpapi.com/search', $params);

                if (!$response->successful()) {
                    return redirect()->route('admin.reviews.serp.config')
                        ->with('error', 'Erreur SerpAPI : ' . $response->status() . ' - ' . $response->body());
                }

                $data = $response->json();
                
                if (isset($data['reviews']) && !empty($data['reviews'])) {
                    $allReviews = array_merge($allReviews, $data['reviews']);
                }

                // Vérifier s'il y a une page suivante
                $nextPageToken = $data['serpapi_pagination']['next_page_token'] ?? null;
                $pageCount++;

                // Pause entre les requêtes pour éviter les limites de taux
                if ($nextPageToken && $pageCount < $maxPages) {
                    sleep(2);
                }

            } while ($nextPageToken && $pageCount < $maxPages);

            if (empty($allReviews)) {
                return redirect()->route('admin.reviews.serp.config')
                    ->with('error', 'Aucun avis trouvé via SerpAPI. Vérifiez votre Place ID.');
            }

            $importedCount = 0;
            $updatedCount = 0;

            foreach ($allReviews as $review) {
                $googleReviewId = md5($review['user']['name'] . $review['date']);
                
                $existingReview = Review::where('google_review_id', $googleReviewId)->first();

                $reviewData = [
                    'google_review_id' => $googleReviewId,
                    'author_name' => $review['user']['name'] ?? 'Auteur inconnu',
                    'rating' => $review['rating'] ?? 5,
                    'review_text' => $review['snippet'] ?? '',
                    'review_date' => isset($review['iso_date']) ? 
                        date('Y-m-d H:i:s', strtotime($review['iso_date'])) : now(),
                    'source' => 'Google',
                    'is_active' => $autoApprove ? 1 : 0,
                    'is_verified' => true
                ];
                
                // Ajouter les nouveaux champs seulement s'ils existent dans la réponse
                if (isset($review['user']['thumbnail']) && $review['user']['thumbnail']) {
                    $reviewData['author_photo'] = $review['user']['thumbnail'];
                }
                if (isset($review['user']['link']) && $review['user']['link']) {
                    $reviewData['author_link'] = $review['user']['link'];
                }

                if ($existingReview) {
                    $existingReview->update($reviewData);
                    $updatedCount++;
                } else {
                    Review::create($reviewData);
                    $importedCount++;
                }
            }

            return redirect()->route('admin.reviews.index')
                ->with('success', "Import SerpAPI terminé ! {$importedCount} nouveaux avis, {$updatedCount} mis à jour. (Total: " . count($allReviews) . " avis récupérés sur {$pageCount} pages)");

        } catch (\Exception $e) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Erreur lors de l\'import SerpAPI : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'ajout manuel
     */
    public function create()
    {
        return view('admin.reviews.create');
    }

    /**
     * Sauvegarder un avis manuel
     */
    public function store(Request $request)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string',
            'review_date' => 'required|date',
            'source' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $reviewData = [
            'author_name' => $request->author_name,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'review_date' => $request->review_date,
            'source' => $request->source,
            'is_active' => $request->boolean('is_active', true),
            'is_verified' => true
        ];
        
        // Ajouter les nouveaux champs seulement s'ils existent
        if ($request->has('author_photo') && $request->author_photo) {
            $reviewData['author_photo'] = $request->author_photo;
        }
        if ($request->has('author_link') && $request->author_link) {
            $reviewData['author_link'] = $request->author_link;
        }
        
        Review::create($reviewData);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis ajouté avec succès !');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $review = Review::findOrFail($id);
        return view('admin.reviews.edit', compact('review'));
    }

    /**
     * Mettre à jour un avis
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);
        
        $request->validate([
            'author_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string',
            'review_date' => 'required|date',
            'source' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $reviewData = [
            'author_name' => $request->author_name,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'review_date' => $request->review_date,
            'source' => $request->source,
            'is_active' => $request->boolean('is_active', true)
        ];
        
        // Ajouter les nouveaux champs seulement s'ils existent
        if ($request->has('author_photo')) {
            $reviewData['author_photo'] = $request->author_photo;
        }
        if ($request->has('author_link')) {
            $reviewData['author_link'] = $request->author_link;
        }
        
        $review->update($reviewData);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Avis mis à jour avec succès !');
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