<?php
/**
 * Environment Configuration Loader
 * Loads .env file manually (no external dependencies)
 */

/**
 * Load .env file without external dependencies
 */
function load_env_file($env_path) {
    // Try .env.production first if APP_ENV env var is set to production
    // Otherwise default to .env
    $env_file = '.env';
    
    if (file_exists($env_path . '/.env.production')) {
        // Check if we're in production mode
        $check_file = $env_path . '/.env.production';
        $first_line = trim(fgets(fopen($check_file, 'r')));
        if (strpos($first_line, 'APP_ENV=production') !== false) {
            $env_file = '.env.production';
        }
    }
    
    $full_path = $env_path . '/' . $env_file;
    
    if (!file_exists($full_path)) {
        // Try fallback to .env if .env.production doesn't exist
        $full_path = $env_path . '/.env';
        if (!file_exists($full_path)) {
            throw new RuntimeException('.env file not found at: ' . $env_path);
        }
    }
    
    $env_content = file_get_contents($full_path);
    if ($env_content === false) {
        throw new RuntimeException('Cannot read .env file: ' . $full_path);
    }
    
    $lines = explode("\n", $env_content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, ' "\'');
            
            // Set environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Load .env file
$env_path = __DIR__ . '/..';
load_env_file($env_path);

// Configuration defaults
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Database
define('DB_CONNECTION', getenv('DB_CONNECTION') ?: 'mysql');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: 3306);
define('DB_DATABASE', getenv('DB_DATABASE') ?: 'ugm_intelligence');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

// LDAP
define('LDAP_ENABLED', getenv('LDAP_ENABLED') === 'true');
define('LDAP_SERVER', getenv('LDAP_SERVER') ?: 'ldap.ugm.ac.id');
define('LDAP_PORT', getenv('LDAP_PORT') ?: 389);
define('LDAP_BASE_DN', getenv('LDAP_BASE_DN') ?: 'dc=ugm,dc=ac,dc=id');

// Session
define('SESSION_TIMEOUT', (int)(getenv('SESSION_TIMEOUT') ?: 1800));
define('SESSION_COOKIE_SECURE', getenv('SESSION_COOKIE_SECURE') === 'true');
define('SESSION_COOKIE_HTTPONLY', getenv('SESSION_COOKIE_HTTPONLY') !== 'false');

// Encryption
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: '');

// Security
define('CSRF_TOKEN_LENGTH', (int)(getenv('CSRF_TOKEN_LENGTH') ?: 32));

// Logging
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'debug');
define('LOG_PATH', getenv('LOG_PATH') ?: __DIR__ . '/../logs');

// Rate Limiting
define('RATE_LIMIT_ENABLED', getenv('RATE_LIMIT_ENABLED') === 'true');
define('RATE_LIMIT_LOGIN_ATTEMPTS', (int)(getenv('RATE_LIMIT_LOGIN_ATTEMPTS') ?: 5));
define('RATE_LIMIT_WINDOW', (int)(getenv('RATE_LIMIT_WINDOW') ?: 300));

// Ensure logs directory exists and is writable
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

if (!is_writable(LOG_PATH)) {
    throw new RuntimeException('Logs directory is not writable: ' . LOG_PATH);
}
?>
