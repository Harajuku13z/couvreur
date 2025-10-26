<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdAdminController extends Controller
{
    public function index()
    {
        $ads = Ad::with('city')->orderByDesc('created_at')->paginate(25);

        // Statistiques des annonces
        $totalAds = Ad::count();
        $publishedAds = Ad::where('status', 'published')->count();
        $draftAds = Ad::where('status', 'draft')->count();

        // Données pour le générateur
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        $services = collect($services)->map(function($s){ return [ 'slug' => $s['slug'] ?? Str::slug($s['name'] ?? ''), 'name' => $s['name'] ?? '' ]; })->values();

        $cities = City::orderBy('name')->limit(200)->get(['id','name','postal_code','region','department']);
        $regions = City::whereNotNull('region')->distinct()->orderBy('region')->pluck('region');
        $departments = City::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');

        return view('admin.ads.index', compact('ads','services','cities','regions','departments','totalAds','publishedAds','draftAds'));
    }

    public function publish(Ad $ad)
    {
        $ad->update(['status' => 'published', 'published_at' => now()]);
        return back()->with('success', 'Annonce publiée');
    }

    public function archive(Ad $ad)
    {
        $ad->update(['status' => 'archived']);
        return back()->with('success', 'Annonce archivée');
    }

    public function destroy(Ad $ad)
    {
        $ad->delete();
        return back()->with('success', 'Annonce supprimée');
    }

    public function createManual(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'keyword' => 'required|string|max:100',
            'city_id' => 'required|integer|exists:cities,id',
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:500',
            'content_html' => 'nullable|string',
            'ai_prompt' => 'nullable|string|max:2000',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['title']);

        // Check if slug already exists
        if (Ad::where('slug', $slug)->exists()) {
            return response()->json(['success' => false, 'message' => 'Une annonce avec ce titre existe déjà'], 422);
        }

        // Si un prompt personnalisé est fourni, l'utiliser pour générer le contenu
        $contentHtml = $validated['content_html'];
        if (!empty($validated['ai_prompt']) && empty($contentHtml)) {
            // Utiliser le prompt personnalisé pour générer le contenu
            $contentHtml = $this->generateContentWithPrompt($validated['ai_prompt'], $validated['title'], $validated['keyword'], $validated['city_id']);
        }

        $ad = Ad::create([
            'title' => $validated['title'],
            'keyword' => $validated['keyword'],
            'city_id' => $validated['city_id'],
            'slug' => $slug,
            'status' => 'published',
            'published_at' => now(),
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'content_html' => $contentHtml,
        ]);

        return response()->json(['success' => true, 'ad_id' => $ad->id]);
    }

    private function generateContentWithPrompt($prompt, $title, $keyword, $cityId)
    {
        $city = City::find($cityId);
        $cityName = $city ? $city->name : '';
        $region = $city ? $city->region : '';
        
        // Utiliser le prompt personnalisé pour générer du contenu
        $content = "<div class=\"max-w-4xl mx-auto\">
            <h1 class=\"text-3xl font-bold text-gray-900 mb-6\">{$title}</h1>
            <div class=\"prose prose-lg max-w-none\">
                <p class=\"text-lg text-gray-700 mb-6\">
                    {$prompt} - Service {$keyword} à {$cityName}, {$region}.
                </p>
                <div class=\"bg-blue-50 p-6 rounded-lg mb-8\">
                    <h2 class=\"text-xl font-semibold text-gray-900 mb-3\">Notre Expertise</h2>
                    <p class=\"text-gray-700\">
                        Basé sur votre demande : \"{$prompt}\", nous vous proposons des solutions adaptées pour {$keyword} à {$cityName}.
                    </p>
                </div>
            </div>
        </div>";
        
        return $content;
    }

    public function removeDuplicates()
    {
        // Trouver les doublons basés sur keyword + city_id
        $duplicates = Ad::select('keyword', 'city_id')
            ->groupBy('keyword', 'city_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $removed = 0;
        foreach ($duplicates as $duplicate) {
            $ads = Ad::where('keyword', $duplicate->keyword)
                ->where('city_id', $duplicate->city_id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Garder le plus récent, supprimer les autres
            $ads->skip(1)->each(function ($ad) use (&$removed) {
                $ad->delete();
                $removed++;
            });
        }

        return back()->with('success', "Doublons supprimés: {$removed} annonces");
    }

    /**
     * Supprimer toutes les annonces
     */
    public function deleteAll(Request $request)
    {
        try {
            $count = Ad::count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune annonce à supprimer'
                ]);
            }

            // Supprimer toutes les annonces
            Ad::truncate();

            return response()->json([
                'success' => true,
                'message' => "Toutes les annonces ({$count}) ont été supprimées avec succès"
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de toutes les annonces: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression des annonces: ' . $e->getMessage()
            ], 500);
        }
    }
}




