<?php
// Super simple test
echo "Testing bootstrap loading...\n";

// Simulate web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_USER_AGENT'] = 'Test';

// Try to include bootstrap
try {
    include __DIR__ . '/bootstrap.php';
    echo "✅ Bootstrap loaded successfully\n";
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

// Try index logic
echo "\nTesting index logic...\n";
try {
    $dashboards = load_dashboards();
    echo "✅ load_dashboards worked\n";
    echo "   Count: " . count($dashboards) . "\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
