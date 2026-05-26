<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdsPilotController extends Controller
{
    public function index()
    {
        return view('admin.ads-pilot.index', $this->dashboardData());
    }

    public function generateCampaign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:180',
            'website_url' => 'required|string|max:255',
            'phone' => 'required|string|max:80',
            'service_areas' => 'required|string|max:1000',
            'services' => 'required|array|min:1',
            'services.*' => 'string|max:120',
            'offer' => 'nullable|string|max:180',
            'daily_budget' => 'required|numeric|min:1|max:500',
            'objective' => 'required|string|max:80',
        ]);

        $areas = collect(explode(',', $validated['service_areas']))
            ->map(fn ($area) => trim($area))
            ->filter()
            ->values()
            ->all();

        $campaign = [
            'name' => 'Couvreur ' . ($areas[0] ?? 'Local') . ' - Search',
            'dailyBudget' => (float) $validated['daily_budget'],
            'objective' => $validated['objective'],
            'humanValidationMode' => true,
            'autoNegativeKeywords' => false,
        ];

        $groups = collect([
            'Couvreur local',
            'Réparation toiture',
            'Fuite toiture urgence',
            'Nettoyage toiture',
            'Zinguerie',
            'Isolation toiture',
            'Rénovation toiture',
        ])->map(fn ($name) => [
            'name' => $name,
            'keywords' => [
                '[' . mb_strtolower($name) . ']',
                '"' . mb_strtolower($name) . ' près de moi"',
                '"devis ' . mb_strtolower($name) . '"',
                '"' . mb_strtolower($name) . ' ' . ($areas[0] ?? '') . '"',
            ],
            'ads' => [
                'headlines' => [
                    $name . ' - Devis gratuit',
                    'Artisan couvreur local',
                    $validated['offer'] ?: 'Intervention rapide',
                ],
                'descriptions' => [
                    'Contactez une entreprise de couverture locale pour un diagnostic clair.',
                    'Campagne sécurisée avec validation humaine avant publication.',
                ],
            ],
        ])->all();

        return response()->json([
            'success' => true,
            'campaign' => $campaign,
            'adGroups' => $groups,
            'negativeKeywords' => $this->defaultNegativeKeywords(),
            'sitelinks' => ['Devis toiture', 'Urgence fuite', 'Rénovation toiture', 'Contact'],
            'callouts' => ['Devis gratuit', 'Entreprise locale', 'Intervention rapide', 'Suivi clair'],
            'callExtension' => $validated['phone'],
            'safety' => [
                'dryRun' => true,
                'message' => 'Simulation prête. Aucune campagne réelle ne sera publiée sans connexion Google Ads et validation humaine.',
            ],
        ]);
    }

    public function scan(): JsonResponse
    {
        $terms = collect($this->searchTerms())->map(function ($term) {
            $analysis = $this->analyzeTerm($term['term']);

            return array_merge($term, $analysis);
        })->values();

        $recommendations = $terms
            ->where('decision', 'exclude')
            ->map(fn ($term) => [
                'type' => 'negative_keyword',
                'title' => 'Exclure "' . $term['term'] . '"',
                'description' => $term['reason'],
                'confidence' => $term['relevanceScore'] <= 10 ? 96 : 78,
                'riskLevel' => 'low',
                'status' => 'requires_validation',
            ])
            ->values()
            ->all();

        Log::info('Ads Pilot scan demo exécuté', [
            'terms' => $terms->count(),
            'recommendations' => count($recommendations),
        ]);

        return response()->json([
            'success' => true,
            'mode' => 'demo',
            'searchTerms' => $terms,
            'recommendations' => $recommendations,
            'summary' => 'Scan terminé: ' . count($recommendations) . ' exclusions sûres proposées en validation humaine.',
        ]);
    }

    public function report(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'summary' => 'Votre campagne Couvreur Chalon-sur-Saône a dépensé 42,80 € aujourd’hui. Elle a généré 18 clics, 3 appels et 1 formulaire. Le coût par lead estimé est de 10,70 €. L’IA a détecté des recherches inutiles comme formation couvreur, salaire couvreur, castorama plaque toiture et tuto réparer fuite toiture.',
        ]);
    }

    private function dashboardData(): array
    {
        return [
            'stats' => [
                'accounts' => 1,
                'campaigns' => 1,
                'spentToday' => '42,80 €',
                'leads' => 4,
                'costPerLead' => '10,70 €',
                'nextScan' => 'Dans 58 min',
            ],
            'campaign' => [
                'name' => 'Couvreur Chalon-sur-Saône',
                'status' => 'Active',
                'dailyBudget' => '15 €/jour',
                'healthScore' => 82,
                'lastScan' => 'Il y a 2h',
            ],
            'searchTerms' => $this->searchTerms(),
            'negativeKeywords' => $this->defaultNegativeKeywords(),
        ];
    }

    private function searchTerms(): array
    {
        return [
            ['term' => 'couvreur chalon sur saone', 'clicks' => 9, 'cost' => '10,80 €', 'conversions' => 2],
            ['term' => 'réparation fuite toiture', 'clicks' => 6, 'cost' => '9,20 €', 'conversions' => 1],
            ['term' => 'formation couvreur', 'clicks' => 4, 'cost' => '4,80 €', 'conversions' => 0],
            ['term' => 'salaire couvreur', 'clicks' => 3, 'cost' => '3,10 €', 'conversions' => 0],
            ['term' => 'castorama plaque toiture', 'clicks' => 7, 'cost' => '7,60 €', 'conversions' => 0],
            ['term' => 'devis toiture gratuit', 'clicks' => 8, 'cost' => '11,40 €', 'conversions' => 2],
            ['term' => 'zingueur autour de moi', 'clicks' => 5, 'cost' => '6,90 €', 'conversions' => 1],
            ['term' => 'tuto réparer fuite toiture', 'clicks' => 5, 'cost' => '4,20 €', 'conversions' => 0],
        ];
    }

    private function analyzeTerm(string $term): array
    {
        $termLower = mb_strtolower($term);
        $wasteTerms = ['emploi', 'recrutement', 'formation', 'stage', 'salaire', 'tuto', 'youtube', 'pdf', 'castorama', 'leroy merlin', 'brico dépôt', 'forum'];

        foreach ($wasteTerms as $waste) {
            if (str_contains($termLower, $waste)) {
                return [
                    'relevanceScore' => 5,
                    'decision' => 'exclude',
                    'reason' => 'Recherche non commerciale ou liée au bricolage/emploi/formation.',
                ];
            }
        }

        return [
            'relevanceScore' => 88,
            'decision' => 'keep',
            'reason' => 'Intention locale et commerciale pertinente.',
        ];
    }

    private function defaultNegativeKeywords(): array
    {
        return [
            'emploi',
            'recrutement',
            'salaire',
            'formation',
            'stage',
            'alternance',
            'cap couvreur',
            'école',
            'tuto',
            'youtube',
            'pdf',
            'gratuit informationnel',
            'définition',
            'bricolage',
            'soi-même',
            'forum',
            'castorama',
            'leroy merlin',
            'brico dépôt',
            'bricoman',
            'matériel',
            'outil',
            'occasion',
            'logiciel',
            'wikipedia',
        ];
    }
}
