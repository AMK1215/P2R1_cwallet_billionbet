<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TransactionLog;

echo "Creating test transaction logs...\n";

// Create some test logs with different ages
$testLogs = [
    ['type' => 'deposit', 'status' => 'success', 'days_ago' => 1],
    ['type' => 'withdraw', 'status' => 'success', 'days_ago' => 2],
    ['type' => 'deposit', 'status' => 'failure', 'days_ago' => 4],
    ['type' => 'withdraw', 'status' => 'success', 'days_ago' => 5],
    ['type' => 'balance', 'status' => 'success', 'days_ago' => 6],
    ['type' => 'deposit', 'status' => 'success', 'days_ago' => 7],
];

foreach ($testLogs as $logData) {
    $log = TransactionLog::create([
        'type' => $logData['type'],
        'batch_request' => [
            'user_id' => rand(1, 100),
            'amount' => rand(100, 10000),
            'currency' => 'USD'
        ],
        'response_data' => [
            'status' => $logData['status'],
            'transaction_id' => 'TXN_' . rand(100000, 999999),
            'timestamp' => now()->toISOString()
        ],
        'status' => $logData['status'],
        'created_at' => now()->subDays($logData['days_ago']),
        'updated_at' => now()->subDays($logData['days_ago'])
    ]);
    
    echo "Created log ID {$log->id} - Type: {$logData['type']}, Status: {$logData['status']}, Days ago: {$logData['days_ago']}\n";
}

echo "\nTest data created successfully!\n";
echo "Total logs in database: " . TransactionLog::count() . "\n";
echo "\nNow you can test the cleanup command:\n";
echo "php artisan logs:cleanup-transaction --dry-run --days=3\n";
