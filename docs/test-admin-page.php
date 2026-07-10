<?php
/**
 * Test the actual admin.php functionality without session auth
 */

// Mock session for testing
$_SESSION = [
    'logged_in' => true,
    'username' => 'test_admin'
];

// Mock auth.php to prevent redirect
define('AUTH_BYPASSED', true);

// Set request method to GET to avoid processing POST
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Capture output
ob_start();

// Include the admin.php file
try {
    include __DIR__ . '/admin.php';
    $output = ob_get_clean();
    
    // Check if admin page loaded successfully
    if (strpos($output, 'Admin - UGM Intelligence Space') !== false) {
        echo "✅ Admin page loaded successfully!\n";
        echo "✅ No 500 error detected\n";
        echo "✅ Page contains expected content\n";
    } else {
        echo "⚠️  Admin page loaded but content not as expected\n";
    }
    
    // Check for fatal errors
    if (strpos($output, 'Fatal error') !== false || 
        strpos($output, 'Uncaught') !== false ||
        strpos($output, 'Call to undefined function') !== false) {
        echo "❌ Fatal error detected in output\n";
        echo "Output: " . substr($output, 0, 500) . "...\n";
    } else {
        echo "✅ No fatal errors in page output\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception caught: " . $e->getMessage() . "\n";
    ob_end_clean();
}

echo "\n✅ Admin panel fix verification complete!\n";
?>
