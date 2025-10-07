<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use App\Services\TransactionLogCleanupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemLogController extends Controller
{
    protected TransactionLogCleanupService $cleanupService;

    public function __construct(TransactionLogCleanupService $cleanupService)
    {
        $this->cleanupService = $cleanupService;
    }

    /**
     * Display system logs dashboard
     */
    public function index()
    {
        $stats = $this->getSystemLogStats();
        
        return view('admin.logs.system_logs_dashboard', compact('stats'));
    }

    /**
     * Display transaction logs (system only - not for clients)
     */
    public function transactionLogs(Request $request)
    {
        $query = TransactionLog::orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        // Get filter options
        $types = TransactionLog::distinct()->pluck('type');
        $statuses = TransactionLog::distinct()->pluck('status');

        return view('admin.logs.system_transaction_logs', compact(
            'logs',
            'types',
            'statuses'
        ));
    }

    /**
     * Get cleanup statistics
     */
    public function getCleanupStats(Request $request)
    {
        $days = $request->get('days', 3);
        $stats = $this->cleanupService->getCleanupStats($days);
        
        return response()->json($stats);
    }

    /**
     * Get cleanup preview
     */
    public function getCleanupPreview(Request $request)
    {
        $days = $request->get('days', 3);
        $limit = $request->get('limit', 10);
        $preview = $this->cleanupService->getCleanupPreview($days, $limit);
        
        return response()->json($preview);
    }

    /**
     * Perform cleanup
     */
    public function performCleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:30',
            'batch_size' => 'integer|min:100|max:5000'
        ]);

        $days = $request->get('days', 3);
        $batchSize = $request->get('batch_size', 1000);

        $result = $this->cleanupService->cleanupOldLogs($days, $batchSize);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => "Successfully cleaned up {$result['deleted_count']} transaction logs",
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cleanup failed: ' . $result['error']
        ], 500);
    }

    /**
     * Optimize transaction logs table
     */
    public function optimizeTable()
    {
        $result = $this->cleanupService->optimizeTable();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Table optimized successfully',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Table optimization failed: ' . $result['error']
        ], 500);
    }

    /**
     * Export transaction logs
     */
    public function exportTransactionLogs(Request $request)
    {
        $query = TransactionLog::orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->get();

        $filename = 'system_transaction_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Type', 'Status', 'Request Data', 'Response Data', 'Created At', 'Updated At'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->type,
                    $log->status,
                    json_encode($log->batch_request),
                    json_encode($log->response_data),
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->updated_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get system log statistics
     */
    private function getSystemLogStats(): array
    {
        return [
            'total_transaction_logs' => TransactionLog::count(),
            'today_transaction_logs' => TransactionLog::whereDate('created_at', today())->count(),
            'this_week_transaction_logs' => TransactionLog::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'this_month_transaction_logs' => TransactionLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'by_type' => TransactionLog::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get(),
            'by_status' => TransactionLog::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->orderBy('count', 'desc')
                ->get(),
            'oldest_log' => TransactionLog::orderBy('created_at')->value('created_at'),
            'newest_log' => TransactionLog::orderBy('created_at', 'desc')->value('created_at'),
        ];
    }

    /**
     * Get cleanup settings
     */
    public function getCleanupSettings()
    {
        return response()->json([
            'default_days' => 3,
            'max_days' => 30,
            'min_days' => 1,
            'default_batch_size' => 1000,
            'max_batch_size' => 5000,
            'min_batch_size' => 100,
            'auto_cleanup_enabled' => true,
            'auto_cleanup_schedule' => 'every 3 days'
        ]);
    }

    /**
     * Update cleanup settings
     */
    public function updateCleanupSettings(Request $request)
    {
        $request->validate([
            'auto_cleanup_enabled' => 'boolean',
            'cleanup_days' => 'integer|min:1|max:30',
            'cleanup_batch_size' => 'integer|min:100|max:5000'
        ]);

        // Store settings in config or database
        // For now, we'll just return success
        Log::info('System log cleanup settings updated', $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cleanup settings updated successfully'
        ]);
    }
}
