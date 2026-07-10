<?php
/**
 * Test index.php without bootstrap (just functions)
 */

echo "Testing Index.php (without bootstrap)\n";
echo "=====================================\n\n";

// Load functions directly
require_once __DIR__ . '/functions.php';

// Simulate index.php logic
echo "1. Loading dashboards from data/dashboards.json\n";
$dashboards = load_dashboards();
echo "✅ Loaded " . count($dashboards) . " dashboards\n\n";

echo "2. Filtering public dashboards\n";
try {
    $publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
    echo "✅ Filtered " . count($publicDashboards) . " public dashboards\n\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "3. Extracting domains\n";
try {
    $domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
    echo "✅ Found " . count($domains) . " unique domains:\n";
    foreach ($domains as $domain) {
        echo "   - $domain\n";
    }
    echo "\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "4. Calculating statistics\n";
try {
    $updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
    $needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
    echo "✅ Updated today: $updatedToday\n";
    echo "✅ Need attention: $needAttention\n\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "5. Checking HTML escaping\n";
try {
    $test = e('<script>alert("xss")</script>');
    echo "✅ e() function works\n";
    echo "   Input:  '<script>alert(\"xss\")</script>'\n";
    echo "   Output: '$test'\n\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "6. Checking dashboard_icon\n";
try {
    $icon = dashboard_icon('Executive Intelligence');
    echo "✅ dashboard_icon() works: $icon\n\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "✅ All index.php logic working correctly!\n";
?>
