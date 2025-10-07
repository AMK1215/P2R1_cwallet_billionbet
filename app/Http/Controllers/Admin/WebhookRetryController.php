<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use App\Services\CustomWalletService;
use App\Services\ApiResponseService;
use App\Enums\SeamlessWalletCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class WebhookRetryController extends Controller
{
    protected CustomWalletService $customWalletService;

    public function __construct(CustomWalletService $customWalletService)
    {
        $this->customWalletService = $customWalletService;
    }

    /**
     * Retry a failed webhook request
     */
    public function retryWebhook(Request $request, $logId)
    {
        $request->validate([
            'retry_type' => 'required|in:full,partial,individual',
            'transaction_ids' => 'array|nullable',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $log = TransactionLog::findOrFail($logId);
            
            // Log the retry attempt
            Log::info('Webhook retry initiated', [
                'log_id' => $logId,
                'retry_type' => $request->retry_type,
                'retry_by' => auth()->user()->user_name,
                'reason' => $request->reason
            ]);

            $result = $this->processRetry($log, $request);

            return response()->json([
                'success' => true,
                'message' => 'Webhook retry completed successfully',
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Webhook retry failed', [
                'log_id' => $logId,
                'error' => $e->getMessage(),
                'retry_by' => auth()->user()->user_name
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook retry failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test a webhook request without processing
     */
    public function testWebhook(Request $request, $logId)
    {
        try {
            $log = TransactionLog::findOrFail($logId);
            
            // Simulate the webhook processing without actual balance changes
            $testResult = $this->simulateWebhookProcessing($log);

            return response()->json([
                'success' => true,
                'message' => 'Webhook test completed',
                'data' => $testResult
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Webhook test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get webhook retry history
     */
    public function getRetryHistory($logId)
    {
        $log = TransactionLog::findOrFail($logId);
        
        // Get retry attempts from logs
        $retryLogs = TransactionLog::where('batch_request->original_log_id', $logId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $retryLogs
        ]);
    }

    /**
     * Process the actual retry
     */
    private function processRetry(TransactionLog $log, Request $request)
    {
        $retryType = $request->retry_type;
        $originalRequest = $log->batch_request;
        $originalResponse = $log->response_data;

        DB::beginTransaction();
        try {
            switch ($retryType) {
                case 'full':
                    return $this->retryFullWebhook($log, $originalRequest);
                    
                case 'partial':
                    return $this->retryPartialWebhook($log, $originalRequest, $originalResponse);
                    
                case 'individual':
                    $transactionIds = $request->transaction_ids ?? [];
                    return $this->retryIndividualTransactions($log, $originalRequest, $transactionIds);
                    
                default:
                    throw new Exception('Invalid retry type');
            }
        } finally {
            DB::commit();
        }
    }

    /**
     * Retry the entire webhook
     */
    private function retryFullWebhook(TransactionLog $log, array $originalRequest)
    {
        // Add retry metadata
        $originalRequest['retry_metadata'] = [
            'original_log_id' => $log->id,
            'retry_type' => 'full',
            'retry_by' => auth()->user()->user_name,
            'retry_at' => now()->toISOString()
        ];

        // Process based on webhook type
        switch ($log->type) {
            case 'deposit':
                return $this->processDepositRetry($originalRequest);
            case 'withdraw':
                return $this->processWithdrawRetry($originalRequest);
            case 'balance':
                return $this->processBalanceRetry($originalRequest);
            default:
                throw new Exception('Unsupported webhook type for retry');
        }
    }

    /**
     * Retry only failed transactions
     */
    private function retryPartialWebhook(TransactionLog $log, array $originalRequest, array $originalResponse)
    {
        $failedTransactions = [];
        
        // Identify failed transactions
        if (isset($originalResponse['data'])) {
            foreach ($originalResponse['data'] as $index => $response) {
                if (isset($response['code']) && $response['code'] !== 0) {
                    $failedTransactions[] = $originalRequest['batch_requests'][$index] ?? null;
                }
            }
        }

        if (empty($failedTransactions)) {
            throw new Exception('No failed transactions found to retry');
        }

        // Create new request with only failed transactions
        $retryRequest = $originalRequest;
        $retryRequest['batch_requests'] = array_filter($failedTransactions);
        $retryRequest['retry_metadata'] = [
            'original_log_id' => $log->id,
            'retry_type' => 'partial',
            'retry_by' => auth()->user()->user_name,
            'retry_at' => now()->toISOString()
        ];

        return $this->retryFullWebhook($log, $retryRequest);
    }

    /**
     * Retry individual transactions
     */
    private function retryIndividualTransactions(TransactionLog $log, array $originalRequest, array $transactionIds)
    {
        $selectedTransactions = [];
        
        foreach ($transactionIds as $id) {
            if (isset($originalRequest['batch_requests'][$id])) {
                $selectedTransactions[] = $originalRequest['batch_requests'][$id];
            }
        }

        if (empty($selectedTransactions)) {
            throw new Exception('No valid transactions selected for retry');
        }

        $retryRequest = $originalRequest;
        $retryRequest['batch_requests'] = $selectedTransactions;
        $retryRequest['retry_metadata'] = [
            'original_log_id' => $log->id,
            'retry_type' => 'individual',
            'retry_by' => auth()->user()->user_name,
            'retry_at' => now()->toISOString()
        ];

        return $this->retryFullWebhook($log, $retryRequest);
    }

    /**
     * Process deposit retry
     */
    private function processDepositRetry(array $request)
    {
        // Use the existing DepositController logic
        $depositController = new \App\Http\Controllers\Api\V1\gplus\Webhook\DepositController($this->customWalletService);
        
        // Create a new request object
        $newRequest = new Request($request);
        
        return $depositController->deposit($newRequest);
    }

    /**
     * Process withdraw retry
     */
    private function processWithdrawRetry(array $request)
    {
        // Use the existing WithdrawController logic
        $withdrawController = new \App\Http\Controllers\Api\V1\gplus\Webhook\WithdrawController($this->customWalletService);
        
        // Create a new request object
        $newRequest = new Request($request);
        
        return $withdrawController->withdraw($newRequest);
    }

    /**
     * Process balance retry
     */
    private function processBalanceRetry(array $request)
    {
        // Use the existing GetBalanceController logic
        $balanceController = new \App\Http\Controllers\Api\V1\gplus\Webhook\GetBalanceController();
        
        // Create a new request object
        $newRequest = new Request($request);
        
        return $balanceController->getBalance($newRequest);
    }

    /**
     * Simulate webhook processing for testing
     */
    private function simulateWebhookProcessing(TransactionLog $log)
    {
        $simulation = [
            'log_id' => $log->id,
            'type' => $log->type,
            'simulation_results' => [],
            'warnings' => [],
            'errors' => []
        ];

        // Simulate processing each transaction
        if (isset($log->batch_request['batch_requests'])) {
            foreach ($log->batch_request['batch_requests'] as $index => $transaction) {
                $simulation['simulation_results'][] = [
                    'transaction_index' => $index,
                    'member_account' => $transaction['member_account'] ?? 'N/A',
                    'amount' => $transaction['amount'] ?? 0,
                    'simulated_result' => 'success', // This would be more complex in reality
                    'balance_check' => $this->checkUserBalance($transaction['member_account'] ?? ''),
                    'validation_result' => $this->validateTransaction($transaction)
                ];
            }
        }

        return $simulation;
    }

    /**
     * Check user balance for simulation
     */
    private function checkUserBalance($memberAccount)
    {
        try {
            $user = \App\Models\User::where('user_name', $memberAccount)->first();
            return [
                'user_found' => $user !== null,
                'current_balance' => $user ? $user->balance : 0,
                'user_status' => $user ? $user->status : 'not_found'
            ];
        } catch (Exception $e) {
            return [
                'user_found' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate transaction for simulation
     */
    private function validateTransaction($transaction)
    {
        $validation = [
            'valid' => true,
            'errors' => []
        ];

        // Check required fields
        if (empty($transaction['member_account'])) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Member account is required';
        }

        if (empty($transaction['amount']) || $transaction['amount'] <= 0) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Valid amount is required';
        }

        return $validation;
    }
}
