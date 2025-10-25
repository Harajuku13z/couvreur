<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class GoogleMyBusinessHelper
{
    private $clientId;
    private $clientSecret;
    private $redirectUri;

    public function __construct()
    {
        $this->clientId = env('GOOGLE_CLIENT_ID', '');
        $this->clientSecret = env('GOOGLE_CLIENT_SECRET', '');
        $this->redirectUri = env('GOOGLE_REDIRECT_URI', 'https://jd-renovation-service.fr/admin/reviews/google/oauth/callback');
    }

    /**
     * Générer l'URL d'autorisation OAuth2
     */
    public function getAuthUrl()
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'https://www.googleapis.com/auth/business.manage',
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }

    /**
     * Échanger le code d'autorisation contre un access token
     */
    public function exchangeCodeForToken($code)
    {
        try {
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur OAuth2: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer les comptes Google My Business
     */
    public function getAccounts($accessToken)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken
            ])->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur récupération comptes: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer les établissements d'un compte
     */
    public function getLocations($accessToken, $accountId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken
            ])->get("https://mybusinessbusinessinformation.googleapis.com/v1/accounts/{$accountId}/locations");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur récupération établissements: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer les avis d'un établissement
     */
    public function getReviews($accessToken, $accountId, $locationId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken
            ])->get("https://mybusiness.googleapis.com/v4/accounts/{$accountId}/locations/{$locationId}/reviews");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur récupération avis: ' . $e->getMessage());
            return null;
        }
    }
}