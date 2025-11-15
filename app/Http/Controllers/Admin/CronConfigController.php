<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CronConfigController extends Controller
{
    /**
     * Afficher la page de configuration du cron
     */
    public function index()
    {
        // Récupérer le token de schedule
        $scheduleToken = \App\Models\Setting::get('schedule_run_token', null);
        $scheduleUrl = $scheduleToken ? url('/schedule/run?token=' . $scheduleToken) : null;
        
        // Récupérer l'intervalle cron configuré
        $cronInterval = (int)\App\Models\Setting::get('seo_automation_cron_interval', 1);
        $cronInterval = max(1, min(60, $cronInterval));
        
        // Récupérer l'heure d'automatisation
        $automationTime = \App\Models\Setting::get('seo_automation_time', '04:00');
        
        // Générer la commande cron selon l'intervalle
        $cronCommand = $this->generateCronCommand($cronInterval, $scheduleUrl);
        
        return view('admin.cron_config.index', compact(
            'scheduleToken',
            'scheduleUrl',
            'cronInterval',
            'automationTime',
            'cronCommand'
        ));
    }
    
    /**
     * Générer la commande cron selon l'intervalle
     */
    protected function generateCronCommand($interval, $url = null)
    {
        $phpPath = '/usr/bin/php';
        $projectPath = '/home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html';
        
        // Commande directe (recommandée)
        $directCommand = "{$phpPath} {$projectPath}/artisan seo:run-automations";
        
        // Commande HTTP (alternative)
        $httpCommand = $url ? "curl -s \"{$url}\" > /dev/null 2>&1" : null;
        
        // Générer la fréquence cron
        $cronFrequency = $this->getCronFrequency($interval);
        
        return [
            'direct' => [
                'command' => $directCommand,
                'frequency' => $cronFrequency,
                'full' => "{$cronFrequency} {$directCommand}"
            ],
            'http' => $httpCommand ? [
                'command' => $httpCommand,
                'frequency' => $cronFrequency,
                'full' => "{$cronFrequency} {$httpCommand}"
            ] : null
        ];
    }
    
    /**
     * Obtenir la fréquence cron selon l'intervalle
     */
    protected function getCronFrequency($interval)
    {
        if ($interval === 1) {
            return '* * * * *'; // Toutes les minutes
        } elseif ($interval <= 59) {
            return "*/{$interval} * * * *"; // Toutes les X minutes
        } else {
            return '* * * * *'; // Par défaut, toutes les minutes
        }
    }
    
    /**
     * Obtenir ou générer le token
     */
    public function getToken()
    {
        $token = \App\Models\Setting::get('schedule_run_token', null);
        
        if (empty($token)) {
            $token = \Illuminate\Support\Str::random(32);
            \App\Models\Setting::set('schedule_run_token', $token, 'string', 'seo');
        }
        
        $url = url('/schedule/run?token=' . $token);
        
        return response()->json([
            'status' => 'success',
            'token' => $token,
            'url' => $url,
            'message' => 'Token récupéré avec succès'
        ]);
    }
    
    /**
     * Régénérer le token
     */
    public function regenerateToken()
    {
        $newToken = \Illuminate\Support\Str::random(32);
        \App\Models\Setting::set('schedule_run_token', $newToken, 'string', 'seo');
        
        $url = url('/schedule/run?token=' . $newToken);
        
        return response()->json([
            'status' => 'success',
            'token' => $newToken,
            'url' => $url,
            'message' => 'Token régénéré avec succès. N\'oubliez pas de mettre à jour votre cron avec le nouveau token.'
        ]);
    }
    
    /**
     * Tester la route HTTP
     */
    public function testHttp()
    {
        $token = \App\Models\Setting::get('schedule_run_token', null);
        
        if (empty($token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun token configuré. Générez d\'abord un token.'
            ], 400);
        }
        
        $url = url('/schedule/run?token=' . $token);
        
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->get($url);
            $data = $response->json();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Route HTTP accessible',
                'response' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du test : ' . $e->getMessage()
            ], 500);
        }
    }
}

