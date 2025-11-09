<?php

namespace App\Services;

use App\Services\AiService;
use Illuminate\Support\Facades\Log;

class GptSeoGenerator
{
    /**
     * Générer un article SEO optimisé via GPT
     * 
     * @param string $keyword Mot-clé principal
     * @param string $cityName Nom de la ville
     * @param array $relatedQueries Requêtes associées
     * @param array $competitors Résultats SERP concurrents
     * @return array|null Données de l'article généré (titre, contenu, meta, etc.)
     */
    public function generateSeoArticle(
        string $keyword,
        string $cityName,
        array $relatedQueries = [],
        array $competitors = []
    ): ?array {
        $prompt = $this->buildPrompt($keyword, $cityName, $relatedQueries, $competitors);
        
        $systemMessage = 'Tu es un rédacteur SEO professionnel spécialisé dans le contenu local pour le secteur du bâtiment et de la rénovation.';
        
        $result = AiService::callAI($prompt, $systemMessage, [
            'max_tokens' => 4000,
            'temperature' => 0.2,
            'timeout' => 120
        ]);

        if (!$result || !isset($result['content'])) {
            Log::error('GptSeoGenerator: Échec génération article', [
                'keyword' => $keyword,
                'city' => $cityName
            ]);
            return null;
        }

        $content = $result['content'];
        
        // Essayer de parser le JSON
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Si pas de JSON, essayer d'extraire un bloc JSON
            if (preg_match('/\{.*\}/s', $content, $matches)) {
                $decoded = json_decode($matches[0], true);
            } else {
                Log::warning('GptSeoGenerator: Réponse non-JSON', [
                    'content_preview' => substr($content, 0, 200)
                ]);
                return null;
            }
        }

        if (!$decoded || empty($decoded['titre']) || empty($decoded['contenu_html'])) {
            Log::error('GptSeoGenerator: Données invalides', [
                'decoded' => $decoded
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * Construire le prompt pour GPT
     */
    protected function buildPrompt(
        string $keyword,
        string $cityName,
        array $relatedQueries,
        array $competitors
    ): string {
        // Récupérer les informations de l'entreprise
        $companyName = \App\Models\Setting::where('key', 'company_name')->value('value') ?? 'notre entreprise';
        $companyDescription = \App\Models\Setting::where('key', 'company_description')->value('value') ?? '';
        $companyCity = \App\Models\Setting::where('key', 'company_city')->value('value') ?? '';
        $companyPhone = \App\Models\Setting::where('key', 'company_phone')->value('value') ?? '';
        
        // Construire la liste des sources (titres + liens)
        $sourcesList = '';
        if (!empty($competitors)) {
            $sourcesList = "\n\n**Sources à utiliser** - Voici " . count($competitors) . " titres d'articles existants + liens pour comprendre le sujet :\n\n";
            foreach ($competitors as $index => $competitor) {
                $title = $competitor['title'] ?? 'Article sans titre';
                $link = $competitor['link'] ?? '#';
                $snippet = $competitor['snippet'] ?? '';
                $sourcesList .= ($index + 1) . ". **{$title}**\n";
                $sourcesList .= "   Lien: {$link}\n";
                if ($snippet) {
                    $sourcesList .= "   Extrait: " . substr($snippet, 0, 150) . "...\n";
                }
                $sourcesList .= "\n";
            }
        }
        
        $related = empty($relatedQueries) ? '' : implode(', ', array_slice($relatedQueries, 0, 6));
        
        $companyInfo = '';
        if ($companyName && $companyName !== 'notre entreprise') {
            $companyInfo = "\n\n**INFORMATIONS DE L'ENTREPRISE À METTRE EN AVANT:**\n";
            $companyInfo .= "- Nom: {$companyName}\n";
            if ($companyDescription) {
                $companyInfo .= "- Description: {$companyDescription}\n";
            }
            if ($companyCity) {
                $companyInfo .= "- Localisation: {$companyCity}\n";
            }
            if ($companyPhone) {
                $companyInfo .= "- Téléphone: {$companyPhone}\n";
            }
            $companyInfo .= "\n**IMPORTANT:** Intègre naturellement ces informations dans le contenu, notamment dans un paragraphe dédié à {$cityName} où tu mentionneras {$companyName} comme acteur local de confiance. Ajoute un appel à l'action à la fin pour inviter les lecteurs à contacter {$companyName}.";
        }
        
        return trim("
Tu es un expert en rédaction SEO et marketing de contenu. Ta tâche est de rédiger un article web de qualité supérieure, structuré, engageant et optimisé pour le référencement Google.

**1. Mot-clé principal :** {$keyword} à {$cityName}

{$sourcesList}
**Requêtes associées à intégrer naturellement :** {$related}
{$companyInfo}

**Objectifs de l'article :**

- Créer un contenu **unique**, qui n'est pas dupliqué par rapport aux sources.
- Fournir une **introduction captivante** et un **résumé/conclusion** clairs.
- Structurer l'article avec des **sous-titres H2 et H3 pertinents**.
- Inclure le **mot-clé principal** et des variantes naturelles tout au long de l'article.
- Utiliser des phrases claires, engageantes et faciles à lire.
- Proposer des **listes, exemples, statistiques ou conseils** si possible.
- Longueur: **entre 1500 et 2500 mots** pour un contenu complet et détaillé.
- HTML propre avec des balises sémantiques: <h1>, <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em>
- Inclure des retours à la ligne appropriés pour une meilleure lisibilité.
- Ajouter un **appel à l'action** à la fin (ex : \"Découvrez nos services\", \"Contactez-nous pour un devis gratuit\", etc.)
- Inclure une **FAQ de 5 à 8 questions** pertinentes avec réponses détaillées.

**Format de sortie STRICTEMENT EN JSON (pas de markdown, pas de code block):**

{
  \"titre\": \"Titre optimisé SEO (60-70 caractères max)\",
  \"meta_description\": \"Description SEO optimisée (155 caractères max)\",
  \"contenu_html\": \"Article complet en HTML avec structure propre (H1, H2, H3, paragraphes, listes, etc.)\",
  \"mots_cles\": [\"mot-clé 1\", \"mot-clé 2\", \"mot-clé 3\", \"mot-clé 4\", \"mot-clé 5\"],
  \"faq\": [
    {\"question\": \"Question 1\", \"reponse\": \"Réponse détaillée 1\"},
    {\"question\": \"Question 2\", \"reponse\": \"Réponse détaillée 2\"},
    ...
  ]
}

**IMPORTANT :**
- Le contenu_html doit être du HTML valide et propre avec des retours à la ligne appropriés.
- Ne te contente pas de reformuler les titres, synthétise les informations, ajoute des exemples, et rends l'article plus complet que les sources existantes.
- L'article doit être significativement plus long et détaillé que les sources (1500-2500 mots).
- Inclure au moins un paragraphe mentionnant explicitement {$cityName} et l'expertise locale.
- Structure HTML recommandée: <h1>Titre</h1> <p>Introduction</p> <h2>Sous-titre 1</h2> <p>Contenu...</p> <h3>Sous-sous-titre</h3> <p>Contenu...</p> <h2>Conclusion</h2> <p>Conclusion...</p> <h2>FAQ</h2> <div class=\"faq\">...</div>
");
    }
}

