<?php
/**
 * DIAGNOSTIC SCRIPT - Find exact error
 */

// Capture all output
ob_start();

// Set error handler to capture errors
$errors = [];
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $errors;
    $errors[] = [
        'errno' => $errno,
        'errstr' => $errstr,
        'errfile' => $errfile,
        'errline' => $errline
    ];
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    global $errors;
    $errors[] = [
        'type' => 'Exception',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
});

echo "=== DIAGNOSTIC REPORT ===\n\n";

// Test 1: Check .env
echo "1. Checking .env file\n";
if (file_exists('.env')) {
    echo "   ✅ .env exists\n";
    $content = file_get_contents('.env');
    if (empty($content)) {
        echo "   ⚠️  .env is empty\n";
    } else {
        echo "   ✅ .env has content (" . strlen($content) . " bytes)\n";
    }
} else {
    echo "   ❌ .env NOT FOUND\n";
}

echo "\n";

// Test 2: Load config/environment.php
echo "2. Loading config/environment.php\n";
try {
    require_once __DIR__ . '/config/environment.php';
    echo "   ✅ Loaded\n";
    echo "   ✅ APP_ENV = " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "\n";
    echo "   ✅ LOG_PATH = " . (defined('LOG_PATH') ? LOG_PATH : 'NOT DEFINED') . "\n";
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   ❌ File: " . $e->getFile() . "\n";
    echo "   ❌ Line: " . $e->getLine() . "\n";
}

echo "\n";

// Test 3: Check logs directory
echo "3. Checking logs directory\n";
if (is_dir('logs')) {
    echo "   ✅ logs/ exists\n";
    echo "   ✅ Writable: " . (is_writable('logs') ? 'YES' : 'NO') . "\n";
} else {
    echo "   ⚠️  logs/ not found, creating...\n";
    mkdir('logs', 0755, true);
    echo "   ✅ Created\n";
}

echo "\n";

// Test 4: Load functions.php
echo "4. Loading functions.php\n";
try {
    require_once __DIR__ . '/functions.php';
    echo "   ✅ Loaded\n";
    echo "   ✅ load_dashboards: " . (function_exists('load_dashboards') ? 'YES' : 'NO') . "\n";
    echo "   ✅ e(): " . (function_exists('e') ? 'YES' : 'NO') . "\n";
    echo "   ✅ Logger: " . (class_exists('Logger') ? 'YES' : 'NO') . "\n";
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Load bootstrap.php
echo "5. Loading bootstrap.php\n";
try {
    // Clear current output
    ob_end_clean();
    ob_start();
    
    // Set up server vars
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    
    require_once __DIR__ . '/bootstrap.php';
    echo "   ✅ Loaded\n";
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Try index.php logic
echo "6. Testing index.php logic\n";
try {
    $dashboards = load_dashboards();
    echo "   ✅ load_dashboards() returned " . count($dashboards) . " items\n";
    
    $publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
    echo "   ✅ Filtered to " . count($publicDashboards) . " public\n";
    
    $domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
    echo "   ✅ Found " . count($domains) . " domains\n";
    
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Show any captured errors
echo "7. Captured Errors/Warnings\n";
if (empty($errors)) {
    echo "   ✅ No errors captured\n";
} else {
    echo "   ❌ " . count($errors) . " errors found:\n";
    foreach ($errors as $error) {
        echo "   \n   Error Details:\n";
        foreach ($error as $key => $value) {
            if (is_array($value)) {
                echo "      $key: (array)\n";
            } else {
                echo "      $key: " . substr($value, 0, 100) . "\n";
            }
        }
    }
}

// Get output
$output = ob_get_clean();
echo $output;

// Show result
echo "\n═══════════════════════════════════════════════════════\n";
if (empty($errors)) {
    echo "✅ NO ERRORS FOUND - All systems operational!\n";
} else {
    echo "❌ ERRORS FOUND - See details above\n";
}
echo "═══════════════════════════════════════════════════════\n";

restore_error_handler();
restore_exception_handler();
?>
