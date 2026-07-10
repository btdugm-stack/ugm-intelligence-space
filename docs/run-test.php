#!/usr/bin/env php
<?php
/**
 * Direct PHP test - No output buffering issues
 */

echo "Direct PHP Test\n";
echo "===============\n\n";

// Setup server vars
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['REQUEST_URI'] = '/';

echo "Step 1: Include config/environment.php\n";
try {
    require __DIR__ . '/config/environment.php';
    echo "✅ Loaded\n";
    echo "   APP_ENV = " . APP_ENV . "\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 2: Include functions.php\n";
try {
    require __DIR__ . '/functions.php';
    echo "✅ Loaded\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 3: Start session\n";
try {
    if (function_exists('init_session')) {
        init_session();
        echo "✅ Session initialized\n";
    } else {
        echo "⚠️  init_session not found\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nStep 4: Apply security headers\n";
try {
    if (function_exists('apply_security_headers')) {
        apply_security_headers();
        echo "✅ Headers applied\n";
    } else {
        echo "⚠️  apply_security_headers not found\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nStep 5: Load dashboards\n";
try {
    $dashboards = load_dashboards();
    echo "✅ Loaded " . count($dashboards) . " dashboards\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 6: Run index logic\n";
try {
    $publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
    echo "✅ Public dashboards: " . count($publicDashboards) . "\n";
    
    $domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
    echo "✅ Domains: " . count($domains) . "\n";
    
    $updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
    echo "✅ Updated today: " . $updatedToday . "\n";
    
    $needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
    echo "✅ Need attention: " . $needAttention . "\n";
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ ALL TESTS PASSED!\n";
?>
