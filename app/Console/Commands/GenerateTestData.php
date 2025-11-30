<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PhoneCall;
use App\Models\Visit;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\Client;
use App\Models\Submission;
use App\Models\Facture;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class GenerateTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:test-data {--force : Supprimer toutes les données existantes avant de générer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer des données de test : 57 appels téléphoniques, 1980 visites et 11 devis pour novembre';

    /**
     * Villes de Côte-d'Or
     */
    protected $villesCoteDor = [
        'Chevigny-Saint-Sauveur' => ['country' => 'France', 'country_code' => 'FR'],
        'Dijon' => ['country' => 'France', 'country_code' => 'FR'],
        'Beaune' => ['country' => 'France', 'country_code' => 'FR'],
        'Chenôve' => ['country' => 'France', 'country_code' => 'FR'],
        'Talant' => ['country' => 'France', 'country_code' => 'FR'],
        'Quetigny' => ['country' => 'France', 'country_code' => 'FR'],
        'Fontaine-lès-Dijon' => ['country' => 'France', 'country_code' => 'FR'],
        'Longvic' => ['country' => 'France', 'country_code' => 'FR'],
        'Auxonne' => ['country' => 'France', 'country_code' => 'FR'],
        'Nuits-Saint-Georges' => ['country' => 'France', 'country_code' => 'FR'],
        'Gevrey-Chambertin' => ['country' => 'France', 'country_code' => 'FR'],
        'Marsannay-la-Côte' => ['country' => 'France', 'country_code' => 'FR'],
        'Génlis' => ['country' => 'France', 'country_code' => 'FR'],
        'Plombières-lès-Dijon' => ['country' => 'France', 'country_code' => 'FR'],
        'Saint-Apollinaire' => ['country' => 'France', 'country_code' => 'FR'],
        'Ruffey-lès-Echirey' => ['country' => 'France', 'country_code' => 'FR'],
        'Sennecey-lès-Dijon' => ['country' => 'France', 'country_code' => 'FR'],
        'Perrigny-lès-Dijon' => ['country' => 'France', 'country_code' => 'FR'],
        'Varois-et-Chaignot' => ['country' => 'France', 'country_code' => 'FR'],
        'Neuilly-lès-Dijon' => ['country' => 'France', 'country_code' => 'FR'],
        'Ahuy' => ['country' => 'France', 'country_code' => 'FR'],
        'Couchey' => ['country' => 'France', 'country_code' => 'FR'],
        'Fixin' => ['country' => 'France', 'country_code' => 'FR'],
        'Brochon' => ['country' => 'France', 'country_code' => 'FR'],
        'Gilly-lès-Cîteaux' => ['country' => 'France', 'country_code' => 'FR'],
    ];

    /**
     * User agents réels pour navigateurs
     */
    protected $userAgents = [
        // Chrome Desktop
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        
        // Firefox Desktop
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:121.0) Gecko/20100101 Firefox/121.0',
        
        // Safari
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        
        // Edge
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        
        // Mobile Android
        'Mozilla/5.0 (Linux; Android 13; SM-S908B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 12; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
    ];

    /**
     * Pages sources pour les appels
     */
    protected $sourcePages = [
        'home',
        'ads/renovation-de-toiture-couverture-chevigny-saint-sauveur',
        'ads/renovation-de-toiture-couverture-dijon',
        'ads/renovation-de-toiture-couverture-beaune',
        'services',
        'services/renovation-de-toiture-couverture',
        'contact',
        'form/success',
    ];

    /**
     * Exécuter la commande
     */
    public function handle()
    {
        $this->info('🚀 Génération des données de test...');
        
        // Vérifier que les tables existent
        if (!Schema::hasTable('phone_calls')) {
            $this->error('❌ La table phone_calls n\'existe pas. Exécutez: php artisan migrate');
            return 1;
        }
        
        if (!Schema::hasTable('visits')) {
            $this->error('❌ La table visits n\'existe pas. Exécutez: php artisan migrate');
            return 1;
        }

        // Supprimer les données existantes si --force
        if ($this->option('force')) {
            $this->warn('⚠️  Suppression des données existantes...');
            
            $phoneCallsCount = PhoneCall::count();
            $visitsCount = Visit::count();
            
            PhoneCall::truncate();
            $this->info("  ✅ {$phoneCallsCount} appels supprimés");
            
            Visit::truncate();
            $this->info("  ✅ {$visitsCount} visites supprimées");
            
            $this->info('');
        } else {
            $existingCalls = PhoneCall::count();
            $existingVisits = Visit::count();
            
            if ($existingCalls > 0 || $existingVisits > 0) {
                $this->warn("⚠️  Attention : Il existe déjà {$existingCalls} appels et {$existingVisits} visites.");
                $this->warn("   Utilisez --force pour les supprimer avant de générer les nouvelles données.");
                $this->warn("   Commande : php artisan generate:test-data --force");
                
                if (!$this->confirm('Continuer quand même ? Les nouvelles données seront ajoutées aux existantes.')) {
                    $this->info('❌ Opération annulée');
                    return 0;
                }
            }
        }

        // Générer les appels téléphoniques
        $this->info('📞 Génération de 57 appels téléphoniques...');
        $this->generatePhoneCalls();

        // Générer les visites
        $this->info('👁️ Génération de 1980 visites...');
        $this->generateVisits();

        // Générer les devis
        $this->info('');
        $this->info('📋 Génération de 32 devis pour novembre 2025 (11 acceptés)...');
        $this->generateDevis();

        // Générer les soumissions
        $this->info('');
        $this->info('📝 Génération de 17 soumissions pour novembre 2025...');
        $this->generateSubmissions();

        $this->info('');
        $this->info('✅ Génération terminée avec succès !');
        $this->info("   - " . PhoneCall::count() . " appels téléphoniques");
        $this->info("   - " . Visit::count() . " visites");
        $this->info("   - " . Devis::count() . " devis");
        $this->info("   - " . Submission::count() . " soumissions");
        
        $totalCA = Devis::sum('total_ttc');
        $this->info("   - CA total : " . number_format($totalCA, 2, ',', ' ') . " €");
        
        return 0;
    }

    /**
     * Générer les appels téléphoniques
     */
    protected function generatePhoneCalls()
    {
        $phoneNumber = \App\Models\Setting::get('company_phone_raw', '0633532123');
        
        // Dates : du 28 octobre 2025 au 29 novembre 2025
        $startDate = Carbon::create(2025, 10, 28, 0, 0, 0);
        $endDate = Carbon::create(2025, 11, 29, 23, 59, 59);
        $daysDiff = $startDate->diffInDays($endDate);
        
        // 32 appels de Chevigny-Saint-Sauveur
        $chevignyCalls = 32;
        // 25 appels des autres villes
        $otherCalls = 25;
        
        $this->info("  → Génération de {$chevignyCalls} appels depuis Chevigny-Saint-Sauveur");
        $this->generateCallsForCity('Chevigny-Saint-Sauveur', $chevignyCalls, $startDate, $endDate, $phoneNumber);
        
        $this->info("  → Génération de {$otherCalls} appels depuis les autres villes");
        // Répartir les 25 appels sur les autres villes
        $otherCities = array_filter($this->villesCoteDor, function($key) {
            return $key !== 'Chevigny-Saint-Sauveur';
        }, ARRAY_FILTER_USE_KEY);
        
        $otherCitiesKeys = array_keys($otherCities);
        $callsPerCity = [];
        $remaining = $otherCalls;
        
        // Distribuer équitablement
        foreach ($otherCitiesKeys as $city) {
            if ($remaining <= 0) break;
            $count = min(rand(1, 4), $remaining); // 1 à 4 appels par ville
            $callsPerCity[$city] = $count;
            $remaining -= $count;
        }
        
        // S'assurer que tous les appels sont distribués
        if ($remaining > 0) {
            $randomCity = $otherCitiesKeys[array_rand($otherCitiesKeys)];
            $callsPerCity[$randomCity] = ($callsPerCity[$randomCity] ?? 0) + $remaining;
        }
        
        foreach ($callsPerCity as $city => $count) {
            if ($count > 0) {
                $this->generateCallsForCity($city, $count, $startDate, $endDate, $phoneNumber);
            }
        }
        
        $this->info('  ✅ Appels générés avec succès');
    }

    /**
     * Générer des appels pour une ville spécifique
     */
    protected function generateCallsForCity($cityName, $count, $startDate, $endDate, $phoneNumber)
    {
        $cityData = $this->villesCoteDor[$cityName] ?? ['country' => 'France', 'country_code' => 'FR'];
        
        for ($i = 0; $i < $count; $i++) {
            // Date aléatoire dans la période (inclus les deux dates)
            // Calculer le timestamp min et max pour garantir la couverture complète
            $startTimestamp = $startDate->timestamp;
            $endTimestamp = $endDate->timestamp;
            $randomTimestamp = rand($startTimestamp, $endTimestamp);
            
            $clickedAt = Carbon::createFromTimestamp($randomTimestamp);
            
            // S'assurer que l'heure est entre 8h et 20h pour les appels
            if ($clickedAt->hour < 8) {
                $clickedAt->setTime(rand(8, 12), rand(0, 59), rand(0, 59));
            } elseif ($clickedAt->hour > 20) {
                $clickedAt->setTime(rand(14, 20), rand(0, 59), rand(0, 59));
            }
            
            // IP aléatoire française (plages privées pour test)
            $ipPrefixes = ['192.168.', '10.0.', '172.16.'];
            $ipPrefix = $ipPrefixes[array_rand($ipPrefixes)];
            $ipAddress = $ipPrefix . rand(1, 255) . '.' . rand(1, 255);
            
            // User agent aléatoire
            $userAgent = $this->userAgents[array_rand($this->userAgents)];
            
            // Page source aléatoire
            $sourcePage = $this->sourcePages[array_rand($this->sourcePages)];
            
            // Referrer URL (parfois vide pour accès direct)
            $referrerUrl = null;
            if (rand(0, 100) < 70) { // 70% avec referrer
                $referrers = [
                    'https://www.google.com/search?q=couvreur+chevigny',
                    'https://www.google.com/search?q=rénovation+toiture+dijon',
                    'https://www.google.fr/search?q=couvreur+cote+dor',
                    'https://maps.google.com/maps?q=couvreur+chevigny',
                ];
                $referrerUrl = $referrers[array_rand($referrers)];
            }
            
            // Session ID unique
            $sessionId = str()->random(40);
            
            // Détecter si c'est un bot (ne devrait pas l'être avec nos user agents)
            $isBot = \App\Services\BotDetectionService::isBot($userAgent);
            
            $phoneCallData = [
                'session_id' => $sessionId,
                'phone_number' => $phoneNumber,
                'source_page' => $sourcePage,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'city' => $cityName,
                'country' => $cityData['country'],
                'country_code' => $cityData['country_code'],
                'referrer_url' => $referrerUrl,
                'clicked_at' => $clickedAt,
            ];
            
            // Ajouter is_bot seulement si la colonne existe
            if (Schema::hasColumn('phone_calls', 'is_bot')) {
                $phoneCallData['is_bot'] = $isBot;
            }
            
            PhoneCall::create($phoneCallData);
        }
    }

    /**
     * Générer les visites
     */
    protected function generateVisits()
    {
        // 1189 visites depuis Google Search
        // 15 visites depuis Google My Business
        // 776 visites autres sources (direct, autres moteurs, etc.)
        
        $googleSearchCount = 1189;
        $googleBusinessCount = 15;
        $otherSourcesCount = 776;
        
        // Dates : du 28 octobre 2025 au 29 novembre 2025
        $startDate = Carbon::create(2025, 10, 28, 0, 0, 0);
        $endDate = Carbon::create(2025, 11, 29, 23, 59, 59);
        
        $this->info("  → Génération de {$googleSearchCount} visites depuis Google Search");
        $this->generateVisitsFromSource('google_search', $googleSearchCount, $startDate, $endDate);
        
        $this->info("  → Génération de {$googleBusinessCount} visites depuis Google My Business");
        $this->generateVisitsFromSource('google_business', $googleBusinessCount, $startDate, $endDate);
        
        $this->info("  → Génération de {$otherSourcesCount} visites depuis d'autres sources");
        $this->generateVisitsFromSource('other', $otherSourcesCount, $startDate, $endDate);
        
        $this->info('  ✅ Visites générées avec succès');
    }

    /**
     * Générer des visites depuis une source spécifique
     */
    protected function generateVisitsFromSource($source, $count, $startDate, $endDate)
    {
        $villesKeys = array_keys($this->villesCoteDor);
        
        for ($i = 0; $i < $count; $i++) {
            // Date aléatoire dans la période (inclus les deux dates)
            // Calculer le timestamp min et max pour garantir la couverture complète
            $startTimestamp = $startDate->timestamp;
            $endTimestamp = $endDate->timestamp;
            $randomTimestamp = rand($startTimestamp, $endTimestamp);
            
            $visitedAt = Carbon::createFromTimestamp($randomTimestamp);
            
            // Ville aléatoire (plus de Chevigny)
            $cityName = $villesKeys[array_rand($villesKeys)];
            // Ajuster pour que Chevigny ait plus de visites
            if (rand(0, 100) < 30) {
                $cityName = 'Chevigny-Saint-Sauveur';
            }
            
            $cityData = $this->villesCoteDor[$cityName];
            
            // IP aléatoire
            $ipPrefixes = ['192.168.', '10.0.', '172.16.'];
            $ipPrefix = $ipPrefixes[array_rand($ipPrefixes)];
            $ipAddress = $ipPrefix . rand(1, 255) . '.' . rand(1, 255);
            
            // User agent aléatoire
            $userAgent = $this->userAgents[array_rand($this->userAgents)];
            
            // Pages du site
            $pages = [
                '/',
                '/services',
                '/services/renovation-de-toiture-couverture',
                '/ads/renovation-de-toiture-couverture-chevigny-saint-sauveur',
                '/ads/renovation-de-toiture-couverture-dijon',
                '/contact',
                '/portfolio',
                '/reviews',
                '/blog',
            ];
            
            $path = $pages[array_rand($pages)];
            $url = 'https://couvreur-chevigny-saint-sauveur.fr' . $path;
            
            // Referrer selon la source
            $referrerUrl = null;
            if ($source === 'google_search') {
                $queries = [
                    'couvreur+chevigny+saint+sauveur',
                    'rénovation+toiture+dijon',
                    'couvreur+cote+dor',
                    'zinguerie+chevigny',
                    'isolation+toiture+beaune',
                    'couvreur+urgent+dijon',
                ];
                $query = $queries[array_rand($queries)];
                $referrerUrl = "https://www.google.com/search?q={$query}";
            } elseif ($source === 'google_business') {
                $referrerUrl = 'https://www.google.com/maps/place/';
            } elseif (rand(0, 100) < 40) { // 40% avec referrer pour autres sources
                $referrers = [
                    'https://www.bing.com/search?q=couvreur+chevigny',
                    'https://fr.search.yahoo.com/search?p=couvreur+dijon',
                    'https://www.facebook.com/',
                    null, // Accès direct
                ];
                $referrerUrl = $referrers[array_rand($referrers)];
            }
            
            // Détecter device et browser depuis user agent
            $deviceInfo = $this->detectDeviceFromUA($userAgent);
            
            // Session ID unique
            $sessionId = str()->random(40);
            
            // Détecter si c'est un bot
            $isBot = \App\Services\BotDetectionService::isBot($userAgent);
            
            $visitData = [
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'url' => $url,
                'path' => $path,
                'method' => 'GET',
                'referrer_url' => $referrerUrl,
                'city' => $cityName,
                'country' => $cityData['country'],
                'country_code' => $cityData['country_code'],
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'duration' => rand(10, 300), // 10 à 300 secondes
                'visited_at' => $visitedAt,
            ];
            
            // Ajouter is_bot seulement si la colonne existe
            if (Schema::hasColumn('visits', 'is_bot')) {
                $visitData['is_bot'] = $isBot;
            }
            
            Visit::create($visitData);
        }
    }

    /**
     * Détecter device depuis user agent
     */
    protected function detectDeviceFromUA($userAgent)
    {
        $ua = strtolower($userAgent);
        
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $os = 'Unknown';
        
        // Device
        if (preg_match('/mobile|android|iphone|ipad|ipod/i', $ua)) {
            if (preg_match('/tablet|ipad/i', $ua)) {
                $deviceType = 'tablet';
            } else {
                $deviceType = 'mobile';
            }
        }
        
        // Browser
        if (strpos($ua, 'chrome') !== false && strpos($ua, 'edg') === false) {
            $browser = 'Chrome';
        } elseif (strpos($ua, 'firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) {
            $browser = 'Safari';
        } elseif (strpos($ua, 'edg') !== false) {
            $browser = 'Edge';
        } elseif (strpos($ua, 'opera') !== false) {
            $browser = 'Opera';
        }
        
        // OS
        if (strpos($ua, 'windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($ua, 'mac') !== false || strpos($ua, 'darwin') !== false) {
            $os = 'macOS';
        } elseif (strpos($ua, 'linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($ua, 'android') !== false) {
            $os = 'Android';
        } elseif (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
            $os = 'iOS';
        }
        
        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * Générer les devis de test
     */
    protected function generateDevis()
    {
        // Vérifier que les tables existent
        if (!Schema::hasTable('devis')) {
            $this->warn('  ⚠️  La table devis n\'existe pas. Ignoré.');
            return;
        }
        
        if (!Schema::hasTable('clients')) {
            $this->warn('  ⚠️  La table clients n\'existe pas. Ignoré.');
            return;
        }

        // Supprimer les devis existants si --force
        if ($this->option('force')) {
            // Ne pas supprimer les clients, seulement les devis
            // Supprimer d'abord les factures liées si la table existe
            if (Schema::hasTable('factures')) {
                \DB::table('factures')->whereIn('devis_id', function($query) {
                    $query->select('id')->from('devis');
                })->delete();
            }
            
            // Supprimer les lignes de devis
            LigneDevis::query()->delete();
            
            // Supprimer les devis (delete au lieu de truncate pour respecter les contraintes)
            $devisCount = Devis::count();
            Devis::query()->delete();
            $this->info("  ✅ {$devisCount} devis existants supprimés");
        }

        // Types de travaux avec prix unitaires réalistes
        $workTypes = [
            'hydrofuge' => [
                'description' => 'Traitement hydrofuge de toiture',
                'unite' => 'm²',
                'prix_unitaire_min' => 6,
                'prix_unitaire_max' => 12,
                'surface_min' => 80,
                'surface_max' => 250,
                'prix_fixe_base' => 1980, // Prix fixe autour de 1980€
                'variation_percent' => 0.15, // ±15% de variation
            ],
            'demoussage' => [
                'description' => 'Démoussage et nettoyage de toiture',
                'unite' => 'm²',
                'prix_unitaire_min' => 5,
                'prix_unitaire_max' => 10,
                'surface_min' => 100,
                'surface_max' => 300,
                'prix_fixe_base' => 2000, // Prix fixe autour de 2000€
                'variation_percent' => 0.20, // ±20% de variation
            ],
            'isolation' => [
                'description' => 'Isolation thermique de toiture',
                'unite' => 'm²',
                'prix_unitaire_min' => 40,
                'prix_unitaire_max' => 80,
                'surface_min' => 50,
                'surface_max' => 150,
                'prix_fixe_base' => null, // Prix variable entre 5000 et 8000
                'prix_min' => 5000,
                'prix_max' => 8000,
            ],
            'renovation_toiture' => [
                'description' => 'Rénovation complète de toiture',
                'unite' => 'm²',
                'prix_unitaire_min' => 80,
                'prix_unitaire_max' => 150,
                'surface_min' => 40,
                'surface_max' => 200,
                'prix_fixe_base' => null, // Prix variable
                'prix_min' => 8000,
                'prix_max' => 25000,
            ],
        ];

        // Dates pour octobre-novembre 2025
        $startDate = Carbon::create(2025, 10, 28, 0, 0, 0);
        $endDate = Carbon::create(2025, 11, 30, 23, 59, 59);

        // Créer 32 devis au total : 11 acceptés, le reste réparti entre refusés, en attente et brouillons
        $devisCreated = 0;
        $totalGeneratedHT = 0;
        $totalAcceptedHT = 0; // Total HT des devis acceptés créés

        // Répartition des statuts : 11 acceptés, 8 refusés, 7 en attente, 6 brouillons
        $acceptedCount = 11;
        $refusedCount = 8;
        $pendingCount = 7;
        $draftCount = 6;
        $totalDevis = $acceptedCount + $refusedCount + $pendingCount + $draftCount; // 32 total
        
        // Répartition des 32 devis
        // Total CA souhaité : 58.000 € TTC (pour les devis acceptés uniquement)
        // Total CA souhaité tous devis : 100.000 € TTC
        // Avec TVA à 20% : 58.000 / 1.20 = 48.333,33 € HT pour acceptés
        $totalTTCAccepted = 58000;
        $totalHTAccepted = $totalTTCAccepted / 1.20; // TVA 20%
        
        $totalTTCAll = 100000;
        $totalHTAll = $totalTTCAll / 1.20; // TVA 20%

        // Répartition des types de travaux
        $repartition = [
            'hydrofuge' => 10,
            'demoussage' => 10,
            'isolation' => 7,
            'renovation_toiture' => 5,
        ];
        
        // Créer une liste de statuts à alterner
        $statutsList = [];
        for ($i = 0; $i < $acceptedCount; $i++) $statutsList[] = 'Accepté';
        for ($i = 0; $i < $refusedCount; $i++) $statutsList[] = 'Refusé';
        for ($i = 0; $i < $pendingCount; $i++) $statutsList[] = 'En Attente';
        for ($i = 0; $i < $draftCount; $i++) $statutsList[] = 'Brouillon';
        shuffle($statutsList); // Mélanger pour alterner
        
        $statutIndex = 0;
        $acceptedCreated = 0;
        $refusedCreated = 0;
        $pendingCreated = 0;
        $draftCreated = 0;

        foreach ($repartition as $workType => $count) {
            $workConfig = $workTypes[$workType];
            
            for ($i = 0; $i < $count; $i++) {
                // Créer un client
                $city = $this->villesCoteDor[array_rand($this->villesCoteDor)];
                $cityName = array_search($city, $this->villesCoteDor);
                
                $prenoms = ['Jean', 'Marie', 'Pierre', 'Sophie', 'Michel', 'Catherine', 'Philippe', 'Isabelle', 'Alain', 'Martine'];
                $noms = ['Dubois', 'Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Leroy', 'Moreau'];
                
                $client = Client::create([
                    'nom' => $noms[array_rand($noms)],
                    'prenom' => $prenoms[array_rand($prenoms)],
                    'email' => strtolower(str_replace(' ', '.', $prenoms[array_rand($prenoms)])) . '.' . strtolower($noms[array_rand($noms)]) . rand(1, 999) . '@example.fr',
                    'telephone' => '0' . rand(6, 7) . rand(10000000, 99999999),
                    'adresse' => rand(1, 99) . ' Rue ' . $cityName,
                    'code_postal' => '21' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
                    'ville' => $cityName,
                    'pays' => 'France',
                ]);

                // Date aléatoire dans la période
                $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
                $dateEmission = Carbon::createFromTimestamp($randomTimestamp);
                $dateValidite = $dateEmission->copy()->addDays(30);

                // Déterminer le statut en alternant
                $statut = $statutsList[$statutIndex] ?? 'Brouillon';
                $statutIndex++;
                
                // Incrémenter les compteurs
                switch ($statut) {
                    case 'Accepté':
                        $acceptedCreated++;
                        break;
                    case 'Refusé':
                        $refusedCreated++;
                        break;
                    case 'En Attente':
                        $pendingCreated++;
                        break;
                    case 'Brouillon':
                        $draftCreated++;
                        break;
                }

                // Calculer le prix selon le type de travail
                if (isset($workConfig['prix_fixe_base'])) {
                    // Pour hydrofuge et demoussage : prix fixe autour d'une base
                    $variation = 1 + ((rand(-100, 100) / 100) * $workConfig['variation_percent']);
                    $devisHT = $workConfig['prix_fixe_base'] * $variation;
                    $surface = rand($workConfig['surface_min'], $workConfig['surface_max']);
                    $prixUnitaire = $devisHT / $surface;
                } elseif (isset($workConfig['prix_min']) && isset($workConfig['prix_max'])) {
                    // Pour isolation et rénovation : prix entre min et max
                    $devisHT = rand($workConfig['prix_min'], $workConfig['prix_max']);
                    $surface = rand($workConfig['surface_min'], $workConfig['surface_max']);
                    $prixUnitaire = $devisHT / $surface;
                } else {
                    // Fallback : calcul classique
                    $surface = rand($workConfig['surface_min'], $workConfig['surface_max']);
                    $prixUnitaire = rand(
                        (int)($workConfig['prix_unitaire_min'] * 100),
                        (int)($workConfig['prix_unitaire_max'] * 100)
                    ) / 100;
                    $devisHT = $surface * $prixUnitaire;
                }
                
                // Ajuster les montants pour atteindre les objectifs
                if ($statut === 'Accepté') {
                    // Calculer le montant restant à atteindre pour les acceptés
                    $remainingAccepted = $acceptedCount - $acceptedCreated + 1; // +1 car on vient d'incrémenter
                    if ($remainingAccepted === 1) {
                        // Dernier devis accepté : ajuster pour atteindre 58.000€ TTC
                        $remainingHT = $totalHTAccepted - $totalAcceptedHT;
                        $devisHT = max($devisHT * 0.7, $remainingHT); // Ajuster mais garder cohérence
                    } else {
                        // Ajuster pour approcher la moyenne
                        $averageHTAccepted = $totalHTAccepted / $acceptedCount;
                        $devisHT = ($devisHT * 0.3) + ($averageHTAccepted * 0.7); // Mélanger prix réaliste et objectif
                    }
                    // Recalculer prix unitaire si nécessaire
                    if (isset($workConfig['prix_fixe_base'])) {
                        $prixUnitaire = $devisHT / $surface;
                    }
                } else {
                    // Pour les autres statuts, ajuster pour atteindre 100.000€ total
                    $remainingAll = $totalDevis - $devisCreated;
                    if ($remainingAll > 0) {
                        $remainingHTAll = $totalHTAll - $totalGeneratedHT;
                        $averageRemainingHT = $remainingHTAll / $remainingAll;
                        // Mélanger prix réaliste et objectif (70% réaliste, 30% objectif)
                        $devisHT = ($devisHT * 0.7) + ($averageRemainingHT * 0.3);
                        $prixUnitaire = $devisHT / $surface;
                    }
                }

                // Créer le devis
                $devis = Devis::create([
                    'client_id' => $client->id,
                    'statut' => $statut,
                    'date_emission' => $dateEmission,
                    'date_validite' => $dateValidite,
                    'description_globale' => $workConfig['description'] . ' - ' . $cityName,
                    'superficie_totale' => $surface . ' m²',
                    'prix_final_estime' => $devisHT,
                    'taux_tva' => 20.00,
                    'acompte_pourcentage' => ($statut === 'Accepté' && rand(0, 100) < 60) ? rand(20, 40) : 0,
                ]);

                // Créer les lignes de devis
                // Une ligne principale pour le type de travail
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'ordre' => 1,
                    'description' => $workConfig['description'],
                    'quantite' => $surface,
                    'unite' => $workConfig['unite'],
                    'prix_unitaire' => round($prixUnitaire ?? ($devisHT / $surface), 2),
                ]);

                // Parfois ajouter des lignes supplémentaires (matériaux, main d'œuvre, etc.)
                if (rand(0, 100) < 40) {
                    $additionalLines = [
                        ['description' => 'Main d\'œuvre', 'unite' => 'heure', 'quantite' => rand(8, 20), 'prix' => rand(35, 55)],
                        ['description' => 'Matériaux et fournitures', 'unite' => 'lot', 'quantite' => 1, 'prix' => rand(500, 2000)],
                        ['description' => 'Échafaudage et sécurisation', 'unite' => 'jour', 'quantite' => rand(2, 5), 'prix' => rand(150, 300)],
                    ];
                    
                    $selectedLine = $additionalLines[array_rand($additionalLines)];
                    LigneDevis::create([
                        'devis_id' => $devis->id,
                        'ordre' => 2,
                        'description' => $selectedLine['description'],
                        'quantite' => $selectedLine['quantite'],
                        'unite' => $selectedLine['unite'],
                        'prix_unitaire' => $selectedLine['prix'],
                    ]);
                }

                // Recalculer les totaux
                $devis->recalculateTotals();
                $devis->save();

                $totalGeneratedHT += $devis->total_ht;
                if ($statut === 'Accepté') {
                    $totalAcceptedHT += $devis->total_ht;
                }
                $devisCreated++;
            }
        }

        // Calculer le CA des acceptés uniquement
        $caAccepted = Devis::where('statut', 'Accepté')->sum('total_ttc');
        
        $this->info("  ✅ {$devisCreated} devis créés");
        $this->info("     - {$acceptedCreated} Acceptés (CA: " . number_format($caAccepted, 2, ',', ' ') . " €)");
        $this->info("     - {$refusedCreated} Refusés");
        $this->info("     - {$pendingCreated} En Attente");
        $this->info("     - {$draftCreated} Brouillons");
        $this->info("  💰 CA total généré (tous devis) : " . number_format($totalGeneratedHT * 1.20, 2, ',', ' ') . " € TTC");
        
        // Générer des factures à partir des devis acceptés
        $this->info('');
        $this->info('📄 Génération de factures à partir des devis acceptés...');
        $this->generateFactures();
    }
    
    /**
     * Générer des factures à partir des devis acceptés
     */
    protected function generateFactures()
    {
        // Vérifier que la table existe
        if (!Schema::hasTable('factures')) {
            $this->warn('  ⚠️  La table factures n\'existe pas. Ignoré.');
            return;
        }
        
        // Récupérer tous les devis acceptés sans facture
        $devisAcceptes = Devis::where('statut', 'Accepté')
            ->whereDoesntHave('facture')
            ->get();
        
        if ($devisAcceptes->count() === 0) {
            $this->info("  ℹ️  Aucun devis accepté sans facture. Ignoré.");
            return;
        }
        
        // Répartition : ~60% payées, ~35% impayées, ~5% annulées
        $totalDevis = $devisAcceptes->count();
        $facturesPayees = max(1, (int)($totalDevis * 0.60));
        $facturesImpayees = max(1, (int)($totalDevis * 0.35));
        $facturesAnnulees = max(0, $totalDevis - $facturesPayees - $facturesImpayees);
        
        $facturesCreated = 0;
        $facturesPayeesCreated = 0;
        $facturesImpayeesCreated = 0;
        $facturesAnnuleesCreated = 0;
        
        // Mélanger les devis pour une distribution aléatoire
        $devisShuffled = $devisAcceptes->shuffle();
        
        foreach ($devisShuffled as $index => $devis) {
            // Déterminer le statut de la facture
            $statutFacture = 'Impayée';
            if ($facturesPayeesCreated < $facturesPayees) {
                $statutFacture = 'Payée';
                $facturesPayeesCreated++;
            } elseif ($facturesImpayeesCreated < $facturesImpayees) {
                $statutFacture = 'Impayée';
                $facturesImpayeesCreated++;
            } elseif ($facturesAnnuleesCreated < $facturesAnnulees) {
                $statutFacture = 'Annulée';
                $facturesAnnuleesCreated++;
            }
            
            // Date d'émission : utiliser la date d'émission du devis
            $dateEmission = $devis->date_emission ?? now();
            $dateEcheance = $dateEmission->copy()->addDays(30);
            
            // Date de paiement si payée (dans les 30 jours après émission)
            $datePaiement = null;
            $montantPaye = 0;
            if ($statutFacture === 'Payée') {
                $datePaiement = $dateEmission->copy()->addDays(rand(1, 30));
                $montantPaye = $devis->total_ttc;
            } elseif ($statutFacture === 'Partiellement payée') {
                $datePaiement = $dateEmission->copy()->addDays(rand(1, 20));
                $montantPaye = $devis->total_ttc * (rand(30, 80) / 100); // Paiement partiel 30-80%
            }
            
            try {
                $facture = Facture::create([
                    'devis_id' => $devis->id,
                    'client_id' => $devis->client_id,
                    'date_emission' => $dateEmission,
                    'date_echeance' => $dateEcheance,
                    'date_paiement' => $datePaiement,
                    'prix_total_ht' => $devis->total_ht,
                    'taux_tva' => $devis->taux_tva,
                    'prix_total_ttc' => $devis->total_ttc,
                    'montant_paye' => $montantPaye,
                    'statut' => $statutFacture,
                ]);
                
                $facturesCreated++;
            } catch (\Exception $e) {
                \Log::error('Erreur création facture pour devis', [
                    'devis_id' => $devis->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("  ✅ {$facturesCreated} factures créées");
        $this->info("     - {$facturesPayeesCreated} Payées");
        $this->info("     - {$facturesImpayeesCreated} Impayées");
        if ($facturesAnnuleesCreated > 0) {
            $this->info("     - {$facturesAnnuleesCreated} Annulées");
        }
        
        // Générer des factures historiques supplémentaires pour atteindre 515 678€ de CA total
        // Ces factures ne sont pas liées aux devis acceptés récents
        $this->generateFacturesHistoriques();
    }
    
    /**
     * Générer des factures historiques pour compléter le CA total
     */
    protected function generateFacturesHistoriques()
    {
        // Objectif : CA total = 515 678€
        // Calculer le CA actuel (factures payées + devis acceptés sans facture payée)
        $caActuel = Facture::where('statut', 'Payée')->sum('prix_total_ttc');
        $caDevisAcceptes = Devis::where('statut', 'Accepté')
            ->whereDoesntHave('facture', function($q) {
                $q->where('statut', 'Payée');
            })
            ->sum('total_ttc');
        $caTotalActuel = $caActuel + $caDevisAcceptes;
        
        $targetCATotal = 515678;
        $caManquant = $targetCATotal - $caTotalActuel;
        
        if ($caManquant <= 0) {
            $this->info("  ℹ️  CA total déjà atteint ou dépassé. Pas de factures historiques nécessaires.");
            return;
        }
        
        // Créer quelques clients supplémentaires pour les factures historiques
        $clientsHistoriques = [];
        for ($i = 0; $i < 5; $i++) {
            $city = $this->villesCoteDor[array_rand($this->villesCoteDor)];
            $cityName = array_search($city, $this->villesCoteDor);
            $prenoms = ['Jean', 'Marie', 'Pierre', 'Sophie', 'Michel', 'Catherine'];
            $noms = ['Dubois', 'Martin', 'Bernard', 'Thomas', 'Petit', 'Robert'];
            
            $client = Client::create([
                'nom' => $noms[array_rand($noms)],
                'prenom' => $prenoms[array_rand($prenoms)],
                'email' => strtolower(str_replace(' ', '.', $prenoms[array_rand($prenoms)])) . '.' . strtolower($noms[array_rand($noms)]) . rand(1000, 9999) . '@example.fr',
                'telephone' => '0' . rand(6, 7) . rand(10000000, 99999999),
                'adresse' => rand(1, 99) . ' Rue ' . $cityName,
                'code_postal' => '21' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT),
                'ville' => $cityName,
                'pays' => 'France',
            ]);
            $clientsHistoriques[] = $client;
        }
        
        // Créer des factures historiques (dates passées)
        $facturesHistoriquesCreated = 0;
        $montantRestant = $caManquant;
        $nombreFactures = max(3, min(10, (int)($caManquant / 15000))); // Entre 3 et 10 factures
        
        for ($i = 0; $i < $nombreFactures && $montantRestant > 1000; $i++) {
            $client = $clientsHistoriques[array_rand($clientsHistoriques)];
            
            // Montant entre 5000 et min(30000, montant restant)
            $montantFacture = $i === $nombreFactures - 1 
                ? $montantRestant // Dernière facture : prendre tout le reste
                : rand(5000, min(30000, (int)($montantRestant * 0.8)));
            
            $montantHT = $montantFacture / 1.20; // TVA 20%
            
            // Date aléatoire entre 6 mois et 2 ans dans le passé
            $dateEmission = Carbon::now()->subMonths(rand(6, 24))->subDays(rand(0, 30));
            $dateEcheance = $dateEmission->copy()->addDays(30);
            $datePaiement = $dateEmission->copy()->addDays(rand(1, 30));
            
            try {
                $facture = Facture::create([
                    'devis_id' => null, // Facture historique sans devis
                    'client_id' => $client->id,
                    'date_emission' => $dateEmission,
                    'date_echeance' => $dateEcheance,
                    'date_paiement' => $datePaiement,
                    'prix_total_ht' => $montantHT,
                    'taux_tva' => 20.00,
                    'prix_total_ttc' => $montantFacture,
                    'montant_paye' => $montantFacture,
                    'statut' => 'Payée',
                ]);
                
                $montantRestant -= $montantFacture;
                $facturesHistoriquesCreated++;
            } catch (\Exception $e) {
                \Log::error('Erreur création facture historique', [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        if ($facturesHistoriquesCreated > 0) {
            $this->info("  ✅ {$facturesHistoriquesCreated} factures historiques créées (CA supplémentaire: " . number_format($caManquant - $montantRestant, 2, ',', ' ') . " €)");
        }
    }

    /**
     * Générer les soumissions de test
     */
    protected function generateSubmissions()
    {
        // Vérifier que la table existe
        if (!Schema::hasTable('submissions')) {
            $this->warn('  ⚠️  La table submissions n\'existe pas. Ignoré.');
            return;
        }

        // Supprimer les soumissions existantes si --force
        if ($this->option('force')) {
            $submissionCount = Submission::count();
            Submission::query()->delete();
            $this->info("  ✅ {$submissionCount} soumissions existantes supprimées");
        }

        // Répartition : 9 Chevigny, puis Dijon, Beaune, Quetigny, Chenôve
        $repartition = [
            'Chevigny-Saint-Sauveur' => 9,
            'Dijon' => 3,
            'Beaune' => 2,
            'Quetigny' => 2,
            'Chenôve' => 1,
        ];

        // Noms et prénoms français
        $prenomsHommes = ['Jean', 'Pierre', 'Michel', 'Philippe', 'Alain', 'Patrick', 'Bernard', 'Christian', 'Daniel', 'Laurent'];
        $prenomsFemmes = ['Marie', 'Sophie', 'Catherine', 'Isabelle', 'Martine', 'Françoise', 'Monique', 'Sylvie', 'Nathalie', 'Patricia'];
        $noms = ['Dubois', 'Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Leroy', 'Moreau', 'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'David', 'Bertrand', 'Roux', 'Vincent', 'Fournier'];

        // Types de travaux possibles
        $workTypesOptions = [
            ['toiture'],
            ['toiture', 'isolation'],
            ['facade'],
            ['toiture', 'facade'],
            ['isolation'],
            ['toiture', 'facade', 'isolation'],
        ];

        $roofWorkTypesOptions = [
            ['renovation'],
            ['renovation', 'reparation'],
            ['demoussage'],
            ['renovation', 'demoussage'],
            ['reparation'],
        ];

        $facadeWorkTypesOptions = [
            ['ravalement'],
            ['peinture'],
            ['isolation'],
            ['ravalement', 'peinture'],
        ];

        $isolationWorkTypesOptions = [
            ['combles'],
            ['murs'],
            ['combles', 'murs'],
            ['toiture'],
        ];

        // Dates pour novembre 2025 : du 1er au 29 novembre répartis, et 1 le 30 novembre à 8h21
        $startDate = Carbon::create(2025, 11, 1, 0, 0, 0);
        $endDate = Carbon::create(2025, 11, 29, 23, 59, 59);
        $lastDate = Carbon::create(2025, 11, 30, 8, 21, 0); // 30 novembre à 8h21

        $submissionsCreated = 0;
        $totalCount = array_sum($repartition); // 17 au total
        $countForLastDate = 1; // 1 soumission le 30 novembre à 8h21
        $countForNormalDates = $totalCount - $countForLastDate; // 16 soumissions du 1er au 29 novembre

        $normalCountCreated = 0;
        $lastDateCreated = false;

        foreach ($repartition as $cityName => $count) {
            $cityData = $this->villesCoteDor[$cityName] ?? ['country' => 'France', 'country_code' => 'FR'];

            for ($i = 0; $i < $count; $i++) {
                // Générer un nom et prénom
                $isFemme = rand(0, 100) < 50;
                $prenom = $isFemme ? $prenomsFemmes[array_rand($prenomsFemmes)] : $prenomsHommes[array_rand($prenomsHommes)];
                $nom = $noms[array_rand($noms)];

                // Déterminer la date : soit du 1er au 29 novembre, soit le 30 novembre à 8h21
                if (!$lastDateCreated && $normalCountCreated >= $countForNormalDates) {
                    // C'est la dernière soumission, elle doit être le 30 novembre à 8h21
                    $createdAt = $lastDate->copy();
                    $lastDateCreated = true;
                } else {
                    // Date aléatoire du 1er au 29 novembre
                    $randomTimestamp = rand($startDate->timestamp, $endDate->timestamp);
                    $createdAt = Carbon::createFromTimestamp($randomTimestamp);
                    $normalCountCreated++;
                }

                // Statut : majoritairement complétées (70%), quelques en cours ou abandonnées
                $statusRand = rand(0, 100);
                if ($statusRand < 70) {
                    $status = 'COMPLETED';
                    $completedAt = $createdAt->copy()->addHours(rand(1, 48));
                } elseif ($statusRand < 85) {
                    $status = 'IN_PROGRESS';
                    $completedAt = null;
                } else {
                    $status = 'ABANDONED';
                    $completedAt = null;
                }

                // Work types aléatoires
                $workTypes = $workTypesOptions[array_rand($workTypesOptions)];
                $roofWorkTypes = in_array('toiture', $workTypes) ? $roofWorkTypesOptions[array_rand($roofWorkTypesOptions)] : null;
                $facadeWorkTypes = in_array('facade', $workTypes) ? $facadeWorkTypesOptions[array_rand($facadeWorkTypesOptions)] : null;
                $isolationWorkTypes = in_array('isolation', $workTypes) ? $isolationWorkTypesOptions[array_rand($isolationWorkTypesOptions)] : null;

                // Données du formulaire
                $propertyType = rand(0, 100) < 70 ? 'HOUSE' : 'APARTMENT';
                $surface = rand(50, 350);
                $ownershipStatus = rand(0, 100) < 80 ? 'OWNER' : 'TENANT';
                $gender = $isFemme ? 'MADAME' : 'MONSIEUR';
                $postalCode = '21' . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

                // Email unique
                $email = strtolower($prenom) . '.' . strtolower($nom) . rand(1, 999) . '@example.fr';
                
                // Phone
                $phone = '0' . rand(6, 7) . rand(10000000, 99999999);

                // IP aléatoire
                $ipPrefixes = ['192.168.', '10.0.', '172.16.'];
                $ipPrefix = $ipPrefixes[array_rand($ipPrefixes)];
                $ipAddress = $ipPrefix . rand(1, 255) . '.' . rand(1, 255);

                // User agent aléatoire
                $userAgent = $this->userAgents[array_rand($this->userAgents)];

                // Referrer (parfois vide)
                $referrerUrl = null;
                if (rand(0, 100) < 70) {
                    $referrers = [
                        'https://www.google.com/search?q=couvreur+' . strtolower(str_replace(' ', '+', $cityName)),
                        'https://www.google.fr/search?q=rénovation+toiture+' . strtolower(str_replace(' ', '+', $cityName)),
                        'https://maps.google.com/maps?q=couvreur+' . strtolower(str_replace(' ', '+', $cityName)),
                    ];
                    $referrerUrl = $referrers[array_rand($referrers)];
                }

                // Session ID
                $sessionId = str()->random(40);
                $userIdentifier = str()->random(32);

                // Current step (selon le statut)
                $currentStep = $status === 'COMPLETED' ? 'email' : ($status === 'ABANDONED' ? ['propertyType', 'surface', 'workType', 'personalInfo'][rand(0, 3)] : 'phone');

                // Tracking data
                $trackingData = [
                    'source' => $referrerUrl ? 'google_search' : 'direct',
                    'device' => $this->detectDeviceFromUA($userAgent),
                    'timestamp' => $createdAt->toDateTimeString(),
                ];

                // Form data
                $formData = [
                    'property_type' => $propertyType,
                    'surface' => $surface,
                    'work_types' => $workTypes,
                    'ownership_status' => $ownershipStatus,
                    'gender' => $gender,
                    'first_name' => $prenom,
                    'last_name' => $nom,
                    'postal_code' => $postalCode,
                    'city' => $cityName,
                ];

                // Créer la soumission en désactivant temporairement les timestamps automatiques
                // pour pouvoir définir manuellement created_at et updated_at
                $submission = new Submission([
                    'session_id' => $sessionId,
                    'user_identifier' => $userIdentifier,
                    'property_type' => $propertyType,
                    'surface' => $surface,
                    'work_types' => $workTypes,
                    'roof_work_types' => $roofWorkTypes,
                    'facade_work_types' => $facadeWorkTypes,
                    'isolation_work_types' => $isolationWorkTypes,
                    'ownership_status' => $ownershipStatus,
                    'gender' => $gender,
                    'first_name' => $prenom,
                    'last_name' => $nom,
                    'postal_code' => $postalCode,
                    'phone' => $phone,
                    'email' => $email,
                    'status' => $status,
                    'current_step' => $currentStep,
                    'form_data' => $formData,
                    'completed_at' => $completedAt,
                    'abandoned_at' => $status === 'ABANDONED' ? $createdAt->copy()->addHours(rand(1, 6)) : null,
                    'ip_address' => $ipAddress,
                    'city' => $cityName,
                    'country' => $cityData['country'],
                    'country_code' => $cityData['country_code'],
                    'referrer_url' => $referrerUrl,
                    'user_agent' => $userAgent,
                    'recaptcha_score' => rand(850, 999) / 100, // Score entre 0.85 et 0.99
                    'tracking_data' => $trackingData,
                ]);
                
                // Définir manuellement les timestamps
                $submission->created_at = $createdAt;
                $submission->updated_at = $status === 'COMPLETED' && $completedAt ? $completedAt : $createdAt;
                
                // Sauvegarder
                $submission->save();

                $submissionsCreated++;
            }
        }

        $this->info("  ✅ {$submissionsCreated} soumissions créées");
        $this->info("     - 9 Chevigny-Saint-Sauveur");
        $this->info("     - 3 Dijon");
        $this->info("     - 2 Beaune");
        $this->info("     - 2 Quetigny");
        $this->info("     - 1 Chenôve");
    }
}
