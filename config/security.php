<?php
/**
 * Security Configuration & Helper Functions
 */

session_set_cookie_params([
    'lifetime' => SESSION_TIMEOUT,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => SESSION_COOKIE_SECURE,
    'httponly' => SESSION_COOKIE_HTTPONLY,
    'samesite' => 'Lax'
]);

/**
 * Initialize secure session
 */
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Session timeout check
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: login.php?expired=1');
            exit;
        }
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Generate CSRF token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate CSRF token field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token = null) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate input based on rules
 */
function validate_input($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule_list) {
        $value = $data[$field] ?? '';
        $rules_array = is_array($rule_list) ? $rule_list : explode('|', $rule_list);
        
        foreach ($rules_array as $rule) {
            $rule = trim($rule);
            
            if (strpos($rule, ':') !== false) {
                [$rule_name, $rule_param] = explode(':', $rule, 2);
            } else {
                $rule_name = $rule;
                $rule_param = null;
            }
            
            switch ($rule_name) {
                case 'required':
                    if (empty(trim($value))) {
                        $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    }
                    break;
                
                case 'email':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = 'Invalid email format.';
                    }
                    break;
                
                case 'url':
                    if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                        $errors[$field][] = 'Invalid URL format.';
                    }
                    break;
                
                case 'min':
                    if (!empty($value) && strlen($value) < (int)$rule_param) {
                        $errors[$field][] = 'Minimum length is ' . $rule_param . ' characters.';
                    }
                    break;
                
                case 'max':
                    if (!empty($value) && strlen($value) > (int)$rule_param) {
                        $errors[$field][] = 'Maximum length is ' . $rule_param . ' characters.';
                    }
                    break;
                
                case 'in':
                    $allowed = explode(',', str_replace(' ', '', $rule_param));
                    if (!empty($value) && !in_array($value, $allowed)) {
                        $errors[$field][] = 'Invalid value selected.';
                    }
                    break;
                
                case 'regex':
                    if (!empty($value) && !preg_match($rule_param, $value)) {
                        $errors[$field][] = 'Invalid format.';
                    }
                    break;
            }
        }
    }
    
    return $errors;
}

/**
 * Sanitize input
 */
function sanitize_input($value, $type = 'string') {
    switch ($type) {
        case 'int':
            return (int)$value;
        case 'email':
            return filter_var($value, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($value, FILTER_SANITIZE_URL);
        case 'string':
        default:
            return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

/**
 * Rate limiting check
 */
function check_rate_limit($key, $max_attempts = null, $window = null) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }
    
    $max_attempts = $max_attempts ?? RATE_LIMIT_LOGIN_ATTEMPTS;
    $window = $window ?? RATE_LIMIT_WINDOW;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cache_key = $key . ':' . $ip;
    
    // Using file-based rate limiting (can be replaced with Redis)
    $rate_limit_file = LOG_PATH . '/.rate_limit_' . md5($cache_key);
    
    if (file_exists($rate_limit_file)) {
        $data = json_decode(file_get_contents($rate_limit_file), true);
        $elapsed = time() - $data['timestamp'];
        
        if ($elapsed < $window) {
            if ($data['attempts'] >= $max_attempts) {
                return false;
            }
            $data['attempts']++;
        } else {
            $data = ['attempts' => 1, 'timestamp' => time()];
        }
    } else {
        $data = ['attempts' => 1, 'timestamp' => time()];
    }
    
    file_put_contents($rate_limit_file, json_encode($data));
    return true;
}

/**
 * Get security headers
 */
function get_security_headers() {
    return [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ];
}

/**
 * Apply security headers
 */
function apply_security_headers() {
    foreach (get_security_headers() as $header => $value) {
        header($header . ': ' . $value);
    }
    
    if (!SESSION_COOKIE_SECURE && strpos(APP_URL, 'https') === 0) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
?>
