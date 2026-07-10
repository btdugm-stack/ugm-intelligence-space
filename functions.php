<?php
function data_file() {
    return __DIR__ . '/data/dashboards.json';
}
function load_dashboards() {
    $file = data_file();
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}
function save_dashboards($dashboards) {
    file_put_contents(data_file(), json_encode(array_values($dashboards), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function status_class($status) {
    return strtolower(str_replace(' ', '-', $status));
}
function dashboard_icon($domain) {
    $icons = [
        'Executive Intelligence' => '◈',
        'Academic Intelligence' => '🎓',
        'Research Intelligence' => '🔬',
        'Finance Intelligence' => '💰',
        'HR Intelligence' => '👥',
        'Service Intelligence' => '⚙️',
        'Risk & Compliance Intelligence' => '🛡️',
        'Infrastructure Intelligence' => '🏛️',
        'Student Intelligence' => '🧑‍🎓',
        'Reputation Intelligence' => '🌐'
    ];
    return $icons[$domain] ?? '📊';
}

/**
 * Validate input with multiple rules
 */
function validate_input($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule_string) {
        $value = $data[$field] ?? '';
        $rules_list = explode('|', $rule_string);
        
        foreach ($rules_list as $rule) {
            $rule = trim($rule);
            
            // Required validation
            if ($rule === 'required' && empty($value)) {
                if (!isset($errors[$field])) {
                    $errors[$field] = [];
                }
                $errors[$field][] = ucfirst($field) . ' is required.';
                break;
            }
            
            // Min length validation
            if (strpos($rule, 'min:') === 0) {
                $min = (int)substr($rule, 4);
                if (!empty($value) && strlen($value) < $min) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = ucfirst($field) . ' must be at least ' . $min . ' characters.';
                }
            }
            
            // Max length validation
            if (strpos($rule, 'max:') === 0) {
                $max = (int)substr($rule, 4);
                if (!empty($value) && strlen($value) > $max) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = ucfirst($field) . ' must not exceed ' . $max . ' characters.';
                }
            }
            
            // Email validation
            if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                if (!isset($errors[$field])) {
                    $errors[$field] = [];
                }
                $errors[$field][] = ucfirst($field) . ' must be a valid email address.';
            }
            
            // URL validation
            if ($rule === 'url' && !empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                if (!isset($errors[$field])) {
                    $errors[$field] = [];
                }
                $errors[$field][] = ucfirst($field) . ' must be a valid URL.';
            }
            
            // In validation (comma separated values)
            if (strpos($rule, 'in:') === 0) {
                $allowed_values = explode(',', substr($rule, 3));
                $allowed_values = array_map('trim', $allowed_values);
                
                if (!empty($value) && !in_array($value, $allowed_values)) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = ucfirst($field) . ' has an invalid value.';
                }
            }
            
            // Regex validation
            if (strpos($rule, 'regex:') === 0) {
                $pattern = substr($rule, 6);
                if (!empty($value) && !preg_match($pattern, $value)) {
                    if (!isset($errors[$field])) {
                        $errors[$field] = [];
                    }
                    $errors[$field][] = ucfirst($field) . ' format is invalid.';
                }
            }
        }
    }
    
    return $errors;
}

/**
 * Sanitize input by type
 */
function sanitize_input($value, $type = 'string') {
    switch ($type) {
        case 'int':
        case 'integer':
            return (int)$value;
        case 'float':
        case 'decimal':
            return (float)$value;
        case 'bool':
        case 'boolean':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'email':
            return filter_var($value, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($value, FILTER_SANITIZE_URL);
        case 'string':
        default:
            return trim(strip_tags($value));
    }
}

/**
 * Log audit trail
 */
function log_audit($action, $resource, $id, $details = []) {
    // Log to file
    $log_file = __DIR__ . '/logs/audit.log';
    $timestamp = date('Y-m-d H:i:s');
    $user = $_SESSION['username'] ?? 'system';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $log_entry = [
        'timestamp' => $timestamp,
        'action' => $action,
        'resource' => $resource,
        'id' => $id,
        'user' => $user,
        'ip' => $ip,
        'details' => $details
    ];
    
    // Ensure logs directory exists
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND);
}

/**
 * Logger class compatibility wrapper
 */
if (!class_exists('Logger')) {
    class Logger {
        public static function log_audit($action, $resource, $id, $details = []) {
            log_audit($action, $resource, $id, $details);
        }
        
        public static function warning($message, $context = []) {
            self::log('warning', $message, $context);
        }
        
        public static function debug($message, $context = []) {
            self::log('debug', $message, $context);
        }
        
        public static function error($message, $context = []) {
            self::log('error', $message, $context);
        }
        
        private static function log($level, $message, $context = []) {
            $log_file = __DIR__ . '/logs/app.log';
            $timestamp = date('Y-m-d H:i:s');
            
            if (!is_dir(__DIR__ . '/logs')) {
                mkdir(__DIR__ . '/logs', 0755, true);
            }
            
            $log_entry = [
                'timestamp' => $timestamp,
                'level' => $level,
                'message' => $message,
                'context' => $context
            ];
            
            file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND);
        }
        
        public static function log_auth_attempt($username, $success) {
            $log_file = __DIR__ . '/logs/auth.log';
            $timestamp = date('Y-m-d H:i:s');
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            if (!is_dir(__DIR__ . '/logs')) {
                mkdir(__DIR__ . '/logs', 0755, true);
            }
            
            $entry = [
                'timestamp' => $timestamp,
                'username' => $username,
                'success' => $success,
                'ip' => $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ];
            
            file_put_contents($log_file, json_encode($entry) . "\n", FILE_APPEND);
        }
    }
}

/**
 * Generate CSRF token for forms
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF token field for forms
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Verify CSRF token from POST/GET
 */
function verify_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check rate limiting
 */
function check_rate_limit($key, $max_attempts = 5, $window_seconds = 300) {
    $cache_file = __DIR__ . '/logs/.rate_limit_' . md5($key . $_SERVER['REMOTE_ADDR']);
    
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    // Clean up old entries
    if (file_exists($cache_file)) {
        $data = json_decode(file_get_contents($cache_file), true);
        $current_time = time();
        
        // Remove old attempts outside the window
        $data['attempts'] = array_filter($data['attempts'], function($time) use ($current_time, $window_seconds) {
            return ($current_time - $time) < $window_seconds;
        });
        
        // Check if exceeded
        if (count($data['attempts']) >= $max_attempts) {
            file_put_contents($cache_file, json_encode($data));
            return false;
        }
        
        // Add new attempt
        $data['attempts'][] = time();
        file_put_contents($cache_file, json_encode($data));
        return true;
    } else {
        // First attempt
        $data = ['attempts' => [time()]];
        file_put_contents($cache_file, json_encode($data));
        return true;
    }
}

/**
 * Get authenticator instance
 */
function get_authenticator() {
    // Return a simple authenticator
    return new class {
        public function authenticate($username, $password) {
            // Demo credentials for development
            $demo_users = [
                'admin' => ['password' => 'admin123', 'name' => 'Admin User', 'role' => 'Administrator'],
                'editor' => ['password' => 'editor123', 'name' => 'Editor User', 'role' => 'Editor']
            ];
            
            if (isset($demo_users[$username]) && $demo_users[$username]['password'] === $password) {
                return [
                    'success' => true,
                    'username' => $username,
                    'name' => $demo_users[$username]['name'],
                    'role' => $demo_users[$username]['role']
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Username atau password tidak sesuai.'
            ];
        }
    };
}

/**
 * Define security constants
 */
if (!defined('RATE_LIMIT_LOGIN_ATTEMPTS')) {
    define('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
}
if (!defined('RATE_LIMIT_WINDOW')) {
    define('RATE_LIMIT_WINDOW', 300);
}
if (!defined('LDAP_ENABLED')) {
    define('LDAP_ENABLED', false);
}

/**
 * Initialize session
 */
function init_session() {
    // Only start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session before starting
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        
        // Start session
        @session_start();
    }
    
    // Check session timeout only for admin users
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : (30 * 60);
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $timeout) {
                session_destroy();
                if (!headers_sent()) {
                    header('Location: login.php?expired=1');
                }
                exit;
            }
        }
    }
}

/**
 * Apply security headers
 */
function apply_security_headers() {
    // Only apply headers if not already sent
    if (headers_sent()) {
        return;
    }
    
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
?>


