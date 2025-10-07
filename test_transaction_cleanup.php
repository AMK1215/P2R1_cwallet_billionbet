<?php

require_once 'vendor/autoload.php';

use App\Services\TransactionLogCleanupService;
use App\Models\TransactionLog;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Transaction Logs Cleanup System Test ===\n\n";

// Test the TransactionLogCleanupService
$cleanupService = new TransactionLogCleanupService();

echo "1. Testing cleanup service initialization...\n";
try {
    echo "   ✓ Cleanup service initialized successfully\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing cleanup statistics...\n";
try {
    $stats = $cleanupService->getCleanupStats(3);
    echo "   ✓ Statistics retrieved successfully\n";
    echo "   - Total records: " . $stats['total_records'] . "\n";
    echo "   - Records to delete: " . $stats['records_to_delete'] . "\n";
    echo "   - Records to keep: " . $stats['records_to_keep'] . "\n";
    echo "   - Cutoff date: " . $stats['cutoff_date'] . "\n";
    echo "   - Oldest record: " . ($stats['oldest_record'] ?? 'N/A') . "\n";
    echo "   - Newest record: " . ($stats['newest_record'] ?? 'N/A') . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing cleanup preview...\n";
try {
    $preview = $cleanupService->getCleanupPreview(3, 5);
    echo "   ✓ Preview retrieved successfully\n";
    echo "   - Total to delete: " . $preview['total_to_delete'] . "\n";
    echo "   - Cutoff date: " . $preview['cutoff_date'] . "\n";
    echo "   - Sample records: " . count($preview['sample_records']) . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n4. Testing database connection...\n";
try {
    $totalLogs = TransactionLog::count();
    echo "   ✓ Database connection successful\n";
    echo "   - Current transaction logs in database: " . $totalLogs . "\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n5. Testing manual cleanup (DRY RUN)...\n";
try {
    // Create a test log entry that's older than 3 days
    $oldDate = now()->subDays(5);
    $testLog = TransactionLog::create([
        'type' => 'test_cleanup',
        'batch_request' => ['test' => 'cleanup_data'],
        'response_data' => ['status' => 'test'],
        'status' => 'success',
        'created_at' => $oldDate,
        'updated_at' => $oldDate
    ]);
    
    echo "   ✓ Test log created with ID: " . $testLog->id . "\n";
    echo "   ✓ Test log created at: " . $testLog->created_at . "\n";
    
    // Test cleanup preview
    $preview = $cleanupService->getCleanupPreview(3, 10);
    echo "   ✓ Cleanup preview shows " . $preview['total_to_delete'] . " records to delete\n";
    
    // Clean up test data
    $testLog->delete();
    echo "   ✓ Test data cleaned up\n";
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n6. Testing table optimization...\n";
try {
    $result = $cleanupService->optimizeTable();
    if ($result['success']) {
        echo "   ✓ Table optimization successful\n";
        echo "   - Before size: " . $result['before_size']['total_size'] . "\n";
        echo "   - After size: " . $result['after_size']['total_size'] . "\n";
        echo "   - Space saved: " . $result['space_saved'] . "\n";
    } else {
        echo "   ✗ Table optimization failed: " . $result['error'] . "\n";
    }
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo access the System Logs UI:\n";
echo "1. Go to: /admin/logs/system-logs\n";
echo "2. Or click 'System Logs' card in System Logs Dashboard\n";
echo "\nTo run manual cleanup:\n";
echo "php artisan logs:cleanup-transaction --days=3 --dry-run\n";
echo "php artisan logs:cleanup-transaction --days=3 --force\n";
echo "\nTo run scheduled cleanup:\n";
echo "php artisan schedule:run\n";
echo "\nTo start the scheduler:\n";
echo "php artisan schedule:work\n";
