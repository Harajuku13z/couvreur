<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\City;
use App\Models\GenerationJob;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdGenerationController extends Controller
{
    /**
     * Génération d'annonces par service et villes
     */
    public function generateByServiceCities(Request $request)
    {
        Log::info('Début génération service-villes', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            // Validation personnalisée pour service_slug
            $request->validate([
                'service_slug' => 'required|string',
                'city_ids' => 'required|array|min:1',
                'city_ids.*' => 'required|integer|exists:cities,id',
                'ai_prompt' => 'nullable|string|max:5000',
                'batch_size' => 'nullable|integer|min:1|max:50'
            ]);

            // Vérifier que le service existe dans les settings
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            if (!is_array($services)) {
                $services = [];
            }
            
            $serviceSlug = $request->input('service_slug');
            $serviceExists = collect($services)->contains('slug', $serviceSlug);
            
            if (!$serviceExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le service sélectionné n\'existe pas'
                ], 422);
            }

            $validated = $request->all();

            $serviceSlug = $validated['service_slug'];
            $cityIds = $validated['city_ids'];
            $aiPrompt = $validated['ai_prompt'] ?? '';
            $batchSize = $validated['batch_size'] ?? 20;

            Log::info('Validation réussie', [
                'service_slug' => $serviceSlug,
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

            // Créer un job de génération
            $job = GenerationJob::create([
                'mode' => 'keyword_cities',
                'payload_json' => json_encode([
                    'service_slug' => $serviceSlug,
                    'city_ids' => $cityIds,
                    'ai_prompt' => $aiPrompt
                ]),
                'status' => 'queued'
            ]);

            Log::info('Job créé', ['job_id' => $job->id]);

            return response()->json([
                'success' => true,
                'message' => 'Génération lancée avec succès',
                'job_id' => $job->id,
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation service-villes', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur génération service-villes: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génération d'annonces par mot-clé et villes
     */
    public function generateByKeywordCities(Request $request)
    {
        Log::info('Début génération mot-clé-villes', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            $validated = $request->validate([
                'keyword' => 'required|string|max:255',
                'city_ids' => 'required|array|min:1',
                'city_ids.*' => 'required|integer|exists:cities,id',
                'ai_prompt' => 'nullable|string|max:5000',
                'batch_size' => 'nullable|integer|min:1|max:50'
            ]);

            $keyword = $validated['keyword'];
            $cityIds = $validated['city_ids'];
            $aiPrompt = $validated['ai_prompt'] ?? '';
            $batchSize = $validated['batch_size'] ?? 20;

            Log::info('Validation réussie', [
                'keyword' => $keyword,
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

            // Créer un job de génération
            $job = GenerationJob::create([
                'mode' => 'keyword_cities',
                'payload_json' => json_encode([
                    'keyword' => $keyword,
                    'city_ids' => $cityIds,
                    'ai_prompt' => $aiPrompt
                ]),
                'status' => 'queued'
            ]);

            Log::info('Job créé', ['job_id' => $job->id]);

            return response()->json([
                'success' => true,
                'message' => 'Génération lancée avec succès',
                'job_id' => $job->id,
                'cities_count' => count($cityIds),
                'batch_size' => $batchSize
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation mot-clé-villes', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(', ', collect($e->errors())->flatten()->toArray())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur génération mot-clé-villes: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statut d'un job de génération
     */
    public function jobStatus(GenerationJob $job)
    {
        return response()->json([
            'id' => $job->id,
            'status' => $job->status,
            'created_at' => $job->created_at,
            'updated_at' => $job->updated_at,
            'payload' => json_decode($job->payload_json, true)
        ]);
    }
}