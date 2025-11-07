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
            try {
                // Essayer ipapi.co (gratuit, 1000 requêtes/jour)
                $response = Http::timeout(5)->get("https://ipapi.co/{$ip}/json/");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if (!isset($data['error'])) {
                        return [
                            'city' => $data['city'] ?? null,
                            'country' => $data['country_name'] ?? null,
                            'country_code' => $data['country_code'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erreur géolocalisation IP (ipapi.co): ' . $e->getMessage());
            }

            // Fallback: ip-api.com (gratuit, 45 requêtes/min)
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=status,message,city,country,countryCode");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    if ($data['status'] === 'success') {
                        return [
                            'city' => $data['city'] ?? null,
                            'country' => $data['country'] ?? null,
                            'country_code' => $data['countryCode'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Erreur géolocalisation IP (ip-api.com): ' . $e->getMessage());
            }

            // Fallback par défaut
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

