<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class GoogleUrlInspectionService
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
     * Initialiser le client Google
     */
    protected function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName('Laravel SEO Automation');
            $this->client->setScopes([
                'https://www.googleapis.com/auth/webmasters.readonly',
            ]);

            // Charger les credentials
            $credentialsJson = Setting::get('google_credentials', null);
            if (empty($credentialsJson)) {
                throw new \Exception('Google credentials non configurés');
            }

            $credentials = json_decode($credentialsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Format JSON invalide pour les credentials');
            }

            $this->client->setAuthConfig($credentials);
            $this->service = new SearchConsole($this->client);
        } catch (\Exception $e) {
            Log::error('Erreur initialisation GoogleUrlInspectionService', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtenir l'URL du site
     */
    protected function getSiteUrl()
    {
        // 1. Vérifier le setting site_url
        $siteUrl = Setting::get('site_url', null);
        
        // 2. Si pas dans les settings, utiliser APP_URL
        if (empty($siteUrl)) {
            $siteUrl = config('app.url', null);
        }
        
        // 3. Si toujours vide, utiliser la requête actuelle
        if (empty($siteUrl)) {
            try {
                $siteUrl = request()->getSchemeAndHttpHost();
            } catch (\Exception $e) {
                $siteUrl = 'https://example.com';
            }
        }

        // S'assurer que l'URL a un protocole
        if (!preg_match('/^https?:\/\//', $siteUrl)) {
            $siteUrl = 'https://' . $siteUrl;
        }

        // Retirer le trailing slash
        $siteUrl = rtrim($siteUrl, '/');

        return $siteUrl;
    }

    /**
     * Vérifier le statut d'indexation d'une URL
     * 
     * @param string $url URL à vérifier
     * @return array Statut d'indexation
     */
    public function inspectUrl(string $url): array
    {
        try {
            if (!$this->service) {
                throw new \Exception('Service Google non initialisé');
            }

            // Normaliser l'URL
            $url = $this->normalizeUrl($url);

            // Utiliser l'API URL Inspection via REST
            // L'API utilise urlInspection.index.inspect
            $requestBody = new \Google\Service\SearchConsole\InspectUrlIndexRequest();
            $requestBody->setInspectionUrl($url);
            $requestBody->setSiteUrl($this->siteUrl);

            // Appeler l'API via la méthode inspect
            $response = $this->service->urlInspection_index->inspect($requestBody);

            // Parser la réponse
            $inspectionResult = $response->getInspectionResult();
            $indexStatusResult = $inspectionResult->getIndexStatusResult();
            $ampInspectionResult = $inspectionResult->getAmpInspectionResult();
            $mobileUsabilityResult = $inspectionResult->getMobileUsabilityResult();
            $richResultsResult = $inspectionResult->getRichResultsResult();

            $status = [
                'url' => $url,
                'indexed' => false,
                'coverage_state' => null,
                'last_crawl_time' => null,
                'indexing_state' => null,
                'page_fetch_state' => null,
                'verdict' => null,
                'details' => [],
                'errors' => [],
                'warnings' => [],
            ];

            if ($indexStatusResult) {
                $status['indexed'] = $indexStatusResult->getCoverageState() === 'Indexed';
                $status['coverage_state'] = $indexStatusResult->getCoverageState();
                $status['indexing_state'] = $indexStatusResult->getIndexingState();
                $status['last_crawl_time'] = $indexStatusResult->getLastCrawlTime();
                $status['page_fetch_state'] = $indexStatusResult->getPageFetchState();
                $status['verdict'] = $indexStatusResult->getVerdict();

                // Détails
                if ($indexStatusResult->getDetails()) {
                    $status['details'] = [
                        'coverage_state' => $indexStatusResult->getDetails()->getCoverageState(),
                        'indexed' => $indexStatusResult->getDetails()->getIndexed(),
                        'crawled_as' => $indexStatusResult->getDetails()->getCrawledAs(),
                    ];
                }

                // Erreurs
                if ($indexStatusResult->getCrawlIssue()) {
                    $status['errors'][] = [
                        'type' => $indexStatusResult->getCrawlIssue()->getIssueType(),
                        'severity' => $indexStatusResult->getCrawlIssue()->getSeverity(),
                        'description' => $indexStatusResult->getCrawlIssue()->getDescription(),
                    ];
                }
            }

            // Mobile usability
            if ($mobileUsabilityResult) {
                $status['mobile_usable'] = $mobileUsabilityResult->getVerdict() === 'PASS';
                if ($mobileUsabilityResult->getIssues()) {
                    foreach ($mobileUsabilityResult->getIssues() as $issue) {
                        $status['warnings'][] = [
                            'type' => 'mobile',
                            'severity' => $issue->getSeverity(),
                            'message' => $issue->getMessage(),
                        ];
                    }
                }
            }

            Log::info('URL Inspection réussie', [
                'url' => $url,
                'indexed' => $status['indexed'],
                'coverage_state' => $status['coverage_state']
            ]);

            return [
                'success' => true,
                'status' => $status
            ];

        } catch (\Google\Service\Exception $e) {
            $error = json_decode($e->getMessage(), true);
            $errorMessage = $error['error']['message'] ?? $e->getMessage();
            
            Log::error('Erreur URL Inspection API', [
                'url' => $url,
                'error' => $errorMessage,
                'code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'code' => $e->getCode()
            ];
        } catch (\Exception $e) {
            Log::error('Exception URL Inspection', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut de plusieurs URLs
     * 
     * @param array $urls URLs à vérifier
     * @return array Résultats
     */
    public function inspectUrls(array $urls): array
    {
        $results = [
            'total' => count($urls),
            'indexed' => 0,
            'not_indexed' => 0,
            'errors' => 0,
            'details' => []
        ];

        foreach ($urls as $url) {
            $result = $this->inspectUrl($url);
            
            if ($result['success']) {
                if ($result['status']['indexed']) {
                    $results['indexed']++;
                } else {
                    $results['not_indexed']++;
                }
            } else {
                $results['errors']++;
            }

            $results['details'][] = $result;
        }

        return $results;
    }

    /**
     * Normaliser une URL
     */
    protected function normalizeUrl(string $url): string
    {
        // S'assurer que l'URL est complète
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = $this->siteUrl . '/' . ltrim($url, '/');
        }

        // Retirer le trailing slash
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Vérifier si le service est configuré
     */
    public function isConfigured(): bool
    {
        try {
            $credentials = Setting::get('google_credentials', null);
            return !empty($credentials);
        } catch (\Exception $e) {
            return false;
        }
    }
}

