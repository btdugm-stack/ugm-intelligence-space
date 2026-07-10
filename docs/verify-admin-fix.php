<?php
/**
 * COMPREHENSIVE ADMIN PANEL FIX VERIFICATION
 * Run this script to verify the 500 error fix is working
 */

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║       ADMIN PANEL 500 ERROR FIX VERIFICATION REPORT            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Track results
$checks = [];
$passed = 0;
$failed = 0;

function check($name, $condition, $details = '') {
    global $checks, $passed, $failed;
    
    if ($condition) {
        echo "✅ $name\n";
        $passed++;
        if ($details) echo "   └─ $details\n";
    } else {
        echo "❌ $name\n";
        $failed++;
        if ($details) echo "   └─ $details\n";
    }
    
    $checks[] = [
        'name' => $name,
        'passed' => $condition
    ];
}

echo "[1] CHECKING REQUIRED FILES\n";
echo "─" . str_repeat("─", 63) . "\n";

check('functions.php exists', file_exists('functions.php'));
check('admin.php exists', file_exists('admin.php'));
check('logs/ directory exists', is_dir('logs'));

echo "\n[2] CHECKING FUNCTION IMPLEMENTATIONS\n";
echo "─" . str_repeat("─", 63) . "\n";

require_once 'functions.php';

check('validate_input() function defined', function_exists('validate_input'));
check('sanitize_input() function defined', function_exists('sanitize_input'));
check('log_audit() function defined', function_exists('log_audit'));
check('Logger class defined', class_exists('Logger'));
check('Logger::log_audit() method defined', method_exists('Logger', 'log_audit'));

echo "\n[3] TESTING VALIDATION LOGIC\n";
echo "─" . str_repeat("─", 63) . "\n";

// Test 1: Valid input
$result = validate_input(
    ['name' => 'Valid Dashboard Name'],
    ['name' => 'required|min:3|max:100']
);
check('Valid input passes', empty($result));

// Test 2: Missing required field
$result = validate_input(
    ['name' => ''],
    ['name' => 'required|min:3']
);
check('Missing required field fails', !empty($result) && isset($result['name']));

// Test 3: Min length validation
$result = validate_input(
    ['name' => 'AB'],
    ['name' => 'min:3']
);
check('Min length validation works', !empty($result) && isset($result['name']));

// Test 4: Email validation
$result = validate_input(
    ['email' => 'invalid.email'],
    ['email' => 'email']
);
check('Invalid email fails', !empty($result) && isset($result['email']));

// Test 5: Valid email
$result = validate_input(
    ['email' => 'test@example.com'],
    ['email' => 'email']
);
check('Valid email passes', empty($result));

// Test 6: URL validation
$result = validate_input(
    ['url' => 'https://example.com'],
    ['url' => 'url']
);
check('Valid URL passes', empty($result));

// Test 7: Invalid URL
$result = validate_input(
    ['url' => 'not a url'],
    ['url' => 'url']
);
check('Invalid URL fails', !empty($result) && isset($result['url']));

// Test 8: In validation
$result = validate_input(
    ['status' => 'Published'],
    ['status' => 'in:Active,Inactive,Published']
);
check('In validation works', empty($result));

echo "\n[4] TESTING SANITIZATION\n";
echo "─" . str_repeat("─", 63) . "\n";

$sanitized = sanitize_input('<script>alert(1)</script>', 'string');
check('Script tags removed', strpos($sanitized, 'script') === false, "Input: '<script>...'");

$sanitized = sanitize_input('123.45', 'float');
check('Float conversion works', is_float($sanitized) && $sanitized == 123.45);

$sanitized = sanitize_input('true', 'bool');
check('Boolean conversion works', is_bool($sanitized) && $sanitized === true);

echo "\n[5] TESTING AUDIT LOGGING\n";
echo "─" . str_repeat("─", 63) . "\n";

// Mock session
$_SESSION['username'] = 'test_admin';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Test logging
log_audit('TEST', 'Dashboard', 'dash-test-001', ['message' => 'Test entry']);

$log_file = 'logs/audit.log';
check('Audit log file created', file_exists($log_file));

if (file_exists($log_file)) {
    $last_line = trim(array_pop(explode("\n", file_get_contents($log_file))));
    $last_entry = json_decode($last_line, true);
    check('Audit log entry is valid JSON', $last_entry !== null, 
        "Last entry: " . substr($last_line, 0, 50) . "...");
    check('Audit log contains action', $last_entry && $last_entry['action'] === 'TEST');
    check('Audit log contains resource', $last_entry && $last_entry['resource'] === 'Dashboard');
}

echo "\n[6] TESTING LOGGER CLASS\n";
echo "─" . str_repeat("─", 63) . "\n";

check('Logger::log_audit() callable', is_callable(['Logger', 'log_audit']));
check('Logger::warning() callable', is_callable(['Logger', 'warning']));
check('Logger::debug() callable', is_callable(['Logger', 'debug']));
check('Logger::error() callable', is_callable(['Logger', 'error']));

echo "\n[7] CHECKING ADMIN.PHP GLOBALS\n";
echo "─" . str_repeat("─", 63) . "\n";

// We can't fully load admin.php without auth, but we can check if the file is syntactically correct
$syntax_check = shell_exec('php -l admin.php 2>&1');
check('admin.php syntax is valid', strpos($syntax_check, 'No syntax errors') !== false);

echo "\n[8] CRITICAL DEPENDENCY CHECK\n";
echo "─" . str_repeat("─", 63) . "\n";

// These are the functions that were missing and causing 500 errors
$critical_functions = [
    'validate_input' => 'Was undefined - caused 500 error',
    'log_audit' => 'Was undefined - caused 500 error',
    'sanitize_input' => 'Required for input safety'
];

foreach ($critical_functions as $func => $reason) {
    check("$func() available", function_exists($func), $reason);
}

echo "\n[9] SUMMARY\n";
echo "═" . str_repeat("═", 63) . "\n";

$total = $passed + $failed;
$percentage = ($total > 0) ? round(($passed / $total) * 100, 1) : 0;

echo "\n✅ Passed: $passed checks\n";
echo "❌ Failed: $failed checks\n";
echo "📊 Success Rate: $percentage%\n";

if ($failed === 0) {
    echo "\n🎉 ALL CHECKS PASSED!\n";
    echo "✅ The admin panel 500 error should be FIXED!\n";
    echo "\nYou can now:\n";
    echo "  1. Access the admin panel\n";
    echo "  2. Edit existing dashboards\n";
    echo "  3. Create new dashboards\n";
    echo "  4. Delete dashboards\n";
    echo "  5. Check audit logs in logs/audit.log\n";
} else {
    echo "\n⚠️  Some checks failed. Review the errors above.\n";
}

echo "\n═" . str_repeat("═", 64) . "\n";

// Return exit code based on results
exit($failed === 0 ? 0 : 1);
?>
