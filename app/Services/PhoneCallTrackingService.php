<?php

namespace App\Services;

use App\Models\PhoneCall;
use App\Models\Submission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class PhoneCallTrackingService
{
    protected $ipGeolocationService;

    public function __construct()
    {
        $this->ipGeolocationService = new IpGeolocationService();
    }

    /**
     * Tracker un appel téléphonique avec déduplication
     */
    public function track(Request $request, $phoneNumber = null, $sourcePage = null, $referrerUrl = null)
    {
        try {
            $sessionId = Session::getId();
            $submission = Submission::where('session_id', $sessionId)->first();

            // Capturer l'IP
            $ipAddress = $this->getClientIp($request);
            
            // Récupérer les données depuis la requête
            $phoneNumber = $phoneNumber 
                        ?? $request->input('phone_number') 
                        ?? $request->query('phone_number')
                        ?? $this->getDefaultPhoneNumber();
            
            $sourcePage = $sourcePage 
                        ?? $request->input('source_page') 
                        ?? $request->query('source_page')
                        ?? $this->extractSourcePage($request, $referrerUrl);
            
            $referrerUrl = $referrerUrl 
                        ?? $request->input('referrer_url') 
                        ?? $request->query('referrer_url')
                        ?? $request->header('referer')
                        ?? null;
            
            // Si les données viennent de sendBeacon (FormData), parser le JSON
            if ($request->has('data')) {
                $data = json_decode($request->input('data'), true);
                if (is_array($data)) {
                    $phoneNumber = $data['phone_number'] ?? $phoneNumber;
                    $sourcePage = $data['source_page'] ?? $sourcePage;
                    $referrerUrl = $data['referrer_url'] ?? $referrerUrl;
                }
            }
            
            // Nettoyer le numéro
            $phoneNumber = $this->cleanPhoneNumber($phoneNumber);
            
            // DÉDUPLICATION : Vérifier si on a déjà tracké cet appel dans les 5 dernières secondes
            // (même session, même numéro, même page)
            $recentCall = PhoneCall::where('session_id', $sessionId)
                ->where('phone_number', $phoneNumber)
                ->where('source_page', $sourcePage)
                ->where('clicked_at', '>=', now()->subSeconds(5))
                ->first();
            
            if ($recentCall) {
                Log::debug('⚠️ Appel déjà tracké récemment (déduplication)', [
                    'existing_id' => $recentCall->id,
                    'phone' => $phoneNumber,
                    'source_page' => $sourcePage,
                    'time_diff' => now()->diffInSeconds($recentCall->clicked_at)
                ]);
                
                // Retourner le même ID pour éviter les doublons
                return [
                    'success' => true,
                    'id' => $recentCall->id,
                    'duplicate' => true
                ];
            }
            
            // Géolocalisation
            $location = $this->ipGeolocationService->getLocationFromIp($ipAddress);
            
            // Créer l'enregistrement
            $phoneCall = PhoneCall::create([
                'submission_id' => $submission ? $submission->id : null,
                'session_id' => $sessionId,
                'phone_number' => $phoneNumber,
                'source_page' => $sourcePage,
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'city' => $location['city'],
                'country' => $location['country'],
                'country_code' => $location['country_code'],
                'referrer_url' => $referrerUrl,
                'clicked_at' => now(),
            ]);

            Log::info('✅ Appel téléphonique tracké', [
                'id' => $phoneCall->id,
                'phone' => $phoneNumber,
                'source_page' => $sourcePage,
                'ip' => $ipAddress,
                'city' => $location['city'],
                'country' => $location['country']
            ]);
            
            // Envoyer l'événement à Google Analytics via JavaScript (sera exécuté côté client)
            // Le tracking Analytics sera fait via le JavaScript dans le frontend

            return [
                'success' => true,
                'id' => $phoneCall->id,
                'phone_call' => $phoneCall,
                'analytics_event' => [
                    'event_name' => 'phone_call',
                    'phone_number' => $phoneNumber,
                    'source_page' => $sourcePage,
                    'city' => $location['city'],
                    'country' => $location['country']
                ]
            ];
        } catch (\Exception $e) {
            Log::error('❌ Erreur tracking appel téléphonique: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'request_method' => $request->method()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Nettoyer le numéro de téléphone
     */
    protected function cleanPhoneNumber($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return $this->getDefaultPhoneNumber();
        }
        
        // Nettoyer (supprimer les caractères non numériques sauf +)
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Si le numéro commence par +33, le convertir en format local pour l'affichage
        if (strpos($cleaned, '+33') === 0) {
            return '0' . substr($cleaned, 3);
        }
        
        return $cleaned ?: $this->getDefaultPhoneNumber();
    }

    /**
     * Extraire la page source
     */
    protected function extractSourcePage(Request $request, $referrerUrl = null)
    {
        $referrerUrl = $referrerUrl ?? $request->header('referer');
        
        if ($referrerUrl) {
            $path = parse_url($referrerUrl, PHP_URL_PATH);
            $path = ltrim($path, '/');
            if (!empty($path)) {
                return $path;
            }
        }
        
        $currentPath = parse_url($request->url(), PHP_URL_PATH);
        $currentPath = ltrim($currentPath, '/');
        
        return $currentPath ?: 'home';
    }

    /**
     * Obtenir le numéro de téléphone par défaut
     */
    protected function getDefaultPhoneNumber()
    {
        return \App\Models\Setting::get('company_phone_raw') 
            ?? \App\Models\Setting::get('company_phone')
            ?? '';
    }

    /**
     * Obtenir l'IP du client
     */
    protected function getClientIp(Request $request)
    {
        // Vérifier les headers de proxy
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_X_FORWARDED_FOR',      // Proxy standard
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $request->server($header);
            if (!empty($ip)) {
                // X-Forwarded-For peut contenir plusieurs IPs
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }
}

