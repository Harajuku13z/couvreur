<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use App\Models\Ad;
use Illuminate\Support\Facades\DB;

class AnalyzeContentQuality extends Command
{
    protected $signature = 'seo:analyze-quality {--export : Exporter les résultats en CSV}';
    protected $description = 'Analyser la qualité du contenu (longueur, duplication, structure)';

    public function handle()
    {
        $this->info('🔍 ANALYSE QUALITÉ DU CONTENU');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();
        
        // Analyser les articles
        $this->info('📰 ARTICLES');
        $articles = Article::where('status', 'published')->get();
        $this->analyzeCollection($articles, 'article');
        
        $this->newLine();
        
        // Analyser les annonces
        $this->info('📢 ANNONCES');
        $ads = Ad::limit(1000)->get(); // Limiter pour ne pas surcharger
        $this->analyzeCollection($ads, 'ad');
        
        $this->newLine();
        
        // Recommandations globales
        $this->info('💡 RECOMMANDATIONS PRIORITAIRES :');
        $this->newLine();
        $this->info('1. Enrichir les contenus < 1000 mots (objectif : 1500-2500 mots)');
        $this->info('2. Supprimer ou noindex les pages sans contenu unique');
        $this->info('3. Réviser les titres dupliqués (rendre unique)');
        $this->info('4. Ajouter des FAQ schema.org sur toutes les pages');
        $this->info('5. Vérifier personnalisation IA activée (éviter duplication)');
        
        return 0;
    }
    
    protected function analyzeCollection($collection, $type)
    {
        if ($collection->isEmpty()) {
            $this->warn("   Aucun {$type} à analyser");
            return;
        }
        
        $stats = [
            'total' => $collection->count(),
            'très_court' => 0,   // < 500 mots
            'court' => 0,        // 500-1000 mots
            'moyen' => 0,        // 1000-1500 mots
            'long' => 0,         // 1500-2500 mots
            'très_long' => 0,    // > 2500 mots
        ];
        
        $wordCounts = [];
        $duplicateTitles = [];
        $titlesCheck = [];
        
        foreach ($collection as $item) {
            $content = $item->content_html ?? '';
            $wordCount = str_word_count(strip_tags($content));
            $wordCounts[] = $wordCount;
            
            // Catégoriser
            if ($wordCount < 500) {
                $stats['très_court']++;
            } elseif ($wordCount < 1000) {
                $stats['court']++;
            } elseif ($wordCount < 1500) {
                $stats['moyen']++;
            } elseif ($wordCount < 2500) {
                $stats['long']++;
            } else {
                $stats['très_long']++;
            }
            
            // Détecter titres dupliqués
            $title = $item->title;
            if (isset($titlesCheck[$title])) {
                $duplicateTitles[] = $title;
            }
            $titlesCheck[$title] = true;
        }
        
        // Stats
        $avgWords = round(array_sum($wordCounts) / count($wordCounts));
        $minWords = min($wordCounts);
        $maxWords = max($wordCounts);
        
        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Total', $stats['total']],
                ['Longueur moyenne', $avgWords . ' mots'],
                ['Min / Max', $minWords . ' / ' . $maxWords . ' mots'],
                ['< 500 mots (Très court)', $stats['très_court'] . ' (' . round($stats['très_court']/$stats['total']*100, 1) . '%)'],
                ['500-1000 mots (Court)', $stats['court'] . ' (' . round($stats['court']/$stats['total']*100, 1) . '%)'],
                ['1000-1500 mots (Moyen)', $stats['moyen'] . ' (' . round($stats['moyen']/$stats['total']*100, 1) . '%)'],
                ['1500-2500 mots (Long)', $stats['long'] . ' (' . round($stats['long']/$stats['total']*100, 1) . '%)'],
                ['> 2500 mots (Très long)', $stats['très_long'] . ' (' . round($stats['très_long']/$stats['total']*100, 1) . '%)'],
                ['Titres dupliqués', count(array_unique($duplicateTitles))],
            ]
        );
        
        // Alertes
        if ($stats['très_court'] > $stats['total'] * 0.3) {
            $this->error("   ❌ CRITIQUE : " . round($stats['très_court']/$stats['total']*100) . "% du contenu < 500 mots (thin content)");
        }
        
        if (count($duplicateTitles) > 10) {
            $this->warn("   ⚠️  " . count($duplicateTitles) . " titres dupliqués - risque de cannibalisation");
        }
    }
}

