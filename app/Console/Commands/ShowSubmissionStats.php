<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;
use Carbon\Carbon;

class ShowSubmissionStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submissions:stats {--days=7 : Number of days to analyze}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display detailed statistics about form submissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $startDate = Carbon::now()->subDays($days);
        
        $this->info("=== Submission Statistics (Last {$days} days) ===");
        $this->newLine();
        
        // Statistiques générales
        $this->displayGeneralStats($startDate);
        
        // Statistiques par statut
        $this->displayStatusStats($startDate);
        
        // Statistiques par jour
        $this->displayDailyStats($startDate);
        
        // Soumissions récentes
        $this->displayRecentSubmissions();
        
        return 0;
    }
    
    /**
     * Display general statistics
     */
    private function displayGeneralStats($startDate)
    {
        $total = Submission::where('created_at', '>=', $startDate)->count();
        $inProgress = Submission::inProgress()->where('created_at', '>=', $startDate)->count();
        $completed = Submission::completed()->where('created_at', '>=', $startDate)->count();
        $abandoned = Submission::abandoned()->where('created_at', '>=', $startDate)->count();
        
        $this->info('📊 General Statistics:');
        $this->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total Submissions', $total, '100%'],
                ['In Progress', $inProgress, $total > 0 ? round(($inProgress / $total) * 100, 1) . '%' : '0%'],
                ['Completed', $completed, $total > 0 ? round(($completed / $total) * 100, 1) . '%' : '0%'],
                ['Abandoned', $abandoned, $total > 0 ? round(($abandoned / $total) * 100, 1) . '%' : '0%'],
            ]
        );
    }
    
    /**
     * Display status statistics
     */
    private function displayStatusStats($startDate)
    {
        $this->newLine();
        $this->info('📈 Status Breakdown:');
        
        $statuses = [
            'IN_PROGRESS' => Submission::inProgress()->where('created_at', '>=', $startDate)->count(),
            'COMPLETED' => Submission::completed()->where('created_at', '>=', $startDate)->count(),
            'ABANDONED' => Submission::abandoned()->where('created_at', '>=', $startDate)->count(),
        ];
        
        foreach ($statuses as $status => $count) {
            $percentage = $statuses['IN_PROGRESS'] + $statuses['COMPLETED'] + $statuses['ABANDONED'] > 0 
                ? round(($count / array_sum($statuses)) * 100, 1) 
                : 0;
            
            $this->line("  {$status}: {$count} ({$percentage}%)");
        }
    }
    
    /**
     * Display daily statistics
     */
    private function displayDailyStats($startDate)
    {
        $this->newLine();
        $this->info('📅 Daily Breakdown:');
        
        $dailyStats = Submission::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total, 
                        SUM(CASE WHEN status = "COMPLETED" THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = "ABANDONED" THEN 1 ELSE 0 END) as abandoned,
                        SUM(CASE WHEN status = "IN_PROGRESS" THEN 1 ELSE 0 END) as in_progress')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
        
        if ($dailyStats->isEmpty()) {
            $this->line('  No data available for the selected period.');
            return;
        }
        
        $this->table(
            ['Date', 'Total', 'Completed', 'Abandoned', 'In Progress'],
            $dailyStats->map(function ($stat) {
                return [
                    $stat->date,
                    $stat->total,
                    $stat->completed,
                    $stat->abandoned,
                    $stat->in_progress,
                ];
            })->toArray()
        );
    }
    
    /**
     * Display recent submissions
     */
    private function displayRecentSubmissions()
    {
        $this->newLine();
        $this->info('🕒 Recent Submissions (Last 10):');
        
        $recent = Submission::latest()
            ->take(10)
            ->get(['id', 'status', 'current_step', 'created_at', 'updated_at']);
        
        if ($recent->isEmpty()) {
            $this->line('  No submissions found.');
            return;
        }
        
        $this->table(
            ['ID', 'Status', 'Step', 'Created', 'Updated'],
            $recent->map(function ($submission) {
                return [
                    $submission->id,
                    $submission->status,
                    $submission->current_step ?? 'N/A',
                    $submission->created_at->format('Y-m-d H:i'),
                    $submission->updated_at->format('Y-m-d H:i'),
                ];
            })->toArray()
        );
    }
}
