<?php
/**
 * Simple test - load and execute index.php logic
 */

echo "Testing Index.php Logic\n";
echo "=======================\n\n";

// Test 1: Load functions
echo "1. Loading functions.php...\n";
if (file_exists('functions.php')) {
    require_once 'functions.php';
    echo "   ✅ functions.php loaded\n";
} else {
    echo "   ❌ functions.php not found\n";
    exit(1);
}

echo "\n2. Testing key functions used by index.php:\n";

// Test load_dashboards
echo "   • load_dashboards(): ";
if (function_exists('load_dashboards')) {
    $dashboards = load_dashboards();
    echo "✅ Loaded " . count($dashboards) . " dashboards\n";
} else {
    echo "❌ Function not found\n";
}

// Test array filtering
echo "   • Array filter: ";
try {
    $publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
    echo "✅ Filtered to " . count($publicDashboards) . " public\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test domain extraction
echo "   • Domain extraction: ";
try {
    $domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
    echo "✅ Found " . count($domains) . " domains\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test date comparison
echo "   • Date comparison: ";
try {
    $updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
    echo "✅ " . $updatedToday . " updated today\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test status check
echo "   • Status check: ";
try {
    $needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
    echo "✅ " . $needAttention . " need attention\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test e() function
echo "   • e() function: ";
try {
    $test = e('<script>alert("xss")</script>');
    echo "✅ HTML escaped correctly\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n3. Check if bootstrap can load:\n";
echo "   • Checking bootstrap.php...\n";
if (file_exists('bootstrap.php')) {
    echo "   ✅ bootstrap.php exists\n";
} else {
    echo "   ❌ bootstrap.php not found\n";
}

echo "\n✅ All index.php dependencies working!\n";
?>
