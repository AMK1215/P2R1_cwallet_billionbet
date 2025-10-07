<?php

namespace App\Console\Commands;

use App\Models\TransactionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupTransactionLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup-transaction 
                            {--days=3 : Number of days to keep transaction logs}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force deletion without confirmation}
                            {--batch-size=1000 : Number of records to delete in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old transaction logs to improve database performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $batchSize = (int) $this->option('batch-size');

        $cutoffDate = now()->subDays($days);

        $this->info("=== Transaction Logs Cleanup ===");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");
        $this->info("Days to keep: {$days}");
        $this->info("Batch size: {$batchSize}");

        // Get count of records to be deleted
        $totalRecords = TransactionLog::where('created_at', '<', $cutoffDate)->count();
        
        if ($totalRecords === 0) {
            $this->info("No transaction logs older than {$days} days found.");
            return 0;
        }

        $this->warn("Found {$totalRecords} transaction logs to delete.");

        if ($dryRun) {
            $this->info("DRY RUN MODE - No records will be deleted.");
            $this->showSampleRecords($cutoffDate);
            return 0;
        }

        if (!$force) {
            if (!$this->confirm("Are you sure you want to delete {$totalRecords} transaction logs?")) {
                $this->info("Cleanup cancelled.");
                return 0;
            }
        }

        $this->info("Starting cleanup process...");
        
        try {
            $deletedCount = $this->performCleanup($cutoffDate, $batchSize);
            
            $this->info("Cleanup completed successfully!");
            $this->info("Deleted {$deletedCount} transaction logs.");
            
            // Log the cleanup action
            Log::info('Transaction logs cleanup completed', [
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days,
                'executed_by' => 'console_command'
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Cleanup failed: " . $e->getMessage());
            Log::error('Transaction logs cleanup failed', [
                'error' => $e->getMessage(),
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days
            ]);
            
            return 1;
        }
    }

    /**
     * Perform the actual cleanup in batches
     */
    private function performCleanup($cutoffDate, $batchSize): int
    {
        $totalDeleted = 0;
        $batchNumber = 1;

        $this->info("Processing in batches of {$batchSize}...");

        do {
            // Get IDs of records to delete in this batch
            $idsToDelete = TransactionLog::where('created_at', '<', $cutoffDate)
                ->limit($batchSize)
                ->pluck('id')
                ->toArray();

            if (empty($idsToDelete)) {
                break;
            }

            // Delete the batch
            $deletedInBatch = TransactionLog::whereIn('id', $idsToDelete)->delete();
            $totalDeleted += $deletedInBatch;

            $this->line("Batch {$batchNumber}: Deleted {$deletedInBatch} records (Total: {$totalDeleted})");
            
            $batchNumber++;

            // Small delay to prevent overwhelming the database
            usleep(100000); // 0.1 second

        } while (count($idsToDelete) === $batchSize);

        return $totalDeleted;
    }

    /**
     * Show sample records that would be deleted
     */
    private function showSampleRecords($cutoffDate): void
    {
        $this->info("\nSample records that would be deleted:");
        
        $sampleRecords = TransactionLog::where('created_at', '<', $cutoffDate)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'type', 'status', 'created_at']);

        if ($sampleRecords->isEmpty()) {
            $this->info("No sample records found.");
            return;
        }

        $headers = ['ID', 'Type', 'Status', 'Created At'];
        $rows = [];

        foreach ($sampleRecords as $record) {
            $rows[] = [
                $record->id,
                $record->type,
                $record->status,
                $record->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Get cleanup statistics
     */
    public function getCleanupStats(int $days = 3): array
    {
        $cutoffDate = now()->subDays($days);
        
        return [
            'total_records' => TransactionLog::count(),
            'records_to_delete' => TransactionLog::where('created_at', '<', $cutoffDate)->count(),
            'records_to_keep' => TransactionLog::where('created_at', '>=', $cutoffDate)->count(),
            'cutoff_date' => $cutoffDate,
            'days_kept' => $days,
            'oldest_record' => TransactionLog::orderBy('created_at')->value('created_at'),
            'newest_record' => TransactionLog::orderBy('created_at', 'desc')->value('created_at'),
        ];
    }
}