<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PhoneCall;
use App\Models\Visit;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class GenerateTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Générer des données de test : 57 appels téléphoniques et 1980 visites';

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

        // Générer les appels téléphoniques
        $this->info('📞 Génération de 57 appels téléphoniques...');
        $this->generatePhoneCalls();

        // Générer les visites
        $this->info('👁️ Génération de 1980 visites...');
        $this->generateVisits();

        $this->info('✅ Génération terminée avec succès !');
        return 0;
    }

    /**
     * Générer les appels téléphoniques
     */
    protected function generatePhoneCalls()
    {
        $phoneNumber = \App\Models\Setting::get('company_phone_raw', '0633532123');
        
        // Dates : du 28 octobre 2025 au 30 novembre 2025
        $startDate = Carbon::create(2025, 10, 28, 0, 0, 0);
        $endDate = Carbon::create(2025, 11, 30, 23, 59, 59);
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
            // Date aléatoire dans la période
            $randomDays = rand(0, $startDate->diffInDays($endDate));
            $randomHours = rand(8, 20); // Entre 8h et 20h
            $randomMinutes = rand(0, 59);
            $randomSeconds = rand(0, 59);
            
            $clickedAt = $startDate->copy()
                ->addDays($randomDays)
                ->setTime($randomHours, $randomMinutes, $randomSeconds);
            
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
        
        // Dates : du 28 octobre 2025 au 30 novembre 2025
        $startDate = Carbon::create(2025, 10, 28, 0, 0, 0);
        $endDate = Carbon::create(2025, 11, 30, 23, 59, 59);
        
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
            // Date aléatoire dans la période
            $randomDays = rand(0, $startDate->diffInDays($endDate));
            $randomHours = rand(0, 23);
            $randomMinutes = rand(0, 59);
            $randomSeconds = rand(0, 59);
            
            $visitedAt = $startDate->copy()
                ->addDays($randomDays)
                ->setTime($randomHours, $randomMinutes, $randomSeconds);
            
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
}
