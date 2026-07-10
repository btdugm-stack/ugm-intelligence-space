<?php
/**
 * Simulate index.php execution
 */

// Set up basic server vars for testing
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Test';

// Suppress output initially
ob_start();

try {
    // Include bootstrap
    if (!file_exists(__DIR__ . '/bootstrap.php')) {
        throw new Exception("bootstrap.php not found");
    }
    require_once __DIR__ . '/bootstrap.php';
    
    // Execute index.php logic
    $dashboards = load_dashboards();
    $publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
    $domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
    $updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
    $needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
    
    $output = ob_get_clean();
    
    echo "✅ Index.php logic executed successfully!\n\n";
    echo "Results:\n";
    echo "  • Total dashboards: " . count($dashboards) . "\n";
    echo "  • Public dashboards: " . count($publicDashboards) . "\n";
    echo "  • Domains: " . count($domains) . "\n";
    echo "  • Updated today: " . $updatedToday . "\n";
    echo "  • Need attention: " . $needAttention . "\n";
    echo "\n✅ No errors detected!\n";
    
} catch (Throwable $e) {
    ob_end_clean();
    echo "❌ Error detected:\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
