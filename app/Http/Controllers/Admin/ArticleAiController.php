<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Setting;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\AiService;
use App\Services\GptSeoGenerator;
use App\Services\SerpApiService;

class ArticleAiController extends Controller
{
    /**
     * Afficher le formulaire de génération d'articles
     */
    public function form()
    {
        return view('admin.articles.ai-generate');
    }

    /**
     * Tester la connexion à l'API IA (ChatGPT ou Groq)
     */
    public function test(Request $request)
    {
        try {
            // Utiliser AiService pour tester (gère ChatGPT et Groq automatiquement)
            $result = AiService::callAI('Réponds: OK', null, [
                'max_tokens' => 10,
                'temperature' => 0.1
            ]);

            if ($result && isset($result['content']) && isset($result['provider'])) {
                $provider = $result['provider'] === 'chatgpt' ? 'ChatGPT' : 'Groq';
                $msg = "Connexion IA réussie avec {$provider}. Réponse: " . trim($result['content']);
                return back()->with('success', $msg);
            } else {
                return back()->with('error', 'Erreur: Impossible de se connecter à l\'API IA. Vérifiez vos clés API dans /config');
            }

        } catch (\Throwable $e) {
            Log::error('Erreur test connexion IA', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Erreur de connexion: ' . $e->getMessage());
        }
    }

    /**
     * Générer des articles avec IA
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'titles' => 'required|string',
            'category' => 'nullable|string|max:120',
            'language' => 'nullable|string|max:10',
            'custom_prompt' => 'nullable|string|max:2000',
            'model' => 'nullable|string|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
        ]);

        $titles = collect(preg_split("/\r?\n/", trim($data['titles'])))->filter();
        $category = $data['category'] ?? 'Blog';
        $language = $data['language'] ?? 'fr';
        $customPrompt = trim($data['custom_prompt'] ?? '');
        $model = $data['model'] ?? setting('chatgpt_model', 'gpt-4o');

        $created = 0;
        $errors = [];
        $featuredImagePath = null;

        // Gérer l'upload de l'image de mise en avant
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $filename = 'article-featured-' . time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/articles'), $filename);
            $featuredImagePath = 'uploads/articles/' . $filename;
        }

        // Initialiser les services pour génération SEO premium
        $gptGenerator = app(GptSeoGenerator::class);
        $serpService = app(SerpApiService::class);
        
        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Récupérer la ville par défaut ou la première ville favorite
        $defaultCity = City::where('is_favorite', true)->first();
        if (!$defaultCity) {
            // Créer une ville par défaut si aucune n'existe
            $defaultCity = City::first();
            if (!$defaultCity) {
                $defaultCity = City::create([
                    'name' => $companyInfo['company_city'] ?? 'Chevigny-Saint-Sauveur',
                    'postal_code' => '21800',
                    'region' => $companyInfo['company_region'] ?? 'Bourgogne-Franche-Comté',
                    'is_favorite' => true,
                ]);
            }
        }

        foreach ($titles as $title) {
            $slug = Str::slug($title);
            
            // Vérifier si l'article existe déjà
            if (Article::where('slug', $slug)->exists()) {
                $errors[] = "Article '$title' existe déjà (slug: $slug)";
                continue;
            }

            try {
                // Extraire le mot-clé principal du titre
                $keyword = $this->extractKeywordFromTitle($title);
                
                // Récupérer les données SERP (requêtes associées et concurrents)
                $relatedQueries = $serpService->getRelatedQueries($keyword, 6);
                $searchQuery = $keyword . ' ' . $defaultCity->name;
                $competitors = $serpService->getTopSERP($searchQuery, 10);
                
                // Générer l'article avec le même système que l'automatisation SEO
                // Note: $serpResults attend un tableau de résultats SERP (title, snippet)
                // $keywordImages attend un tableau d'images (peut être vide)
                // Suivant le pattern de SeoAutomationManager, on passe $competitors comme $serpResults
                // et un tableau vide pour $keywordImages (pas d'images pour l'instant)
                $gptData = $gptGenerator->generateSeoArticle(
                    $keyword,
                    $defaultCity->name,
                    $competitors, // Résultats SERP avec title/snippet
                    [] // Pas d'images pour l'instant
                );
                
                if (!$gptData || empty($gptData['titre']) || empty($gptData['contenu_html'])) {
                    $errors[] = "Échec de génération pour '$title' - réponse GPT invalide";
                    continue;
                }

                // Utiliser l'image de mise en avant si fournie
                $finalFeaturedImage = $featuredImagePath;

                // Créer l'article avec toutes les métadonnées SEO
                Article::create([
                    'title' => $gptData['titre'],
                    'meta_title' => $gptData['titre'], // Utiliser le même titre pour meta_title
                    'slug' => $gptData['slug'] ?? Str::slug($gptData['titre'] . '-' . $defaultCity->name),
                    'excerpt' => $this->generateExcerpt($gptData['contenu_html']),
                    'content_html' => $gptData['contenu_html'],
                    'meta_description' => $gptData['meta_description'] ?? $this->generateMetaDescription($gptData['contenu_html']),
                    'meta_keywords' => is_array($gptData['mots_cles'] ?? []) 
                        ? implode(', ', $gptData['mots_cles']) 
                        : ($gptData['mots_cles'] ?? $this->generateKeywords($title, $category)),
                    'focus_keyword' => $keyword,
                    'featured_image' => $finalFeaturedImage,
                    'status' => 'published',
                    'published_at' => now(),
                    'city_id' => $defaultCity->id,
                ]);

                $created++;

            } catch (\Throwable $e) {
                $errors[] = "Erreur pour '$title': " . $e->getMessage();
                Log::error('Erreur génération article IA', [
                    'title' => $title,
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null
                ]);
            }
        }

        $message = "$created article(s) généré(s) avec succès.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', $errors);
        }

        return redirect()->route('admin.articles.index')->with('success', $message);
    }

    /**
     * Extraire le mot-clé principal du titre
     */
    private function extractKeywordFromTitle($title)
    {
        // Enlever les mots communs et extraire le mot-clé principal
        $stopWords = ['comment', 'les', 'guide', 'complet', 'de', 'la', 'le', 'un', 'une', 'des', 'du', 'pour', 'avec', 'sans', 'quoi', 'quand', 'où', 'pourquoi', 'comment', 'combien'];
        $words = explode(' ', strtolower($title));
        $keywords = array_filter($words, function($word) use ($stopWords) {
            $cleanWord = trim($word, '.,!?;:');
            return strlen($cleanWord) > 3 && !in_array($cleanWord, $stopWords);
        });
        
        // Prendre les 2-3 premiers mots pertinents comme mot-clé
        $keyword = implode(' ', array_slice($keywords, 0, 3));
        
        // Si pas de mot-clé trouvé, utiliser le titre complet
        if (empty($keyword)) {
            $keyword = strtolower($title);
            }

        return $keyword;
    }

    /**
     * Récupérer les informations de l'entreprise
     */
    private function getCompanyInfo()
    {
        return [
            'company_name' => setting('company_name', 'Notre Entreprise'),
            'company_city' => setting('company_city', 'Paris'),
            'company_region' => setting('company_region', 'Île-de-France'),
            'company_phone' => setting('company_phone', ''),
            'company_email' => setting('company_email', ''),
        ];
    }

    /**
     * Générer un extrait à partir du contenu
     */
    private function generateExcerpt($content)
    {
        $plain = trim(strip_tags($content));
        return Str::limit(preg_replace('/\s+/', ' ', $plain), 200);
    }

    /**
     * Générer une meta description
     */
    private function generateMetaDescription($content)
    {
        $plain = trim(strip_tags($content));
        return Str::limit(preg_replace('/\s+/', ' ', $plain), 155);
    }

    /**
     * Générer des mots-clés
     */
    private function generateKeywords($title, $category)
    {
        $baseKeywords = ['couvreur', 'toiture', 'rénovation', 'devis gratuit'];
        $titleKeywords = explode(' ', strtolower($title));
        $categoryKeywords = explode(' ', strtolower($category));
        
        $keywords = array_merge($baseKeywords, $titleKeywords, $categoryKeywords);
        $keywords = array_unique(array_filter($keywords, function($k) {
            return strlen($k) > 2;
        }));
        
        return implode(', ', array_slice($keywords, 0, 10));
    }
}
