<?php

namespace App\Services;

use App\Models\DeadlockLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use PDO;

class DeadlockMonitoringService
{
    /**
     * Monitor PostgreSQL deadlocks by checking system logs
     */
    public function monitorDeadlocks(): array
    {
        $deadlocks = [];
        
        try {
            // Check PostgreSQL system logs for deadlock entries
            $deadlocks = $this->checkPostgreSQLLogs();
            
            // Log any new deadlocks found
            foreach ($deadlocks as $deadlock) {
                $this->logDeadlock($deadlock);
            }
            
        } catch (Exception $e) {
            Log::error('Deadlock monitoring failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $deadlocks;
    }

    /**
     * Check PostgreSQL system logs for deadlock entries
     */
    private function checkPostgreSQLLogs(): array
    {
        $deadlocks = [];
        
        try {
            // Query PostgreSQL system catalogs for deadlock information
            $query = "
                SELECT 
                    pg_stat_activity.pid,
                    pg_stat_activity.datname,
                    pg_stat_activity.usename,
                    pg_stat_activity.application_name,
                    pg_stat_activity.client_addr,
                    pg_stat_activity.query,
                    pg_stat_activity.query_start,
                    pg_stat_activity.state,
                    pg_stat_activity.wait_event_type,
                    pg_stat_activity.wait_event
                FROM pg_stat_activity 
                WHERE pg_stat_activity.state = 'active'
                AND pg_stat_activity.wait_event_type = 'Lock'
                AND pg_stat_activity.query NOT LIKE '%pg_stat_activity%'
                ORDER BY pg_stat_activity.query_start DESC
            ";
            
            $results = DB::select($query);
            
            // Analyze for potential deadlocks
            foreach ($results as $result) {
                if ($this->isPotentialDeadlock($result)) {
                    $deadlocks[] = $this->formatDeadlockData($result);
                }
            }
            
        } catch (Exception $e) {
            Log::error('Failed to check PostgreSQL logs', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $deadlocks;
    }

    /**
     * Check if a query result indicates a potential deadlock
     */
    private function isPotentialDeadlock($result): bool
    {
        // Check for lock wait events that might indicate deadlocks
        $deadlockIndicators = [
            'tuple',
            'transactionid',
            'relation',
            'extend',
            'page',
            'key',
            'advisory'
        ];
        
        return in_array($result->wait_event, $deadlockIndicators);
    }

    /**
     * Format deadlock data for logging
     */
    private function formatDeadlockData($result): array
    {
        return [
            'session_id' => $result->pid,
            'process_id' => $result->pid,
            'database_name' => $result->datname,
            'table_name' => $this->extractTableName($result->query),
            'lock_type' => $result->wait_event,
            'query_text' => $result->query,
            'error_code' => '40P01', // PostgreSQL deadlock error code
            'error_message' => 'Deadlock detected - process waiting for lock',
            'severity' => 'ERROR',
            'user_name' => $result->usename,
            'application_name' => $result->application_name,
            'client_addr' => $result->client_addr,
            'duration_ms' => $this->calculateDuration($result->query_start),
            'stack_trace' => $this->getStackTrace(),
            'is_resolved' => false,
        ];
    }

    /**
     * Extract table name from SQL query
     */
    private function extractTableName(string $query): ?string
    {
        // Simple regex to extract table names from common SQL patterns
        $patterns = [
            '/FROM\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
            '/UPDATE\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
            '/INSERT\s+INTO\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
            '/DELETE\s+FROM\s+([a-zA-Z_][a-zA-Z0-9_]*)/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    /**
     * Calculate duration in milliseconds
     */
    private function calculateDuration($queryStart): ?int
    {
        if (!$queryStart) {
            return null;
        }
        
        $start = is_string($queryStart) ? strtotime($queryStart) : $queryStart;
        $duration = time() - $start;
        
        return $duration * 1000; // Convert to milliseconds
    }

    /**
     * Get current stack trace
     */
    private function getStackTrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        return array_map(function ($item) {
            return [
                'file' => $item['file'] ?? null,
                'line' => $item['line'] ?? null,
                'function' => $item['function'] ?? null,
                'class' => $item['class'] ?? null,
            ];
        }, $trace);
    }

    /**
     * Log deadlock to database
     */
    public function logDeadlock(array $deadlockData): DeadlockLog
    {
        // Check if this deadlock is already logged (avoid duplicates)
        $existing = DeadlockLog::where('session_id', $deadlockData['session_id'])
            ->where('query_text', $deadlockData['query_text'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();
            
        if ($existing) {
            return $existing;
        }
        
        return DeadlockLog::create($deadlockData);
    }

    /**
     * Handle deadlock exception and log it
     */
    public function handleDeadlockException(Exception $exception, array $context = []): DeadlockLog
    {
        $deadlockData = [
            'error_code' => '40P01',
            'error_message' => $exception->getMessage(),
            'severity' => 'ERROR',
            'query_text' => $context['query'] ?? null,
            'table_name' => $context['table'] ?? null,
            'stack_trace' => $this->getStackTrace(),
            'is_resolved' => false,
            'resolved_by' => 'exception_handled',
        ];
        
        // Extract additional context if available
        if (isset($context['user_id'])) {
            $deadlockData['user_name'] = $context['user_id'];
        }
        
        if (isset($context['duration'])) {
            $deadlockData['duration_ms'] = $context['duration'];
        }
        
        return $this->logDeadlock($deadlockData);
    }

    /**
     * Get deadlock statistics
     */
    public function getStatistics(): array
    {
        return DeadlockLog::getStatistics();
    }

    /**
     * Get deadlock trends
     */
    public function getTrends(int $days = 30): array
    {
        return DeadlockLog::getTrends($days);
    }

    /**
     * Resolve deadlock manually
     */
    public function resolveDeadlock(int $deadlockId, string $resolvedBy = 'manual'): bool
    {
        $deadlock = DeadlockLog::find($deadlockId);
        
        if (!$deadlock) {
            return false;
        }
        
        $deadlock->markAsResolved($resolvedBy);
        
        Log::info('Deadlock resolved manually', [
            'deadlock_id' => $deadlockId,
            'resolved_by' => $resolvedBy,
        ]);
        
        return true;
    }

    /**
     * Clean up old deadlock logs
     */
    public function cleanupOldLogs(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        
        return DeadlockLog::where('created_at', '<', $cutoffDate)
            ->where('is_resolved', true)
            ->delete();
    }

    /**
     * Get active deadlocks (unresolved)
     */
    public function getActiveDeadlocks(): array
    {
        return DeadlockLog::unresolved()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }

    /**
     * Monitor deadlocks in real-time (for background job)
     */
    public function monitorRealTime(): void
    {
        $deadlocks = $this->monitorDeadlocks();
        
        if (!empty($deadlocks)) {
            Log::warning('Deadlocks detected', [
                'count' => count($deadlocks),
                'deadlocks' => $deadlocks
            ]);
        }
    }
}
