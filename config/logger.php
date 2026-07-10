<?php
/**
 * Centralized Logging System
 */

class Logger {
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    
    private static $log_levels = [
        'debug' => 0,
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    ];
    
    /**
     * Log message
     */
    public static function log($level, $message, $context = []) {
        if (!self::should_log($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $user = $_SESSION['username'] ?? 'anonymous';
        
        $log_entry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'user' => $user,
            'ip_address' => $ip_address,
            'url' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'context' => $context
        ];
        
        // Write to file
        $log_file = LOG_PATH . '/' . date('Y-m-d') . '.log';
        $log_line = json_encode($log_entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        error_log($log_line, 3, $log_file);
        
        // Also write critical errors to error log
        if (strtoupper($level) === self::LEVEL_CRITICAL) {
            error_log($log_line, 3, LOG_PATH . '/error.log');
        }
    }
    
    /**
     * Check if level should be logged
     */
    private static function should_log($level) {
        $current_level = self::$log_levels[strtolower(LOG_LEVEL)] ?? 0;
        $message_level = self::$log_levels[strtolower($level)] ?? 0;
        return $message_level >= $current_level;
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log(self::LEVEL_ERROR, $message, $context);
    }
    
    /**
     * Log critical message
     */
    public static function critical($message, $context = []) {
        self::log(self::LEVEL_CRITICAL, $message, $context);
    }
    
    /**
     * Log authentication attempt
     */
    public static function log_auth_attempt($username, $success, $ip = null) {
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        
        self::log(
            $success ? self::LEVEL_INFO : self::LEVEL_WARNING,
            $success ? 'Login successful' : 'Login failed',
            [
                'username' => $username,
                'ip_address' => $ip,
                'success' => $success
            ]
        );
    }
    
    /**
     * Log audit event (CRUD operations)
     */
    public static function log_audit($action, $resource, $resource_id, $details = []) {
        self::log(
            self::LEVEL_INFO,
            'Audit: ' . $action . ' on ' . $resource,
            [
                'action' => $action,
                'resource' => $resource,
                'resource_id' => $resource_id,
                'user' => $_SESSION['username'] ?? 'system',
                'details' => $details
            ]
        );
    }
}

/**
 * Exception handler
 */
function exception_handler($exception) {
    Logger::critical(
        'Uncaught Exception: ' . $exception->getMessage(),
        [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]
    );
    
    if (APP_DEBUG) {
        throw $exception;
    } else {
        http_response_code(500);
        include __DIR__ . '/../views/error_500.php';
        exit;
    }
}

/**
 * Error handler
 */
function error_handler($errno, $errstr, $errfile, $errline) {
    Logger::error(
        'PHP Error: ' . $errstr,
        [
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline
        ]
    );
    
    if (APP_DEBUG) {
        return false;
    } else {
        http_response_code(500);
        exit('An error occurred. Please contact administrator.');
    }
}

// Set handlers
set_exception_handler('exception_handler');
set_error_handler('error_handler');

// Register shutdown function for fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        Logger::critical('Fatal Error', $error);
    }
});
?>
