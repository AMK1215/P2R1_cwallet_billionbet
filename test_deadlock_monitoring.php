<?php

require_once 'vendor/autoload.php';

use App\Services\DeadlockMonitoringService;
use App\Models\DeadlockLog;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PostgreSQL Deadlock Monitoring System Test ===\n\n";

// Test the DeadlockMonitoringService
$deadlockService = new DeadlockMonitoringService();

echo "1. Testing deadlock monitoring service...\n";
try {
    $deadlocks = $deadlockService->monitorDeadlocks();
    echo "   ✓ Monitoring service initialized successfully\n";
    echo "   ✓ Found " . count($deadlocks) . " potential deadlocks\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing deadlock statistics...\n";
try {
    $stats = $deadlockService->getStatistics();
    echo "   ✓ Statistics retrieved successfully\n";
    echo "   - Total deadlocks: " . $stats['total'] . "\n";
    echo "   - Unresolved: " . $stats['unresolved'] . "\n";
    echo "   - Today: " . $stats['today'] . "\n";
    echo "   - This week: " . $stats['this_week'] . "\n";
    echo "   - This month: " . $stats['this_month'] . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing deadlock trends...\n";
try {
    $trends = $deadlockService->getTrends(7);
    echo "   ✓ Trends retrieved successfully\n";
    echo "   - Last 7 days trend data: " . count($trends) . " data points\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing database connection...\n";
try {
    $deadlockCount = DeadlockLog::count();
    echo "   ✓ Database connection successful\n";
    echo "   - Current deadlock logs in database: " . $deadlockCount . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing manual deadlock logging...\n";
try {
    $testDeadlock = [
        'session_id' => 'test_session_' . time(),
        'process_id' => 'test_process_' . time(),
        'database_name' => 'test_db',
        'table_name' => 'test_table',
        'lock_type' => 'tuple',
        'query_text' => 'SELECT * FROM test_table WHERE id = 1',
        'error_code' => '40P01',
        'error_message' => 'Test deadlock for monitoring system',
        'severity' => 'ERROR',
        'user_name' => 'test_user',
        'application_name' => 'test_app',
        'client_addr' => '127.0.0.1',
        'duration_ms' => 1500,
        'is_resolved' => false,
    ];
    
    $loggedDeadlock = $deadlockService->logDeadlock($testDeadlock);
    echo "   ✓ Test deadlock logged successfully\n";
    echo "   - Deadlock ID: " . $loggedDeadlock->id . "\n";
    echo "   - Session ID: " . $loggedDeadlock->session_id . "\n";
    
    // Clean up test data
    $loggedDeadlock->delete();
    echo "   ✓ Test data cleaned up\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo access the deadlock monitoring UI:\n";
echo "1. Go to: /admin/logs/deadlock-logs\n";
echo "2. Or click 'Deadlock Monitoring' card in System Logs Dashboard\n";
echo "\nTo run continuous monitoring:\n";
echo "php artisan deadlock:monitor --interval=60\n";
echo "\nTo run once:\n";
echo "php artisan deadlock:monitor --once\n";
echo "\nTo cleanup old logs:\n";
echo "php artisan deadlock:monitor --cleanup\n";
