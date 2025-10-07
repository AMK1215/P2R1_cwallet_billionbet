<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TransactionLog;

echo "Creating proper old test transaction logs...\n";

// Clear existing test data
TransactionLog::whereIn('type', ['deposit', 'withdraw', 'balance', 'old_test'])->delete();
echo "Cleared existing test data\n";

// Create some test logs with different ages (older than 3 days)
$testLogs = [
    ['type' => 'deposit', 'status' => 'success', 'days_ago' => 4],
    ['type' => 'withdraw', 'status' => 'success', 'days_ago' => 5],
    ['type' => 'deposit', 'status' => 'failure', 'days_ago' => 6],
    ['type' => 'withdraw', 'status' => 'success', 'days_ago' => 7],
    ['type' => 'balance', 'status' => 'success', 'days_ago' => 8],
    ['type' => 'deposit', 'status' => 'success', 'days_ago' => 10],
];

foreach ($testLogs as $logData) {
    $createdAt = now()->subDays($logData['days_ago']);
    
    // Use DB::table to bypass model timestamps
    $id = \DB::table('transaction_logs')->insertGetId([
        'type' => $logData['type'],
        'batch_request' => json_encode([
            'user_id' => rand(1, 100),
            'amount' => rand(100, 10000),
            'currency' => 'USD'
        ]),
        'response_data' => json_encode([
            'status' => $logData['status'],
            'transaction_id' => 'TXN_' . rand(100000, 999999),
            'timestamp' => $createdAt->toISOString()
        ]),
        'status' => $logData['status'],
        'created_at' => $createdAt,
        'updated_at' => $createdAt
    ]);
    
    echo "Created log ID {$id} - Type: {$logData['type']}, Status: {$logData['status']}, Days ago: {$logData['days_ago']}, Created: {$createdAt}\n";
}

echo "\nProper old test data created successfully!\n";
echo "Total logs in database: " . TransactionLog::count() . "\n";

// Check how many would be deleted with 3-day retention
$cutoffDate = now()->subDays(3);
$logsToDelete = TransactionLog::where('created_at', '<', $cutoffDate)->count();
echo "Logs older than 3 days: {$logsToDelete}\n";

echo "\nNow you can test the cleanup command:\n";
echo "php artisan logs:cleanup-transaction --dry-run --days=3\n";
