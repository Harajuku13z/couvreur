<?php

namespace App\Services;

use Google_Client;
use Google_Service_SearchConsole;
use Google_Service_SearchConsole_UrlNotification;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class GoogleSearchConsoleService
{
    protected $client;
    protected $service;
    protected $siteUrl;

    public function __construct()
    {
        $this->siteUrl = $this->getSiteUrl();
        $this->initializeClient();
    }

    /**
     * Initialiser le client Google API
     */
    protected function initializeClient()
    {
        try {
            $credentials = $this->getCredentials();
            
            if (empty($credentials)) {
                Log::warning('Google Search Console: Aucune clé API configurée');
                return false;
            }

            $this->client = new Google_Client();
            $this->client->setAuthConfig($credentials);
            $this->client->addScope(Google_Service_SearchConsole::WEBMASTERS);
            $this->client->setAccessType('offline');
            
            $this->service = new Google_Service_SearchConsole($this->client);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur initialisation Google Search Console: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les credentials depuis les settings
     */
    protected function getCredentials()
    {
        $credentialsJson = Setting::get('google_search_console_credentials', '');
        
        if (empty($credentialsJson)) {
            return null;
        }

        try {
            $credentials = is_string($credentialsJson) ? json_decode($credentialsJson, true) : $credentialsJson;
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erreur parsing JSON credentials Google Search Console');
                return null;
            }

            return $credentials;
        } catch (\Exception $e) {
            Log::error('Erreur récupération credentials: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer l'URL du site
     */
    protected function getSiteUrl()
    {
        // Essayer de récupérer depuis les settings
        $siteUrl = Setting::get('site_url', '');
        
        if (empty($siteUrl)) {
            // Fallback: utiliser l'URL de la requête actuelle
            $siteUrl = request()->getSchemeAndHttpHost();
        }

        // S'assurer que l'URL se termine par /
        if (!str_ends_with($siteUrl, '/')) {
            $siteUrl .= '/';
        }

        return $siteUrl;
    }

    /**
     * Indexer une URL via l'API
     */
    public function indexUrl($url)
    {
        try {
            if (!$this->service) {
                return [
                    'success' => false,
                    'message' => 'Service Google Search Console non initialisé'
                ];
            }

            // S'assurer que l'URL est complète
            if (!str_starts_with($url, 'http')) {
                $url = rtrim($this->siteUrl, '/') . '/' . ltrim($url, '/');
            }

            $notification = new Google_Service_SearchConsole_UrlNotification();
            $notification->setUrl($url);
            $notification->setType('URL_UPDATED');

            $request = new \Google_Service_SearchConsole_UrlNotification();
            $request->setUrl($url);
            $request->setType('URL_UPDATED');

            $this->service->urlNotifications->publish($this->siteUrl, $request);

            Log::info("URL indexée avec succès: {$url}");

            return [
                'success' => true,
                'message' => "URL indexée avec succès: {$url}"
            ];
        } catch (\Exception $e) {
            Log::error("Erreur indexation URL {$url}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Indexer plusieurs URLs en batch
     */
    public function indexUrls(array $urls, $batchSize = 100)
    {
        $results = [];
        $batches = array_chunk($urls, $batchSize);

        foreach ($batches as $batchIndex => $batch) {
            Log::info("Traitement du batch " . ($batchIndex + 1) . " / " . count($batches) . " (" . count($batch) . " URLs)");
            
            foreach ($batch as $url) {
                $result = $this->indexUrl($url);
                $results[] = [
                    'url' => $url,
                    'result' => $result
                ];
                
                // Petite pause pour éviter les limites de rate
                usleep(100000); // 0.1 seconde
            }
            
            // Pause plus longue entre les batches
            if ($batchIndex < count($batches) - 1) {
                sleep(1);
            }
        }

        $successCount = count(array_filter($results, function($r) {
            return $r['result']['success'] ?? false;
        }));

        return [
            'total' => count($urls),
            'success' => $successCount,
            'failed' => count($urls) - $successCount,
            'results' => $results
        ];
    }

    /**
     * Vérifier si le service est configuré
     */
    public function isConfigured()
    {
        $credentials = $this->getCredentials();
        return !empty($credentials) && $this->service !== null;
    }

    /**
     * Tester la connexion
     */
    public function testConnection()
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Service non configuré'
                ];
            }

            // Essayer de récupérer les sites
            $sites = $this->service->sites->listSites();
            
            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'sites' => count($sites->getSiteEntry())
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }
}

