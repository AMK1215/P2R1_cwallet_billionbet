<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TransactionLog;

echo "Checking transaction logs...\n";
echo "Total logs: " . TransactionLog::count() . "\n\n";

$logs = TransactionLog::all();
foreach ($logs as $log) {
    echo "ID: {$log->id}, Type: {$log->type}, Created: {$log->created_at}\n";
}

echo "\nTesting cleanup with 1 day retention...\n";
$cutoffDate = now()->subDays(1);
$oldLogs = TransactionLog::where('created_at', '<', $cutoffDate)->get();
echo "Logs older than 1 day: " . $oldLogs->count() . "\n";

foreach ($oldLogs as $log) {
    echo "  - ID: {$log->id}, Type: {$log->type}, Created: {$log->created_at}\n";
}
