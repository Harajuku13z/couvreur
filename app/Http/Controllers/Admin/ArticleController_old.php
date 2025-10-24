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

        $featuredImagePath = $article->featured_image; // Garder l'image actuelle par défaut
        if ($request->hasFile('featured_image')) {
            // Supprimer l'ancienne image si elle existe
            if ($article->featured_image && Storage::exists($article->featured_image)) {
                Storage::delete($article->featured_image);
            }
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'));
        }

        $article->update([
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
            ->with('success', 'Article mis à jour avec succès');
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
                'instruction' => 'nullable|string|max:5000',
                'count' => 'nullable|integer|min:1|max:10'
            ]);

            // Récupérer la clé API depuis la base de données
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

                    $count = $validated['count'] ?? 5;
                    
                    // Construire le prompt en combinant instructions personnalisées et règles strictes
                    if (!empty($validated['instruction'])) {
                        $prompt = "Tu es un générateur de titres SEO. {$validated['instruction']}

MOT-CLÉ: {$validated['keyword']}
NOMBRE DE TITRES: {$count}

RÈGLES STRICTES OBLIGATOIRES:
- Réponds UNIQUEMENT avec les {$count} titres
- Un titre par ligne
- Pas de numérotation
- Pas d'explication
- Pas de questions
- Pas de texte supplémentaire
- Pas de conversation
- Pas de 'Voici' ou 'Voilà'
- Pas de 'Je vais' ou 'Je peux'
- Pas de phrases d'introduction

FORMAT OBLIGATOIRE:
Titre 1
Titre 2
Titre 3
Titre 4
Titre 5

Génère maintenant {$count} titres pour: {$validated['keyword']}";
                    } else {
                        $prompt = "Tu es un générateur de titres SEO. Génère exactement {$count} titres d'articles pour le mot-clé: {$validated['keyword']}.

RÈGLES STRICTES:
- Réponds UNIQUEMENT avec les {$count} titres
- Un titre par ligne
- Pas de numérotation
- Pas d'explication
- Pas de questions
- Pas de texte supplémentaire

Génère maintenant {$count} titres pour: {$validated['keyword']}";
                    }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1000,
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
                
                // Filtrer les titres valides et éliminer les réponses conversationnelles
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
     * Générer un article basique en cas d'échec de l'API
     */
    private function generateBasicArticle($title)
    {
        $emojis = ['🏠', '🔧', '⭐', '💡', '✅', '📋', '🎯', '💪', '🚀', '🔍'];
        $randomEmoji = $emojis[array_rand($emojis)];
        
        $html = '<article>';
        $html .= '<header><h1>' . htmlspecialchars($title) . '</h1></header>';
        
        // Introduction
        $html .= '<section class="introduction">';
        $html .= '<h2>Introduction</h2>';
        $html .= '<p>' . $randomEmoji . ' Découvrez tout ce que vous devez savoir sur ' . htmlspecialchars($title) . '. Cet article vous guide à travers les aspects essentiels pour faire les bons choix.</p>';
        $html .= '</section>';
        
        // Section 1
        $html .= '<section class="contenu">';
        $html .= '<h2>Les Points Clés à Retenir</h2>';
        $html .= '<p>Voici les éléments importants à considérer :</p>';
        $html .= '<ul>';
        $html .= '<li>🔍 Recherchez la qualité avant tout</li>';
        $html .= '<li>⭐ Vérifiez les certifications</li>';
        $html .= '<li>💡 Comparez plusieurs options</li>';
        $html .= '<li>✅ Demandez des références</li>';
        $html .= '</ul>';
        $html .= '</section>';
        
        // Section 2
        $html .= '<section class="contenu">';
        $html .= '<h2>Conseils Pratiques</h2>';
        $html .= '<p>Pour réussir votre projet, suivez ces étapes :</p>';
        $html .= '<ol>';
        $html .= '<li>Évaluez vos besoins spécifiques</li>';
        $html .= '<li>Recherchez des professionnels qualifiés</li>';
        $html .= '<li>Comparez les devis détaillés</li>';
        $html .= '<li>Vérifiez les garanties offertes</li>';
        $html .= '</ol>';
        $html .= '</section>';
        
        // Section 3
        $html .= '<section class="contenu">';
        $html .= '<h2>Points d\'Attention</h2>';
        $html .= '<p>Il est important de faire attention à certains aspects pour éviter les déconvenues.</p>';
        $html .= '<p>Prenez le temps de bien analyser chaque proposition et n\'hésitez pas à poser des questions.</p>';
        $html .= '</section>';
        
        // FAQ
        $html .= '<section class="faq">';
        $html .= '<h2>Questions Fréquentes</h2>';
        $html .= '<h3>Comment bien choisir ?</h3>';
        $html .= '<p>La qualité et l\'expérience sont les critères les plus importants à considérer.</p>';
        $html .= '<h3>Quels sont les délais ?</h3>';
        $html .= '<p>Les délais varient selon la complexité du projet et la disponibilité des professionnels.</p>';
        $html .= '</section>';
        
        // Conclusion
        $html .= '<footer class="conclusion">';
        $html .= '<h2>Conclusion</h2>';
        $html .= '<p>' . $randomEmoji . ' En suivant ces conseils, vous serez en mesure de faire le bon choix pour votre projet.</p>';
        $html .= '</footer>';
        
        $html .= '</article>';
        
        return $html;
    }

    /**
     * Nettoyer le contenu HTML généré
     */
    private function cleanHtmlContent($content)
    {
        // Supprimer les blocs de code markdown
        $content = preg_replace('/```html\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        
        // Supprimer les explications avant/après
        $content = preg_replace('/^[^<]*/', '', $content);
        $content = preg_replace('/[^>]*$/', '', $content);
        
        // Nettoyer les espaces et retours à la ligne
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        // Si le contenu ne commence pas par <article>, le convertir en HTML
        if (!preg_match('/^<article>/', $content)) {
            // Diviser le contenu en sections
            $lines = explode("\n", $content);
            $html = '<article>';
            $html .= '<header><h1>' . $lines[0] . '</h1></header>';
            
            $currentSection = '';
            $inFaq = false;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Détecter les sections
                if (stripos($line, 'Introduction') !== false) {
                    $html .= '<section class="introduction"><h2>Introduction</h2>';
                    $currentSection = 'introduction';
                } elseif (stripos($line, 'Questions Fréquentes') !== false || stripos($line, 'FAQ') !== false) {
                    $html .= '</section><section class="faq"><h2>Questions Fréquentes</h2>';
                    $currentSection = 'faq';
                    $inFaq = true;
                } elseif (stripos($line, 'Conclusion') !== false) {
                    $html .= '</section><footer class="conclusion"><h2>Conclusion</h2>';
                    $currentSection = 'conclusion';
                } elseif (preg_match('/^[A-Z][^.!?]*[.!?]$/', $line) && !preg_match('/^[A-Z][a-z]+ [A-Z]/', $line)) {
                    // Titre de section
                    if ($currentSection !== 'introduction' && $currentSection !== 'faq' && $currentSection !== 'conclusion') {
                        $html .= '</section><section class="contenu-principal"><h2>' . $line . '</h2>';
                        $currentSection = 'contenu';
                    } else {
                        $html .= '<h3>' . $line . '</h3>';
                    }
                } elseif (preg_match('/\?$/', $line)) {
                    // Question FAQ
                    $html .= '<h3>' . $line . '</h3>';
                } else {
                    // Paragraphe
                    $html .= '<p>' . $line . '</p>';
                }
            }
            
            $html .= '</footer></article>';
            $content = $html;
        }
        
        return $content;
    }

    /**
     * Forcer la conversion en HTML si le contenu n'est pas en HTML
     */
    private function forceHtmlConversion($content, $title)
    {
        // Si le contenu est déjà en HTML, le retourner tel quel
        if (preg_match('/^<article>/', $content)) {
            return $content;
        }
        
        // Nettoyer le contenu
        $content = trim($content);
        
        // Diviser le contenu en paragraphes en utilisant plusieurs méthodes
        $paragraphs = [];
        
        // Essayer de diviser par double retour à la ligne
        if (strpos($content, "\n\n") !== false) {
            $paragraphs = array_filter(array_map('trim', explode("\n\n", $content)));
        } else {
            // Sinon diviser par simple retour à la ligne
            $paragraphs = array_filter(array_map('trim', explode("\n", $content)));
        }
        
        // Si toujours pas de paragraphes, diviser par phrases
        if (empty($paragraphs)) {
            $paragraphs = array_filter(array_map('trim', explode('. ', $content)));
        }
        
        $html = '<article>';
        $html .= '<header><h1>' . htmlspecialchars($title) . '</h1></header>';
        
        // Ajouter des emojis et du contenu enrichi
        $emojis = ['🏠', '🔧', '⭐', '💡', '✅', '📋', '🎯', '💪', '🚀', '🔍'];
        $randomEmoji = $emojis[array_rand($emojis)];
        
        $currentSection = 'introduction';
        $sectionCount = 0;
        $inFaq = false;
        
        foreach ($paragraphs as $paragraph) {
            if (empty($paragraph)) continue;
            
            // Nettoyer le paragraphe
            $paragraph = trim($paragraph);
            if (strlen($paragraph) < 10) continue;
            
            // Détecter les sections
            if (stripos($paragraph, 'Introduction') !== false) {
                $html .= '<section class="introduction"><h2>Introduction</h2>';
                $currentSection = 'introduction';
            } elseif (stripos($paragraph, 'Questions Fréquentes') !== false || stripos($paragraph, 'FAQ') !== false) {
                $html .= '</section><section class="faq"><h2>Questions Fréquentes</h2>';
                $currentSection = 'faq';
                $inFaq = true;
            } elseif (stripos($paragraph, 'Conclusion') !== false) {
                $html .= '</section><footer class="conclusion"><h2>Conclusion</h2>';
                $currentSection = 'conclusion';
            } elseif (preg_match('/^[A-Z][^.!?]*[.!?]$/', $paragraph) && !preg_match('/^[A-Z][a-z]+ [A-Z]/', $paragraph)) {
                // Titre de section
                if ($currentSection === 'introduction') {
                    $html .= '</section><section class="contenu-principal"><h2>' . htmlspecialchars($paragraph) . '</h2>';
                    $currentSection = 'contenu';
                    $sectionCount++;
                } elseif ($currentSection === 'contenu') {
                    $html .= '</section><section class="contenu-principal"><h2>' . htmlspecialchars($paragraph) . '</h2>';
                    $sectionCount++;
                } else {
                    $html .= '<h3>' . htmlspecialchars($paragraph) . '</h3>';
                }
            } elseif (preg_match('/\?$/', $paragraph)) {
                // Question FAQ
                $html .= '<h3>' . htmlspecialchars($paragraph) . '</h3>';
            } else {
                // Paragraphe normal
                $html .= '<p>' . htmlspecialchars($paragraph) . '</p>';
            }
        }
        
        // Fermer les sections ouvertes
        if ($currentSection === 'introduction') {
            $html .= '</section>';
        } elseif ($currentSection === 'contenu') {
            $html .= '</section>';
        } elseif ($currentSection === 'faq') {
            $html .= '</section>';
        }
        
        $html .= '</footer></article>';
        
        return $html;
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
        
        // Fallback en cas d'échec
        return $this->generateBasicKeywords($title);
    }

    /**
     * Générer des mots-clés basiques en fallback
     */
    private function generateBasicKeywords($title)
    {
        $keywords = [];
        
        // Extraire les mots du titre
        $words = explode(' ', strtolower($title));
        $keywords = array_merge($keywords, $words);
        
        // Ajouter des mots-clés génériques
        $generic = [
            'rénovation', 'toiture', 'couvreur', 'expert', 'professionnel',
            'dijon', 'bourgogne', 'franche-comté', 'travaux', 'devis',
            'qualité', 'garantie', 'artisan', 'couverture', 'tuiles'
        ];
        
        $keywords = array_merge($keywords, $generic);
        
        // Supprimer les doublons et les mots trop courts
        $keywords = array_unique($keywords);
        $keywords = array_filter($keywords, function($word) {
            return strlen($word) > 2;
        });
        
        return implode(', ', array_slice($keywords, 0, 15));
    }

    /**
     * Supprimer tous les articles
     */
    public function destroyAll()
    {
        try {
            $count = Article::count();
            
            if ($count > 0) {
                // Supprimer toutes les images associées
                $articles = Article::all();
                foreach ($articles as $article) {
                    if ($article->featured_image) {
                        $imagePath = str_replace('/storage/', '', $article->featured_image);
                        if (file_exists(storage_path('app/public/' . $imagePath))) {
                            unlink(storage_path('app/public/' . $imagePath));
                        }
                    }
                }
                
                // Supprimer tous les articles
                Article::truncate();
                
                return redirect()->route('admin.articles.index')
                    ->with('success', "✅ {$count} articles supprimés avec succès");
            } else {
                return redirect()->route('admin.articles.index')
                    ->with('info', 'Aucun article à supprimer');
            }
        } catch (\Exception $e) {
            Log::error('Erreur suppression tous articles: ' . $e->getMessage());
            return redirect()->route('admin.articles.index')
                ->with('error', 'Erreur lors de la suppression des articles');
        }
    }

    /**
     * Gérer l'upload d'image pour les articles
     */
    private function handleImageUpload($file)
    {
        $filename = 'article_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Utiliser le même système que le portfolio - stockage direct dans public/uploads/articles
        $uploadPath = public_path('uploads/articles');
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                throw new \Exception("Failed to create upload directory: {$uploadPath}");
            }
        }
        
        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($uploadPath)) {
            throw new \Exception("Upload directory is not writable: {$uploadPath}");
        }
        
        $file->move($uploadPath, $filename);
        return 'uploads/articles/' . $filename;
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
     * Améliorer la génération de contenu avec post-traitement
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
     * Générer un contenu de fallback amélioré et spécifique au sujet
     */
    private function generateEnhancedFallback($title)
    {
        // Utiliser l'IA pour générer le contenu même en fallback
        try {
            $apiKey = setting('chatgpt_api_key');
            
            if ($apiKey) {
                $simplePrompt = "Génère un article complet sur : {$title}
                
                Format HTML avec Tailwind CSS :
                - Container : max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
                - Titre : text-4xl font-bold text-gray-900 mb-6 text-center
                - Sections : bg-white p-6 rounded-xl shadow mb-6
                - Paragraphes : text-gray-700 text-base leading-relaxed mb-4
                - Listes : list-disc list-inside text-gray-700 mb-2
                - FAQ : bg-green-50 p-4 rounded-lg mb-4
                - Call-to-action : bg-blue-500 text-white px-6 py-3 rounded-lg
                
                Contenu : 1000-2000 mots, informatif, avec emojis, FAQ, conclusion.
                Génère directement le HTML complet.";

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => setting('chatgpt_model', 'gpt-4o'),
                    'messages' => [
                        ['role' => 'user', 'content' => $simplePrompt],
                    ],
                    'max_tokens' => 6000,
                    'temperature' => 0.8,
                ]);

                if ($response->successful()) {
                    $responseData = $response->json();
                    $content = $responseData['choices'][0]['message']['content'] ?? '';
                    
                    if (!empty(trim($content))) {
                        return $content;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur fallback IA: ' . $e->getMessage());
        }
        
        // Fallback final simple
        return $this->generateGenericContent($title);
    }
    
    /**
     * Contenu spécifique pour l'hydrofuge de toiture
     */
    private function generateHydrofugeContent($title, $companyName, $companyPhone, $companySpecialization)
    {
        return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'hydrofuge de toiture est une technique essentielle pour protéger votre toit contre les intempéries et prolonger sa durée de vie. 
                    Cette solution imperméabilisante permet de créer une barrière protectrice qui repousse l\'eau tout en laissant respirer les matériaux.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Chez ' . $companyName . ', nous sommes spécialisés dans ' . $companySpecialization . ' et nous vous accompagnons dans tous vos projets d\'hydrofuge de toiture en Essonne.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🛠️ Techniques d\'hydrofuge</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'hydrofuge peut être appliqué selon différentes techniques selon le type de toiture :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">🏠 <strong>Hydrofuge pour tuiles :</strong> Protection des tuiles en terre cuite ou béton</li>
                    <li class="mb-2">🏠 <strong>Hydrofuge pour ardoises :</strong> Traitement spécifique pour l\'ardoise naturelle</li>
                    <li class="mb-2">🏠 <strong>Hydrofuge pour zinc :</strong> Protection des toitures en zinc</li>
                    <li class="mb-2">🏠 <strong>Hydrofuge pour bac acier :</strong> Traitement des toitures industrielles</li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">💡 Avantages de l\'hydrofuge</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'application d\'un traitement hydrofuge sur votre toiture présente de nombreux avantages :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">✅ <strong>Protection contre l\'eau :</strong> Imperméabilisation efficace</li>
                    <li class="mb-2">✅ <strong>Résistance aux UV :</strong> Protection contre le soleil</li>
                    <li class="mb-2">✅ <strong>Anti-mousse :</strong> Prévention de la formation de mousse</li>
                    <li class="mb-2">✅ <strong>Durée de vie :</strong> Prolongation de la longévité du toit</li>
                    <li class="mb-2">✅ <strong>Économies :</strong> Réduction des coûts d\'entretien</li>
                </ul>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg mb-4">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">❓ Questions Fréquentes</h2>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Qu\'est-ce que l\'hydrofuge de toiture ?</h3>
                    <p class="text-gray-700">L\'hydrofuge est un traitement imperméabilisant qui protège votre toiture contre l\'eau tout en laissant respirer les matériaux.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Combien de temps dure un traitement hydrofuge ?</h3>
                    <p class="text-gray-700">Un traitement hydrofuge de qualité peut durer entre 5 et 10 ans selon les conditions climatiques et l\'entretien.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Quel est le prix d\'un hydrofuge de toiture ?</h3>
                    <p class="text-gray-700">Le prix varie selon la surface, le type de toiture et la complexité du chantier. Contactez-nous pour un devis personnalisé.</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🎯 Conclusion</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'hydrofuge de toiture est un investissement judicieux pour protéger votre bien immobilier. 
                    Cette technique professionnelle vous garantit une protection durable contre les intempéries.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    N\'hésitez pas à contacter ' . $companyName . ' pour tous vos besoins en hydrofuge de toiture en Essonne. 
                    Notre équipe de professionnels vous accompagne dans votre projet avec expertise et qualité.
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
     * Contenu spécifique pour les conseils d'hydrofugation
     */
    private function generateHydrofugeConseilsContent($title, $companyName, $companyPhone, $companySpecialization)
    {
        return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Une hydrofugation réussie de votre toiture nécessite une préparation minutieuse et l\'application de techniques professionnelles. 
                    Ces conseils vous permettront d\'obtenir un résultat optimal et durable.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Chez ' . $companyName . ', nous appliquons ces méthodes depuis des années pour garantir la satisfaction de nos clients en Essonne.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🛠️ Préparation de la surface</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    La réussite de l\'hydrofugation dépend en grande partie de la qualité de la préparation :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">🧹 <strong>Nettoyage complet :</strong> Élimination de toutes les salissures et mousses</li>
                    <li class="mb-2">🔧 <strong>Réparation des défauts :</strong> Correction des fissures et dégradations</li>
                    <li class="mb-2">🌡️ <strong>Conditions météo :</strong> Température entre 5°C et 25°C, temps sec</li>
                    <li class="mb-2">⏰ <strong>Timing optimal :</strong> Application par temps stable</li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">💡 Techniques d\'application</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Les techniques d\'application varient selon le type de matériau :
                </p>
                <ol class="list-decimal list-inside text-gray-700 mb-2">
                    <li class="mb-2"><strong>Pulvérisation :</strong> Application uniforme avec pulvérisateur</li>
                    <li class="mb-2"><strong>Rouleau :</strong> Pour les surfaces planes et accessibles</li>
                    <li class="mb-2"><strong>Pinceau :</strong> Pour les zones délicates et les détails</li>
                    <li class="mb-2"><strong>Épaisseur :</strong> Respecter la dose recommandée par le fabricant</li>
                </ol>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">⚡ Erreurs à éviter</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Certaines erreurs peuvent compromettre la qualité de l\'hydrofugation :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">❌ <strong>Application par temps humide :</strong> Risque de non-adhérence</li>
                    <li class="mb-2">❌ <strong>Surdosage :</strong> Peut créer des résidus visibles</li>
                    <li class="mb-2">❌ <strong>Mélange de produits :</strong> Incompatibilité possible</li>
                    <li class="mb-2">❌ <strong>Négliger la préparation :</strong> Résultat décevant garanti</li>
                </ul>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg mb-4">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">❓ Questions Fréquentes</h2>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Quand faire l\'hydrofugation ?</h3>
                    <p class="text-gray-700">Le meilleur moment est au printemps ou en automne, par temps sec et stable.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Combien de temps dure le traitement ?</h3>
                    <p class="text-gray-700">Un hydrofuge de qualité peut durer 5 à 10 ans selon les conditions climatiques.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Peut-on faire l\'hydrofugation soi-même ?</h3>
                    <p class="text-gray-700">C\'est possible mais risqué. Un professionnel garantit un résultat optimal et durable.</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🎯 Conclusion</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Une hydrofugation réussie nécessite expertise et savoir-faire. Ces conseils vous aideront à comprendre l\'importance d\'un travail professionnel.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Contactez ' . $companyName . ' pour une hydrofugation professionnelle de votre toiture en Essonne. 
                    Notre équipe maîtrise toutes ces techniques pour un résultat parfait.
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
     * Contenu spécifique pour les avantages de l'hydrofugation
     */
    private function generateHydrofugeAvantagesContent($title, $companyName, $companyPhone, $companySpecialization)
    {
        return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'hydrofugation de votre toiture offre de nombreux avantages concrets et mesurables. 
                    Cette technique de protection permet de préserver votre investissement immobilier tout en réduisant les coûts d\'entretien.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Découvrez pourquoi ' . $companyName . ' recommande cette solution à tous nos clients en Essonne.
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🛡️ Protection contre l\'eau</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'avantage principal de l\'hydrofugation est la protection efficace contre l\'infiltration d\'eau :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">💧 <strong>Imperméabilisation :</strong> Barrière protectrice contre la pluie</li>
                    <li class="mb-2">🌊 <strong>Résistance aux intempéries :</strong> Protection renforcée</li>
                    <li class="mb-2">🏠 <strong>Préservation du bâti :</strong> Évite les dégradations</li>
                    <li class="mb-2">💰 <strong>Économies :</strong> Réduction des réparations coûteuses</li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">☀️ Résistance aux UV</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'exposition prolongée au soleil peut endommager votre toiture. L\'hydrofugation apporte une protection supplémentaire :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">🌞 <strong>Protection UV :</strong> Filtre les rayons nocifs</li>
                    <li class="mb-2">🎨 <strong>Conservation des couleurs :</strong> Évite le ternissement</li>
                    <li class="mb-2">⏰ <strong>Longévité :</strong> Prolonge la durée de vie des matériaux</li>
                    <li class="mb-2">🔧 <strong>Entretien réduit :</strong> Moins d\'interventions nécessaires</li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🌿 Action anti-mousse</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'hydrofugation empêche la formation de mousse et de lichens :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">🚫 <strong>Prévention :</strong> Évite l\'apparition de mousse</li>
                    <li class="mb-2">🧹 <strong>Nettoyage facilité :</strong> Moins de salissures</li>
                    <li class="mb-2">💎 <strong>Esthétique :</strong> Toiture toujours propre</li>
                    <li class="mb-2">🏆 <strong>Valeur :</strong> Améliore l\'apparence de votre maison</li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">💡 Avantages économiques</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    L\'hydrofugation représente un investissement rentable à long terme :
                </p>
                <ul class="list-disc list-inside text-gray-700 mb-2">
                    <li class="mb-2">💵 <strong>Coût réduit :</strong> Moins cher qu\'une réparation complète</li>
                    <li class="mb-2">⏳ <strong>Durabilité :</strong> Protection de 5 à 10 ans</li>
                    <li class="mb-2">📈 <strong>Plus-value :</strong> Améliore la valeur de votre bien</li>
                    <li class="mb-2">🔧 <strong>Maintenance :</strong> Réduit les coûts d\'entretien</li>
                </ul>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg mb-4">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">❓ Questions Fréquentes</h2>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">L\'hydrofugation est-elle vraiment efficace ?</h3>
                    <p class="text-gray-700">Oui, avec un produit de qualité et une application professionnelle, l\'efficacité est garantie.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Quels sont les avantages par rapport à d\'autres solutions ?</h3>
                    <p class="text-gray-700">L\'hydrofugation est plus économique et moins invasive qu\'une réparation complète.</p>
                </div>
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Combien coûte une hydrofugation ?</h3>
                    <p class="text-gray-700">Le prix varie selon la surface et le type de toiture. Contactez-nous pour un devis gratuit.</p>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🎯 Conclusion</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Les avantages de l\'hydrofugation sont nombreux et mesurables. Cette solution vous protège efficacement tout en préservant votre budget.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Faites confiance à ' . $companyName . ' pour profiter de tous ces avantages. 
                    Notre expertise garantit un résultat optimal pour votre toiture en Essonne.
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
     * Contenu générique de qualité
     */
    private function generateGenericContent($title, $companyName, $companyPhone, $companySpecialization)
    {
        return '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-6 text-center">' . $title . '</h1>
            
            <div class="bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300">
                <h2 class="text-2xl font-semibold text-gray-800 my-4">🏠 Introduction</h2>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Découvrez tout ce que vous devez savoir sur ' . $title . '. Cet article vous guide à travers les aspects essentiels pour faire les bons choix.
                </p>
                <p class="text-gray-700 text-base leading-relaxed mb-4">
                    Chez ' . $companyName . ', nous sommes spécialisés dans ' . $companySpecialization . ' et nous vous accompagnons dans tous vos projets de rénovation.
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
}
