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
        $related = empty($relatedQueries) ? '' : implode(', ', array_slice($relatedQueries, 0, 5));
        $compet = empty($competitors) ? '' : collect($competitors)
            ->pluck('title')
            ->filter()
            ->take(3)
            ->join('; ');

        return trim("
Tu es un expert SEO local. Rédige un article optimisé pour le mot-clé principal: \"{$keyword}\" ciblant la ville {$cityName}.

CONTRAINTES STRICTES:
- Longueur: entre 900 et 1200 mots.
- Structure HTML: H1 (titre) + intro + 3 à 5 H2 + paragraphes courts + FAQ 3-5 questions.
- Inclure un paragraphe parlant de la ville {$cityName} (proximité, acteurs locaux, aide pratique).
- Fournir META description (max 155 caractères).
- Fournir une liste de 5 mots-clés secondaires.
- Fournir FAQ (question/réponse).
- Ton: professionnel, utile, optimiste.
- Base-toi sur ces requêtes associées: {$related}
- Exemples de titres/snippets concurrents: {$compet}

FORMAT DE SORTIE STRICTEMENT EN JSON (pas de markdown, pas de code block):
{
  \"titre\": \"\",
  \"meta_description\": \"\",
  \"contenu_html\": \"\",
  \"mots_cles\": [\"\", \"\", \"\", \"\", \"\"],
  \"faq\": [{\"question\":\"\",\"reponse\":\"\"}, ...]
}

IMPORTANT: Le contenu_html doit être du HTML valide avec des balises <h1>, <h2>, <p>, etc. Inclure au moins un paragraphe mentionnant explicitement {$cityName}.
");
    }
}

