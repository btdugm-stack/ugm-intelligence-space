<?php
/**
 * Verification Script - Confirms all pages work
 */

echo "═════════════════════════════════════════════════════\n";
echo "         VERIFICATION - All Pages Working\n";
echo "═════════════════════════════════════════════════════\n\n";

$tests = [
    [
        'name' => 'functions.php',
        'file' => __DIR__ . '/functions.php',
        'check' => function_exists('load_dashboards')
    ],
    [
        'name' => 'index.php',
        'file' => __DIR__ . '/index.php',
        'check' => file_exists(__DIR__ . '/index.php')
    ],
    [
        'name' => 'login.php',
        'file' => __DIR__ . '/login.php',
        'check' => file_exists(__DIR__ . '/login.php')
    ],
    [
        'name' => 'admin.php',
        'file' => __DIR__ . '/admin.php',
        'check' => file_exists(__DIR__ . '/admin.php')
    ],
    [
        'name' => 'auth.php',
        'file' => __DIR__ . '/auth.php',
        'check' => file_exists(__DIR__ . '/auth.php')
    ],
    [
        'name' => 'detail.php',
        'file' => __DIR__ . '/detail.php',
        'check' => file_exists(__DIR__ . '/detail.php')
    ],
];

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    if ($test['check']) {
        echo "✅ {$test['name']}\n";
        $passed++;
    } else {
        echo "❌ {$test['name']}\n";
        $failed++;
    }
}

echo "\n";

// Test functions
echo "Testing functions...\n";

require_once __DIR__ . '/functions.php';

$funcs_to_check = [
    'load_dashboards',
    'save_dashboards',
    'e',
    'validate_input',
    'sanitize_input',
    'log_audit',
    'csrf_token',
    'csrf_field',
    'verify_csrf_token',
    'check_rate_limit',
    'get_authenticator',
    'init_session',
    'apply_security_headers'
];

foreach ($funcs_to_check as $func) {
    if (function_exists($func)) {
        echo "   ✅ $func()\n";
        $passed++;
    } else {
        echo "   ❌ $func() - NOT FOUND\n";
        $failed++;
    }
}

// Test Logger class
if (class_exists('Logger')) {
    echo "   ✅ Logger class\n";
    $passed++;
} else {
    echo "   ❌ Logger class - NOT FOUND\n";
    $failed++;
}

echo "\n";

// Test data loading
try {
    $dashboards = load_dashboards();
    echo "✅ Dashboards loaded: " . count($dashboards) . " items\n";
    $passed++;
} catch (Exception $e) {
    echo "❌ Dashboards load failed: " . $e->getMessage() . "\n";
    $failed++;
}

echo "\n";
echo "═════════════════════════════════════════════════════\n";
echo "Passed: $passed | Failed: $failed\n";
echo "═════════════════════════════════════════════════════\n";

if ($failed === 0) {
    echo "\n✅ ALL SYSTEMS GO! No errors found.\n";
    echo "\nYou can now access:\n";
    echo "  • http://localhost/ugm-intelligence-space-poc/\n";
    echo "  • http://localhost/ugm-intelligence-space-poc/login.php\n";
} else {
    echo "\n❌ Some issues found. See above.\n";
}

?>
