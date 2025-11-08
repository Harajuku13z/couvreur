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
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=status,message,city,regionName,country,countryCode,lat,lon");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success') {
                        $city = $data['city'] ?? null;
                        $region = $data['regionName'] ?? '';
                        $lat = $data['lat'] ?? null;
                        $lon = $data['lon'] ?? null;
                        
                        // Correction pour la Bourgogne-Franche-Comté
                        // Si la ville est "Paris" mais qu'on est en Bourgogne, vérifier la région ou les coordonnées
                        if ($city === 'Paris') {
                            // Vérifier la région
                            if (!empty($region) && (stripos($region, 'Bourgogne') !== false || 
                                stripos($region, 'Franche-Comté') !== false || 
                                stripos($region, 'Dijon') !== false)) {
                                $city = 'Dijon';
                                Log::info("Correction géolocalisation: Paris -> Dijon (région: {$region})");
                            }
                            // Vérifier les coordonnées (Dijon: ~47.32°N, 5.04°E)
                            elseif ($lat !== null && $lon !== null) {
                                // Si les coordonnées sont proches de Dijon (environ 47.3°N, 5.0°E)
                                if ($lat >= 47.0 && $lat <= 47.5 && $lon >= 4.5 && $lon <= 5.5) {
                                    $city = 'Dijon';
                                    Log::info("Correction géolocalisation: Paris -> Dijon (coordonnées: {$lat}, {$lon})");
                                }
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

