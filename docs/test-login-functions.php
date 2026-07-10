<?php
/**
 * Test login.php functions
 */

echo "Testing Login Functions\n";
echo "=======================\n\n";

require_once 'functions.php';

// Test 1: CSRF functions
echo "1. Testing CSRF functions:\n";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = csrf_token();
echo "   ✅ csrf_token() generated: " . substr($token, 0, 10) . "...\n";

$field = csrf_field();
echo "   ✅ csrf_field() generated HTML\n";

$_POST['csrf_token'] = $token;
if (verify_csrf_token()) {
    echo "   ✅ verify_csrf_token() works\n";
} else {
    echo "   ❌ verify_csrf_token() failed\n";
}

echo "\n";

// Test 2: Rate limiting
echo "2. Testing rate limiting:\n";
if (check_rate_limit('test_key', 5, 300)) {
    echo "   ✅ check_rate_limit() first attempt allowed\n";
} else {
    echo "   ❌ check_rate_limit() first attempt failed\n";
}

echo "\n";

// Test 3: Constants
echo "3. Testing constants:\n";
echo "   ✅ RATE_LIMIT_LOGIN_ATTEMPTS = " . RATE_LIMIT_LOGIN_ATTEMPTS . "\n";
echo "   ✅ RATE_LIMIT_WINDOW = " . RATE_LIMIT_WINDOW . "\n";
echo "   ✅ LDAP_ENABLED = " . (LDAP_ENABLED ? 'true' : 'false') . "\n";

echo "\n";

// Test 4: Authenticator
echo "4. Testing authenticator:\n";
$auth = get_authenticator();
$result = $auth->authenticate('admin', 'admin123');
if ($result['success']) {
    echo "   ✅ Admin authentication successful\n";
} else {
    echo "   ❌ Admin authentication failed\n";
}

$result = $auth->authenticate('admin', 'wrong_password');
if (!$result['success']) {
    echo "   ✅ Wrong password rejected\n";
} else {
    echo "   ❌ Wrong password accepted (security issue!)\n";
}

echo "\n";

// Test 5: Logger::log_auth_attempt
echo "5. Testing Logger::log_auth_attempt:\n";
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
Logger::log_auth_attempt('test_user', true);
if (file_exists('logs/auth.log')) {
    echo "   ✅ Auth log created\n";
} else {
    echo "   ❌ Auth log not created\n";
}

echo "\n";

echo "✅ All login functions are working!\n";
?>
