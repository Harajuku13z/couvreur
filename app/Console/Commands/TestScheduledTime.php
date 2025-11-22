<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SeoArticleScheduler;
use App\Models\Article;
use App\Models\SeoAutomation;
use Carbon\Carbon;

class TestScheduledTime extends Command
{
    protected $signature = 'seo:test-time {time?}';
    protected $description = 'Tester un créneau horaire spécifique pour voir pourquoi il est marqué comme manqué';

    public function handle()
    {
        $timeInput = $this->argument('time') ?? '12:36';
        
        $this->info("🔍 Test du créneau horaire : {$timeInput}");
        $this->line('');
        
        // Parser l'heure
        $timeParts = explode(':', $timeInput);
        if (count($timeParts) !== 2) {
            $this->error("Format d'heure invalide. Utilisez HH:MM (ex: 12:36)");
            return 1;
        }
        
        $hour = (int)$timeParts[0];
        $minute = (int)$timeParts[1];
        
        $testTime = Carbon::today()->setTime($hour, $minute);
        $windowStart = $testTime->copy()->subMinutes(30);
        $windowEnd = $testTime->copy()->addMinutes(30);
        
        $this->info("📅 Créneau testé : {$testTime->format('H:i')}");
        $this->info("   Fenêtre de vérification : {$windowStart->format('H:i')} - {$windowEnd->format('H:i')}");
        $this->line('');
        
        // 1. Vérifier les articles créés dans cette fenêtre
        $this->info("1️⃣ Articles créés dans cette fenêtre :");
        $articles = Article::whereBetween('created_at', [$windowStart, $windowEnd])
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($articles->count() > 0) {
            $this->info("   ✅ {$articles->count()} article(s) trouvé(s) :");
            foreach ($articles as $article) {
                $cityName = $article->city ? $article->city->name : 'N/A';
                $diff = abs($testTime->diffInMinutes($article->created_at));
                $this->line("      - {$article->created_at->format('H:i:s')} : {$cityName} (diff: {$diff} min)");
            }
        } else {
            $this->warn("   ❌ Aucun article créé dans cette fenêtre");
        }
        $this->line('');
        
        // 2. Vérifier les erreurs dans cette fenêtre
        $this->info("2️⃣ Erreurs dans cette fenêtre :");
        $errors = SeoAutomation::whereBetween('created_at', [$windowStart, $windowEnd])
            ->where('status', 'failed')
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($errors->count() > 0) {
            $this->warn("   ⚠️ {$errors->count()} erreur(s) trouvée(s) :");
            foreach ($errors as $error) {
                $cityName = $error->city ? $error->city->name : 'N/A';
                $errorMsg = substr($error->error_message ?? 'Erreur inconnue', 0, 100);
                $this->line("      - {$error->created_at->format('H:i:s')} : {$cityName}");
                $this->line("        → {$errorMsg}");
            }
        } else {
            $this->info("   ✅ Aucune erreur dans cette fenêtre");
        }
        $this->line('');
        
        // 3. Vérifier le scheduler
        $this->info("3️⃣ État du scheduler :");
        $scheduler = app(SeoArticleScheduler::class);
        $nextTime = $scheduler->getNextScheduledTime();
        $shouldCreate = $scheduler->shouldCreateArticle();
        $stats = $scheduler->getScheduleStats();
        
        $this->line("   - Prochain créneau : " . ($nextTime ? $nextTime->format('H:i') : 'N/A'));
        $this->line("   - Doit créer maintenant : " . ($shouldCreate ? '✅ OUI' : '❌ NON'));
        $this->line("   - Articles aujourd'hui : {$stats['articles_today']}/{$stats['total_articles_per_day']}");
        $this->line("   - Intervalle : {$stats['interval_minutes']} minutes");
        $this->line('');
        
        // 4. Vérifier si le créneau est dans le passé
        $isPast = $testTime->isPast();
        $this->info("4️⃣ Statut du créneau :");
        $this->line("   - Est dans le passé : " . ($isPast ? '✅ OUI' : '❌ NON'));
        
        if ($isPast) {
            $this->line("   - Différence avec maintenant : " . now()->diffInMinutes($testTime) . " minutes");
        }
        $this->line('');
        
        // 5. Résumé
        $this->info("📊 Résumé :");
        if ($articles->count() > 0) {
            $this->info("   ✅ Un article a été créé dans la fenêtre. Le créneau devrait être marqué comme 'Créé'.");
        } elseif ($errors->count() > 0) {
            $this->warn("   ⚠️ Des erreurs ont été détectées. Le créneau devrait afficher l'erreur.");
        } else {
            $this->error("   ❌ Aucun article créé et aucune erreur détectée.");
            $this->line("      → Le cron Hostinger n'a probablement pas été exécuté à cette heure.");
            $this->line("      → Vérifiez que le cron est bien configuré et s'exécute.");
        }
        
        return 0;
    }
}



