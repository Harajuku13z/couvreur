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
                // Certaines configurations exigent le scope complet pour URL Inspection
                'https://www.googleapis.com/auth/webmasters',
                'https://www.googleapis.com/auth/webmasters.readonly',
            ]);

            // Charger les credentials (essayer google_search_console_credentials puis google_credentials)
            $credentialsJson = Setting::get('google_search_console_credentials', null);
            if (empty($credentialsJson)) {
                $credentialsJson = Setting::get('google_credentials', null);
            }
            if (empty($credentialsJson)) {
                throw new \Exception('Google credentials non configurés');
            }

            // Accepter soit un tableau, soit une chaîne JSON
            if (is_array($credentialsJson)) {
                $credentials = $credentialsJson;
            } elseif (is_string($credentialsJson)) {
                $credentials = json_decode($credentialsJson, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($credentials)) {
                    throw new \Exception('Format JSON invalide pour les credentials');
                }
            } else {
                throw new \Exception('Format des credentials non supporté');
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

        // Si site_url a été configuré en format sc-domain:, le convertir en https://domaine
        if (is_string($siteUrl) && str_starts_with($siteUrl, 'sc-domain:')) {
            $domain = substr($siteUrl, strlen('sc-domain:'));
            $domain = preg_replace('/^www\./', '', $domain);
            $siteUrl = 'https://' . $domain;
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

            // Essayer plusieurs variantes de siteUrl pour éviter DOMAIN_MISMATCH:
            // 1) site_url configuré (URL-prefix)
            // 2) sc-domain:example.com (domain property)
            // 3) https://example.com (URL-prefix sur le domaine racine)
            $candidates = [];
            $configuredSiteUrl = rtrim($this->siteUrl, '/');
            if (!empty($configuredSiteUrl)) {
                $candidates[] = $configuredSiteUrl;
            }
            // Extraire le domaine de l'URL
            $parsed = parse_url($url);
            if (!empty($parsed['host'])) {
                $host = preg_replace('/^www\./', '', $parsed['host']);
                $candidates[] = 'sc-domain:' . $host;
                $candidates[] = 'https://' . $host;
                $candidates[] = 'http://' . $host;
            }

            $triedVariants = [];
            $lastError = null;
            $winningStatus = null;
            foreach ($candidates as $candidateSiteUrl) {
                try {
                    // Utiliser l'API URL Inspection
                    // Format: urlInspection.index.inspect avec InspectUrlIndexRequest
                    $requestBody = new \Google\Service\SearchConsole\InspectUrlIndexRequest();
                    $requestBody->setInspectionUrl($url);
                    $requestBody->setSiteUrl($candidateSiteUrl);
                    // Optionnel: préciser la langue
                    $requestBody->setLanguageCode('fr-FR');

                    // Appeler l'API
                    $response = $this->service->urlInspection_index->inspect($requestBody);

                    // Parser la réponse
                    $inspectionResult = $response->getInspectionResult();
                    $indexStatusResult = method_exists($inspectionResult, 'getIndexStatusResult')
                        ? $inspectionResult->getIndexStatusResult()
                        : null;

                    $status = [
                        'url' => $url,
                        'site_url_used' => $candidateSiteUrl,
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
                        $coverage = $indexStatusResult->getCoverageState();
                        $status['coverage_state'] = $coverage;
                        
                        // Certains SDKs ne fournissent pas ces champs; utiliser method_exists par sécurité
                        $status['indexing_state'] = method_exists($indexStatusResult, 'getIndexingState')
                            ? $indexStatusResult->getIndexingState()
                            : null;
                        $status['last_crawl_time'] = $indexStatusResult->getLastCrawlTime();
                        $status['page_fetch_state'] = method_exists($indexStatusResult, 'getPageFetchState')
                            ? $indexStatusResult->getPageFetchState()
                            : null;
                        $status['verdict'] = method_exists($indexStatusResult, 'getVerdict')
                            ? $indexStatusResult->getVerdict()
                            : null;

                        // ⚠️ LOGIQUE AMÉLIORÉE : Une URL est considérée comme indexée si :
                        // 1. coverage_state === 'INDEXED' OU
                        // 2. indexing_state === 'URL_IS_ON_GOOGLE' (l'URL est sur Google) OU
                        // 3. verdict === 'PASS' ET coverage_state n'est pas explicitement 'NOT_INDEXED'
                        $coverageUpper = is_string($coverage) ? strtoupper($coverage) : '';
                        $indexingStateUpper = is_string($status['indexing_state']) ? strtoupper($status['indexing_state']) : '';
                        $verdictUpper = is_string($status['verdict']) ? strtoupper($status['verdict']) : '';
                        
                        $isIndexedByCoverage = $coverageUpper === 'INDEXED';
                        $isIndexedByIndexingState = $indexingStateUpper === 'URL_IS_ON_GOOGLE';
                        $isIndexedByVerdict = $verdictUpper === 'PASS' && $coverageUpper !== 'NOT_INDEXED';
                        
                        $status['indexed'] = $isIndexedByCoverage || $isIndexedByIndexingState || $isIndexedByVerdict;
                        
                        // Log détaillé pour diagnostic
                        Log::info('URL Inspection - Analyse détaillée', [
                            'url' => $url,
                            'site_url_used' => $candidateSiteUrl,
                            'coverage_state' => $coverage,
                            'indexing_state' => $status['indexing_state'],
                            'verdict' => $status['verdict'],
                            'indexed_by_coverage' => $isIndexedByCoverage,
                            'indexed_by_indexing_state' => $isIndexedByIndexingState,
                            'indexed_by_verdict' => $isIndexedByVerdict,
                            'final_indexed' => $status['indexed'],
                            'last_crawl_time' => $status['last_crawl_time'],
                        ]);

                        // Ne pas utiliser getDetails()/getCrawlIssue(), non disponibles dans certaines versions
                    }

                    $triedVariants[] = $status;
                    // Conserver la première réponse indexée comme gagnante
                    if ($status['indexed'] && !$winningStatus) {
                        $winningStatus = $status;
                        // Ne pas break: on continue pour documenter toutes les variantes testées
                    }
                } catch (\Google\Service\Exception $e) {
                    // Garder le dernier message d'erreur, essayer la variante suivante
                    $lastError = $e;
                    // Enregistrer l'erreur pour cette variante
                    $error = json_decode($e->getMessage(), true);
                    $errorMessage = $error['error']['message'] ?? $e->getMessage();
                    
                    // ⚠️ Si c'est une erreur "You do not own this site" mais que la propriété sc-domain: fonctionne,
                    // on peut quand même considérer que l'URL est indexée si on a déjà une réponse positive de sc-domain:
                    $isOwnershipError = stripos($errorMessage, 'do not own') !== false || 
                                       stripos($errorMessage, 'not part of this property') !== false;
                    
                    $triedVariants[] = [
                        'url' => $url,
                        'site_url_used' => $candidateSiteUrl,
                        'error' => $errorMessage,
                        'code' => $e->getCode(),
                        'is_ownership_error' => $isOwnershipError,
                    ];
                    
                    // Si on a déjà une réponse positive de sc-domain:, on peut ignorer les erreurs de propriété
                    if ($isOwnershipError && $winningStatus && str_starts_with($winningStatus['site_url_used'], 'sc-domain:')) {
                        Log::info('Erreur de propriété ignorée car sc-domain: a confirmé l\'indexation', [
                            'url' => $url,
                            'failed_property' => $candidateSiteUrl,
                            'winning_property' => $winningStatus['site_url_used']
                        ]);
                    }
                    
                    continue;
                }
            }

            // Si une variante a confirmé l'indexation, la retourner
            if ($winningStatus) {
                return [
                    'success' => true,
                    'status' => $winningStatus,
                    'indexed' => true,
                    'property_used' => $winningStatus['site_url_used'],
                    'tried_variants' => $triedVariants,
                ];
            }

            // Si au moins une variante a renvoyé une réponse mais non indexée, renvoyer le premier statut
            foreach ($triedVariants as $variant) {
                if (!isset($variant['error'])) {
                    return [
                        'success' => true,
                        'status' => $variant,
                        'indexed' => false,
                        'property_used' => $variant['site_url_used'],
                        'tried_variants' => $triedVariants,
                    ];
                }
            }

            // Si toutes les variantes échouent, lever la dernière erreur
            if ($lastError instanceof \Google\Service\Exception) {
                $error = json_decode($lastError->getMessage(), true);
                $errorMessage = $error['error']['message'] ?? $lastError->getMessage();
                
                Log::error('Erreur URL Inspection API (toutes variantes échouées)', [
                    'url' => $url,
                    'site_url_tried' => $candidates,
                    'error' => $errorMessage,
                    'code' => $lastError->getCode()
                ]);

                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'code' => $lastError->getCode(),
                    'tried_variants' => $triedVariants,
                ];
            }

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
     * ⚠️ IMPORTANT: L'URL à inspecter doit TOUJOURS être au format https://... ou http://...
     * sc-domain: n'est PAS valide pour l'inspectionUrl (seulement pour siteUrl)
     * ⚠️ On privilégie toujours https:// au lieu de http:// pour éviter les erreurs de propriété
     */
    protected function normalizeUrl(string $url): string
    {
        // Si c'est un format sc-domain:, le convertir en https://
        if (str_starts_with($url, 'sc-domain:')) {
            $originalUrl = $url;
            $domain = str_replace('sc-domain:', '', $url);
            $url = 'https://' . ltrim($domain, '/');
            Log::warning('Conversion sc-domain: en https:// pour inspectionUrl', [
                'original' => $originalUrl,
                'converted' => $url
            ]);
        }
        
        // S'assurer que l'URL est complète
        if (!preg_match('/^https?:\/\//', $url)) {
            // Utiliser le siteUrl comme base, mais s'assurer qu'il est en https://
            $baseUrl = $this->siteUrl;
            if (str_starts_with($baseUrl, 'sc-domain:')) {
                $domain = str_replace('sc-domain:', '', $baseUrl);
                $baseUrl = 'https://' . ltrim($domain, '/');
            }
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        // ⚠️ CRITIQUE: Toujours convertir http:// en https:// pour éviter les erreurs "You do not own this site"
        // Les propriétés GSC sont généralement configurées pour https://, pas http://
        if (str_starts_with($url, 'http://')) {
            $originalUrl = $url;
            $url = str_replace('http://', 'https://', $url);
            Log::info('Conversion http:// en https:// pour éviter erreurs de propriété GSC', [
                'original' => $originalUrl,
                'converted' => $url
            ]);
        }

        // Retirer le trailing slash
        $url = rtrim($url, '/');

        // Validation finale: s'assurer que l'URL est bien au format http:// ou https://
        if (!preg_match('/^https?:\/\//', $url)) {
            throw new \Exception("URL invalide après normalisation: {$url}. L'URL doit être au format https://... ou http://...");
        }

        return $url;
    }

    /**
     * Vérifier si le service est configuré
     */
    public function isConfigured(): bool
    {
        try {
            $credentials = Setting::get('google_search_console_credentials', null);
            if (empty($credentials)) {
                $credentials = Setting::get('google_credentials', null);
            }
            return !empty($credentials);
        } catch (\Exception $e) {
            return false;
        }
    }
}

