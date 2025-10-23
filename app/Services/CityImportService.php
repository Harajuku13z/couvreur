<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CityImportService
{
    public function importByDepartment(string $department): array
    {
        $prompt = $this->buildPrompt([
            'mode' => 'department',
            'department' => $department,
        ]);
        return $this->callOpenAI($prompt);
    }

    public function importByRegion(string $region): array
    {
        $prompt = $this->buildPrompt([
            'mode' => 'region',
            'region' => $region,
        ]);
        return $this->callOpenAI($prompt);
    }

    public function importByRadius(string $address, int $radiusKm): array
    {
        $prompt = $this->buildPrompt([
            'mode' => 'radius',
            'address' => $address,
            'radius_km' => $radiusKm,
        ]);
        return $this->callOpenAI($prompt);
    }

    private function buildPrompt(array $params): string
    {
        $base = "Tu es un assistant de données géographiques pour la France. Retourne uniquement un JSON valide respectant strictement ce schéma: {\n  \"cities\": [ { \"name\": string, \"postal_code\": string, \"department\": string, \"region\": string } ]\n}.\nRègles:\n- Inclure villes, communes et villages (toutes entités habitées).\n- Pas de doublons.\n- Entre 20 et 100 entrées pour éviter les timeouts.\n- Codes postaux français à 5 chiffres.\n- N'ajoute aucun texte hors JSON.\n";

        if ($params['mode'] === 'department') {
            $base .= "Contexte: Lister les principales villes/communes/villages du département français: {$params['department']}.";
        } elseif ($params['mode'] === 'region') {
            $base .= "Contexte: Lister les principales villes/communes/villages de la région française: {$params['region']}.";
        } else {
            $base .= "Contexte: Lister les principales villes/communes/villages dans un rayon de {$params['radius_km']} km autour de l'adresse: {$params['address']}, en France.";
        }

        return $base;
    }

    private function callOpenAI(string $prompt): array
    {
        $apiKey = \App\Models\Setting::get('chatgpt_api_key') ?: (config('services.openai.key') ?? env('OPENAI_API_KEY'));
        if (!$apiKey) {
            return [
                'error' => 'OPENAI_API_KEY manquant. Ajoutez-le dans .env ou config/services.php.',
                'cities' => [],
            ];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->retry(2, 1000)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu retournes uniquement du JSON valide.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 4000,
                ]);

            if (!$response->ok()) {
                $error = $response->json()['error']['message'] ?? 'Erreur API OpenAI: ' . $response->status();
                return ['error' => $error, 'cities' => []];
            }

            $content = data_get($response->json(), 'choices.0.message.content');
            $decoded = json_decode($content, true);
            if (!is_array($decoded) || !isset($decoded['cities']) || !is_array($decoded['cities'])) {
                return ['error' => 'Réponse non conforme. Veuillez réessayer.', 'cities' => []];
            }

            // Sanitize minimal
            $clean = [];
            foreach ($decoded['cities'] as $c) {
                if (!isset($c['name'], $c['postal_code'])) { continue; }
                $clean[] = [
                    'name' => trim((string)$c['name']),
                    'postal_code' => substr(preg_replace('/[^0-9]/', '', (string)$c['postal_code']), 0, 5),
                    'department' => $c['department'] ?? null,
                    'region' => $c['region'] ?? null,
                ];
            }
            return ['cities' => $clean];
        } catch (\Throwable $e) {
            return ['error' => 'Exception: ' . $e->getMessage(), 'cities' => []];
        }
    }
}


