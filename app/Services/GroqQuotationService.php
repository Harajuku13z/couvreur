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

        // Ajouter les lignes standard à la fin
        $standardLines = $this->getStandardLines($prixFinalEstime, count($validatedLines));
        $validatedLines = array_merge($validatedLines, $standardLines);

        Log::info('GroqQuotationService: Lignes générées avec succès', [
            'count' => count($validatedLines),
            'standard_lines' => count($standardLines),
        ]);

        return $validatedLines;
    }

    /**
     * Obtenir les lignes standard à ajouter à tous les devis
     */
    private function getStandardLines(?float $prixFinalEstime, int $existingLinesCount): array
    {
        $standardLines = [
            [
                'description' => 'Nettoyage et remise en état du chantier - Chantier rendu propre',
                'quantite' => 1,
                'unite' => 'lot',
                'prix_unitaire' => 150.00,
                'total_ligne' => 150.00,
                'ordre' => $existingLinesCount + 1,
            ],
            [
                'description' => 'Évacuation des déchets et gravats vers déchetterie agréée',
                'quantite' => 1,
                'unite' => 'lot',
                'prix_unitaire' => 200.00,
                'total_ligne' => 200.00,
                'ordre' => $existingLinesCount + 2,
            ],
            [
                'description' => 'Assurance décennale et garantie de parfait achèvement',
                'quantite' => 1,
                'unite' => 'lot',
                'prix_unitaire' => 0.00, // Inclus dans le devis
                'total_ligne' => 0.00,
                'ordre' => $existingLinesCount + 3,
            ],
        ];

        // Si un prix final est fourni, ajuster les lignes standard proportionnellement
        // mais garder un montant raisonnable (max 5% du total)
        if ($prixFinalEstime && $prixFinalEstime > 0) {
            $maxStandardAmount = $prixFinalEstime * 0.05; // 5% max pour les lignes standard
            $currentStandardAmount = 350.00; // 150 + 200
            
            if ($currentStandardAmount > $maxStandardAmount) {
                $ratio = $maxStandardAmount / $currentStandardAmount;
                foreach ($standardLines as &$line) {
                    if ($line['prix_unitaire'] > 0) {
                        $line['prix_unitaire'] = round($line['prix_unitaire'] * $ratio, 2);
                        $line['total_ligne'] = round($line['quantite'] * $line['prix_unitaire'], 2);
                    }
                }
            }
        }

        return $standardLines;
    }

    /**
     * Construire le prompt pour l'IA
     */
    private function buildPrompt(
        string $description,
        ?string $superficie,
        ?float $prixFinalEstime
    ): string {
        $prompt = "Tu es un expert en chiffrage de travaux de rénovation et bâtiment en France.\n\n";
        $prompt .= "Description du Client : \"$description\"\n\n";

        if ($superficie) {
            $prompt .= "Superficie Totale : \"$superficie\"\n";
        }

        if ($prixFinalEstime) {
            $prompt .= "Prix Final Estimé (Global) : \"$prixFinalEstime EUR\"\n";
        }

        $prompt .= "\nINSTRUCTIONS IMPORTANTES :\n";
        $prompt .= "1. Décompose les travaux en lignes détaillées et professionnelles\n";
        $prompt .= "2. Chaque description doit être précise, technique et professionnelle (ex: \"Fourniture et pose de tuiles en terre cuite modèle XX avec liteaux neufs\" plutôt que \"Pose tuiles\")\n";
        $prompt .= "3. Utilise un vocabulaire professionnel du bâtiment (fourniture, pose, dépose, mise en œuvre, etc.)\n";
        $prompt .= "4. Inclus les détails techniques importants (matériaux, dimensions, finitions)\n";
        $prompt .= "5. Les quantités doivent être réalistes et précises\n";
        $prompt .= "6. Les prix unitaires doivent correspondre aux tarifs du marché français en 2025\n";
        
        $prompt .= "\nContrainte : ";
        
        if ($prixFinalEstime) {
            $prompt .= "Répartis le prix final estimé sur les lignes générées de manière cohérente";
            if ($superficie) {
                $prompt .= ", en utilisant la superficie comme base de quantité pour la majorité des items";
            }
            $prompt .= ".";
        } else {
            $prompt .= "Génère des prix unitaires réalistes pour le marché français du bâtiment en 2025.";
        }

        $prompt .= "\n\nEXEMPLES DE BONNES DESCRIPTIONS :\n";
        $prompt .= "- \"Dépose et mise en décharge des anciennes tuiles et éléments de zinguerie existants\"\n";
        $prompt .= "- \"Fourniture et pose de tuiles en terre cuite (modèle XX) avec liteaux neufs et fixation mécanique\"\n";
        $prompt .= "- \"Isolation de la toiture par l'extérieur (Sarking) avec panneaux isolants haute performance 140mm\"\n";
        $prompt .= "- \"Fourniture et installation de fenêtre de toit Velux standard 114x118 avec finition intérieure\"\n";

        $prompt .= "\n\nIMPORTANT : Réponds UNIQUEMENT avec un JSON valide, sans texte avant ou après. Format attendu :\n";
        $prompt .= "[\n";
        $prompt .= "  {\"description\": \"Description détaillée et professionnelle\", \"quantite\": 150, \"unite\": \"m²\", \"prix_unitaire\": 15},\n";
        $prompt .= "  {\"description\": \"Autre description détaillée\", \"quantite\": 2, \"unite\": \"unité\", \"prix_unitaire\": 1200}\n";
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

