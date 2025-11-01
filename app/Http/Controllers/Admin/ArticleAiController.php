<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\AiService;

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

        foreach ($titles as $title) {
            $slug = Str::slug($title);
            
            // Vérifier si l'article existe déjà
            if (Article::where('slug', $slug)->exists()) {
                $errors[] = "Article '$title' existe déjà (slug: $slug)";
                continue;
            }

            try {
                $content = $this->generateArticleContent($title, $category, $language, $customPrompt, $model);
                
                if (!$content) {
                    $errors[] = "Échec de génération pour '$title'";
                    continue;
                }

                // Créer l'article
                Article::create([
                    'title' => $title,
                    'slug' => $slug,
                    'excerpt' => $this->generateExcerpt($content),
                    'content_html' => $content,
                    'meta_description' => $this->generateMetaDescription($content),
                    'meta_keywords' => $this->generateKeywords($title, $category),
                    'featured_image' => $featuredImagePath,
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
    private function generateArticleContent($title, $category, $language, $customPrompt, $model)
    {
        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Prompt système optimisé pour contenu SEO de qualité
        $system = "Tu es un expert en rédaction web SEO spécialisé pour couvreurs et entreprises de rénovation. Tu produis STRICTEMENT du HTML avec classes Tailwind CSS, sans balises <html> ni <body>, uniquement le contenu. Privilégie des paragraphes de qualité avec des phrases complètes plutôt que des listes. Utilise des classes Tailwind pour le styling et des icônes Font Awesome uniquement pour les titres de sections. Ton professionnel, informatif, orienté SEO avec du contenu textuel de qualité.";

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
                "- Structure HTML optimisée pour SEO (exemple):\n" .
                "<h1 class=\"text-4xl font-bold text-gray-900 mb-6\">Titre principal optimisé SEO</h1>\n" .
                "<p class=\"text-lg text-gray-700 mb-8 leading-relaxed\">Introduction accrocheuse de 3-4 phrases qui présente le sujet et ses enjeux. Cette introduction doit captiver le lecteur et introduire naturellement les mots-clés principaux.</p>\n" .
                "<h2 class=\"text-2xl font-bold text-gray-900 mb-6 flex items-center\"><i class=\"fas fa-lightbulb text-blue-600 mr-3\"></i>Section 1 - Titre descriptif</h2>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe de 4-5 phrases développant le premier point important. Utilise des phrases complètes et variées pour expliquer en détail les concepts. Intègre naturellement les mots-clés pertinents dans le texte.</p>\n" .
                "<h3 class=\"text-xl font-semibold text-gray-800 mb-4\">Sous-section détaillée</h3>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe approfondi de 5-6 phrases qui détaille les aspects techniques ou pratiques. Fournis des informations précises et utiles pour le lecteur, en utilisant un vocabulaire professionnel mais accessible.</p>\n" .
                "<div class=\"bg-blue-50 border-l-4 border-blue-500 p-6 mb-8 rounded-r-lg\">\n" .
                "  <h3 class=\"text-xl font-semibold text-gray-800 mb-4 flex items-center\"><i class=\"fas fa-exclamation-triangle text-orange-600 mr-3\"></i>Point important à retenir</h3>\n" .
                "  <p class=\"text-gray-700 leading-relaxed\">Paragraphe de 3-4 phrases dans une boîte colorée pour mettre en évidence un conseil important ou une erreur à éviter. Utilise un ton direct et informatif.</p>\n" .
                "</div>\n" .
                "<h2 class=\"text-2xl font-bold text-gray-900 mb-6 flex items-center\"><i class=\"fas fa-tools text-blue-600 mr-3\"></i>Section 2 - Titre descriptif</h2>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe de 4-5 phrases développant le deuxième point important. Varie les structures de phrases et utilise des connecteurs logiques pour une lecture fluide.</p>\n" .
                "<h3 class=\"text-xl font-semibold text-gray-800 mb-4\">Sous-section technique</h3>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe technique de 5-6 phrases expliquant les aspects pratiques ou les procédures. Fournis des détails précis et des exemples concrets quand c'est pertinent.</p>\n" .
                "<div class=\"bg-gray-50 rounded-lg p-6 mb-8\">\n" .
                "  <h3 class=\"text-xl font-semibold text-gray-800 mb-4 flex items-center\"><i class=\"fas fa-info-circle text-blue-600 mr-3\"></i>Information complémentaire</h3>\n" .
                "  <p class=\"text-gray-700 leading-relaxed\">Paragraphe de 3-4 phrases dans une boîte grise pour apporter des informations supplémentaires ou des précisions importantes.</p>\n" .
                "</div>\n" .
                "<h2 class=\"text-2xl font-bold text-gray-900 mb-6 flex items-center\"><i class=\"fas fa-check-circle text-green-600 mr-3\"></i>Section 3 - Titre descriptif</h2>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe de 4-5 phrases développant le troisième point important. Utilise des exemples concrets et des cas d'usage pour illustrer tes propos.</p>\n" .
                "<h3 class=\"text-xl font-semibold text-gray-800 mb-4\">Sous-section pratique</h3>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe pratique de 5-6 phrases donnant des conseils applicables et des recommandations professionnelles. Termine par une phrase qui résume l'importance du point abordé.</p>\n" .
                "<h2 class=\"text-2xl font-bold text-gray-900 mb-6 flex items-center\"><i class=\"fas fa-flag-checkered text-blue-600 mr-3\"></i>Conclusion</h2>\n" .
                "<p class=\"text-gray-700 mb-6 leading-relaxed\">Paragraphe de conclusion de 4-5 phrases qui synthétise les points principaux abordés dans l'article. Termine par une phrase qui encourage l'action ou qui résume la valeur ajoutée de l'article.</p>\n" .
                "Contraintes importantes:\n" .
                "- 3 à 4 <h2> et 5 à 6 <h3> minimum\n" .
                "- PRIVILÉGIER les paragraphes de 4-6 phrases plutôt que les listes\n" .
                "- Utiliser UNIQUEMENT des classes Tailwind CSS\n" .
                "- Icônes Font Awesome UNIQUEMENT dans les titres de sections (pas dans le contenu)\n" .
                "- NE PAS inclure de boutons ou CTA 'Demander un devis gratuit' dans le contenu\n" .
                "- Utiliser des couleurs cohérentes (blue-600, green-600, orange-600, gray-900, etc.)\n" .
                "- Espacement approprié (mb-4, mb-6, mb-8, p-6, etc.)\n" .
                "- Backgrounds colorés pour les encadrés importants\n" .
                "- Optimiser chaque paragraphe avec des mots-clés pertinents du secteur couverture/rénovation\n" .
                "- Contenu informatif et professionnel, orienté expertise technique\n" .
                "- Phrases complètes et variées pour un bon SEO textuel\n" .
                "- Éviter les listes à puces, privilégier le contenu narratif";

        try {
            // Pour Groq on-demand: ajuster max_tokens pour respecter la limite TPM (6000)
            // Estimation: ~1 token = 4 caractères pour le texte
            $totalMessageLength = strlen($system ?? '') + strlen($user);
            $estimatedInputTokens = (int)($totalMessageLength / 4);
            // Laisser une marge de sécurité: limiter à 5500 tokens totaux
            // Réduire max_tokens si nécessaire pour respecter la limite
            $maxTokens = min(4000, max(1000, 5500 - $estimatedInputTokens));
            
            Log::info('Calcul tokens pour génération article', [
                'estimated_input_tokens' => $estimatedInputTokens,
                'adjusted_max_tokens' => $maxTokens,
                'model' => $model
            ]);
            
            $result = AiService::callAI($user, $system, [
                'model' => $model,
                'temperature' => 0.7,
                'max_tokens' => $maxTokens,
                'timeout' => 120 // Plus de temps pour les articles longs
            ]);

            if ($result && isset($result['content'])) {
                Log::info('Article généré avec succès', [
                    'title' => $title,
                    'provider' => $result['provider'] ?? 'unknown',
                    'content_length' => strlen($result['content'])
                ]);
                return $result['content'];
            }

            return null;

        } catch (\Throwable $e) {
            Log::error('Erreur génération contenu article', [
                'title' => $title,
                'error' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
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
