<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Submission;
use Carbon\Carbon;

class MarkAbandonedSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submissions:mark-abandoned {--hours=3 : Number of hours after which to mark as abandoned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark form submissions as abandoned after a specified number of hours (default: 3 hours)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoffTime = Carbon::now()->subHours($hours);
        
        $this->info("Marking submissions as abandoned that haven't been updated since {$cutoffTime->format('Y-m-d H:i:s')}...");
        
        // Trouver les soumissions en cours qui n'ont pas été mises à jour depuis X heures
        $abandonedSubmissions = Submission::where('status', 'IN_PROGRESS')
            ->where('updated_at', '<', $cutoffTime)
            ->get();
        
        if ($abandonedSubmissions->isEmpty()) {
            $this->info('No submissions found to mark as abandoned.');
            return 0;
        }
        
        $count = 0;
        foreach ($abandonedSubmissions as $submission) {
            $submission->markAsAbandoned();
            $count++;
            
            $this->line("Marked submission ID {$submission->id} as abandoned (last updated: {$submission->updated_at->format('Y-m-d H:i:s')})");
        }
        
        $this->info("Successfully marked {$count} submissions as abandoned.");
        
        // Afficher des statistiques
        $this->displayStatistics();
        
        return 0;
    }
    
    /**
     * Display submission statistics
     */
    private function displayStatistics()
    {
        $this->newLine();
        $this->info('=== Submission Statistics ===');
        
        $total = Submission::count();
        $inProgress = Submission::inProgress()->count();
        $completed = Submission::completed()->count();
        $abandoned = Submission::abandoned()->count();
        
        $this->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Total', $total, '100%'],
                ['In Progress', $inProgress, $total > 0 ? round(($inProgress / $total) * 100, 1) . '%' : '0%'],
                ['Completed', $completed, $total > 0 ? round(($completed / $total) * 100, 1) . '%' : '0%'],
                ['Abandoned', $abandoned, $total > 0 ? round(($abandoned / $total) * 100, 1) . '%' : '0%'],
            ]
        );
    }
}
