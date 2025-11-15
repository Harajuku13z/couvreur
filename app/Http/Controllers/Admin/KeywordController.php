<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeywordImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KeywordController extends Controller
{
    /**
     * Afficher la page de gestion des mots-clés
     */
    public function index()
    {
        // Récupérer les mots-clés personnalisés
        $customKeywordsData = \App\Models\Setting::where('key', 'seo_custom_keywords')->value('value') ?? '[]';
        $customKeywords = json_decode($customKeywordsData, true) ?? [];
        if (!is_array($customKeywords)) {
            $customKeywords = [];
        }
        
        // Récupérer toutes les images de mots-clés
        $keywordImages = KeywordImage::orderBy('keyword')->orderBy('display_order')->get();
        
        // Créer un tableau associatif keyword => images
        $keywordsWithImages = [];
        foreach ($customKeywords as $keyword) {
            $keywordsWithImages[$keyword] = $keywordImages->where('keyword', $keyword)->first();
        }
        
        // Ajouter les mots-clés qui ont des images mais ne sont pas dans la liste
        foreach ($keywordImages as $keywordImage) {
            if (!in_array($keywordImage->keyword, $customKeywords)) {
                $keywordsWithImages[$keywordImage->keyword] = $keywordImage;
            }
        }
        
        return view('admin.keywords.index', compact('customKeywords', 'keywordImages', 'keywordsWithImages'));
    }
    
    /**
     * Générer des mots-clés via IA
     */
    public function generateKeywords(Request $request)
    {
        try {
            $servicesData = \App\Models\Setting::where('key', 'services')->value('value') ?? '[]';
            $services = json_decode($servicesData, true) ?? [];
            
            if (empty($services)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun service configuré. Configurez d\'abord vos services dans les paramètres.'
                ], 400);
            }
            
            $aiService = app(\App\Services\AiService::class);
            
            // Construire le prompt pour générer des mots-clés
            $prompt = "Génère une liste de 20-30 mots-clés SEO pertinents pour un site de couvreur/toiture en France. ";
            $prompt .= "Les mots-clés doivent être :\n";
            $prompt .= "- Spécifiques et locaux (ex: 'couvreur Paris', 'réparation toiture Lyon')\n";
            $prompt .= "- Variés (services, urgences, matériaux, types de toiture)\n";
            $prompt .= "- Longue traîne (3-5 mots)\n";
            $prompt .= "- En français\n\n";
            $prompt .= "Services proposés : " . implode(', ', array_column($services, 'name')) . "\n\n";
            $prompt .= "Retourne UNIQUEMENT la liste des mots-clés, un par ligne, sans numérotation ni puces.";
            
            $response = $aiService->generateContent($prompt, [
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);
            
            $keywords = [];
            if (!empty($response)) {
                $lines = explode("\n", $response);
                foreach ($lines as $line) {
                    $line = trim($line);
                    // Nettoyer les lignes (enlever numéros, puces, etc.)
                    $line = preg_replace('/^[\d\.\-\*\)\s]+/', '', $line);
                    $line = trim($line);
                    if (!empty($line) && !in_array($line, $keywords)) {
                        $keywords[] = $line;
                    }
                }
            }
            
            // Si pas assez de mots-clés, en générer depuis les services
            if (count($keywords) < 10) {
                foreach ($services as $service) {
                    $serviceName = $service['name'] ?? '';
                    if (!empty($serviceName)) {
                        $variations = [
                            $serviceName,
                            "devis {$serviceName}",
                            "prix {$serviceName}",
                            "{$serviceName} pas cher",
                            "{$serviceName} professionnel",
                        ];
                        foreach ($variations as $item) {
                            if (!empty($item) && strlen($item) >= 3 && strlen($item) <= 100 && !in_array($item, $keywords)) {
                                $keywords[] = $item;
                            }
                        }
                    }
                }
            }
            
            $keywords = array_slice(array_unique($keywords), 0, 30);
            
            return response()->json([
                'status' => 'success',
                'keywords' => $keywords,
                'message' => count($keywords) . ' mots-clés générés avec succès.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération mots-clés', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sauvegarder les mots-clés
     */
    public function saveKeywords(Request $request)
    {
        try {
            // Récupérer les mots-clés depuis le textarea (séparés par des retours à la ligne)
            $keywordsText = $request->input('keywords_text', '');
            
            // Si keywords_text n'existe pas, essayer keywords[] (tableau)
            if (empty($keywordsText)) {
                $keywordsArray = $request->input('keywords', []);
                if (is_array($keywordsArray)) {
                    $keywordsText = implode("\n", $keywordsArray);
                }
            }
            
            // Séparer par lignes et nettoyer
            $keywords = array_filter(
                array_map('trim', explode("\n", $keywordsText)),
                function($keyword) {
                    return !empty($keyword) && strlen($keyword) <= 255;
                }
            );
            $keywords = array_values(array_unique($keywords)); // Supprimer les doublons
            
            // Vérifier qu'il y a au moins un mot-clé
            if (empty($keywords)) {
                return redirect()->back()
                    ->with('error', 'Aucun mot-clé valide à sauvegarder. Veuillez ajouter au moins un mot-clé.');
            }
            
            // Sauvegarder la liste des mots-clés
            \App\Models\Setting::set('seo_custom_keywords', json_encode($keywords), 'json', 'seo');
            
            // Mettre à jour l'ordre d'affichage des images existantes
            foreach ($keywords as $index => $keyword) {
                $existingImage = KeywordImage::where('keyword', $keyword)->first();
                if ($existingImage) {
                    $existingImage->update(['display_order' => $index]);
                }
            }
            
            Log::info('Mots-clés sauvegardés', [
                'count' => count($keywords),
                'keywords' => $keywords
            ]);
            
            return redirect()->back()
                ->with('success', count($keywords) . ' mots-clés sauvegardés avec succès.');
                
        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde mots-clés', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la sauvegarde : ' . $e->getMessage());
        }
    }
    
    /**
     * Ajouter une image à un mot-clé
     */
    public function storeImage(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $image = $request->file('image');
            $keyword = trim($validated['keyword']);
            
            // Créer le dossier s'il n'existe pas
            $uploadDir = public_path('images/keywords');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Nom du fichier
            $filename = 'keyword-' . Str::slug($keyword) . '-' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = 'images/keywords/' . $filename;
            
            // Déplacer l'image
            $image->move($uploadDir, $filename);
            
            // Créer ou mettre à jour l'entrée dans la base de données
            $keywordImage = KeywordImage::updateOrCreate(
                ['keyword' => $keyword],
                [
                    'image_path' => $imagePath,
                    'title' => $validated['title'] ?? $keyword,
                    'is_active' => true,
                ]
            );
            
            return redirect()->back()
                ->with('success', "✅ Image ajoutée avec succès pour le mot-clé \"{$keyword}\"");
                
        } catch (\Exception $e) {
            Log::error('Erreur ajout image mot-clé', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de l\'ajout de l\'image : ' . $e->getMessage());
        }
    }
    
    /**
     * Supprimer une image de mot-clé
     */
    public function destroyImage(KeywordImage $keywordImage)
    {
        try {
            $keyword = $keywordImage->keyword;
            
            // Supprimer le fichier physique
            if ($keywordImage->image_path && file_exists(public_path($keywordImage->image_path))) {
                unlink(public_path($keywordImage->image_path));
            }
            
            // Supprimer l'entrée de la base de données
            $keywordImage->delete();
            
            return redirect()->back()
                ->with('success', "✅ Image supprimée pour le mot-clé \"{$keyword}\"");
                
        } catch (\Exception $e) {
            Log::error('Erreur suppression image mot-clé', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
    
    /**
     * Mettre à jour une image de mot-clé
     */
    public function updateImage(Request $request, KeywordImage $keywordImage)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        try {
            $keywordImage->update([
                'title' => $validated['title'] ?? $keywordImage->title,
                'is_active' => $validated['is_active'] ?? $keywordImage->is_active,
                'display_order' => $validated['display_order'] ?? $keywordImage->display_order,
            ]);
            
            return redirect()->back()
                ->with('success', "✅ Image mise à jour avec succès");
                
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour image mot-clé', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', '❌ Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }
}

