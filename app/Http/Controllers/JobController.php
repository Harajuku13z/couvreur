<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class JobController extends Controller
{
    /**
     * Afficher la page des offres d'emploi
     */
    public function index()
    {
        try {
            // Offres d'emploi légitimes pour une entreprise de couverture
            $jobs = [
            [
                'id' => 1,
                'title' => 'Couvreur / Zingueur H/F',
                'description' => 'Nous recherchons un couvreur expérimenté pour rejoindre notre équipe. Missions : pose et réparation de toitures, zinguerie, étanchéité. CDI temps plein.',
                'employmentType' => 'FULL_TIME',
                'datePosted' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'validThrough' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'baseSalary' => [
                    'currency' => 'EUR',
                    'value' => [
                        'minValue' => 2500,
                        'maxValue' => 3500
                    ]
                ],
                'jobLocation' => [
                    'address' => [
                        'addressLocality' => 'Bretagne',
                        'addressRegion' => 'Bretagne',
                        'addressCountry' => 'FR'
                    ]
                ],
                'hiringOrganization' => [
                    'name' => 'Normes Rénovation Bretagne',
                    'sameAs' => 'https://normesrenovationbretagne.fr'
                ],
                'workHours' => 'Temps plein, 35h/semaine',
                'qualifications' => 'CAP Couvreur ou expérience équivalente, permis B requis'
            ],
            [
                'id' => 2,
                'title' => 'Apprenti Couvreur H/F',
                'description' => 'Poste d\'apprentissage pour devenir couvreur professionnel. Formation en alternance, encadrement par des professionnels expérimentés.',
                'employmentType' => 'PART_TIME',
                'datePosted' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'validThrough' => Carbon::now()->addMonths(2)->format('Y-m-d'),
                'baseSalary' => [
                    'currency' => 'EUR',
                    'value' => [
                        'minValue' => 800,
                        'maxValue' => 1200
                    ]
                ],
                'jobLocation' => [
                    'address' => [
                        'addressLocality' => 'Bretagne',
                        'addressRegion' => 'Bretagne',
                        'addressCountry' => 'FR'
                    ]
                ],
                'hiringOrganization' => [
                    'name' => 'Normes Rénovation Bretagne',
                    'sameAs' => 'https://normesrenovationbretagne.fr'
                ],
                'workHours' => 'Alternance, 20h/semaine',
                'qualifications' => 'Niveau 3ème minimum, motivation et sérieux'
            ],
            [
                'id' => 3,
                'title' => 'Chef d\'équipe Couverture H/F',
                'description' => 'Poste de chef d\'équipe pour encadrer une équipe de couvreurs. Gestion de chantiers, coordination des travaux, respect des normes de sécurité.',
                'employmentType' => 'FULL_TIME',
                'datePosted' => Carbon::now()->subDays(3)->format('Y-m-d'),
                'validThrough' => Carbon::now()->addMonths(4)->format('Y-m-d'),
                'baseSalary' => [
                    'currency' => 'EUR',
                    'value' => [
                        'minValue' => 3500,
                        'maxValue' => 4500
                    ]
                ],
                'jobLocation' => [
                    'address' => [
                        'addressLocality' => 'Bretagne',
                        'addressRegion' => 'Bretagne',
                        'addressCountry' => 'FR'
                    ]
                ],
                'hiringOrganization' => [
                    'name' => 'Normes Rénovation Bretagne',
                    'sameAs' => 'https://normesrenovationbretagne.fr'
                ],
                'workHours' => 'Temps plein, 39h/semaine',
                'qualifications' => 'Minimum 5 ans d\'expérience, permis B, capacité d\'encadrement'
            ],
            [
                'id' => 4,
                'title' => 'Spécialiste Étanchéité / Hydrofuge H/F',
                'description' => 'Recherche d\'un spécialiste en étanchéité et traitement hydrofuge. Missions : application de produits hydrofuges, réparation d\'étanchéité, diagnostic.',
                'employmentType' => 'FULL_TIME',
                'datePosted' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'validThrough' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'baseSalary' => [
                    'currency' => 'EUR',
                    'value' => [
                        'minValue' => 2800,
                        'maxValue' => 3800
                    ]
                ],
                'jobLocation' => [
                    'address' => [
                        'addressLocality' => 'Bretagne',
                        'addressRegion' => 'Bretagne',
                        'addressCountry' => 'FR'
                    ]
                ],
                'hiringOrganization' => [
                    'name' => 'Normes Rénovation Bretagne',
                    'sameAs' => 'https://normesrenovationbretagne.fr'
                ],
                'workHours' => 'Temps plein, 35h/semaine',
                'qualifications' => 'Formation en étanchéité ou expérience équivalente, permis B'
            ]
        ];

        return view('jobs.index', compact('jobs'));
        } catch (\Exception $e) {
            \Log::error('Erreur JobController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('errors.500', [
                'message' => 'Erreur lors du chargement des offres d\'emploi'
            ], 500);
        }
    }
}

