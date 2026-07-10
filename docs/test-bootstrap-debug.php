<?php
/**
 * Debug bootstrap loading
 */

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "Debug: Testing bootstrap loading\n";
echo "================================\n\n";

echo "Step 1: Check config/environment.php\n";
$env_file = __DIR__ . '/config/environment.php';
if (file_exists($env_file)) {
    echo "✅ File exists\n";
} else {
    echo "❌ File not found\n";
    exit(1);
}

echo "\nStep 2: Check .env file\n";
$dot_env = __DIR__ . '/.env';
if (file_exists($dot_env)) {
    echo "✅ .env exists\n";
} else {
    echo "❌ .env not found\n";
    exit(1);
}

echo "\nStep 3: Load environment.php\n";
try {
    require_once $env_file;
    echo "✅ Loaded successfully\n";
    echo "   APP_ENV = " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "\n";
    echo "   LOG_PATH = " . (defined('LOG_PATH') ? LOG_PATH : 'NOT DEFINED') . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 4: Check logs directory\n";
$log_path = defined('LOG_PATH') ? LOG_PATH : __DIR__ . '/logs';
if (is_dir($log_path)) {
    echo "✅ Directory exists: $log_path\n";
    echo "   Writable: " . (is_writable($log_path) ? 'YES' : 'NO') . "\n";
} else {
    echo "⚠️  Directory not found, creating: $log_path\n";
    mkdir($log_path, 0755, true);
    echo "✅ Created\n";
}

echo "\nStep 5: Load functions.php\n";
try {
    require_once __DIR__ . '/functions.php';
    echo "✅ Loaded\n";
    echo "   load_dashboards: " . (function_exists('load_dashboards') ? 'YES' : 'NO') . "\n";
    echo "   Logger class: " . (class_exists('Logger') ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nStep 6: Try loading bootstrap.php\n";
try {
    require_once __DIR__ . '/bootstrap.php';
    echo "✅ Loaded\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ All components loaded successfully!\n";
?>
