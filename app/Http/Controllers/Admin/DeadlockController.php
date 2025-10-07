<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeadlockLog;
use App\Services\DeadlockMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeadlockController extends Controller
{
    protected DeadlockMonitoringService $deadlockService;

    public function __construct(DeadlockMonitoringService $deadlockService)
    {
        $this->deadlockService = $deadlockService;
    }

    /**
     * Display deadlock logs with filtering
     */
    public function index(Request $request)
    {
        $query = DeadlockLog::orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'resolved') {
                $query->resolved();
            } elseif ($request->status === 'unresolved') {
                $query->unresolved();
            }
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('table_name')) {
            $query->where('table_name', 'like', '%' . $request->table_name . '%');
        }

        if ($request->filled('error_code')) {
            $query->where('error_code', $request->error_code);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_name')) {
            $query->where('user_name', 'like', '%' . $request->user_name . '%');
        }

        $deadlocks = $query->paginate(50);

        // Get filter options
        $severities = DeadlockLog::distinct()->pluck('severity');
        $errorCodes = DeadlockLog::distinct()->pluck('error_code');
        $tableNames = DeadlockLog::whereNotNull('table_name')->distinct()->pluck('table_name');
        $userNames = DeadlockLog::whereNotNull('user_name')->distinct()->pluck('user_name');

        return view('admin.logs.deadlock_logs', compact(
            'deadlocks',
            'severities',
            'errorCodes',
            'tableNames',
            'userNames'
        ));
    }

    /**
     * Show detailed deadlock information
     */
    public function show($id)
    {
        $deadlock = DeadlockLog::findOrFail($id);
        
        return view('admin.logs.deadlock_detail', compact('deadlock'));
    }

    /**
     * Get deadlock statistics
     */
    public function getStatistics()
    {
        $stats = $this->deadlockService->getStatistics();
        
        return response()->json($stats);
    }

    /**
     * Get deadlock trends
     */
    public function getTrends(Request $request)
    {
        $days = $request->get('days', 30);
        $trends = $this->deadlockService->getTrends($days);
        
        return response()->json($trends);
    }

    /**
     * Resolve a deadlock manually
     */
    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolved_by' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        $success = $this->deadlockService->resolveDeadlock(
            $id, 
            $request->resolved_by
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Deadlock resolved successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to resolve deadlock'
        ], 404);
    }

    /**
     * Manually trigger deadlock monitoring
     */
    public function monitor()
    {
        try {
            $deadlocks = $this->deadlockService->monitorDeadlocks();
            
            return response()->json([
                'success' => true,
                'message' => 'Deadlock monitoring completed',
                'deadlocks_found' => count($deadlocks)
            ]);
        } catch (\Exception $e) {
            Log::error('Manual deadlock monitoring failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Deadlock monitoring failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active deadlocks
     */
    public function getActive()
    {
        $activeDeadlocks = $this->deadlockService->getActiveDeadlocks();
        
        return response()->json([
            'success' => true,
            'data' => $activeDeadlocks
        ]);
    }

    /**
     * Clean up old deadlock logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $deletedCount = $this->deadlockService->cleanupOldLogs($request->days);
        
        return response()->json([
            'success' => true,
            'message' => "Cleaned up {$deletedCount} old deadlock logs",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * Export deadlock logs to CSV
     */
    public function export(Request $request)
    {
        $query = DeadlockLog::orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('status')) {
            if ($request->status === 'resolved') {
                $query->resolved();
            } elseif ($request->status === 'unresolved') {
                $query->unresolved();
            }
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $deadlocks = $query->get();

        $filename = 'deadlock_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($deadlocks) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Session ID', 'Process ID', 'Database', 'Table', 'Lock Type',
                'Error Code', 'Error Message', 'Severity', 'User', 'Application',
                'Client IP', 'Duration (ms)', 'Status', 'Resolved By', 'Resolved At',
                'Created At'
            ]);

            // CSV data
            foreach ($deadlocks as $deadlock) {
                fputcsv($file, [
                    $deadlock->id,
                    $deadlock->session_id,
                    $deadlock->process_id,
                    $deadlock->database_name,
                    $deadlock->table_name,
                    $deadlock->lock_type,
                    $deadlock->error_code,
                    $deadlock->error_message,
                    $deadlock->severity,
                    $deadlock->user_name,
                    $deadlock->application_name,
                    $deadlock->client_addr,
                    $deadlock->duration_ms,
                    $deadlock->status_text,
                    $deadlock->resolved_by,
                    $deadlock->resolved_at?->format('Y-m-d H:i:s'),
                    $deadlock->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get deadlock dashboard data
     */
    public function dashboard()
    {
        $stats = $this->deadlockService->getStatistics();
        $trends = $this->deadlockService->getTrends(30);
        $activeDeadlocks = $this->deadlockService->getActiveDeadlocks();
        
        return view('admin.logs.deadlock_dashboard', compact(
            'stats',
            'trends',
            'activeDeadlocks'
        ));
    }
}
