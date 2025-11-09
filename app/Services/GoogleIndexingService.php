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
                    'url' => $url
                ]);
                return false;
            }

            $result = $this->googleService->indexUrl($url);
            
            if ($result['success'] ?? false) {
                Log::info('GoogleIndexingService: URL indexée', [
                    'url' => $url
                ]);
                return true;
            } else {
                Log::warning('GoogleIndexingService: Échec indexation', [
                    'url' => $url,
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('GoogleIndexingService: Exception', [
                'url' => $url,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }
}

