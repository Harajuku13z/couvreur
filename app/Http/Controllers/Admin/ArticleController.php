<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\AiService;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.articles.index', compact('articles'));
    }

    public function show(Article $article)
    {
        return view('admin.articles.show', compact('article'));
    }

    public function create()
    {
        return view('admin.articles.create');
    }

    public function generate()
    {
        return view('admin.articles.generate');
    }

    public function seoCreate()
    {
        $cities = collect();

        try {
            $query = City::query();

            if (\Illuminate\Support\Facades\Schema::hasColumn('cities', 'is_active')) {
                $query->where(function ($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasColumn('cities', 'active')) {
                $query->where(function ($q) {
                    $q->where('active', true)->orWhereNull('active');
                });
            }

            $cities = $query
                ->orderBy('name')
                ->limit(3000)
                ->get(['id', 'name', 'postal_code', 'department', 'region']);
        } catch (\Throwable $e) {
            Log::warning('Impossible de charger les villes pour l assistant SEO article', [
                'message' => $e->getMessage(),
            ]);
        }

        return view('admin.articles.seo-create', compact('cities'));
    }

    public function seoStore(Request $request)
    {
        @set_time_limit(300);

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'city_id' => 'nullable|exists:cities,id',
            'city_name' => 'nullable|string|max:180',
            'keywords' => 'required|string|max:8000',
            'secondary_keywords' => 'nullable|string|max:8000',
            'brief' => 'nullable|string|max:8000',
            'tone' => 'nullable|in:expert,local,practical,premium',
            'word_count' => 'nullable|integer|min:900|max:3500',
            'status' => 'required|in:draft,published',
            'photos' => 'nullable|array|max:12',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
            'photo_alt' => 'nullable|array',
            'photo_alt.*' => 'nullable|string|max:255',
            'photo_caption' => 'nullable|array',
            'photo_caption.*' => 'nullable|string|max:500',
        ]);

        try {
            $city = !empty($validated['city_id']) ? City::find($validated['city_id']) : null;
            $cityName = trim($validated['city_name'] ?? '') ?: ($city?->name ?? setting('company_city', ''));
            $primaryKeywords = $this->parseSeoKeywords($validated['keywords'], 80);
            $secondaryKeywords = $this->parseSeoKeywords($validated['secondary_keywords'] ?? '', 80);
            $allKeywords = array_values(array_unique(array_merge($primaryKeywords, $secondaryKeywords)));
            $uploadedImages = $this->uploadSeoArticlePhotos($request, $validated['title'], $cityName, $allKeywords);

            $payload = $this->generateLongSeoArticlePayload([
                'title' => $validated['title'],
                'city_name' => $cityName,
                'keywords' => $primaryKeywords,
                'secondary_keywords' => $secondaryKeywords,
                'brief' => $validated['brief'] ?? '',
                'tone' => $validated['tone'] ?? 'expert',
                'word_count' => (int) ($validated['word_count'] ?? 1800),
            ], $uploadedImages);

            $contentHtml = $this->injectSeoArticleImages(
                $payload['content_html'] ?? '',
                $uploadedImages
            );
            $contentHtml = $this->normalizeImageUrlsInContent($contentHtml);
            $plainText = trim(preg_replace('/\s+/', ' ', strip_tags($contentHtml)));
            $readingTime = max(1, (int) ceil(str_word_count($plainText) / 220));
            $featuredImage = $uploadedImages[0]['path'] ?? null;
            $focusKeyword = $payload['focus_keyword'] ?? ($primaryKeywords[0] ?? $this->extractFocusKeyword($validated['title']));
            $tags = array_slice(array_values(array_unique(array_filter(array_merge([$focusKeyword], $allKeywords)))), 0, 20);

            $article = Article::create([
                'title' => $validated['title'],
                'slug' => $this->makeUniqueArticleSlug($validated['title'], $cityName),
                'excerpt' => $payload['excerpt'] ?? Str::limit($plainText, 220),
                'content_html' => $contentHtml,
                'featured_image' => $featuredImage,
                'meta_title' => Str::limit($payload['meta_title'] ?? $this->buildSeoMetaTitle($validated['title'], $cityName), 500, ''),
                'meta_description' => Str::limit($payload['meta_description'] ?? $this->buildSeoMetaDescription($validated['title'], $cityName), 500, ''),
                'meta_keywords' => Str::limit($payload['meta_keywords'] ?? implode(', ', $tags), 5000, ''),
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null,
                'is_published' => $validated['status'] === 'published',
                'city_id' => $city?->id,
                'focus_keyword' => $focusKeyword,
                'estimated_reading_time' => $readingTime,
                'tags' => $tags,
            ]);

            foreach ($uploadedImages as $image) {
                ArticleImage::create([
                    'article_id' => $article->id,
                    'image_path' => $image['path'],
                    'alt_text' => $image['alt'],
                    'keywords' => implode(', ', array_slice($allKeywords, 0, 12)),
                    'title' => $image['title'],
                    'description' => $image['caption'],
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'file_size' => $image['file_size'],
                    'mime_type' => $image['mime_type'],
                ]);
            }

            return redirect()
                ->route('admin.articles.show', $article)
                ->with('success', 'Article SEO créé avec succès. Vous pouvez le relire, ajuster le HTML et publier si besoin.');
        } catch (\Throwable $e) {
            Log::error('Erreur création article SEO IA', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Impossible de créer l article SEO: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Validation conditionnelle selon le type d'input
        $rules = [
            'title' => 'required|string|max:500',
            'content_html' => 'required|string',
            'meta_title' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:5000',
            'status' => 'required|in:draft,published'
        ];
        
        // Si c'est un fichier uploadé, valider comme image
        if ($request->hasFile('featured_image')) {
            $rules['featured_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        } else {
            $rules['featured_image'] = 'nullable|string';
        }
        
        $validated = $request->validate($rules);

        $featuredImagePath = null;
        
        // Cas 1: Upload de fichier
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $featuredImagePath = $this->handleImageUpload($file);
        }
        // Cas 2: Path depuis la galerie (string qui commence par uploads/ ou images/)
        elseif ($request->has('featured_image') && is_string($request->input('featured_image'))) {
            $imageInput = $request->input('featured_image');
            // Vérifier que le path existe et est valide
            if (str_starts_with($imageInput, 'uploads/') || str_starts_with($imageInput, 'images/')) {
                $fullPath = public_path($imageInput);
                if (file_exists($fullPath) && is_file($fullPath)) {
                    $featuredImagePath = $imageInput;
        }
            }
            // Cas 3: URL externe (commence par http:// ou https://)
            elseif (filter_var($imageInput, FILTER_VALIDATE_URL)) {
                $featuredImagePath = $imageInput;
            }
        }

        // Normaliser les URLs d'images dans le contenu HTML
        $normalizedContent = $this->normalizeImageUrlsInContent($validated['content_html']);

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content_html' => $normalizedContent,
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'meta_keywords' => $validated['meta_keywords'],
            'featured_image' => $featuredImagePath,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        // Lier les images uploadées sans article_id à cet article
        \App\Models\ArticleImage::whereNull('article_id')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->update(['article_id' => $article->id]);

        return redirect()->route('admin.articles.show', $article)
            ->with('success', 'Article créé avec succès');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        try {
            Log::info('ArticleController::update - Début', [
                'article_id' => $article->id,
                'request_method' => $request->method(),
                'has_content_html' => $request->has('content_html'),
                'content_html_length' => strlen($request->input('content_html', '')),
                'has_featured_image' => $request->has('featured_image'),
                'has_file_featured_image' => $request->hasFile('featured_image'),
                'all_inputs' => array_keys($request->all()),
            ]);

            // Validation conditionnelle selon le type d'input
            $rules = [
            'title' => 'required|string|max:500',
            'content_html' => 'required|string',
            'meta_title' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:5000',
            'status' => 'required|in:draft,published'
            ];
            
            // Si c'est un fichier uploadé, valider comme image
            if ($request->hasFile('featured_image')) {
                $rules['featured_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
                Log::info('ArticleController::update - Validation fichier image', [
                    'file_name' => $request->file('featured_image')->getClientOriginalName(),
                    'file_size' => $request->file('featured_image')->getSize(),
                ]);
            } else {
                $rules['featured_image'] = 'nullable|string';
                Log::info('ArticleController::update - Validation string image', [
                    'featured_image_value' => $request->input('featured_image'),
                    'featured_image_type' => gettype($request->input('featured_image')),
                ]);
            }
            
            $validated = $request->validate($rules);
            
            Log::info('ArticleController::update - Validation réussie', [
                'validated_keys' => array_keys($validated),
                'title' => $validated['title'] ?? 'N/A',
                'content_html_length' => strlen($validated['content_html'] ?? ''),
                'status' => $validated['status'] ?? 'N/A',
            ]);

            $featuredImagePath = $article->featured_image; // Garder l'image actuelle par défaut
            
            Log::info('ArticleController::update - Traitement image', [
                'current_featured_image' => $featuredImagePath,
                'has_file' => $request->hasFile('featured_image'),
                'has_input' => $request->has('featured_image'),
                'input_value' => $request->input('featured_image'),
            ]);
            
            // Cas 1: Upload de fichier
        if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $featuredImagePath = $this->handleImageUpload($file);
                Log::info('ArticleController::update - Fichier uploadé', [
                    'new_path' => $featuredImagePath,
                ]);
            }
            // Cas 2: Path depuis la galerie (string qui commence par uploads/ ou images/)
            elseif ($request->has('featured_image') && is_string($request->input('featured_image'))) {
                $imageInput = $request->input('featured_image');
                Log::info('ArticleController::update - Traitement path/URL', [
                    'image_input' => $imageInput,
                    'starts_with_uploads' => str_starts_with($imageInput, 'uploads/'),
                    'starts_with_images' => str_starts_with($imageInput, 'images/'),
                    'is_valid_url' => filter_var($imageInput, FILTER_VALIDATE_URL),
                ]);
                
                // Vérifier que le path existe et est valide
                if (str_starts_with($imageInput, 'uploads/') || str_starts_with($imageInput, 'images/')) {
                    $fullPath = public_path($imageInput);
                    if (file_exists($fullPath) && is_file($fullPath)) {
                        $featuredImagePath = $imageInput;
                        Log::info('ArticleController::update - Path valide trouvé', [
                            'full_path' => $fullPath,
                            'exists' => file_exists($fullPath),
                        ]);
                    } else {
                        Log::warning('ArticleController::update - Path invalide', [
                            'full_path' => $fullPath,
                            'exists' => file_exists($fullPath),
                        ]);
                    }
                }
                // Cas 3: URL externe (commence par http:// ou https://)
                elseif (filter_var($imageInput, FILTER_VALIDATE_URL)) {
                    $featuredImagePath = $imageInput;
                    Log::info('ArticleController::update - URL externe détectée', [
                        'url' => $imageInput,
                    ]);
        }
            }

            // Normaliser les URLs d'images dans le contenu HTML
            $normalizedContent = $this->normalizeImageUrlsInContent($validated['content_html']);
            $validated['content_html'] = $normalizedContent;

            $validated['featured_image'] = $featuredImagePath;
        $validated['published_at'] = $validated['status'] === 'published' ? now() : null;
            
            Log::info('ArticleController::update - Données avant update', [
                'featured_image' => $validated['featured_image'],
                'published_at' => $validated['published_at'],
                'title' => $validated['title'],
                'status' => $validated['status'],
            ]);
            
        $article->update($validated);
            
            Log::info('ArticleController::update - Update réussi', [
                'article_id' => $article->id,
                'updated_title' => $article->title,
                'updated_featured_image' => $article->featured_image,
                'updated_status' => $article->status,
            ]);

        return redirect()->route('admin.articles.show', $article)
            ->with('success', 'Article modifié avec succès');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ArticleController::update - Erreur de validation', [
                'errors' => $e->errors(),
                'article_id' => $article->id,
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('ArticleController::update - Erreur exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'article_id' => $article->id,
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', 'Article supprimé avec succès');
    }

    public function destroyAll(Request $request)
    {
        $request->validate([
            'ids' => 'nullable|array',
            'ids.*' => 'exists:articles,id'
        ]);

        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            // Si aucun ID n'est fourni, supprimer tous les articles
            $count = Article::count();
            Article::truncate();
            return redirect()->route('admin.articles.index')
                ->with('success', "Tous les articles ({$count}) ont été supprimés avec succès");
        } else {
            // Supprimer les articles sélectionnés
            $count = Article::whereIn('id', $ids)->count();
            Article::whereIn('id', $ids)->delete();
            return redirect()->route('admin.articles.index')
                ->with('success', "{$count} article(s) supprimé(s) avec succès");
        }
    }

    /**
     * Génération de titres d'articles avec IA
     */
    public function generateTitles(Request $request)
    {
        try {
            $validated = $request->validate([
                'keyword' => 'required|string|max:255',
                'instruction' => 'required|string|max:10000',
                'count' => 'required|integer|min:1|max:10'
            ]);

            $prompt = "Génère {$validated['count']} titres d'articles SEO optimisés pour le mot-clé : {$validated['keyword']}

            {$validated['instruction']}

            RÈGLES :
            - Titres entre 50 et 70 caractères
            - Inclure le mot-clé principal
            - Varier les formulations (guide, conseils, prix, comparatif, etc.)
            - Titres accrocheurs et informatifs
- Un titre par ligne
- Pas de numérotation

            GÉNÈRE LES TITRES :";

            $result = AiService::callAI($prompt, null, [
                'max_tokens' => 2000,
                'temperature' => 0.8
            ]);

            if ($result && isset($result['content'])) {
                $content = $result['content'];
                
                // Parser les titres
                $titles = array_filter(array_map('trim', explode("\n", $content)));
                
                // Nettoyer les titres
                $titles = array_map(function($title) {
                    // Supprimer les numéros, puces, tirets au début
                    $title = preg_replace('/^[\d\.\-\*\•\s]+/', '', $title);
                    // Supprimer les espaces multiples
                    $title = preg_replace('/\s+/', ' ', $title);
                    return trim($title);
                }, $titles);
                
                // Filtrer les titres valides
                $titles = array_filter($titles, function($title) {
                    return !empty($title) && 
                           strlen($title) > 10 && 
                           !preg_match('/^(Bien sûr|Pourriez-vous|Pouvez-vous|Je peux|Je serais|Voici|Voilà|Bien|D\'accord|Parfait|Excellente|Je vais|Je peux vous aider|Comment puis-je|Que souhaitez-vous|Je comprends|Je vais vous aider|Voici les titres|Voilà les titres|Voici une liste|Voilà une liste|Voici {$count}|Voilà {$count}|Voici exactement|Voilà exactement)/i', $title) &&
                           !preg_match('/\?$/', $title) && // Éliminer les questions
                           !preg_match('/^Voici/', $title) && // Éliminer "Voici les titres..."
                           !preg_match('/^Voilà/', $title) && // Éliminer "Voilà les titres..."
                           !preg_match('/^Je vais/', $title) && // Éliminer "Je vais générer..."
                           !preg_match('/^Voici une/', $title) && // Éliminer "Voici une liste..."
                           !preg_match('/^Voilà une/', $title); // Éliminer "Voilà une liste..."
                });
                
                // Si aucun titre valide n'est trouvé, créer des titres de base
                if (empty($titles)) {
                    $keyword = $validated['keyword'];
                    $count = $validated['count'] ?? 5;
                    $baseTitles = [
                        "Guide Complet pour {$keyword}",
                        "Top 10 des Meilleurs {$keyword} en 2024",
                        "Prix {$keyword}: Devis et Tarifs Détaillés",
                        "Comment Trouver un {$keyword} Fiable",
                        "Rénovation {$keyword}: Conseils d'Experts",
                        "Les Meilleurs {$keyword} de Qualité",
                        "Guide Pratique pour {$keyword}",
                        "Conseils d'Experts pour {$keyword}",
                        "Tout Savoir sur {$keyword}",
                        "Guide Débutant pour {$keyword}"
                    ];
                    $titles = array_slice($baseTitles, 0, $count);
                }
                
                return response()->json([
                    'success' => true,
                    'titles' => array_slice($titles, 0, $validated['count'] ?? 5)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération des titres'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération titres: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des titres: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génération de contenu d'article avec IA
     */
    public function generateContent(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:500',
                'keyword' => 'required|string|max:255',
                'instruction' => 'required|string|max:10000'
            ]);

            $prompt = "{$validated['instruction']}

Titre de l'article: {$validated['title']}
Mot-clé principal: {$validated['keyword']}

Génère l'article HTML complet selon les consignes du prompt ci-dessus.";

            $result = AiService::callAI($prompt, null, [
                'max_tokens' => 6000,
                'temperature' => 0.8
            ]);

            if ($result && isset($result['content'])) {
                $content = $result['content'];
                
                if (empty(trim($content))) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Contenu généré vide'
                    ], 400);
                }
                
                return response()->json([
                    'success' => true,
                    'content' => $content
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la génération du contenu'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération contenu: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du contenu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload d'image pour article avec métadonnées SEO
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
                'alt_text' => 'nullable|string|max:255',
                'keywords' => 'nullable|string|max:500',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'article_id' => 'nullable|exists:articles,id',
            ]);

            $image = $request->file('image');
            
            // Récupérer les informations AVANT de déplacer le fichier
            $fileSize = $image->getSize();
            $mimeType = $image->getMimeType();
            
            $filename = 'article_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('uploads/articles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Sauvegarder directement dans public/uploads/articles/
            $image->move($uploadPath, $filename);
            
            // Obtenir les dimensions de l'image (après le déplacement, utiliser le fichier déplacé)
            $fullPath = $uploadPath . '/' . $filename;
            $imageInfo = @getimagesize($fullPath);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;
            
            // Générer l'URL complète et le chemin relatif
            $imagePath = 'uploads/articles/' . $filename;
            // Utiliser asset() au lieu de url() pour générer une URL absolue correcte
            $imageUrl = asset($imagePath);
            
            // Générer un alt text automatique si non fourni
            $altText = $request->input('alt_text');
            if (empty($altText) && $request->input('title')) {
                $altText = $request->input('title');
            } elseif (empty($altText)) {
                // Générer un alt text basique à partir du nom de fichier
                $altText = 'Image article - ' . pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            }
            
            // Créer l'enregistrement dans la base de données
            $articleImage = \App\Models\ArticleImage::create([
                'article_id' => $request->input('article_id'),
                'image_path' => $imagePath,
                'alt_text' => $altText,
                'keywords' => $request->input('keywords'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'width' => $width,
                'height' => $height,
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
            ]);
            
            return response()->json([
                'success' => true,
                'image_url' => $imageUrl,
                'image_id' => $articleImage->id,
                'image_path' => $imagePath,
                'alt_text' => $altText,
                'message' => 'Image uploadée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur upload image article: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload de l\'image: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mettre à jour les métadonnées d'une image
     */
    public function updateImageMetadata(Request $request, $imageId)
    {
        try {
            $request->validate([
                'alt_text' => 'nullable|string|max:255',
                'keywords' => 'nullable|string|max:500',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $image = \App\Models\ArticleImage::findOrFail($imageId);
            
            $image->update([
                'alt_text' => $request->input('alt_text', $image->alt_text),
                'keywords' => $request->input('keywords', $image->keywords),
                'title' => $request->input('title', $image->title),
                'description' => $request->input('description', $image->description),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Métadonnées mises à jour avec succès',
                'image' => $image
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour métadonnées image: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lister les images d'un article
     */
    public function getArticleImages($articleId)
    {
        try {
            $images = \App\Models\ArticleImage::where('article_id', $articleId)
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'images' => $images
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération images article: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les liens du menu pour l'éditeur
     */
    public function getMenuLinks()
    {
        try {
            $links = [];
            
            // Lien Accueil
            $links[] = [
                'url' => route('home'),
                'label' => 'Accueil',
                'category' => 'Navigation principale'
            ];
            
            // Services
            $servicesData = \App\Models\Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            if (!is_array($services)) {
                $services = [];
            }
            
            $featuredServices = array_filter($services, function($service) {
                return is_array($service) && ($service['is_menu'] ?? false) && ($service['is_visible'] ?? true);
            });
            
            if (count($featuredServices) > 0) {
                $links[] = [
                    'url' => route('services.index'),
                    'label' => 'Tous nos services',
                    'category' => 'Services'
                ];
                
                foreach ($featuredServices as $service) {
                    if (is_array($service) && isset($service['name']) && isset($service['slug'])) {
                        $links[] = [
                            'url' => route('services.show', $service['slug']),
                            'label' => $service['name'],
                            'category' => 'Services'
                        ];
                    }
                }
            } else {
                $links[] = [
                    'url' => route('services.index'),
                    'label' => 'Nos Services',
                    'category' => 'Services'
                ];
            }
            
            // Réalisations
            $links[] = [
                'url' => route('portfolio.index'),
                'label' => 'Nos Réalisations',
                'category' => 'Navigation principale'
            ];
            
            // Blog
            $links[] = [
                'url' => route('blog.index'),
                'label' => 'Blog et Astuces',
                'category' => 'Navigation principale'
            ];
            
            // Contact
            $links[] = [
                'url' => route('contact'),
                'label' => 'Contact',
                'category' => 'Navigation principale'
            ];
            
            // Pages légales
            $links[] = [
                'url' => route('legal.mentions'),
                'label' => 'Mentions légales',
                'category' => 'Pages légales'
            ];
            
            $links[] = [
                'url' => route('legal.privacy'),
                'label' => 'Politique de confidentialité',
                'category' => 'Pages légales'
            ];
            
            $links[] = [
                'url' => route('legal.cgv'),
                'label' => 'CGV',
                'category' => 'Pages légales'
            ];
            
            // Formulaire de devis
            $links[] = [
                'url' => route('form.step', 'propertyType'),
                'label' => 'Simulateur de Prix / Devis',
                'category' => 'Actions'
            ];
            
            return response()->json([
                'success' => true,
                'links' => $links
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération liens menu: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister toutes les images disponibles dans le projet (portfolio, articles, services, etc.)
     */
    public function getAvailableImages()
    {
        try {
            $images = [];
            
            // Dossiers à scanner
            $directories = [
                'uploads/portfolio' => 'Réalisations',
                'uploads/articles' => 'Articles',
                'uploads/services' => 'Services',
                'uploads/homepage' => 'Page d\'accueil',
                'images' => 'Images générales',
            ];
            
            foreach ($directories as $dir => $category) {
                $fullPath = public_path($dir);
                if (is_dir($fullPath)) {
                    // Scanner récursivement tous les fichiers images
                    $iterator = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                        \RecursiveIteratorIterator::SELF_FIRST
                    );
                    
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $extension = strtolower($file->getExtension());
                            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                $fullPath = $file->getPathname();
                                // Normaliser le chemin (remplacer les backslashes par des slashes)
                                $normalizedPath = str_replace('\\', '/', $fullPath);
                                $publicPath = str_replace('\\', '/', public_path());
                                $relativePath = str_replace($publicPath . '/', '', $normalizedPath);
                                
                                $images[] = [
                                    'path' => $relativePath,
                                    'url' => asset($relativePath),
                                    'name' => $file->getFilename(),
                                    'category' => $category,
                                    'size' => $file->getSize(),
                                    'modified' => $file->getMTime()
                                ];
                            }
                        }
                    }
                }
            }
            
            // Trier par date de modification (plus récentes en premier)
            usort($images, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
            
            return response()->json([
                'success' => true,
                'images' => $images,
                'count' => count($images)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération images disponibles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createFromTitles(Request $request)
    {
        $request->validate([
            'titles' => 'required|array|min:1',
            'titles.*' => 'required|string|max:500',
            'featured_image' => 'nullable|string'
        ]);

        $created = 0;
        $titles = $request->input('titles');
        $featuredImage = $request->input('featured_image');

        foreach ($titles as $title) {
            // Vérifier si l'article existe déjà
            $existingArticle = Article::where('title', $title)->first();
            if ($existingArticle) {
                continue;
            }

            // Générer le contenu avec l'IA
            $content = $this->generateArticleContent($title, '');
            
            // Calculer le temps de lecture estimé
            $wordCount = str_word_count(strip_tags($content));
            $estimatedReadingTime = max(1, round($wordCount / 200)); // 200 mots par minute
            
            // Créer l'article
            $article = new Article();
            $article->title = $title;
            $article->slug = \Str::slug($title);
            $article->content_html = $content;
            $article->meta_title = $title . ' - Guide Complet 2024';
            $article->meta_description = 'Découvrez tout sur ' . $title . ' : guide complet, conseils d\'experts, et informations détaillées.';
            $article->meta_keywords = $this->generateMetaKeywords($title);
            $article->featured_image = $featuredImage;
            $article->status = 'published';
            $article->published_at = now();
            $article->estimated_reading_time = $estimatedReadingTime;
            $article->focus_keyword = $this->extractFocusKeyword($title);
            $article->save();

            $created++;
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'message' => $created . ' articles créés avec succès'
        ]);
    }

    /**
     * Génération de contenu d'article avec IA (copié exactement des services)
     */
    private function generateArticleContent($title, $keyword)
    {
        // Récupérer la clé API depuis la base de données
            $apiKey = setting('chatgpt_api_key');
            
        // Si pas trouvée, essayer directement en base
            if (!$apiKey) {
            $setting = \App\Models\Setting::where('key', 'chatgpt_api_key')->first();
            $apiKey = $setting ? $setting->value : null;
        }
        
        if (!$apiKey) {
            Log::error('Clé API manquante pour génération article');
            return $this->generateFallbackContent($title, $keyword);
        }
        
        try {
            // Récupérer les informations de l'entreprise
            $companyInfo = $this->getCompanyInfo();
            
            // Prompt avec structure spécifique demandée (copié des services)
            $prompt = "Crée un contenu HTML professionnel pour cet article de blog.

INFORMATIONS:
- Entreprise: {$companyInfo['company_name']}
- Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
- Article: {$title}
- Mot-clé: {$keyword}";

            $prompt .= "\n\nSTRUCTURE HTML OBLIGATOIRE - EXACTEMENT COMME CET EXEMPLE:
<div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8\">
  <h1 class=\"text-4xl font-bold text-gray-900 mb-6 text-center\">{$title}</h1>
  
  <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
    <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🏠 Introduction</h2>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Introduction engageante sur {$title} à {$companyInfo['company_city']}, {$companyInfo['company_region']}]</p>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Expertise spécifique sur {$title} par {$companyInfo['company_name']}]</p>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Approche professionnelle et satisfaction client]</p>
  </div>
  
  <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
    <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">[Titre naturel et accrocheur lié à {$title}]</h2>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Contenu détaillé et informatif sur {$title}]</p>
    <ul class=\"list-disc list-inside text-gray-700 mb-2\">
      <li class=\"mb-2\">[Point important 1 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 2 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 3 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 4 spécifique à {$title}]</li>
    </ul>
  </div>
  
  <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
    <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">[Titre naturel et accrocheur lié à {$title}]</h2>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Contenu détaillé et informatif sur {$title}]</p>
    <ul class=\"list-disc list-inside text-gray-700 mb-2\">
      <li class=\"mb-2\">[Point important 1 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 2 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 3 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 4 spécifique à {$title}]</li>
    </ul>
  </div>
  
  <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
    <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">[Titre naturel et accrocheur lié à {$title}]</h2>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Contenu détaillé et informatif sur {$title}]</p>
    <ul class=\"list-disc list-inside text-gray-700 mb-2\">
      <li class=\"mb-2\">[Point important 1 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 2 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 3 spécifique à {$title}]</li>
      <li class=\"mb-2\">[Point important 4 spécifique à {$title}]</li>
    </ul>
  </div>
  
  <div class=\"bg-green-50 p-4 rounded-lg mb-4\">
    <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">❓ Questions Fréquentes</h2>
    <div class=\"mb-4\">
      <h3 class=\"font-bold text-gray-800\">[Question 1 sur {$title}]</h3>
      <p class=\"text-gray-700\">[Réponse détaillée sur {$title}]</p>
    </div>
    <div class=\"mb-4\">
      <h3 class=\"font-bold text-gray-800\">[Question 2 sur {$title}]</h3>
      <p class=\"text-gray-700\">[Réponse détaillée sur {$title}]</p>
    </div>
    <div class=\"mb-4\">
      <h3 class=\"font-bold text-gray-800\">[Question 3 sur {$title}]</h3>
      <p class=\"text-gray-700\">[Réponse détaillée sur {$title}]</p>
    </div>
  </div>
  
  <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
    <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🎯 Conclusion</h2>
    <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Conclusion avec appel à l'action sur {$title}]</p>
    <div class=\"text-center mt-6\">
      <a href=\"tel:{$companyInfo['company_phone']}\" class=\"bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 inline-block\">
        📞 Appelez {$companyInfo['company_name']} maintenant
      </a>
    </div>
  </div>
</div>

INSTRUCTIONS DÉTAILLÉES:
1. ADAPTE complètement le contenu à l'article spécifique: {$title}
2. ÉCRIS du contenu PERSONNALISÉ selon le sujet de l'article
3. UTILISE les informations de l'entreprise: {$companyInfo['company_name']}
4. INTÉGRE la localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}
5. GARDE la structure HTML exacte de l'exemple ci-dessus
6. PERSONNALISE le contenu selon l'article (pas de contenu générique)
7. ÉCRIS du contenu UNIQUE et SPÉCIFIQUE à l'article
8. ADAPTE le vocabulaire et les formulations selon le sujet
9. INCLUE des informations sur le financement, les garanties, les délais
10. VARIE le contenu pour éviter les répétitions

CRITIQUE: NE PAS UTILISER DE PLACEHOLDERS COMME [Introduction engageante sur...]
- REMPLACE tous les [texte entre crochets] par du contenu réel et personnalisé
- ÉCRIS du contenu complet et détaillé, pas des descriptions de ce qu'il faut écrire
- GÉNÈRE du contenu professionnel et informatif
- ADAPTE le contenu à l'article spécifique: {$title}

IMPORTANT POUR LES TITRES DE SECTIONS:
- NE PAS utiliser des titres techniques comme Section Technique, Section Conseils, Section Avantages
- UTILISER des titres naturels et accrocheurs comme dans l'exemple:
  * Pourquoi hydrofuger sa toiture ?
  * Les facteurs influençant le coût d'un traitement hydrofuge
  * Prix moyen pour hydrofuger une toiture
  * Faire appel à un professionnel pour hydrofuger sa toiture
  * Optimisez votre budget pour hydrofuger votre toiture
- Les titres doivent être NATURELS et AGRÉABLES à lire
- Éviter les emojis dans les titres de sections (sauf pour Introduction et Questions Fréquentes)
- Créer des titres qui donnent envie de lire la suite

FORMAT JSON:
{
  \"content_html\": \"[HTML complet avec la structure exacte ci-dessus]\",
  \"meta_title\": \"[Titre SEO optimisé - 60 caractères max]\",
  \"meta_description\": \"[Description SEO engageante - 160 caractères max]\",
  \"meta_keywords\": \"[Mots-clés pertinents séparés par virgules]\"
}

IMPORTANT:
- SUIVEZ EXACTEMENT la structure HTML de l'exemple
- ÉCRIVEZ du contenu PERSONNALISÉ pour l'article {$title}
- ADAPTEZ le contenu selon le sujet spécifique
- GARDEZ les classes CSS et la structure
- UTILISEZ les informations de l'entreprise et de la localisation
- Le contenu doit être professionnel et engageant
- ÉVITEZ la répétition de phrases identiques
- Variez le vocabulaire et les formulations
- INCLUEZ des informations sur le financement et les garanties
- ADAPTEZ le contenu selon l'article spécifique

Réponds UNIQUEMENT avec le JSON valide, sans texte avant ou après.";

            Log::info('Génération IA complète pour article', [
                'title' => $title,
                'prompt_length' => strlen($prompt)
            ]);

            $result = AiService::callAI($prompt, null, [
                'max_tokens' => 4000,
                'temperature' => 0.8
            ]);

            if ($result && isset($result['content'])) {
                $content = $result['content'];
                
                Log::info('Réponse IA complète reçue', [
                    'title' => $title,
                    'provider' => $result['provider'],
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 300)
                ]);
                
                // Parser le JSON
                $jsonStart = strpos($content, '{');
                $jsonEnd = strrpos($content, '}');
                
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $jsonContent = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                    $aiData = json_decode($jsonContent, true);
                    
                    if ($aiData) {
                        return $aiData['content_html'] ?? $this->generateFallbackContent($title, $keyword);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération IA complète: ' . $e->getMessage());
        }
        
        // Fallback en cas d'échec
        return $this->generateFallbackContent($title, $keyword);
    }

    private function parseSeoKeywords(?string $keywords, int $limit = 60): array
    {
        $parts = preg_split('/[\n,;|]+/', (string) $keywords, -1, PREG_SPLIT_NO_EMPTY);
        $clean = [];

        foreach ($parts as $keyword) {
            $keyword = trim(preg_replace('/\s+/', ' ', strip_tags($keyword)));

            if ($keyword !== '' && mb_strlen($keyword) >= 2) {
                $clean[] = mb_strtolower($keyword);
            }
        }

        return array_slice(array_values(array_unique($clean)), 0, $limit);
    }

    private function uploadSeoArticlePhotos(Request $request, string $title, string $cityName, array $keywords): array
    {
        $photos = [];
        $files = $request->file('photos', []);
        $alts = $request->input('photo_alt', []);
        $captions = $request->input('photo_caption', []);
        $focusKeyword = $keywords[0] ?? $this->extractFocusKeyword($title);

        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $this->handleImageUpload($file);
            $imageInfo = @getimagesize(public_path($path));
            $alt = trim($alts[$index] ?? '');
            $caption = trim($captions[$index] ?? '');
            $fallbackAlt = trim("{$focusKeyword} {$cityName} - {$title}");

            $photos[] = [
                'path' => $path,
                'url' => asset($path),
                'alt' => $alt ?: Str::limit($fallbackAlt, 250, ''),
                'title' => Str::limit($alt ?: $fallbackAlt, 250, ''),
                'caption' => $caption ?: Str::limit("Illustration de {$focusKeyword}" . ($cityName ? " à {$cityName}" : ''), 480, ''),
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
                'file_size' => @filesize(public_path($path)) ?: null,
                'mime_type' => $file->getMimeType(),
            ];
        }

        return $photos;
    }

    private function generateLongSeoArticlePayload(array $brief, array $photos): array
    {
        $companyInfo = $this->getCompanyInfo();
        $keywords = implode(', ', array_slice($brief['keywords'], 0, 45));
        $secondaryKeywords = implode(', ', array_slice($brief['secondary_keywords'], 0, 45));
        $cityName = $brief['city_name'] ?: ($companyInfo['company_city'] ?? '');
        $wordCount = max(900, min(3500, (int) ($brief['word_count'] ?? 1800)));
        $imageInstructions = empty($photos)
            ? 'Aucune photo fournie: ne mets pas de placeholder image.'
            : 'Insere exactement ces placeholders aux endroits naturels du contenu: ' . implode(', ', array_map(fn ($i) => '[IMAGE_' . ($i + 1) . ']', array_keys($photos))) . '.';

        $systemMessage = "Tu es un redacteur SEO senior, specialiste des articles locaux pour artisans. Tu produis uniquement un JSON valide, sans markdown, sans texte avant ou apres.";

        $prompt = "Cree un article SEO HTML long, utile et pret a publier.

DONNEES:
- Titre: {$brief['title']}
- Ville liee: {$cityName}
- Entreprise: {$companyInfo['company_name']}
- Telephone: {$companyInfo['company_phone']}
- Specialite: {$companyInfo['company_specialization']}
- Adresse: {$companyInfo['company_address']}
- Mot-cles principaux: {$keywords}
- Mot-cles secondaires: {$secondaryKeywords}
- Brief libre: {$brief['brief']}
- Ton: {$brief['tone']}
- Longueur cible: environ {$wordCount} mots
- Photos: {$imageInstructions}

OBJECTIF QUALITE:
- Article original, local et vraiment utile pour un visiteur.
- Integrer beaucoup de mots-cles, mais naturellement: pas de bourrage, pas de listes cachees, pas de contenu spam.
- Structure claire type WordPress premium: introduction, sommaire, sections H2/H3, listes pratiques, FAQ, conclusion et appel a l'action.
- Repondre aux intentions de recherche: prix/facteurs, delais, methodes, erreurs a eviter, choix d'un professionnel, conseils d'entretien.
- Mentionner la ville et la zone locale sans repetition artificielle.
- Ne pas inventer de certifications ou de garanties precises non fournies.
- Ne pas parler de financement, MaPrimeRenov, aides CEE ou subventions.

HTML ATTENDU:
- Retourne uniquement le contenu du corps de l'article, sans <html>, <head>, <body>.
- Utilise des sections lisibles avec classes Tailwind compatibles:
  container: max-w-5xl mx-auto px-4 sm:px-6 lg:px-8
  sections: bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8
  titres: text-slate-950 / text-slate-900
  textes: text-slate-800 leading-relaxed
  listes: space-y-2 text-slate-800
- Inclure un seul h1 au debut.
- Inclure un sommaire avec ancres vers les H2.
- Inclure 5 a 7 questions FAQ visibles en HTML, sans schema JSON-LD FAQPage.
- Ajouter loading=\"lazy\" uniquement sur les images si tu generes une balise image. Sinon utilise seulement les placeholders fournis.

FORMAT JSON STRICT:
{
  \"content_html\": \"HTML complet\",
  \"meta_title\": \"Titre SEO unique 50-65 caracteres\",
  \"meta_description\": \"Meta description engageante 140-160 caracteres\",
  \"meta_keywords\": \"Liste longue de mots-cles separes par virgules\",
  \"excerpt\": \"Resume court de 180-220 caracteres\",
  \"focus_keyword\": \"mot-cle principal\",
  \"tags\": [\"tag 1\", \"tag 2\", \"tag 3\"]
}

Reponds uniquement avec ce JSON valide.";

        try {
            $result = AiService::callAI($prompt, $systemMessage, [
                'max_tokens' => 12000,
                'temperature' => 0.72,
            ]);

            $content = $result['content'] ?? '';
            $data = $this->extractJsonPayload($content);

            if (is_array($data) && !empty($data['content_html'])) {
                $data['tags'] = is_array($data['tags'] ?? null) ? $data['tags'] : $this->parseSeoKeywords((string) ($data['tags'] ?? ''), 20);
                return $data;
            }
        } catch (\Throwable $e) {
            Log::error('Erreur generation article SEO long', [
                'title' => $brief['title'] ?? null,
                'message' => $e->getMessage(),
            ]);
        }

        return $this->generateLongSeoFallbackPayload($brief, $photos);
    }

    private function extractJsonPayload(string $content): ?array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $json = substr($content, $start, $end - $start + 1);
        $decoded = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded) ? $decoded : null;
    }

    private function injectSeoArticleImages(string $html, array $photos): string
    {
        if (empty($photos)) {
            return preg_replace('/\[IMAGE_\d+\]/', '', $html);
        }

        foreach ($photos as $index => $photo) {
            $placeholder = '[IMAGE_' . ($index + 1) . ']';
            $figure = $this->buildSeoArticleFigure($photo);

            if (str_contains($html, $placeholder)) {
                $html = str_replace($placeholder, $figure, $html);
                continue;
            }

            $html = $this->insertFigureAfterSection($html, $figure, $index + 1);
        }

        return preg_replace('/\[IMAGE_\d+\]/', '', $html);
    }

    private function buildSeoArticleFigure(array $photo): string
    {
        $src = htmlspecialchars(asset($photo['path']), ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($photo['alt'], ENT_QUOTES, 'UTF-8');
        $caption = htmlspecialchars($photo['caption'], ENT_QUOTES, 'UTF-8');
        $width = $photo['width'] ? ' width="' . (int) $photo['width'] . '"' : '';
        $height = $photo['height'] ? ' height="' . (int) $photo['height'] . '"' : '';

        return '<figure class="my-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">'
            . '<img src="' . $src . '" alt="' . $alt . '"' . $width . $height . ' loading="lazy" decoding="async" class="w-full h-auto object-cover">'
            . '<figcaption class="px-5 py-3 text-sm text-slate-700 bg-slate-50">' . $caption . '</figcaption>'
            . '</figure>';
    }

    private function insertFigureAfterSection(string $html, string $figure, int $sectionNumber): string
    {
        $offset = 0;

        for ($i = 0; $i < $sectionNumber; $i++) {
            $position = stripos($html, '</section>', $offset);

            if ($position === false) {
                return $html . $figure;
            }

            $offset = $position + strlen('</section>');
        }

        return substr($html, 0, $offset) . $figure . substr($html, $offset);
    }

    private function makeUniqueArticleSlug(string $title, ?string $cityName = null): string
    {
        $base = Str::slug(trim($title . ' ' . ($cityName ?: ''))) ?: 'article-seo';
        $slug = $base;
        $counter = 2;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function buildSeoMetaTitle(string $title, ?string $cityName = null): string
    {
        return trim($title . ($cityName ? " a {$cityName}" : '') . ' | Conseils expert');
    }

    private function buildSeoMetaDescription(string $title, ?string $cityName = null): string
    {
        $location = $cityName ? " a {$cityName}" : '';

        return "Guide complet sur {$title}{$location}: conseils, methodes, erreurs a eviter et accompagnement professionnel.";
    }

    private function generateLongSeoFallbackPayload(array $brief, array $photos): array
    {
        $companyInfo = $this->getCompanyInfo();
        $cityName = $brief['city_name'] ?: ($companyInfo['company_city'] ?? '');
        $title = $brief['title'];
        $keywords = array_slice($brief['keywords'] ?? [], 0, 16);
        $focusKeyword = $keywords[0] ?? $this->extractFocusKeyword($title);
        $keywordList = implode(', ', $keywords);
        $imagePlaceholder = empty($photos) ? '' : '[IMAGE_1]';

        $html = '<article class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">'
            . '<header class="mb-8"><p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Guide local</p>'
            . '<h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-950 leading-tight">' . e($title) . ($cityName ? ' a ' . e($cityName) : '') . '</h1>'
            . '<p class="mt-5 text-lg text-slate-800 leading-relaxed">Ce guide vous aide a comprendre les points essentiels, les bonnes pratiques et les criteres a verifier avant de lancer votre projet avec un professionnel local.</p></header>'
            . '<nav class="bg-slate-50 border border-slate-200 rounded-2xl p-5 mb-8"><p class="font-semibold text-slate-950 mb-3">Sommaire</p><ol class="space-y-2 text-slate-800 list-decimal list-inside">'
            . '<li><a href="#comprendre" class="underline">Comprendre le besoin</a></li><li><a href="#methodes" class="underline">Methodes et etapes</a></li><li><a href="#choisir" class="underline">Choisir le bon professionnel</a></li><li><a href="#faq" class="underline">Questions frequentes</a></li></ol></nav>'
            . '<section id="comprendre" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8"><h2 class="text-2xl font-bold text-slate-950 mb-4">Comprendre votre projet' . ($cityName ? ' a ' . e($cityName) : '') . '</h2><p class="text-slate-800 leading-relaxed mb-4">' . e($title) . ' demande une analyse precise du contexte, de l etat existant, des contraintes techniques et du resultat attendu. Une preparation serieuse permet d eviter les mauvaises surprises et d obtenir un travail durable.</p><p class="text-slate-800 leading-relaxed">Les mots-cles importants a travailler naturellement sont: ' . e($keywordList) . '.</p></section>'
            . $imagePlaceholder
            . '<section id="methodes" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8"><h2 class="text-2xl font-bold text-slate-950 mb-4">Methodes, etapes et points de controle</h2><p class="text-slate-800 leading-relaxed mb-4">Un chantier reussi commence par un diagnostic, continue avec une methode adaptee, puis se termine par un controle propre du resultat.</p><ul class="space-y-2 text-slate-800 list-disc pl-6"><li>Identifier les contraintes d acces et de securite.</li><li>Choisir une technique adaptee au support et a l objectif.</li><li>Verifier la proprete, la finition et la durabilite de l intervention.</li><li>Conserver un contact clair avec l artisan pendant tout le chantier.</li></ul></section>'
            . '<section id="choisir" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8"><h2 class="text-2xl font-bold text-slate-950 mb-4">Pourquoi choisir ' . e($companyInfo['company_name']) . '</h2><p class="text-slate-800 leading-relaxed">' . e($companyInfo['company_name']) . ' accompagne les particuliers avec une approche locale, des explications claires et un travail soigne. L objectif est simple: vous aider a prendre la bonne decision et obtenir un resultat propre.</p></section>'
            . '<section id="faq" class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8"><h2 class="text-2xl font-bold text-slate-950 mb-5">Questions frequentes</h2><div class="space-y-5"><div><h3 class="font-semibold text-slate-950">Quand demander un devis ?</h3><p class="text-slate-800">Idealement des que le besoin est identifie, afin de comparer les solutions avant que la situation ne se degrade.</p></div><div><h3 class="font-semibold text-slate-950">Combien de temps prevoir ?</h3><p class="text-slate-800">Le delai depend de la complexite, de l acces et de la disponibilite des materiaux ou equipements.</p></div><div><h3 class="font-semibold text-slate-950">Comment eviter les erreurs ?</h3><p class="text-slate-800">Demandez une explication claire de la methode, des limites et des points de controle avant validation.</p></div></div></section>'
            . '<section class="bg-emerald-700 rounded-2xl p-6 md:p-8 text-white mb-8"><h2 class="text-2xl font-bold mb-3">Besoin d un avis professionnel ?</h2><p class="leading-relaxed mb-5">Contactez ' . e($companyInfo['company_name']) . ' pour obtenir un conseil adapte a votre situation.</p><a href="tel:' . e($companyInfo['company_phone']) . '" class="inline-flex px-5 py-3 rounded-xl bg-white text-emerald-800 font-semibold">Appeler maintenant</a></section>'
            . '</article>';

        return [
            'content_html' => $html,
            'meta_title' => $this->buildSeoMetaTitle($title, $cityName),
            'meta_description' => $this->buildSeoMetaDescription($title, $cityName),
            'meta_keywords' => implode(', ', array_slice(array_unique(array_merge([$focusKeyword], $keywords)), 0, 80)),
            'excerpt' => Str::limit(strip_tags($html), 220),
            'focus_keyword' => $focusKeyword,
            'tags' => array_slice(array_unique(array_merge([$focusKeyword], $keywords)), 0, 20),
        ];
    }

    /**
     * Récupérer les informations de l'entreprise (copié des services)
     */
    private function getCompanyInfo()
    {
        return [
            'company_name' => setting('company_name', 'Artisan Elfrick'),
            'company_phone' => setting('company_phone', '0777840495'),
            'company_city' => setting('company_city', 'Avrainville'),
            'company_region' => setting('company_region', 'Essonne'),
            'company_address' => setting('company_address', '4 bis, Chemin des Postes, Avrainville (91)'),
            'company_specialization' => setting('company_specialization', 'Travaux de Rénovation')
        ];
    }

    /**
     * Contenu de fallback en cas d'échec de l'IA (copié des services)
     */
    private function generateFallbackContent($title, $keyword)
    {
        $companyInfo = $this->getCompanyInfo();
        
        // Détecter le type d'article pour un contenu plus spécifique
        $titleLower = strtolower($title);
        $isHydrofuge = strpos($titleLower, 'hydrofuge') !== false || strpos($titleLower, 'hydrofugation') !== false;
        $isNettoyage = strpos($titleLower, 'nettoyage') !== false || strpos($titleLower, 'démoussage') !== false;
        $isElagage = strpos($titleLower, 'élagage') !== false || strpos($titleLower, 'abattage') !== false;
        $isRenovation = strpos($titleLower, 'rénovation') !== false || strpos($titleLower, 'renovation') !== false;
        
        // Contenu spécifique selon le type d'article
        if ($isHydrofuge) {
            $intro = "L'hydrofugation de toiture est une solution durable pour protéger votre couverture des intempéries et prolonger sa durée de vie. Chez " . $companyInfo['company_name'] . ", nous maîtrisons parfaitement cette technique essentielle pour maintenir l'intégrité de votre toit à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ".";
            $section1Title = "Pourquoi hydrofuger sa toiture ?";
            $section1Content = "L'hydrofugation apporte de nombreux avantages : protection contre l'humidité, limitation de la formation de mousses et lichens, amélioration de l'aspect esthétique, et prolongation de la durée de vie des matériaux.";
            $section2Title = "Les facteurs influençant le coût d'un traitement hydrofuge";
            $section2Content = "Le prix dépend de plusieurs éléments : la surface de la toiture, le type de produit utilisé, l'état initial de la couverture, l'accessibilité du chantier, et la région géographique.";
            $section3Title = "Prix moyen pour hydrofuger une toiture";
            $section3Content = "Le coût varie généralement entre 10€ et 30€ par m² pour l'hydrofuge seul, et entre 20€ et 50€ par m² si un nettoyage préalable est nécessaire.";
        } elseif ($isNettoyage) {
            $intro = "Le nettoyage de toiture est une étape essentielle pour maintenir l'intégrité de votre couverture. " . $companyInfo['company_name'] . " propose des services de nettoyage professionnel adaptés à tous types de toitures à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ".";
            $section1Title = "Pourquoi nettoyer sa toiture ?";
            $section1Content = "Le nettoyage régulier prévient l'accumulation de mousses, lichens et salissures qui peuvent endommager les matériaux et réduire l'efficacité de l'isolation.";
            $section2Title = "Les techniques de nettoyage professionnel";
            $section2Content = "Nous utilisons des méthodes adaptées à chaque type de toiture : nettoyage haute pression, traitement anti-mousse, et application de produits protecteurs.";
            $section3Title = "Fréquence recommandée pour le nettoyage";
            $section3Content = "Un nettoyage tous les 2 à 3 ans est généralement suffisant, mais cela peut varier selon l'exposition, l'orientation et l'environnement de votre toiture.";
        } elseif ($isElagage) {
            $intro = "L'élagage et l'abattage d'arbres nécessitent une expertise technique et des équipements spécialisés. " . $companyInfo['company_name'] . " intervient en toute sécurité pour tous vos travaux d'élagage à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ".";
            $section1Title = "Pourquoi élaguer vos arbres ?";
            $section1Content = "L'élagage améliore la santé des arbres, prévient les chutes de branches dangereuses, améliore l'éclairage naturel, et protège votre toiture des dommages.";
            $section2Title = "Les techniques d'élagage professionnel";
            $section2Content = "Nous pratiquons l'élagage raisonné, respectueux de la biologie de l'arbre, avec des techniques de grimpe sécurisées et un matériel professionnel.";
            $section3Title = "Période idéale pour l'élagage";
            $section3Content = "L'automne et l'hiver sont les saisons privilégiées pour l'élagage, lorsque les arbres sont en dormance et moins sensibles aux interventions.";
        } elseif ($isRenovation) {
            $intro = "La rénovation de toiture est un investissement important qui nécessite une expertise technique. " . $companyInfo['company_name'] . " accompagne vos projets de rénovation avec professionnalisme à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ".";
            $section1Title = "Pourquoi rénover sa toiture ?";
            $section1Content = "La rénovation améliore l'étanchéité, l'isolation thermique, l'esthétique, et la valeur de votre bien immobilier tout en prévenant les infiltrations d'eau.";
            $section2Title = "Les étapes d'une rénovation réussie";
            $section2Content = "Notre processus comprend : diagnostic complet, choix des matériaux, préparation du support, pose des nouveaux éléments, et finitions soignées.";
            $section3Title = "Garanties et suivi post-rénovation";
            $section3Content = "Nous offrons des garanties décennales sur nos travaux et assurons un suivi régulier pour maintenir la qualité de votre toiture dans le temps.";
        } else {
            $intro = "Découvrez tout ce que vous devez savoir sur " . $title . ". Chez " . $companyInfo['company_name'] . ", nous sommes spécialisés dans " . $companyInfo['company_specialization'] . " et nous vous accompagnons dans tous vos projets à " . $companyInfo['company_city'] . ", " . $companyInfo['company_region'] . ".";
            $section1Title = "Les Points Clés à Retenir";
            $section1Content = "Voici les éléments importants à considérer pour votre projet :";
            $section2Title = "Notre Approche Professionnelle";
            $section2Content = "Nous privilégions la qualité, la transparence et la satisfaction client dans tous nos projets.";
            $section3Title = "Pourquoi Nous Choisir ?";
            $section3Content = "Notre expertise, notre savoir-faire et notre engagement qualité font la différence.";
        }
        
        return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
                
                <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
                    <p class="text-gray-700 text-base leading-relaxed mb-4">' . $intro . '</p>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 my-4">' . $section1Title . '</h2>
                    <p class="text-gray-700 text-base leading-relaxed mb-4">' . $section1Content . '</p>
                    <ul class="list-disc list-inside text-gray-700 mb-2">
                        <li class="mb-2">🔍 Expertise technique reconnue</li>
                        <li class="mb-2">⭐ Matériaux de qualité</li>
                        <li class="mb-2">💡 Solutions personnalisées</li>
                        <li class="mb-2">✅ Garanties de satisfaction</li>
                        <li class="mb-2">📞 Accompagnement personnalisé</li>
                    </ul>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 my-4">' . $section2Title . '</h2>
                    <p class="text-gray-700 text-base leading-relaxed mb-4">' . $section2Content . '</p>
                    <ul class="list-disc list-inside text-gray-700 mb-2">
                        <li class="mb-2">📋 Diagnostic complet et gratuit</li>
                        <li class="mb-2">🛠️ Techniques professionnelles</li>
                        <li class="mb-2">⏱️ Respect des délais</li>
                        <li class="mb-2">🔒 Sécurité garantie</li>
                        <li class="mb-2">📞 Suivi post-intervention</li>
                    </ul>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 my-4">' . $section3Title . '</h2>
                    <p class="text-gray-700 text-base leading-relaxed mb-4">' . $section3Content . '</p>
                    <ul class="list-disc list-inside text-gray-700 mb-2">
                        <li class="mb-2">🏆 Entreprise certifiée et assurée</li>
                        <li class="mb-2">💼 Équipe qualifiée et expérimentée</li>
                        <li class="mb-2">💰 Devis gratuit et sans engagement</li>
                        <li class="mb-2">🔄 Garanties décennales</li>
                        <li class="mb-2">📱 Service client réactif</li>
                    </ul>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800 my-4">❓ Questions Fréquentes</h2>
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-800">Quels sont les délais d\'intervention ?</h3>
                        <p class="text-gray-700">Les délais varient selon la complexité du projet, mais nous nous engageons à respecter les échéances convenues.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-800">Proposez-vous des garanties ?</h3>
                        <p class="text-gray-700">Oui, nous offrons des garanties décennales sur nos travaux et assurons un suivi post-intervention.</p>
                    </div>
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-800">Comment obtenir un devis ?</h3>
                        <p class="text-gray-700">Contactez-nous pour un diagnostic gratuit et sans engagement. Nous vous fournirons un devis détaillé et transparent.</p>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                    <h2 class="text-2xl font-semibold text-gray-800 my-4">🎯 Conclusion</h2>
                    <p class="text-gray-700 text-base leading-relaxed mb-4">
                        Faire appel à ' . $companyInfo['company_name'] . ' pour vos projets, c\'est choisir l\'expertise, la qualité et la tranquillité. 
                        Notre équipe de professionnels qualifiés vous accompagne de A à Z pour garantir votre satisfaction.
                    </p>
                    <div class="text-center mt-6">
                        <a href="tel:' . $companyInfo['company_phone'] . '" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 inline-block">
                            📞 Appelez ' . $companyInfo['company_name'] . ' maintenant
                        </a>
                    </div>
                </div>
            </div>';
    }

    /**
     * Construire un prompt avancé pour la génération d'articles
     */
    private function buildAdvancedPrompt($title, $keyword)
    {
        $companyName = setting('company_name', 'Artisan Elfrick');
        $companyPhone = setting('company_phone', '0777840495');
        $companySpecialization = setting('company_specialization', 'Travaux de Rénovation');
        $companyAddress = setting('company_address', '4 bis, Chemin des Postes, Avrainville (91)');
        
        return "Tu es un rédacteur web professionnel et expert en rénovation de bâtiments (toiture, isolation, plomberie, électricité, façade, etc.) et SEO.

MISSION : Rédiger un article complet, informatif et optimisé SEO sur le sujet : {$title}

STRUCTURE HTML OBLIGATOIRE :
<div class=\"max-w-7xl mx-auto px-4 sm:px-6 lg:px-8\">
    <h1 class=\"text-4xl font-bold text-gray-900 mb-6 text-center\">{$title}</h1>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🏠 Introduction</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Introduction engageante avec statistiques]</p>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🛠️ [Section 1 - Technique]</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Contenu technique détaillé]</p>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">💡 [Section 2 - Conseils]</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Conseils pratiques]</p>
        <ul class=\"list-disc list-inside text-gray-700 mb-2\">
            <li class=\"mb-2\">[Point 1]</li>
            <li class=\"mb-2\">[Point 2]</li>
        </ul>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">⚡ [Section 3 - Avantages]</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Avantages et bénéfices]</p>
    </div>
    
    <div class=\"bg-green-50 p-4 rounded-lg mb-4\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">❓ Questions Fréquentes</h2>
        <div class=\"mb-4\">
            <h3 class=\"font-bold text-gray-800\">[Question 1]</h3>
            <p class=\"text-gray-700\">[Réponse détaillée]</p>
        </div>
        <div class=\"mb-4\">
            <h3 class=\"font-bold text-gray-800\">[Question 2]</h3>
            <p class=\"text-gray-700\">[Réponse détaillée]</p>
        </div>
    </div>
    
    <div class=\"bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300\">
        <h2 class=\"text-2xl font-semibold text-gray-800 my-4\">🎯 Conclusion</h2>
        <p class=\"text-gray-700 text-base leading-relaxed mb-4\">[Conclusion avec appel à l'action]</p>
        <div class=\"text-center mt-6\">
            <a href=\"tel:{$companyPhone}\" class=\"bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 inline-block\">
                📞 Appelez {$companyName} maintenant
            </a>
        </div>
    </div>
</div>

CONTENU À GÉNÉRER (2000-3000 mots) :
• Article original et informatif sur {$title}
• Contenu technique détaillé et précis
• Conseils pratiques pour les propriétaires
• Statistiques et données concrètes
• FAQ pertinente avec 5-7 questions
• Ton professionnel mais accessible

MOTS-CLÉS À INTÉGRER :
• {$title} (mot-clé principal)
• rénovation, toiture, façade, isolation, plomberie, électricité
• énergie, maison, entretien, travaux, {$companySpecialization}
• Essonne, 91, professionnel, expert

INFORMATIONS ENTREPRISE :
• Nom : {$companyName}
• Spécialisation : {$companySpecialization}
• Téléphone : {$companyPhone}
• Adresse : {$companyAddress}
• Zone : Essonne (91)

IMPORTANT :
• Générer UNIQUEMENT le HTML complet
• Ne pas inclure de texte explicatif
• Utiliser des emojis appropriés
• Rendre le contenu actionnable
• Optimiser pour le SEO

Génère maintenant l'article HTML complet sur : {$title}";
    }

    /**
     * Améliorer le contenu généré
     */
    private function enhanceGeneratedContent($content, $title)
    {
        // Nettoyer le contenu
        $content = trim($content);
        
        // S'assurer que le contenu commence par un container
        if (!str_contains($content, 'max-w-7xl')) {
            $content = '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">' . $content . '</div>';
        }
        
        // Améliorer les titres
        $content = preg_replace('/<h1[^>]*>/', '<h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">', $content);
        $content = preg_replace('/<h2[^>]*>/', '<h2 class="text-2xl font-semibold text-gray-800 my-4">', $content);
        $content = preg_replace('/<h3[^>]*>/', '<h3 class="text-xl font-semibold text-gray-800 my-3">', $content);
        
        // Améliorer les paragraphes
        $content = preg_replace('/<p[^>]*>/', '<p class="text-gray-700 text-base leading-relaxed mb-4">', $content);
        
        // Améliorer les listes
        $content = preg_replace('/<ul[^>]*>/', '<ul class="list-disc list-inside text-gray-700 mb-2">', $content);
        $content = preg_replace('/<li[^>]*>/', '<li class="mb-2">', $content);
        
        // Améliorer les sections
        $content = preg_replace('/<div class="bg-white[^>]*>/', '<div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">', $content);
        
        // Ajouter des emojis manquants
        $content = str_replace('Introduction', '🏠 Introduction', $content);
        $content = str_replace('Conseils', '💡 Conseils', $content);
        $content = str_replace('FAQ', '❓ FAQ', $content);
        $content = str_replace('Conclusion', '🎯 Conclusion', $content);
        
        return $content;
    }

    /**
     * Générer un contenu générique simple
     */
    private function generateGenericContent($title)
    {
        $companyName = setting('company_name', 'Artisan Elfrick');
        $companyPhone = setting('company_phone', '0777840495');
        $companySpecialization = setting('company_specialization', 'Travaux de Rénovation');
        
        return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Découvrez tout ce que vous devez savoir sur ' . $title . '. Cet article vous guide à travers les aspects essentiels pour faire les bons choix.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Chez ' . $companyName . ', nous sommes spécialisés dans ' . $companySpecialization . ' et nous vous accompagnons dans tous vos projets.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">💡 Les Points Clés à Retenir</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">Voici les éléments importants à considérer :</p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">🔍 Recherchez la qualité avant tout</li>
                    <li class="mb-2">⭐ Vérifiez les certifications</li>
                    <li class="mb-2">💡 Comparez plusieurs options</li>
                    <li class="mb-2">✅ Demandez des références</li>
                    <li class="mb-2">📞 Contactez des professionnels qualifiés</li>
                </ul>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg mb-4">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">❓ Questions Fréquentes</h2>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Comment bien choisir ?</h3>
                    <p class="text-gray-700">La qualité et l\'expérience sont les critères les plus importants à considérer.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Quels sont les délais ?</h3>
                    <p class="text-gray-700">Les délais varient selon la complexité du projet et la disponibilité des professionnels.</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🎯 Conclusion</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    En suivant ces conseils, vous serez en mesure de faire le bon choix pour votre projet.
                </p>
                <div class="text-center mt-6">
                    <a href="tel:' . $companyPhone . '" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300 inline-block">
                        📞 Appelez ' . $companyName . ' maintenant
                    </a>
                </div>
            </div>
        </div>';
    }

    /**
     * Extraire le mot-clé principal du titre
     */
    private function extractFocusKeyword($title)
    {
        $titleLower = strtolower($title);
        
        // Mots-clés prioritaires
        $priorityKeywords = [
            'hydrofuge', 'hydrofugation', 'toiture', 'couverture', 'rénovation',
            'isolation', 'façade', 'plomberie', 'électricité', 'élagage',
            'nettoyage', 'démoussage', 'réparation', 'entretien'
        ];
        
        foreach ($priorityKeywords as $keyword) {
            if (strpos($titleLower, $keyword) !== false) {
                return ucfirst($keyword);
            }
        }
        
        // Si aucun mot-clé prioritaire trouvé, prendre le premier mot significatif
        $words = explode(' ', $title);
        foreach ($words as $word) {
            $cleanWord = trim($word, '.,!?;:');
            if (strlen($cleanWord) > 3) {
                return ucfirst($cleanWord);
            }
        }
        
        return 'Rénovation';
    }

    /**
     * Générer des mots-clés SEO avec l'IA
     */
    private function generateMetaKeywords($title)
    {
        try {
            $prompt = "Génère 10 mots-clés SEO pertinents pour l'article: {$title}

RÈGLES:
- Mots-clés liés à la rénovation, couverture, toiture
- Inclure des variantes et synonymes
- Mots-clés locaux (Dijon, Bourgogne, etc.)
- Mots-clés techniques du métier
- Format: mot1, mot2, mot3, etc.

GÉNÈRE LES MOTS-CLÉS:";

            $result = AiService::callAI($prompt, null, [
                'max_tokens' => 500,
                'temperature' => 0.8
            ]);

            if ($result && isset($result['content'])) {
                $content = $result['content'];
                
                if (!empty(trim($content))) {
                    // Nettoyer la réponse
                    $keywords = trim($content);
                    // Supprimer les numéros, tirets, puces
                    $keywords = preg_replace('/^[\d\.\-\*\•\s]+/', '', $keywords);
                    $keywords = preg_replace('/\s+/', ' ', $keywords);
                    $keywords = trim($keywords);
                    
                    // S'assurer que c'est une liste de mots-clés
                    if (strlen($keywords) > 10) {
                        return $keywords;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération mots-clés: ' . $e->getMessage());
        }
        
        // Fallback
        return $this->generateBasicKeywords($title);
    }

    /**
     * Générer des mots-clés basiques
     */
    private function generateBasicKeywords($title)
    {
        $titleLower = strtolower($title);
        $keywords = [];
        
        // Mots-clés de base
        $baseKeywords = ['rénovation', 'travaux', 'professionnel', 'expert', 'qualité'];
        
        // Ajouter des mots-clés selon le titre
        if (strpos($titleLower, 'toiture') !== false) {
            $keywords[] = 'toiture, couverture, toit';
        }
        if (strpos($titleLower, 'isolation') !== false) {
            $keywords[] = 'isolation, thermique, énergie';
        }
        if (strpos($titleLower, 'façade') !== false) {
            $keywords[] = 'façade, extérieur, peinture';
        }
        
        $keywords = array_merge($baseKeywords, $keywords);
        return implode(', ', array_unique($keywords));
    }

    /**
     * Normaliser les URLs d'images dans le contenu HTML
     * Convertit les URLs absolues en chemins relatifs pour un stockage cohérent
     */
    private function normalizeImageUrlsInContent($content)
    {
        if (empty($content)) {
            return $content;
        }
        
        // Récupérer le domaine de base
        $baseUrl = config('app.url', url('/'));
        $baseUrl = rtrim($baseUrl, '/');
        
        // Pattern pour trouver toutes les balises <img> avec src
        $content = preg_replace_callback(
            '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i',
            function($matches) use ($baseUrl) {
                $originalSrc = $matches[1];
                $fullMatch = $matches[0];
                
                // Si c'est déjà un chemin relatif (commence par uploads/ ou images/), le garder tel quel
                if (str_starts_with($originalSrc, 'uploads/') || str_starts_with($originalSrc, 'images/')) {
                    return $fullMatch;
                }
                
                // Si c'est une URL absolue avec notre domaine, convertir en chemin relatif
                if (str_starts_with($originalSrc, $baseUrl)) {
                    $relativePath = str_replace($baseUrl, '', $originalSrc);
                    $relativePath = ltrim($relativePath, '/');
                    
                    // Vérifier que c'est bien un chemin d'image valide
                    if (str_starts_with($relativePath, 'uploads/') || str_starts_with($relativePath, 'images/')) {
                        // Remplacer l'URL absolue par le chemin relatif dans la balise
                        $newSrc = str_replace($originalSrc, $relativePath, $fullMatch);
                        Log::info('Image URL normalisée', [
                            'original' => $originalSrc,
                            'normalized' => $relativePath
                        ]);
                        return $newSrc;
                    }
                }
                
                // Si c'est une URL externe (autre domaine), la garder telle quelle
                if (filter_var($originalSrc, FILTER_VALIDATE_URL) && !str_starts_with($originalSrc, $baseUrl)) {
                    return $fullMatch;
                }
                
                // Pour les autres cas, garder tel quel
                return $fullMatch;
            },
            $content
        );
        
        return $content;
    }

    /**
     * Upload d'image pour article
     */
    private function handleImageUpload($file)
    {
        $filename = 'article_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Créer le dossier s'il n'existe pas
        $uploadPath = public_path('uploads/articles');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // Sauvegarder directement dans public/uploads/articles/
        $file->move($uploadPath, $filename);
        
        return 'uploads/articles/' . $filename;
    }
}
