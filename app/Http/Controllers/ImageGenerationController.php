<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageGenerationController extends Controller
{
    public function generateImage(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        try {
            // Configuration de l'API OpenAI
            $apiKey = setting('chatgpt_api_key');
            
            // Si pas trouvée, essayer directement en base
            if (!$apiKey) {
                $setting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
                $apiKey = $setting ? $setting->value : null;
            }
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API OpenAI non configurée. Veuillez la configurer dans /config'
                ], 400);
            }

            // Optimiser le prompt pour des images réalistes
            $realisticPrompt = "Photographie réaliste et professionnelle. " . $validated['prompt'] . " Photo de haute qualité, style documentaire, éclairage naturel, composition professionnelle, image authentique et réaliste, pas de dessin ni d'illustration.";
            
            // Appel à l'API DALL-E
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/images/generations', [
                'prompt' => $realisticPrompt,
                'n' => 1,
                'size' => '1792x1024',
                'quality' => 'hd',
                'response_format' => 'url'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $imageUrl = $data['data'][0]['url'] ?? null;

                if ($imageUrl) {
                    // Télécharger l'image et la sauvegarder
                    $imageResponse = Http::get($imageUrl);
                    if ($imageResponse->successful()) {
                        $filename = 'generated_images/' . uniqid() . '.png';
                        Storage::disk('public')->put($filename, $imageResponse->body());
                        
                        // S'assurer que l'URL inclut le port correct
                        $baseUrl = request()->getSchemeAndHttpHost();
                        $localUrl = $baseUrl . '/storage/' . $filename;
                        
                        Log::info('Image générée avec succès', [
                            'prompt' => $validated['prompt'],
                            'filename' => $filename,
                            'url' => $localUrl
                        ]);

                        return response()->json([
                            'success' => true,
                            'image_url' => $localUrl,
                            'filename' => $filename
                        ]);
                    }
                }
            }

            Log::error('Erreur génération image', [
                'prompt' => $validated['prompt'],
                'response' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de l\'image'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Exception génération image', [
                'prompt' => $validated['prompt'],
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur interne: ' . $e->getMessage()
            ], 500);
        }
    }
}
