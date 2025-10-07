<?php

namespace App\Services;

use App\Models\TransactionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionLogCleanupService
{
    /**
     * Clean up old transaction logs
     */
    public function cleanupOldLogs(int $days = 3, int $batchSize = 1000): array
    {
        $cutoffDate = now()->subDays($days);
        
        try {
            // Get statistics before cleanup
            $beforeStats = $this->getCleanupStats($days);
            
            $totalDeleted = 0;
            $batchNumber = 1;
            
            Log::info('Starting transaction logs cleanup', [
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days,
                'batch_size' => $batchSize
            ]);
            
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

                Log::debug('Transaction logs cleanup batch', [
                    'batch_number' => $batchNumber,
                    'deleted_in_batch' => $deletedInBatch,
                    'total_deleted' => $totalDeleted
                ]);
                
                $batchNumber++;

                // Small delay to prevent overwhelming the database
                usleep(100000); // 0.1 second

            } while (count($idsToDelete) === $batchSize);
            
            // Get statistics after cleanup
            $afterStats = $this->getCleanupStats($days);
            
            Log::info('Transaction logs cleanup completed', [
                'total_deleted' => $totalDeleted,
                'before_count' => $beforeStats['total_records'],
                'after_count' => $afterStats['total_records'],
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days
            ]);
            
            return [
                'success' => true,
                'deleted_count' => $totalDeleted,
                'before_stats' => $beforeStats,
                'after_stats' => $afterStats,
                'cutoff_date' => $cutoffDate,
                'days_kept' => $days
            ];
            
        } catch (Exception $e) {
            Log::error('Transaction logs cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'deleted_count' => 0
            ];
        }
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
            'by_type' => TransactionLog::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'by_status' => TransactionLog::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
        ];
    }

    /**
     * Get cleanup preview (what would be deleted)
     */
    public function getCleanupPreview(int $days = 3, int $limit = 10): array
    {
        $cutoffDate = now()->subDays($days);
        
        $recordsToDelete = TransactionLog::where('created_at', '<', $cutoffDate)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'type', 'status', 'created_at']);
            
        return [
            'cutoff_date' => $cutoffDate,
            'days_kept' => $days,
            'total_to_delete' => TransactionLog::where('created_at', '<', $cutoffDate)->count(),
            'sample_records' => $recordsToDelete
        ];
    }

    /**
     * Archive old logs instead of deleting (optional feature)
     */
    public function archiveOldLogs(int $days = 3): array
    {
        $cutoffDate = now()->subDays($days);
        
        try {
            // This would require creating an archive table
            // For now, we'll just return the count of records that would be archived
            $recordsToArchive = TransactionLog::where('created_at', '<', $cutoffDate)->count();
            
            Log::info('Transaction logs archive preview', [
                'records_to_archive' => $recordsToArchive,
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days
            ]);
            
            return [
                'success' => true,
                'records_to_archive' => $recordsToArchive,
                'cutoff_date' => $cutoffDate,
                'days_kept' => $days,
                'note' => 'Archive functionality not implemented yet'
            ];
            
        } catch (Exception $e) {
            Log::error('Transaction logs archive failed', [
                'error' => $e->getMessage(),
                'cutoff_date' => $cutoffDate->toISOString(),
                'days_kept' => $days
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Optimize transaction logs table
     */
    public function optimizeTable(): array
    {
        try {
            // Get table size before optimization
            $beforeSize = $this->getTableSize();
            
            // Run VACUUM and ANALYZE on PostgreSQL
            DB::statement('VACUUM ANALYZE transaction_logs');
            
            // Get table size after optimization
            $afterSize = $this->getTableSize();
            
            Log::info('Transaction logs table optimized', [
                'before_size' => $beforeSize,
                'after_size' => $afterSize
            ]);
            
            return [
                'success' => true,
                'before_size' => $beforeSize,
                'after_size' => $afterSize,
                'space_saved' => 'Optimized'
            ];
            
        } catch (Exception $e) {
            Log::error('Transaction logs table optimization failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get table size information
     */
    private function getTableSize(): array
    {
        try {
            $result = DB::select("
                SELECT 
                    pg_size_pretty(pg_total_relation_size('transaction_logs')) as total_size,
                    pg_size_pretty(pg_relation_size('transaction_logs')) as table_size,
                    pg_size_pretty(pg_total_relation_size('transaction_logs') - pg_relation_size('transaction_logs')) as index_size
            ")[0];
            
            return [
                'total_size' => $result->total_size,
                'table_size' => $result->table_size,
                'index_size' => $result->index_size
            ];
            
        } catch (Exception $e) {
            return [
                'total_size' => 'Unknown',
                'table_size' => 'Unknown',
                'index_size' => 'Unknown'
            ];
        }
    }

    /**
     * Get cleanup history
     */
    public function getCleanupHistory(int $limit = 10): array
    {
        // This would require a cleanup_logs table to track cleanup history
        // For now, we'll return empty array
        return [
            'cleanups' => [],
            'note' => 'Cleanup history tracking not implemented yet'
        ];
    }
}
