<?php

namespace App\Services;

use App\Services\GoogleSearchConsoleService;
use Illuminate\Support\Facades\Log;

class GoogleIndexingService
{
    protected $googleService;

    public function __construct()
    {
        $this->googleService = new GoogleSearchConsoleService();
    }

    /**
     * Indexer une URL via l'API Google Indexing
     * 
     * @param string $url URL à indexer
     * @return bool True si succès, false sinon
     */
    public function indexUrl(string $url): bool
    {
        try {
            if (!$this->googleService->isConfigured()) {
                Log::warning('GoogleIndexingService: Service non configuré', [
                    'url' => $url,
                    'reason' => 'Google Search Console credentials not configured'
                ]);
                return false;
            }

            Log::info('GoogleIndexingService: Tentative d\'indexation', [
                'url' => $url
            ]);
            
            $result = $this->googleService->indexUrl($url);
            
            if ($result['success'] ?? false) {
                Log::info('GoogleIndexingService: URL indexée avec succès', [
                    'url' => $url,
                    'message' => $result['message'] ?? 'Success'
                ]);
                return true;
            } else {
                $errorMessage = $result['message'] ?? 'Unknown error';
                $errorCode = $result['error_code'] ?? 'UNKNOWN';
                
                Log::error('GoogleIndexingService: Échec indexation', [
                    'url' => $url,
                    'message' => $errorMessage,
                    'error_code' => $errorCode,
                    'error_details' => $result['error_details'] ?? null
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('GoogleIndexingService: Exception lors de l\'indexation', [
                'url' => $url,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}

