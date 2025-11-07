<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class VisitTrackingService
{
    protected $ipGeolocationService;

    public function __construct()
    {
        $this->ipGeolocationService = new IpGeolocationService();
    }

    /**
     * Tracker une visite
     */
    public function track(Request $request)
    {
        try {
            // Vérifier si la table visits existe
            if (!\Schema::hasTable('visits')) {
                // Table n'existe pas encore, on ignore silencieusement
                return null;
            }
            
            // Ignorer les requêtes admin et certaines routes
            $path = $request->path();
            if ($this->shouldIgnore($path)) {
                return null;
            }

            // Détecter si c'est un bot
            $userAgent = $request->userAgent();
            $isBot = $this->isBot($userAgent);
            
            // Si c'est un bot, on peut quand même tracker mais avec un flag
            // ou on peut les ignorer complètement selon les besoins
            
            $sessionId = Session::getId();
            $ipAddress = $this->getClientIp($request);
            
            // Géolocalisation
            $location = $this->ipGeolocationService->getLocationFromIp($ipAddress);
            
            // Détecter le device et le navigateur
            $deviceInfo = $this->detectDevice($userAgent);
            
            // Créer l'enregistrement
            $visit = Visit::create([
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'url' => $request->fullUrl(),
                'path' => $path,
                'method' => $request->method(),
                'referrer_url' => $request->header('referer'),
                'city' => $location['city'],
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'os' => $deviceInfo['os'],
                'is_bot' => $isBot,
                'visited_at' => now(),
            ]);

            // Log uniquement pour debug (peut être désactivé en production)
            if (config('app.debug')) {
                Log::debug('✅ Visite trackée', [
                    'id' => $visit->id,
                    'path' => $path,
                    'ip' => $ipAddress,
                    'city' => $location['city']
                ]);
            }

            return $visit;
        } catch (\Illuminate\Database\QueryException $e) {
            // Erreur de base de données (table n'existe pas, etc.)
            // Ne pas bloquer la requête en cas d'erreur de tracking
            if (config('app.debug')) {
                Log::warning('⚠️ Erreur tracking visite (table peut-être absente): ' . $e->getMessage());
            }
            return null;
        } catch (\Exception $e) {
            // Ne pas bloquer la requête en cas d'erreur de tracking
            if (config('app.debug')) {
                Log::error('❌ Erreur tracking visite: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Vérifier si on doit ignorer cette route
     */
    protected function shouldIgnore($path)
    {
        $ignoredPaths = [
            'admin',
            'api',
            'config',
            '_debugbar',
            'storage',
            'favicon.ico',
            'robots.txt',
            'sitemap.xml',
            'manifest.json',
        ];

        foreach ($ignoredPaths as $ignored) {
            if (strpos($path, $ignored) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecter si c'est un bot
     */
    protected function isBot($userAgent)
    {
        if (empty($userAgent)) {
            return true;
        }

        $bots = [
            'googlebot',
            'bingbot',
            'slurp',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'sogou',
            'exabot',
            'facebot',
            'ia_archiver',
            'curl',
            'wget',
            'python',
            'php',
            'scrapy',
        ];

        $userAgentLower = strtolower($userAgent);
        foreach ($bots as $bot) {
            if (strpos($userAgentLower, $bot) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecter le device et le navigateur
     */
    protected function detectDevice($userAgent)
    {
        $deviceType = 'desktop';
        $browser = 'unknown';
        $os = 'unknown';

        if (empty($userAgent)) {
            return [
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os
            ];
        }

        $ua = strtolower($userAgent);

        // Détecter le device
        if (preg_match('/mobile|android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i', $ua)) {
            if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
                $deviceType = 'tablet';
            } else {
                $deviceType = 'mobile';
            }
        }

        // Détecter le navigateur
        if (strpos($ua, 'chrome') !== false && strpos($ua, 'edg') === false) {
            $browser = 'Chrome';
        } elseif (strpos($ua, 'firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) {
            $browser = 'Safari';
        } elseif (strpos($ua, 'edg') !== false) {
            $browser = 'Edge';
        } elseif (strpos($ua, 'opera') !== false || strpos($ua, 'opr') !== false) {
            $browser = 'Opera';
        }

        // Détecter l'OS
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
            'os' => $os
        ];
    }

    /**
     * Obtenir l'IP du client
     */
    protected function getClientIp(Request $request)
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $request->server($header);
            if (!empty($ip)) {
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }
}

