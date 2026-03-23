<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class DepartmentTopCitiesService
{
    public function getTopCitiesByPopulation(string $departmentName, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));

        // Cache pour éviter d'appeler l'API à chaque chargement.
        $cacheKey = 'top_cities_population:' . md5($departmentName . ':' . $limit);

        return Cache::remember($cacheKey, 60 * 24 * 30, function () use ($departmentName, $limit) {
            $apiKey = \App\Models\Setting::get('chatgpt_api_key')
                ?: (config('services.openai.key') ?? env('OPENAI_API_KEY'));

            if (!$apiKey) {
                return [];
            }

            $prompt = $this->buildPrompt($departmentName, $limit);

            try {
                $response = Http::withToken($apiKey)
                    ->timeout(90)
                    ->retry(1, 1500)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => 'Tu renvoies uniquement un JSON valide. Aucune phrase autour.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 1200,
                    ]);

                if (!$response->ok()) {
                    return [];
                }

                $content = data_get($response->json(), 'choices.0.message.content');
                if (!is_string($content)) {
                    return [];
                }

                $decoded = json_decode($content, true);
                $cities = $decoded['cities'] ?? null;
                if (!is_array($cities)) {
                    return [];
                }

                $clean = [];
                foreach ($cities as $c) {
                    $name = isset($c['name']) ? trim((string) $c['name']) : '';
                    $postal = isset($c['postal_code']) ? (string) $c['postal_code'] : '';
                    $population = $c['population'] ?? null;

                    if ($name === '' || $postal === '' || $population === null) {
                        continue;
                    }

                    $postal = substr(preg_replace('/[^0-9]/', '', $postal), 0, 5);
                    if ($postal === '') {
                        continue;
                    }

                    $clean[] = [
                        'name' => $name,
                        'postal_code' => $postal,
                        'population' => (int) $population,
                    ];
                }

                return $clean;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    private function buildPrompt(string $departmentName, int $limit): string
    {
        return "Fournis les {$limit} communes principales du département de {$departmentName}, classées par population décroissante (habitants).
Retourne uniquement du JSON valide avec strictement ce schéma :
{
  \"cities\": [
    {\"name\": string, \"postal_code\": string, \"population\": number}
  ]
}

Contraintes :
- Pas de doublons.
- population = entier (nombre).
- postal_code = code postal français sur 5 chiffres (string).
- Ordre = du plus peuplé au moins peuplé.
";
    }
}

