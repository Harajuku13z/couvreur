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

            // Vérifier que les credentials sont valides (doivent contenir au moins 'type' et 'project_id' ou 'client_email')
            if (!isset($credentials['type'])) {
                Log::error('Google Search Console: Format de credentials invalide (type manquant)');
                return false;
            }
            
            // Vérifier que c'est bien un service account
            if ($credentials['type'] !== 'service_account') {
                Log::warning('Google Search Console: Le type de credentials doit être "service_account"');
            }

            $this->client = new Google_Client();
            $this->client->setAuthConfig($credentials);
            $this->client->addScope(SearchConsole::WEBMASTERS);
            $this->client->addScope('https://www.googleapis.com/auth/indexing');
            $this->client->setAccessType('offline');
            
            $this->service = new SearchConsole($this->client);
            $this->indexingService = new Indexing($this->client);
            
            Log::info('✅ Google Search Console client initialisé avec succès');
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur initialisation Google Search Console: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Récupérer les credentials depuis les settings
     */
    protected function getCredentials()
    {
        try {
            $credentialsJson = Setting::get('google_search_console_credentials', '');
            
            if (empty($credentialsJson)) {
                Log::debug('Google Search Console: Aucune credentials JSON trouvée dans les settings');
                return null;
            }

            // Si c'est déjà un tableau, le retourner directement
            if (is_array($credentialsJson)) {
                return $credentialsJson;
            }

            // Sinon, essayer de décoder le JSON
            $credentials = json_decode($credentialsJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erreur parsing JSON credentials Google Search Console: ' . json_last_error_msg());
                return null;
            }

            if (!is_array($credentials)) {
                Log::error('Google Search Console: Les credentials décodées ne sont pas un tableau');
                return null;
            }

            return $credentials;
        } catch (\Exception $e) {
            Log::error('Erreur récupération credentials: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
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
                    'message' => 'Service Google Indexing non initialisé',
                    'error_code' => 'SERVICE_NOT_INITIALIZED'
                ];
            }

            // S'assurer que l'URL est complète
            if (!str_starts_with($url, 'http')) {
                $url = rtrim($this->siteUrl, '/') . '/' . ltrim($url, '/');
            }

            // Vérifier que l'URL appartient au domaine configuré
            $parsedUrl = parse_url($url);
            $parsedSiteUrl = parse_url($this->siteUrl);
            
            if (isset($parsedUrl['host']) && isset($parsedSiteUrl['host']) && 
                $parsedUrl['host'] !== $parsedSiteUrl['host']) {
                return [
                    'success' => false,
                    'message' => "L'URL n'appartient pas au domaine configuré: {$parsedUrl['host']} vs {$parsedSiteUrl['host']}",
                    'error_code' => 'DOMAIN_MISMATCH'
                ];
            }

            // Créer la notification d'URL
            $notification = new UrlNotification();
            $notification->setUrl($url);
            $notification->setType('URL_UPDATED');

            // Publier la notification via l'API Indexing
            $response = $this->indexingService->urlNotifications->publish($notification);

            Log::info("URL indexée avec succès: {$url}");

            return [
                'success' => true,
                'message' => "URL indexée avec succès: {$url}",
                'response' => $response
            ];
        } catch (\Google\Service\Exception $e) {
            // Erreur spécifique de l'API Google
            $errorDetails = $e->getErrors();
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            
            Log::error("Erreur API Google Indexing pour URL {$url}", [
                'code' => $errorCode,
                'message' => $errorMessage,
                'errors' => $errorDetails,
                'url' => $url
            ]);
            
            // Extraire le message d'erreur le plus utile
            $userMessage = $errorMessage;
            if (!empty($errorDetails) && is_array($errorDetails)) {
                $firstError = $errorDetails[0] ?? [];
                if (isset($firstError['message'])) {
                    $userMessage = $firstError['message'];
                }
                if (isset($firstError['reason'])) {
                    $userMessage .= ' (Reason: ' . $firstError['reason'] . ')';
                }
            }
            
            // Améliorer le message pour les erreurs de permission
            if ($errorCode == 403 || (isset($firstError['reason']) && $firstError['reason'] === 'forbidden')) {
                $serviceAccountEmail = $this->getCredentials()['client_email'] ?? 'votre-compte-service@...';
                $userMessage .= "\n\n💡 Solution : Le compte de service doit être ajouté comme propriétaire dans Google Search Console.\n";
                $userMessage .= "1. Allez sur https://search.google.com/search-console\n";
                $userMessage .= "2. Sélectionnez votre propriété (site)\n";
                $userMessage .= "3. Allez dans Paramètres > Utilisateurs et permissions\n";
                $userMessage .= "4. Cliquez sur 'Ajouter un utilisateur'\n";
                $userMessage .= "5. Ajoutez l'email du compte de service : {$serviceAccountEmail}\n";
                $userMessage .= "6. Donnez-lui le rôle 'Propriétaire'";
            }
            
            return [
                'success' => false,
                'message' => $userMessage,
                'error_code' => $errorCode,
                'error_details' => $errorDetails
            ];
        } catch (\Exception $e) {
            Log::error("Erreur indexation URL {$url}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'url' => $url
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'error_code' => 'GENERAL_ERROR'
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
                
                // Log de progression tous les 50 URLs avec détails des erreurs
                if (($index + 1) % 50 === 0) {
                    $successCount = count(array_filter(array_slice($results, 0, $index + 1), function($r) {
                        return $r['result']['success'] ?? false;
                    }));
                    Log::info("Progression: " . ($index + 1) . "/{$totalUrls} URLs traitées ({$successCount} réussies)");
                }
                
                // Log les 5 premières erreurs pour diagnostic
                if (!$result['success'] && count(array_filter($results, function($r) {
                    return !($r['result']['success'] ?? false);
                })) <= 5) {
                    Log::warning("Échec indexation URL: {$url}", [
                        'error' => $result['message'] ?? 'Erreur inconnue',
                        'error_code' => $result['error_code'] ?? null
                    ]);
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
        // Vérifier d'abord si les credentials existent
        $credentials = $this->getCredentials();
        if (empty($credentials)) {
            Log::debug('Google Search Console: Aucune credentials trouvée');
            return false;
        }
        
        // Vérifier que les credentials ont le format correct
        if (!is_array($credentials)) {
            Log::error('Google Search Console: Les credentials ne sont pas un tableau');
            return false;
        }
        
        if (!isset($credentials['type'])) {
            Log::error('Google Search Console: Le type de credentials est manquant');
            return false;
        }
        
        if ($credentials['type'] !== 'service_account') {
            Log::warning('Google Search Console: Le type de credentials n\'est pas "service_account" (type: ' . $credentials['type'] . ')');
            return false;
        }
        
        // Si les credentials existent mais que le service n'est pas initialisé, essayer de l'initialiser
        if ($this->indexingService === null || $this->client === null) {
            Log::info('Réinitialisation du client Google Search Console...');
            $initialized = $this->initializeClient();
            if (!$initialized) {
                Log::warning('Impossible d\'initialiser le client Google Search Console malgré la présence de credentials');
                return false;
            }
        }
        
        // Vérifier que le service est bien initialisé
        $isConfigured = $this->indexingService !== null && $this->client !== null;
        
        if (!$isConfigured) {
            Log::warning('Google Search Console: Le service n\'est pas initialisé (indexingService: ' . ($this->indexingService === null ? 'null' : 'ok') . ', client: ' . ($this->client === null ? 'null' : 'ok') . ')');
        }
        
        return $isConfigured;
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
            $siteEntries = $sites->getSiteEntry();
            
            // Vérifier si le site est dans la liste
            $siteUrl = rtrim($this->siteUrl, '/');
            $siteFound = false;
            $sitePermission = null;
            
            foreach ($siteEntries as $site) {
                if ($site->getSiteUrl() === $siteUrl || $site->getSiteUrl() === $siteUrl . '/') {
                    $siteFound = true;
                    $sitePermission = $site->getPermissionLevel();
                    break;
                }
            }
            
            $serviceAccountEmail = $this->getCredentials()['client_email'] ?? 'votre-compte-service@...';
            $warningMessage = null;
            
            if (!$siteFound) {
                $warningMessage = "⚠️ Le site {$siteUrl} n'est pas trouvé dans Google Search Console.\n\n";
                $warningMessage .= "💡 Solution : Ajoutez le compte de service comme propriétaire :\n";
                $warningMessage .= "1. Allez sur https://search.google.com/search-console\n";
                $warningMessage .= "2. Sélectionnez votre propriété (site)\n";
                $warningMessage .= "3. Allez dans Paramètres > Utilisateurs et permissions\n";
                $warningMessage .= "4. Cliquez sur 'Ajouter un utilisateur'\n";
                $warningMessage .= "5. Ajoutez l'email : {$serviceAccountEmail}\n";
                $warningMessage .= "6. Donnez-lui le rôle 'Propriétaire'";
            } elseif ($sitePermission && $sitePermission !== 'siteOwner' && $sitePermission !== 'siteFullUser') {
                $warningMessage = "⚠️ Le compte de service n'a pas les permissions suffisantes (permission actuelle: {$sitePermission}).\n\n";
                $warningMessage .= "💡 Solution : Donnez le rôle 'Propriétaire' ou 'Utilisateur complet' au compte de service dans Google Search Console.";
            }
            
            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'sites_count' => count($siteEntries),
                'site_url' => $siteUrl,
                'site_found' => $siteFound,
                'site_permission' => $sitePermission,
                'warning' => $warningMessage
            ];
        } catch (\Google\Service\Exception $e) {
            $errorDetails = $e->getErrors();
            $errorMessage = $e->getMessage();
            
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $errorMessage,
                'error_code' => $e->getCode(),
                'error_details' => $errorDetails
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Tester l'indexation d'une URL de test
     */
    public function testIndexing()
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'message' => 'Service non configuré'
                ];
            }

            // Tester avec l'URL de base du site
            $testUrl = rtrim($this->siteUrl, '/');
            $result = $this->indexUrl($testUrl);
            
            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors du test: ' . $e->getMessage()
            ];
        }
    }
}

