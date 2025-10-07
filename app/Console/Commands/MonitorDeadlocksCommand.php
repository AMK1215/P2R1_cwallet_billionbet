<?php

namespace App\Console\Commands;

use App\Services\DeadlockMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonitorDeadlocksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadlock:monitor 
                            {--interval=60 : Monitoring interval in seconds}
                            {--once : Run once and exit}
                            {--cleanup : Clean up old deadlock logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor PostgreSQL deadlocks and log them to the database';

    protected DeadlockMonitoringService $deadlockService;

    public function __construct(DeadlockMonitoringService $deadlockService)
    {
        parent::__construct();
        $this->deadlockService = $deadlockService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = (int) $this->option('interval');
        $runOnce = $this->option('once');
        $cleanup = $this->option('cleanup');

        if ($cleanup) {
            return $this->cleanupOldLogs();
        }

        $this->info('Starting deadlock monitoring...');
        $this->info("Monitoring interval: {$interval} seconds");

        if ($runOnce) {
            $this->monitorOnce();
        } else {
            $this->monitorContinuously($interval);
        }
    }

    /**
     * Run monitoring once
     */
    private function monitorOnce(): void
    {
        $this->info('Running deadlock check...');
        
        try {
            $deadlocks = $this->deadlockService->monitorDeadlocks();
            
            if (empty($deadlocks)) {
                $this->info('No deadlocks detected.');
            } else {
                $this->warn('Deadlocks detected: ' . count($deadlocks));
                
                foreach ($deadlocks as $deadlock) {
                    $this->line("  - Session: {$deadlock['session_id']}, Table: {$deadlock['table_name']}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error('Deadlock monitoring failed: ' . $e->getMessage());
            Log::error('Deadlock monitoring command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Run monitoring continuously
     */
    private function monitorContinuously(int $interval): void
    {
        $this->info('Starting continuous monitoring (Press Ctrl+C to stop)...');
        
        while (true) {
            try {
                $this->monitorOnce();
                
                // Show statistics
                $stats = $this->deadlockService->getStatistics();
                $this->line("Stats - Total: {$stats['total']}, Unresolved: {$stats['unresolved']}, Today: {$stats['today']}");
                
                sleep($interval);
                
            } catch (\Exception $e) {
                $this->error('Monitoring error: ' . $e->getMessage());
                Log::error('Deadlock monitoring continuous error', [
                    'error' => $e->getMessage()
                ]);
                
                // Wait before retrying
                sleep(min($interval, 300)); // Max 5 minutes
            }
        }
    }

    /**
     * Clean up old deadlock logs
     */
    private function cleanupOldLogs(): int
    {
        $this->info('Cleaning up old deadlock logs...');
        
        try {
            $deletedCount = $this->deadlockService->cleanupOldLogs(30);
            
            $this->info("Cleaned up {$deletedCount} old deadlock logs.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            Log::error('Deadlock cleanup failed', [
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
    }
}