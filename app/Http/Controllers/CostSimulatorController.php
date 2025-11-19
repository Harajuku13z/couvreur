<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class CostSimulatorController extends Controller
{
    /**
     * Afficher le simulateur de coûts
     */
    public function index()
    {
        // Récupérer la configuration du simulateur
        $simulatorConfig = $this->getSimulatorConfig();
        
        return view('simulator.index', compact('simulatorConfig'));
    }
    
    /**
     * Calculer le coût estimé
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'service_type' => 'required|string',
            'property_type' => 'required|string',
            'surface' => 'required|numeric|min:1|max:10000',
            'quality_level' => 'required|string|in:standard,premium,luxury',
            'urgency' => 'required|string|in:normal,urgent,emergency',
            'additional_options' => 'nullable|array',
        ]);
        
        try {
            $result = $this->calculateCost($validated);
            
            // Logger la simulation pour analytics
            Log::info('Simulation de coût effectuée', [
                'service' => $validated['service_type'],
                'surface' => $validated['surface'],
                'quality' => $validated['quality_level'],
                'estimated_cost' => $result['total_cost']
            ]);
            
            return response()->json([
                'success' => true,
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur calcul simulation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul. Veuillez réessayer.'
            ], 500);
        }
    }
    
    /**
     * Calculer le coût avec les paramètres configurables
     */
    protected function calculateCost($params)
    {
        $config = $this->getSimulatorConfig();
        
        // Récupérer la configuration du service
        $serviceConfig = collect($config['services'])->firstWhere('id', $params['service_type']);
        
        if (!$serviceConfig) {
            throw new \Exception('Service non trouvé');
        }
        
        // Coût de base par m²
        $baseCostPerSqm = $serviceConfig['base_cost_per_sqm'] ?? 50;
        
        // Multiplicateurs de qualité
        $qualityMultipliers = [
            'standard' => 1.0,
            'premium' => 1.4,
            'luxury' => 2.0
        ];
        
        // Multiplicateurs d'urgence
        $urgencyMultipliers = [
            'normal' => 1.0,
            'urgent' => 1.25,
            'emergency' => 1.6
        ];
        
        // Multiplicateurs par type de propriété
        $propertyMultipliers = [
            'house' => 1.0,
            'apartment' => 0.9,
            'commercial' => 1.3,
            'industrial' => 1.5
        ];
        
        $surface = $params['surface'];
        $qualityLevel = $params['quality_level'];
        $urgency = $params['urgency'];
        $propertyType = $params['property_type'];
        
        // Calcul de base
        $baseCost = $baseCostPerSqm * $surface;
        
        // Application des multiplicateurs
        $totalCost = $baseCost 
            * ($qualityMultipliers[$qualityLevel] ?? 1.0)
            * ($urgencyMultipliers[$urgency] ?? 1.0)
            * ($propertyMultipliers[$propertyType] ?? 1.0);
        
        // Coûts additionnels (options)
        $additionalCosts = 0;
        $selectedOptions = [];
        
        if (!empty($params['additional_options'])) {
            foreach ($params['additional_options'] as $optionId) {
                $option = collect($serviceConfig['additional_options'] ?? [])->firstWhere('id', $optionId);
                if ($option) {
                    $optionCost = $option['cost_per_sqm'] * $surface;
                    $additionalCosts += $optionCost;
                    $selectedOptions[] = [
                        'name' => $option['name'],
                        'cost' => $optionCost
                    ];
                }
            }
        }
        
        $totalCost += $additionalCosts;
        
        // Dégressivité pour grandes surfaces
        if ($surface > 100) {
            $discount = min(0.15, ($surface - 100) / 1000); // Max 15% de réduction
            $totalCost *= (1 - $discount);
        }
        
        // Arrondir au millier supérieur pour plus de professionnalisme
        $totalCostRounded = ceil($totalCost / 1000) * 1000;
        
        // Calculer la fourchette (±20%)
        $minCost = $totalCostRounded * 0.8;
        $maxCost = $totalCostRounded * 1.2;
        
        return [
            'base_cost' => round($baseCost, 2),
            'total_cost' => $totalCostRounded,
            'min_cost' => round($minCost, 0),
            'max_cost' => round($maxCost, 0),
            'surface' => $surface,
            'cost_per_sqm' => round($totalCostRounded / $surface, 2),
            'quality_level' => $qualityLevel,
            'quality_label' => $this->getQualityLabel($qualityLevel),
            'urgency' => $urgency,
            'urgency_label' => $this->getUrgencyLabel($urgency),
            'property_type' => $propertyType,
            'property_label' => $this->getPropertyLabel($propertyType),
            'service_name' => $serviceConfig['name'],
            'selected_options' => $selectedOptions,
            'additional_costs' => round($additionalCosts, 2),
            'breakdown' => [
                'base' => round($baseCost, 2),
                'quality_multiplier' => $qualityMultipliers[$qualityLevel] ?? 1.0,
                'urgency_multiplier' => $urgencyMultipliers[$urgency] ?? 1.0,
                'property_multiplier' => $propertyMultipliers[$propertyType] ?? 1.0,
                'options' => round($additionalCosts, 2),
            ]
        ];
    }
    
    /**
     * Récupérer la configuration du simulateur
     */
    protected function getSimulatorConfig()
    {
        $configData = Setting::get('cost_simulator_config', null);
        
        if ($configData) {
            $config = is_string($configData) ? json_decode($configData, true) : $configData;
            if (is_array($config)) {
                return $config;
            }
        }
        
        // Configuration par défaut
        return $this->getDefaultConfig();
    }
    
    /**
     * Configuration par défaut du simulateur
     */
    protected function getDefaultConfig()
    {
        return [
            'title' => 'Simulateur de Coûts',
            'description' => 'Estimez rapidement le coût de vos travaux de rénovation',
            'services' => [
                [
                    'id' => 'toiture',
                    'name' => 'Rénovation de toiture',
                    'base_cost_per_sqm' => 80,
                    'description' => 'Remplacement ou rénovation complète de votre toiture',
                    'additional_options' => [
                        [
                            'id' => 'isolation',
                            'name' => 'Isolation thermique renforcée',
                            'cost_per_sqm' => 25
                        ],
                        [
                            'id' => 'zinc',
                            'name' => 'Couverture en zinc',
                            'cost_per_sqm' => 40
                        ],
                        [
                            'id' => 'velux',
                            'name' => 'Installation fenêtres de toit',
                            'cost_per_sqm' => 15
                        ]
                    ]
                ],
                [
                    'id' => 'facade',
                    'name' => 'Ravalement de façade',
                    'base_cost_per_sqm' => 60,
                    'description' => 'Nettoyage et rénovation de votre façade',
                    'additional_options' => [
                        [
                            'id' => 'ite',
                            'name' => 'Isolation thermique extérieure (ITE)',
                            'cost_per_sqm' => 50
                        ],
                        [
                            'id' => 'peinture',
                            'name' => 'Peinture de finition premium',
                            'cost_per_sqm' => 20
                        ]
                    ]
                ],
                [
                    'id' => 'isolation',
                    'name' => 'Isolation des combles',
                    'base_cost_per_sqm' => 35,
                    'description' => 'Isolation thermique de vos combles',
                    'additional_options' => [
                        [
                            'id' => 'laine_roche',
                            'name' => 'Laine de roche haute performance',
                            'cost_per_sqm' => 10
                        ],
                        [
                            'id' => 'pare_vapeur',
                            'name' => 'Pare-vapeur renforcé',
                            'cost_per_sqm' => 5
                        ]
                    ]
                ],
                [
                    'id' => 'charpente',
                    'name' => 'Rénovation de charpente',
                    'base_cost_per_sqm' => 120,
                    'description' => 'Réparation ou remplacement de charpente',
                    'additional_options' => [
                        [
                            'id' => 'traitement',
                            'name' => 'Traitement anti-insectes et anti-humidité',
                            'cost_per_sqm' => 15
                        ],
                        [
                            'id' => 'renfort',
                            'name' => 'Renforcement structure',
                            'cost_per_sqm' => 30
                        ]
                    ]
                ]
            ],
            'disclaimers' => [
                'Les prix affichés sont des estimations indicatives basées sur des moyennes nationales.',
                'Le coût final peut varier selon la complexité du projet, l\'état existant et votre localisation.',
                'Un devis personnalisé gratuit est nécessaire pour obtenir un prix précis.',
                'Les prix incluent la main d\'œuvre et les matériaux standard.'
            ]
        ];
    }
    
    /**
     * Sauvegarder la configuration du simulateur
     */
    public function saveConfig(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'services' => 'required|array',
            'services.*.id' => 'required|string',
            'services.*.name' => 'required|string',
            'services.*.base_cost_per_sqm' => 'required|numeric|min:0',
            'services.*.description' => 'required|string',
            'services.*.additional_options' => 'nullable|array',
            'disclaimers' => 'nullable|array',
        ]);
        
        Setting::set('cost_simulator_config', json_encode($validated), 'json', 'simulator');
        
        return redirect()->back()->with('success', 'Configuration du simulateur sauvegardée avec succès!');
    }
    
    /**
     * Afficher la page de configuration (admin)
     */
    public function config()
    {
        $config = $this->getSimulatorConfig();
        
        return view('admin.simulator.config', compact('config'));
    }
    
    /**
     * Labels pour l'affichage
     */
    protected function getQualityLabel($level)
    {
        return [
            'standard' => 'Standard',
            'premium' => 'Premium',
            'luxury' => 'Luxe'
        ][$level] ?? 'Standard';
    }
    
    protected function getUrgencyLabel($level)
    {
        return [
            'normal' => 'Normal (sous 2-4 semaines)',
            'urgent' => 'Urgent (sous 1 semaine)',
            'emergency' => 'Urgence (sous 48h)'
        ][$level] ?? 'Normal';
    }
    
    protected function getPropertyLabel($type)
    {
        return [
            'house' => 'Maison individuelle',
            'apartment' => 'Appartement',
            'commercial' => 'Local commercial',
            'industrial' => 'Bâtiment industriel'
        ][$type] ?? 'Maison individuelle';
    }
}

