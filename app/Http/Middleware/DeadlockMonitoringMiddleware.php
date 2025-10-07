<?php

namespace App\Http\Middleware;

use App\Services\DeadlockMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class DeadlockMonitoringMiddleware
{
    protected DeadlockMonitoringService $deadlockService;

    public function __construct(DeadlockMonitoringService $deadlockService)
    {
        $this->deadlockService = $deadlockService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (QueryException $e) {
            // Check if this is a deadlock error
            if ($this->isDeadlockError($e)) {
                $this->handleDeadlockException($e, $request);
            }
            
            throw $e; // Re-throw the exception
        }
    }

    /**
     * Check if the exception is a deadlock error
     */
    private function isDeadlockError(QueryException $e): bool
    {
        $errorCode = $e->getCode();
        
        // PostgreSQL deadlock error codes
        $deadlockCodes = [
            '40P01', // deadlock_detected
            '40001', // serialization_failure
        ];
        
        return in_array($errorCode, $deadlockCodes) || 
               str_contains(strtolower($e->getMessage()), 'deadlock');
    }

    /**
     * Handle deadlock exception by logging it
     */
    private function handleDeadlockException(QueryException $e, Request $request): void
    {
        try {
            $context = [
                'query' => $e->getSql() ?? null,
                'user_id' => auth()->id(),
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'duration' => microtime(true) - LARAVEL_START,
            ];
            
            $this->deadlockService->handleDeadlockException($e, $context);
            
        } catch (\Exception $logException) {
            // Don't let logging errors break the main flow
            \Log::error('Failed to log deadlock exception', [
                'original_error' => $e->getMessage(),
                'logging_error' => $logException->getMessage()
            ]);
        }
    }
}
