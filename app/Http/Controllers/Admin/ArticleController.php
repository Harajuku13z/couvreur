<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'content_html' => 'required|string',
            'meta_title' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:2000',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'status' => 'required|in:draft,published'
        ]);

        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'));
        }

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content_html' => $validated['content_html'],
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'meta_keywords' => $validated['meta_keywords'],
            'featured_image' => $featuredImagePath,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        return redirect()->route('admin.articles.show', $article)
            ->with('success', 'Article créé avec succès');
    }

    public function edit(Article $article)
    {
        return view('admin.articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'content_html' => 'required|string',
            'meta_title' => 'nullable|string|max:500',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:2000',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'status' => 'required|in:draft,published'
        ]);

        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'));
            $validated['featured_image'] = $featuredImagePath;
        }

        $validated['published_at'] = $validated['status'] === 'published' ? now() : null;
        $article->update($validated);

        return redirect()->route('admin.articles.show', $article)
            ->with('success', 'Article modifié avec succès');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', 'Article supprimé avec succès');
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

            $apiKey = setting('chatgpt_api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API OpenAI non configurée'
                ], 400);
            }

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

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2000,
                'temperature' => 0.8,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                
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

            // Récupérer la clé API depuis la base de données
            $apiKey = setting('chatgpt_api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API OpenAI non configurée. Veuillez la configurer dans /config'
                ], 400);
            }

            $prompt = "{$validated['instruction']}

Titre de l'article: {$validated['title']}
Mot-clé principal: {$validated['keyword']}

Génère l'article HTML complet selon les consignes du prompt ci-dessus.";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 6000,
                'temperature' => 0.8,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                
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
     * Upload d'image pour article
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            ]);

            $image = $request->file('image');
            $filename = 'article_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Créer le dossier s'il n'existe pas
            $uploadPath = public_path('uploads/articles');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Sauvegarder directement dans public/uploads/articles/
            $image->move($uploadPath, $filename);
            
            // Générer l'URL complète
            $imageUrl = url('uploads/articles/' . $filename);
            
            return response()->json([
                'success' => true,
                'image_url' => $imageUrl,
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
     * Génération de contenu d'article avec IA
     */
    private function generateArticleContent($title, $keyword)
    {
        try {
            $apiKey = setting('chatgpt_api_key');
            
            if (!$apiKey) {
                return '<p>Contenu à générer...</p>';
            }

            $prompt = $this->buildAdvancedPrompt($title, $keyword);

            // Log du prompt pour debug
            Log::info('Prompt envoyé à OpenAI', [
                'title' => $title,
                'keyword' => $keyword,
                'prompt_length' => strlen($prompt),
                'prompt_preview' => substr($prompt, 0, 200)
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu es un expert en rédaction web SEO spécialisé dans la rénovation de bâtiments.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 8000,
                'temperature' => 0.7,
                'top_p' => 0.9,
                'frequency_penalty' => 0.1,
                'presence_penalty' => 0.1,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $content = $responseData['choices'][0]['message']['content'] ?? '';
                
                Log::info('Réponse API OpenAI', [
                    'status' => $response->status(),
                    'has_content' => !empty($content),
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 100)
                ]);
                
                if (!empty(trim($content))) {
                    // Améliorer le contenu généré
                    $content = $this->enhanceGeneratedContent($content, $title);
                    
                    return $content;
                } else {
                    Log::warning('Contenu vide reçu de l\'API OpenAI');
                }
            } else {
                Log::error('Erreur API OpenAI', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération contenu article: ' . $e->getMessage());
        }
        
        // Même si l'API échoue, créer un article HTML simple
        return $this->generateGenericContent($title);
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
            $apiKey = setting('chatgpt_api_key');
            
            if (!$apiKey) {
                // Fallback : mots-clés basiques
                return $this->generateBasicKeywords($title);
            }

            $prompt = "Génère 10 mots-clés SEO pertinents pour l'article: {$title}

RÈGLES:
- Mots-clés liés à la rénovation, couverture, toiture
- Inclure des variantes et synonymes
- Mots-clés locaux (Dijon, Bourgogne, etc.)
- Mots-clés techniques du métier
- Format: mot1, mot2, mot3, etc.

GÉNÈRE LES MOTS-CLÉS:";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 500,
                'temperature' => 0.8,
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'] ?? '';
                
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
