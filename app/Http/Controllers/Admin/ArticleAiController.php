<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
     * Tester la connexion à l'API OpenAI
     */
    public function test(Request $request)
    {
        $apiKey = setting('chatgpt_api_key');
        
        if (!$apiKey) {
            return back()->with('error', 'Clé API OpenAI manquante. Veuillez la configurer dans /config');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => setting('chatgpt_model', 'gpt-4o'),
                'messages' => [
                    ['role' => 'user', 'content' => 'Réponds: OK']
                ],
                'max_tokens' => 5
            ]);

            $status = $response->status();
            $ok = $response->ok();
            $body = substr($response->body(), 0, 200);

            $msg = $ok ? 'Connexion IA: OK (' . $status . ')' : 'Connexion IA: ECHEC (' . $status . ')';
            return back()->with($ok ? 'success' : 'error', $msg . ($body ? ' Réponse: ' . $body : ''));

        } catch (\Throwable $e) {
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
        ]);

        $titles = collect(preg_split("/\r?\n/", trim($data['titles'])))->filter();
        $category = $data['category'] ?? 'Blog';
        $language = $data['language'] ?? 'fr';
        $customPrompt = trim($data['custom_prompt'] ?? '');
        $model = $data['model'] ?? setting('chatgpt_model', 'gpt-4o');

        $apiKey = setting('chatgpt_api_key');
        
        if (!$apiKey) {
            return back()->with('error', 'Clé API OpenAI manquante. Veuillez la configurer dans /config');
        }

        $created = 0;
        $errors = [];

        foreach ($titles as $title) {
            $slug = Str::slug($title);
            
            // Vérifier si l'article existe déjà
            if (Article::where('slug', $slug)->exists()) {
                $errors[] = "Article '$title' existe déjà (slug: $slug)";
                continue;
            }

            try {
                $content = $this->generateArticleContent($title, $category, $language, $customPrompt, $model, $apiKey);
                
                if (!$content) {
                    $errors[] = "Échec de génération pour '$title'";
                    continue;
                }

                // Créer l'article
                Article::create([
                    'title' => $title,
                    'slug' => $slug,
                    'category' => $category,
                    'excerpt' => $this->generateExcerpt($content),
                    'content' => $content,
                    'meta_description' => $this->generateMetaDescription($content),
                    'meta_keywords' => $this->generateKeywords($title, $category),
                    'status' => 'published',
                    'published_at' => now(),
                ]);

                $created++;

            } catch (\Throwable $e) {
                $errors[] = "Erreur pour '$title': " . $e->getMessage();
                Log::error('Erreur génération article IA', [
                    'title' => $title,
                    'error' => $e->getMessage()
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
     * Générer le contenu d'un article avec IA
     */
    private function generateArticleContent($title, $category, $language, $customPrompt, $model, $apiKey)
    {
        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Prompt système inspiré d'Osmose Consulting
        $system = "Tu es un expert en marketing digital spécialisé pour couvreurs et entreprises de rénovation. Tu produis STRICTEMENT du HTML (aucun Markdown), sans balises <html> ni <body>, uniquement le contenu. Utilise <h1> pour le titre principal, des <h2> pour les sections et des <h3> pour les sous-sections, des paragraphes <p>, et des listes <ul><li>. Inclure des CTA avec <a href=\"#\" class=\"cta-link\">Texte CTA</a>. Ton professionnel, persuasif, orienté SEO et conversion.";

        // Prompt utilisateur détaillé
        $user = ($customPrompt ? ($customPrompt . "\n\n") : '') . 
                "Sujet: {$title}\n" .
                "Catégorie: {$category}\n" .
                "Langue: {$language}\n" .
                "Entreprise: {$companyInfo['company_name']}\n" .
                "Localisation: {$companyInfo['company_city']}, {$companyInfo['company_region']}\n\n" .
                "Consignes détaillées:\n" .
                "- Longueur: 1500 à 2000 mots minimum\n" .
                "- Style: professionnel, engageant, orienté prospection/conversion\n" .
                "- Inclure des mots-clés (ex: devis toiture, réparation urgence toiture, couvreur professionnel, rénovation toiture) naturellement dans les titres et le texte\n" .
                "- Structure HTML attendue (exemple):\n" .
                "<h1>Titre principal optimisé SEO</h1>\n" .
                "<p>Introduction accrocheuse… (ne répète pas de meta description séparée)</p>\n" .
                "<h2>Section 1 (intention forte)</h2>\n" .
                "<h3>Sous-section détaillée</h3>\n" .
                "<ul><li>Conseil pratique</li><li>Erreur à éviter</li></ul>\n" .
                "<h2>Section 2</h2>\n" .
                "<h3>Sous-section</h3>\n" .
                "<p>Exemples concrets…</p>\n" .
                "<h2>Section 3</h2>\n" .
                "<h3>Sous-section</h3>\n" .
                "<ul><li>Étapes actionnables</li></ul>\n" .
                "<h2>Conclusion</h2>\n" .
                "<p>Résumé persuasif.</p>\n" .
                "<p><a href=\"#\" class=\"cta-link\">Demander un devis gratuit</a></p>\n" .
                "Contraintes supplémentaires:\n" .
                "- 3 à 4 <h2>, 5 à 6 <h3> minimum\n" .
                "- HTML uniquement (pas de CSS/JS)\n" .
                "- Commencer par <h1> puis <p> d'introduction\n" .
                "- Optimiser chaque section avec des mots-clés pertinents du secteur couverture/rénovation\n" .
                "- Pas de code ni d'assets, uniquement le HTML du contenu\n" .
                "- Inclure des conseils pratiques et des erreurs à éviter\n" .
                "- Terminer par un CTA fort pour demander un devis";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000,
            ]);

            if ($response->ok()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('Erreur API OpenAI', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

        } catch (\Throwable $e) {
            Log::error('Erreur génération contenu article', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Récupérer les informations de l'entreprise
     */
    private function getCompanyInfo()
    {
        return [
            'company_name' => setting('company_name', 'Sauser Couverture'),
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
