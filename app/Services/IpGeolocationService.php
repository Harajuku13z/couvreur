<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IpGeolocationService
{
    /**
     * Obtenir la géolocalisation depuis une adresse IP
     */
    public function getLocationFromIp(string $ip): array
    {
        // Ignorer les IPs locales
        if ($this->isLocalIp($ip)) {
            return [
                'city' => null,
                'country' => 'France',
                'country_code' => 'FR',
            ];
        }

        // Utiliser le cache pour éviter trop de requêtes
        $cacheKey = 'ip_geo_' . md5($ip);
        
        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            $results = [];
            
            // Essayer plusieurs services et comparer les résultats
            // 1. ip-api.com (gratuit, 45 requêtes/min, souvent plus précis pour la France)
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=status,message,city,regionName,country,countryCode");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success') {
                        $city = $data['city'] ?? null;
                        // Si la ville est "Paris" mais qu'on est en Bourgogne, vérifier la région
                        if ($city === 'Paris' && isset($data['regionName'])) {
                            $region = $data['regionName'] ?? '';
                            // Si la région indique Bourgogne, utiliser la région comme ville
                            if (stripos($region, 'Bourgogne') !== false || stripos($region, 'Dijon') !== false) {
                                $city = 'Dijon'; // Ou utiliser la région
                                Log::info("Correction géolocalisation: Paris -> Dijon (région: {$region})");
                            }
                        }
                        
                        $results['ip-api'] = [
                            'city' => $city,
                            'region' => $data['regionName'] ?? null,
                            'country' => $data['country'] ?? null,
                            'country_code' => $data['countryCode'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erreur géolocalisation IP (ip-api.com): ' . $e->getMessage());
            }

            // 2. ipapi.co (gratuit, 1000 requêtes/jour)
            try {
                $response = Http::timeout(5)->get("https://ipapi.co/{$ip}/json/");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (!isset($data['error'])) {
                        $city = $data['city'] ?? null;
                        // Vérifier aussi avec ipapi.co
                        if ($city === 'Paris' && isset($data['region'])) {
                            $region = $data['region'] ?? '';
                            if (stripos($region, 'Bourgogne') !== false || stripos($region, 'Dijon') !== false) {
                                $city = 'Dijon';
                                Log::info("Correction géolocalisation ipapi.co: Paris -> Dijon (région: {$region})");
                            }
                        }
                        
                        $results['ipapi'] = [
                            'city' => $city,
                            'region' => $data['region'] ?? null,
                            'country' => $data['country_name'] ?? null,
                            'country_code' => $data['country_code'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erreur géolocalisation IP (ipapi.co): ' . $e->getMessage());
            }

            // 3. ipgeolocation.io (plus précis, mais nécessite une clé API)
            // On peut l'ajouter plus tard si nécessaire

            // Choisir le meilleur résultat
            // Priorité: ip-api.com (souvent plus précis pour la France)
            if (isset($results['ip-api']) && !empty($results['ip-api']['city'])) {
                $result = $results['ip-api'];
                Log::info("Géolocalisation IP utilisée: ip-api.com", [
                    'ip' => $ip,
                    'city' => $result['city'],
                    'region' => $result['region'] ?? null
                ]);
                return [
                    'city' => $result['city'],
                    'country' => $result['country'],
                    'country_code' => $result['country_code'],
                ];
            }

            // Fallback: ipapi.co
            if (isset($results['ipapi']) && !empty($results['ipapi']['city'])) {
                $result = $results['ipapi'];
                Log::info("Géolocalisation IP utilisée: ipapi.co", [
                    'ip' => $ip,
                    'city' => $result['city'],
                    'region' => $result['region'] ?? null
                ]);
                return [
                    'city' => $result['city'],
                    'country' => $result['country'],
                    'country_code' => $result['country_code'],
                ];
            }

            // Fallback par défaut
            Log::warning("Impossible de géolocaliser l'IP: {$ip}");
            return [
                'city' => null,
                'country' => null,
                'country_code' => null,
            ];
        });
    }

    /**
     * Vérifier si l'IP est locale
     */
    private function isLocalIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}

