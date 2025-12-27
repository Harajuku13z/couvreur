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
        // Récupérer le type de simulateur actif
        $simulatorType = Setting::get('simulator_type', 'couvreur');
        
        // Récupérer la configuration pour ce type
        $configKey = 'cost_simulator_config_' . $simulatorType;
        $configData = Setting::get($configKey, null);
        
        if ($configData) {
            $config = is_string($configData) ? json_decode($configData, true) : $configData;
            if (is_array($config)) {
                return $config;
            }
        }
        
        // Configuration par défaut selon le type
        return $this->getDefaultConfigForType($simulatorType);
    }
    
    /**
     * Obtenir la configuration par défaut selon le type de simulateur
     */
    protected function getDefaultConfigForType($type)
    {
        $defaultConfigs = $this->getDefaultConfigs();
        return $defaultConfigs[$type] ?? $defaultConfigs['couvreur'];
    }
    
    /**
     * Obtenir toutes les configurations par défaut
     */
    protected function getDefaultConfigs()
    {
        return [
            'couvreur' => $this->getDefaultConfig(),
            'elagueur' => $this->getElagueurConfig(),
            'peintre' => $this->getPeintreConfig(),
            'plombier' => $this->getPlombierConfig(),
        ];
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
        
        // Sauvegarder la configuration pour le type actuel
        $simulatorType = Setting::get('simulator_type', 'couvreur');
        $configKey = 'cost_simulator_config_' . $simulatorType;
        
        Setting::set($configKey, json_encode($validated), 'json', 'simulator');
        
        return redirect()->back()->with('success', 'Configuration du simulateur sauvegardée avec succès!');
    }
    
    /**
     * Réinitialiser la configuration à la valeur par défaut
     */
    public function resetConfig(Request $request)
    {
        $simulatorType = $request->input('type', Setting::get('simulator_type', 'couvreur'));
        $defaultConfig = $this->getDefaultConfigForType($simulatorType);
        
        $configKey = 'cost_simulator_config_' . $simulatorType;
        Setting::set($configKey, json_encode($defaultConfig), 'json', 'simulator');
        
        return redirect()->back()->with('success', 'Configuration réinitialisée aux valeurs par défaut !');
    }
    
    /**
     * Afficher la page de gestion des simulateurs (admin)
     */
    public function manage()
    {
        $simulatorType = Setting::get('simulator_type', 'couvreur');
        $availableTypes = $this->getAvailableSimulatorTypes();
        
        return view('admin.simulator.index', compact('simulatorType', 'availableTypes'));
    }
    
    /**
     * Afficher la page de configuration (admin)
     */
    public function config()
    {
        $simulatorType = Setting::get('simulator_type', 'couvreur');
        $config = $this->getSimulatorConfig();
        
        return view('admin.simulator.config', compact('config', 'simulatorType'));
    }
    
    /**
     * Changer le type de simulateur actif
     */
    public function setType(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:couvreur,elagueur,peintre,plombier'
        ]);
        
        Setting::set('simulator_type', $validated['type'], 'string', 'simulator');
        
        return redirect()->route('admin.simulator.index')
            ->with('success', 'Type de simulateur changé avec succès !');
    }
    
    /**
     * Obtenir les types de simulateurs disponibles
     */
    protected function getAvailableSimulatorTypes()
    {
        return [
            'couvreur' => [
                'name' => 'Couvreur',
                'icon' => 'fas fa-home',
                'description' => 'Simulateur pour travaux de couverture et toiture'
            ],
            'elagueur' => [
                'name' => 'Élagueur',
                'icon' => 'fas fa-tree',
                'description' => 'Simulateur pour travaux d\'élagage et d\'abattage'
            ],
            'peintre' => [
                'name' => 'Peintre',
                'icon' => 'fas fa-paint-brush',
                'description' => 'Simulateur pour travaux de peinture intérieure et extérieure'
            ],
            'plombier' => [
                'name' => 'Plombier',
                'icon' => 'fas fa-faucet',
                'description' => 'Simulateur pour travaux de plomberie et sanitaires'
            ],
        ];
    }
    
    /**
     * Configuration par défaut pour élagueur
     */
    protected function getElagueurConfig()
    {
        return [
            'title' => 'Simulateur de Coûts - Élagage',
            'description' => 'Estimez rapidement le coût de vos travaux d\'élagage et d\'abattage',
            'services' => [
                [
                    'id' => 'elagage',
                    'name' => 'Élagage d\'arbre',
                    'base_cost_per_sqm' => 150,
                    'description' => 'Taille et élagage d\'arbres (par arbre)',
                    'additional_options' => [
                        [
                            'id' => 'hauteur',
                            'name' => 'Arbre de plus de 10m',
                            'cost_per_sqm' => 50
                        ],
                        [
                            'id' => 'difficulte',
                            'name' => 'Accès difficile',
                            'cost_per_sqm' => 80
                        ],
                        [
                            'id' => 'evacuation',
                            'name' => 'Évacuation des déchets',
                            'cost_per_sqm' => 30
                        ]
                    ]
                ],
                [
                    'id' => 'abattage',
                    'name' => 'Abattage d\'arbre',
                    'base_cost_per_sqm' => 300,
                    'description' => 'Abattage complet d\'un arbre',
                    'additional_options' => [
                        [
                            'id' => 'demenagement',
                            'name' => 'Démontage par tronçons',
                            'cost_per_sqm' => 100
                        ],
                        [
                            'id' => 'grue',
                            'name' => 'Utilisation d\'une grue',
                            'cost_per_sqm' => 200
                        ],
                        [
                            'id' => 'souche',
                            'name' => 'Dessouchage',
                            'cost_per_sqm' => 150
                        ]
                    ]
                ],
                [
                    'id' => 'haie',
                    'name' => 'Taille de haie',
                    'base_cost_per_sqm' => 25,
                    'description' => 'Taille et entretien de haie',
                    'additional_options' => [
                        [
                            'id' => 'haute',
                            'name' => 'Haie de plus de 2m',
                            'cost_per_sqm' => 10
                        ],
                        [
                            'id' => 'forme',
                            'name' => 'Taille artistique',
                            'cost_per_sqm' => 15
                        ]
                    ]
                ],
                [
                    'id' => 'debroussaillage',
                    'name' => 'Débroussaillage',
                    'base_cost_per_sqm' => 20,
                    'description' => 'Nettoyage et débroussaillage de terrain',
                    'additional_options' => [
                        [
                            'id' => 'pente',
                            'name' => 'Terrain en pente',
                            'cost_per_sqm' => 8
                        ],
                        [
                            'id' => 'evacuation',
                            'name' => 'Évacuation des déchets verts',
                            'cost_per_sqm' => 12
                        ]
                    ]
                ]
            ],
            'disclaimers' => [
                'Les prix affichés sont des estimations indicatives basées sur des moyennes nationales.',
                'Le coût final peut varier selon la hauteur, l\'accessibilité et la complexité du travail.',
                'Un devis personnalisé gratuit est nécessaire pour obtenir un prix précis.',
                'Les prix incluent la main d\'œuvre et l\'évacuation des déchets.'
            ]
        ];
    }
    
    /**
     * Configuration par défaut pour peintre
     */
    protected function getPeintreConfig()
    {
        return [
            'title' => 'Simulateur de Coûts - Peinture',
            'description' => 'Estimez rapidement le coût de vos travaux de peinture',
            'services' => [
                [
                    'id' => 'interieur',
                    'name' => 'Peinture intérieure',
                    'base_cost_per_sqm' => 25,
                    'description' => 'Peinture des murs et plafonds intérieurs',
                    'additional_options' => [
                        [
                            'id' => 'preparation',
                            'name' => 'Préparation complète (ponçage, rebouchage)',
                            'cost_per_sqm' => 8
                        ],
                        [
                            'id' => 'plafond',
                            'name' => 'Peinture plafond inclus',
                            'cost_per_sqm' => 5
                        ],
                        [
                            'id' => 'premium',
                            'name' => 'Peinture premium (qualité supérieure)',
                            'cost_per_sqm' => 10
                        ]
                    ]
                ],
                [
                    'id' => 'exterieur',
                    'name' => 'Peinture extérieure',
                    'base_cost_per_sqm' => 35,
                    'description' => 'Peinture de façade et extérieur',
                    'additional_options' => [
                        [
                            'id' => 'preparation',
                            'name' => 'Préparation complète (décapage, rebouchage)',
                            'cost_per_sqm' => 15
                        ],
                        [
                            'id' => 'echafaudage',
                            'name' => 'Échafaudage nécessaire',
                            'cost_per_sqm' => 12
                        ],
                        [
                            'id' => 'hydrofuge',
                            'name' => 'Traitement hydrofuge',
                            'cost_per_sqm' => 8
                        ]
                    ]
                ],
                [
                    'id' => 'boiserie',
                    'name' => 'Peinture de boiseries',
                    'base_cost_per_sqm' => 40,
                    'description' => 'Peinture de portes, fenêtres et volets',
                    'additional_options' => [
                        [
                            'id' => 'decapage',
                            'name' => 'Décapage ancienne peinture',
                            'cost_per_sqm' => 20
                        ],
                        [
                            'id' => 'vernis',
                            'name' => 'Vernis protection',
                            'cost_per_sqm' => 10
                        ]
                    ]
                ],
                [
                    'id' => 'decorative',
                    'name' => 'Peinture décorative',
                    'base_cost_per_sqm' => 50,
                    'description' => 'Faux-finis, patines, effets décoratifs',
                    'additional_options' => [
                        [
                            'id' => 'faux_fin',
                            'name' => 'Faux-fin (marbre, bois)',
                            'cost_per_sqm' => 30
                        ],
                        [
                            'id' => 'patine',
                            'name' => 'Patine vieillie',
                            'cost_per_sqm' => 25
                        ]
                    ]
                ]
            ],
            'disclaimers' => [
                'Les prix affichés sont des estimations indicatives basées sur des moyennes nationales.',
                'Le coût final peut varier selon l\'état des surfaces, le nombre de couches et la qualité des peintures.',
                'Un devis personnalisé gratuit est nécessaire pour obtenir un prix précis.',
                'Les prix incluent la main d\'œuvre et les matériaux standard.'
            ]
        ];
    }
    
    /**
     * Configuration par défaut pour plombier
     */
    protected function getPlombierConfig()
    {
        return [
            'title' => 'Simulateur de Coûts - Plomberie',
            'description' => 'Estimez rapidement le coût de vos travaux de plomberie',
            'services' => [
                [
                    'id' => 'renovation',
                    'name' => 'Rénovation plomberie',
                    'base_cost_per_sqm' => 120,
                    'description' => 'Remplacement complet de la plomberie (par pièce)',
                    'additional_options' => [
                        [
                            'id' => 'mur',
                            'name' => 'Travaux dans les murs',
                            'cost_per_sqm' => 50
                        ],
                        [
                            'id' => 'salle_bain',
                            'name' => 'Salle de bain complète',
                            'cost_per_sqm' => 200
                        ],
                        [
                            'id' => 'chaudiere',
                            'name' => 'Raccordement chaudière',
                            'cost_per_sqm' => 150
                        ]
                    ]
                ],
                [
                    'id' => 'sanitaire',
                    'name' => 'Installation sanitaire',
                    'base_cost_per_sqm' => 200,
                    'description' => 'Installation WC, lavabo, douche, baignoire',
                    'additional_options' => [
                        [
                            'id' => 'wc',
                            'name' => 'WC suspendu',
                            'cost_per_sqm' => 100
                        ],
                        [
                            'id' => 'douche',
                            'name' => 'Douche à l\'italienne',
                            'cost_per_sqm' => 150
                        ],
                        [
                            'id' => 'robinetterie',
                            'name' => 'Robinetterie haut de gamme',
                            'cost_per_sqm' => 80
                        ]
                    ]
                ],
                [
                    'id' => 'chauffage',
                    'name' => 'Installation chauffage',
                    'base_cost_per_sqm' => 80,
                    'description' => 'Installation radiateurs et plomberie chauffage',
                    'additional_options' => [
                        [
                            'id' => 'radiateur',
                            'name' => 'Radiateur design',
                            'cost_per_sqm' => 120
                        ],
                        [
                            'id' => 'plancher',
                            'name' => 'Plancher chauffant',
                            'cost_per_sqm' => 60
                        ],
                        [
                            'id' => 'regulation',
                            'name' => 'Système de régulation',
                            'cost_per_sqm' => 100
                        ]
                    ]
                ],
                [
                    'id' => 'depannage',
                    'name' => 'Dépannage urgence',
                    'base_cost_per_sqm' => 150,
                    'description' => 'Intervention d\'urgence (fuite, panne)',
                    'additional_options' => [
                        [
                            'id' => 'nuit',
                            'name' => 'Intervention de nuit',
                            'cost_per_sqm' => 100
                        ],
                        [
                            'id' => 'weekend',
                            'name' => 'Intervention week-end',
                            'cost_per_sqm' => 80
                        ]
                    ]
                ]
            ],
            'disclaimers' => [
                'Les prix affichés sont des estimations indicatives basées sur des moyennes nationales.',
                'Le coût final peut varier selon la complexité de l\'installation et les matériaux choisis.',
                'Un devis personnalisé gratuit est nécessaire pour obtenir un prix précis.',
                'Les prix incluent la main d\'œuvre et les matériaux standard.'
            ]
        ];
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

