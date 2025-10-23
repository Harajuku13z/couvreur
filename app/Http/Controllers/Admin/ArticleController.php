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
            'featured_image' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published'
        ]);

        $article = Article::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content_html' => $validated['content_html'], // HTML tel quel
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'meta_keywords' => $validated['meta_keywords'],
            'featured_image' => $validated['featured_image'],
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
            'featured_image' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published'
        ]);

        $article->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'content_html' => $validated['content_html'], // HTML tel quel
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'meta_keywords' => $validated['meta_keywords'],
            'featured_image' => $validated['featured_image'],
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

            $apiKey = setting('chatgpt_api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API OpenAI non configurée'
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

            $apiKey = setting('chatgpt_api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé API OpenAI non configurée'
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
            
            // Sauvegarder dans storage/app/public/articles/
            $path = $image->storeAs('articles', $filename, 'public');
            
            // Générer l'URL complète
            $imageUrl = request()->getSchemeAndHttpHost() . '/storage/' . $path;
            
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

            $prompt = "Crée un article HTML professionnel sur: {$title}

Instructions:
- Format HTML uniquement
- Commence par <article> et finit par </article>
- 1000-1500 mots
- Structure: introduction, 3 sections, FAQ, conclusion
- Utilise des emojis appropriés
- Inclut des listes

Format:
<article>
<header><h1>Titre</h1></header>
<section class=\"introduction\"><h2>Introduction</h2><p>Texte...</p></section>
<section class=\"contenu\"><h2>Section 1</h2><p>Texte...</p><ul><li>Point 1</li></ul></section>
<section class=\"contenu\"><h2>Section 2</h2><p>Texte...</p></section>
<section class=\"contenu\"><h2>Section 3</h2><p>Texte...</p></section>
<section class=\"faq\"><h2>FAQ</h2><h3>Question?</h3><p>Réponse...</p></section>
<footer class=\"conclusion\"><h2>Conclusion</h2><p>Texte...</p></footer>
</article>

Génère l'article:";

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
                $responseData = $response->json();
                $content = $responseData['choices'][0]['message']['content'] ?? '';
                
                Log::info('Réponse API OpenAI', [
                    'status' => $response->status(),
                    'has_content' => !empty($content),
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 100)
                ]);
                
                if (!empty(trim($content))) {
                    // Nettoyer le contenu HTML
                    $content = $this->cleanHtmlContent($content);
                    
                    // TOUJOURS forcer la conversion en HTML
                    if (!preg_match('/^<article>/', $content)) {
                        Log::info('Conversion forcée en HTML', ['content_preview' => substr($content, 0, 100)]);
                        $content = $this->forceHtmlConversion($content, $title);
                    }
                    
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
        
        // Même si l'API échoue, créer un article HTML basique
        return $this->generateBasicArticle($title);
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
}
