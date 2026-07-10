<?php
/**
 * Application Bootstrap
 * Include this file at the start of every PHP script
 */

// Strict error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load configuration with error handling
try {
    // 1. Load environment configuration
    $env_file = __DIR__ . '/config/environment.php';
    if (!file_exists($env_file)) {
        throw new RuntimeException('Missing: config/environment.php');
    }
    
    // Include environment config
    require_once $env_file;
    
    // Verify critical constant is defined
    if (!defined('APP_ENV')) {
        throw new RuntimeException('APP_ENV constant not defined after loading environment.php');
    }
    
    // 2. Load functions (before anything else that needs them)
    $functions_file = __DIR__ . '/functions.php';
    if (!file_exists($functions_file)) {
        throw new RuntimeException('Missing: functions.php');
    }
    require_once $functions_file;
    
    // 3. Load config files (optional - don't fail if missing)
    $config_files = [
        'config/logger.php',
        'config/security.php',
        'config/ldap_authenticator.php'
    ];
    
    foreach ($config_files as $config_file) {
        $full_path = __DIR__ . '/' . $config_file;
        if (file_exists($full_path)) {
            require_once $full_path;
        }
    }
    
} catch (Throwable $e) {
    // Output error and stop
    http_response_code(500);
    
    // Show error if not in production
    if (!isset($_ENV['APP_ENV']) || $_ENV['APP_ENV'] !== 'production') {
        echo "Bootstrap Error: " . htmlspecialchars($e->getMessage());
        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo "\n\nDetails:\n";
            echo "File: " . htmlspecialchars($e->getFile()) . "\n";
            echo "Line: " . $e->getLine() . "\n";
        }
    } else {
        echo "Internal Server Error";
    }
    
    exit(1);
}

// Initialize application (only if all dependencies loaded)
if (function_exists('init_session')) {
    init_session();
}

if (function_exists('apply_security_headers')) {
    apply_security_headers();
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Cache headers
if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Expires: 0');
    header('Pragma: no-cache');
}

// Debug logging
if (defined('APP_DEBUG') && APP_DEBUG && class_exists('Logger')) {
    Logger::debug('Bootstrap complete', ['env' => APP_ENV]);
}
?>


