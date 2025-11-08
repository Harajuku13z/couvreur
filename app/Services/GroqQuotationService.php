<?php

namespace App\Services;

use App\Services\AiService;
use Illuminate\Support\Facades\Log;

class GroqQuotationService
{
    /**
     * Générer les lignes de devis à partir d'une description libre
     * 
     * @param string $description Description globale des travaux
     * @param string|null $superficie Superficie totale (ex: "150 m²")
     * @param float|null $prixFinalEstime Prix final estimé en EUR
     * @return array Tableau de lignes de devis
     */
    public function generateQuotationLines(
        string $description,
        ?string $superficie = null,
        ?float $prixFinalEstime = null
    ): array {
        $systemMessage = "Tu es un expert en chiffrage de travaux de bâtiment. Ta mission est de décomposer une description globale de travaux en lignes de devis détaillées et quantifiées. Réponds UNIQUEMENT avec un objet JSON valide. Ne donne aucune explication, aucun texte avant ou après le JSON. Le JSON doit être un tableau d'objets, chacun ayant exactement les clés suivantes : description, quantite, unite et prix_unitaire.";

        $prompt = $this->buildPrompt($description, $superficie, $prixFinalEstime);

        Log::info('GroqQuotationService: Génération de lignes de devis', [
            'description_length' => strlen($description),
            'superficie' => $superficie,
            'prix_final_estime' => $prixFinalEstime,
        ]);

        $response = AiService::callAI($prompt, $systemMessage, [
            'temperature' => 0.3, // Plus bas pour des réponses plus cohérentes
            'max_tokens' => 2000,
        ]);

        if (!$response || !isset($response['content'])) {
            Log::error('GroqQuotationService: Échec de l\'appel IA');
            throw new \Exception('Impossible de générer les lignes de devis. Veuillez réessayer.');
        }

        $content = $response['content'];
        
        // Nettoyer le contenu pour extraire uniquement le JSON
        $jsonContent = $this->extractJson($content);

        if (!$jsonContent) {
            Log::error('GroqQuotationService: Impossible d\'extraire le JSON', [
                'content' => $content,
            ]);
            throw new \Exception('Format de réponse invalide. Veuillez réessayer.');
        }

        $lines = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('GroqQuotationService: Erreur de parsing JSON', [
                'json_error' => json_last_error_msg(),
                'content' => $jsonContent,
            ]);
            throw new \Exception('Erreur lors du traitement de la réponse. Veuillez réessayer.');
        }

        if (!is_array($lines)) {
            Log::error('GroqQuotationService: Le JSON n\'est pas un tableau');
            throw new \Exception('Format de réponse invalide. Veuillez réessayer.');
        }

        // Valider et normaliser les lignes
        $validatedLines = $this->validateAndNormalizeLines($lines, $prixFinalEstime);

        Log::info('GroqQuotationService: Lignes générées avec succès', [
            'count' => count($validatedLines),
        ]);

        return $validatedLines;
    }

    /**
     * Construire le prompt pour l'IA
     */
    private function buildPrompt(
        string $description,
        ?string $superficie,
        ?float $prixFinalEstime
    ): string {
        $prompt = "Description du Client : \"$description\"\n\n";

        if ($superficie) {
            $prompt .= "Superficie Totale : \"$superficie\"\n";
        }

        if ($prixFinalEstime) {
            $prompt .= "Prix Final Estimé (Global) : \"$prixFinalEstime EUR\"\n";
        }

        $prompt .= "\nContrainte : ";
        
        if ($prixFinalEstime) {
            $prompt .= "Répartis le prix final estimé sur les lignes générées de manière cohérente";
            if ($superficie) {
                $prompt .= ", en utilisant la superficie comme base de quantité pour la majorité des items";
            }
            $prompt .= ".";
        } else {
            $prompt .= "Génère des prix unitaires réalistes pour le marché français.";
        }

        $prompt .= "\n\nIMPORTANT : Réponds UNIQUEMENT avec un JSON valide, sans texte avant ou après. Format attendu :\n";
        $prompt .= "[\n";
        $prompt .= "  {\"description\": \"...\", \"quantite\": 150, \"unite\": \"m²\", \"prix_unitaire\": 15},\n";
        $prompt .= "  {\"description\": \"...\", \"quantite\": 2, \"unite\": \"unité\", \"prix_unitaire\": 1200}\n";
        $prompt .= "]";

        return $prompt;
    }

    /**
     * Extraire le JSON du contenu (peut contenir du markdown ou du texte)
     */
    private function extractJson(string $content): ?string
    {
        // Essayer de trouver un bloc JSON dans le contenu
        // Chercher un tableau JSON
        if (preg_match('/\[[\s\S]*\]/', $content, $matches)) {
            return $matches[0];
        }

        // Chercher un objet JSON
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            return $matches[0];
        }

        // Si le contenu commence directement par [ ou {, l'utiliser tel quel
        $trimmed = trim($content);
        if (($trimmed[0] === '[' || $trimmed[0] === '{') && 
            ($trimmed[strlen($trimmed) - 1] === ']' || $trimmed[strlen($trimmed) - 1] === '}')) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Valider et normaliser les lignes générées
     */
    private function validateAndNormalizeLines(array $lines, ?float $prixFinalEstime): array
    {
        $validated = [];
        $totalCalculated = 0;

        foreach ($lines as $index => $line) {
            if (!is_array($line)) {
                continue;
            }

            $description = trim($line['description'] ?? '');
            $quantite = (float) ($line['quantite'] ?? 0);
            $unite = trim($line['unite'] ?? 'unité');
            $prixUnitaire = (float) ($line['prix_unitaire'] ?? 0);

            if (empty($description) || $quantite <= 0 || $prixUnitaire <= 0) {
                continue;
            }

            $totalLigne = $quantite * $prixUnitaire;
            $totalCalculated += $totalLigne;

            $validated[] = [
                'description' => $description,
                'quantite' => $quantite,
                'unite' => $unite,
                'prix_unitaire' => $prixUnitaire,
                'total_ligne' => $totalLigne,
                'ordre' => $index + 1,
            ];
        }

        // Si un prix final était fourni et que la somme diffère, ajuster proportionnellement
        if ($prixFinalEstime && $totalCalculated > 0 && abs($totalCalculated - $prixFinalEstime) > 1) {
            $ratio = $prixFinalEstime / $totalCalculated;
            
            foreach ($validated as &$line) {
                $line['prix_unitaire'] = round($line['prix_unitaire'] * $ratio, 2);
                $line['total_ligne'] = round($line['quantite'] * $line['prix_unitaire'], 2);
            }
        }

        return $validated;
    }
}

