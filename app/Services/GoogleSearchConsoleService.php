<?php

namespace App\Services;

use Google_Client;
use Google\Service\Indexing;
use Google\Service\Indexing\UrlNotification;
use Google\Service\SearchConsole;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class GoogleSearchConsoleService
{
    protected $client;
    protected $service;
    protected $indexingService;
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
            $this->client->addScope(SearchConsole::WEBMASTERS);
            $this->client->addScope('https://www.googleapis.com/auth/indexing');
            $this->client->setAccessType('offline');
            
            $this->service = new SearchConsole($this->client);
            $this->indexingService = new Indexing($this->client);
            
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
        $siteUrl = Setting::get('site_url', null);
        
        if (empty($siteUrl)) {
            // Utiliser APP_URL depuis la config Laravel
            $siteUrl = config('app.url', url('/'));
        }
        
        // S'assurer que l'URL a un protocole (https:// ou http://)
        if (!preg_match('/^https?:\/\//', $siteUrl)) {
            // Si pas de protocole, ajouter https://
            $siteUrl = 'https://' . $siteUrl;
        }

        // S'assurer que l'URL se termine par /
        if (!str_ends_with($siteUrl, '/')) {
            $siteUrl .= '/';
        }

        return $siteUrl;
    }

    /**
     * Indexer une URL via l'API Indexing
     */
    public function indexUrl($url)
    {
        try {
            if (!$this->indexingService) {
                return [
                    'success' => false,
                    'message' => 'Service Google Indexing non initialisé'
                ];
            }

            // S'assurer que l'URL est complète
            if (!str_starts_with($url, 'http')) {
                $url = rtrim($this->siteUrl, '/') . '/' . ltrim($url, '/');
            }

            // Créer la notification d'URL
            $notification = new UrlNotification();
            $notification->setUrl($url);
            $notification->setType('URL_UPDATED');

            // Publier la notification via l'API Indexing
            $this->indexingService->urlNotifications->publish($notification);

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
    public function indexUrls(array $urls, $batchSize = 200)
    {
        $results = [];
        $totalUrls = count($urls);
        
        // Si le batch est déjà découpé, traiter directement
        if ($totalUrls <= $batchSize) {
            // Traiter toutes les URLs du batch
            foreach ($urls as $index => $url) {
                $result = $this->indexUrl($url);
                $results[] = [
                    'url' => $url,
                    'result' => $result
                ];
                
                // Petite pause pour éviter les limites de rate (0.05 seconde entre chaque URL)
                if ($index < $totalUrls - 1) {
                    usleep(50000); // 0.05 seconde
                }
                
                // Log de progression tous les 50 URLs
                if (($index + 1) % 50 === 0) {
                    Log::info("Progression: " . ($index + 1) . "/{$totalUrls} URLs traitées");
                }
            }
        } else {
            // Si plus grand que le batch size, découper
            $batches = array_chunk($urls, $batchSize);

            foreach ($batches as $batchIndex => $batch) {
                Log::info("Traitement du batch " . ($batchIndex + 1) . " / " . count($batches) . " (" . count($batch) . " URLs)");
                
                foreach ($batch as $index => $url) {
                    $result = $this->indexUrl($url);
                    $results[] = [
                        'url' => $url,
                        'result' => $result
                    ];
                    
                    // Petite pause pour éviter les limites de rate
                    if ($index < count($batch) - 1) {
                        usleep(50000); // 0.05 seconde
                    }
                }
                
                // Pause plus longue entre les batches
                if ($batchIndex < count($batches) - 1) {
                    sleep(2); // 2 secondes entre chaque batch
                }
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
        return !empty($credentials) && $this->indexingService !== null;
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

