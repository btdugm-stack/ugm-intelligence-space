<?php
/**
 * Test script to verify admin.php 500 error is fixed
 */

echo "=== Testing Admin Panel Fix ===\n\n";

// Load required files
require_once __DIR__ . '/functions.php';

// Test 1: Check if validate_input function exists
echo "Test 1: validate_input() function\n";
if (function_exists('validate_input')) {
    echo "✅ validate_input() exists\n";
    
    // Test validation
    $errors = validate_input(
        ['name' => 'Test Dashboard', 'description' => 'Test Description'],
        ['name' => 'required|min:3|max:255', 'description' => 'required|min:10']
    );
    
    if (empty($errors)) {
        echo "✅ validate_input() works correctly\n";
    } else {
        echo "❌ validate_input() returned errors: " . json_encode($errors) . "\n";
    }
} else {
    echo "❌ validate_input() NOT FOUND\n";
}

echo "\n";

// Test 2: Check if Logger class exists with log_audit method
echo "Test 2: Logger class with log_audit() method\n";
if (class_exists('Logger')) {
    echo "✅ Logger class exists\n";
    
    if (method_exists('Logger', 'log_audit')) {
        echo "✅ Logger::log_audit() method exists\n";
    } else {
        echo "❌ Logger::log_audit() method NOT FOUND\n";
    }
} else {
    echo "❌ Logger class NOT FOUND\n";
}

echo "\n";

// Test 3: Check if sanitize_input function exists
echo "Test 3: sanitize_input() function\n";
if (function_exists('sanitize_input')) {
    echo "✅ sanitize_input() exists\n";
    
    $sanitized = sanitize_input('<script>alert("xss")</script>', 'string');
    echo "  Input: '<script>alert(\"xss\")</script>'\n";
    echo "  Output: '" . htmlspecialchars($sanitized) . "'\n";
} else {
    echo "❌ sanitize_input() NOT FOUND\n";
}

echo "\n";

// Test 4: Check if log_audit function exists
echo "Test 4: log_audit() function\n";
if (function_exists('log_audit')) {
    echo "✅ log_audit() exists\n";
} else {
    echo "❌ log_audit() NOT FOUND\n";
}

echo "\n";

// Test 5: Test validation with invalid email
echo "Test 5: validate_input() with email validation\n";
$errors = validate_input(
    ['email' => 'invalid-email'],
    ['email' => 'email']
);

if (!empty($errors['email'])) {
    echo "✅ Email validation works: " . implode(', ', $errors['email']) . "\n";
} else {
    echo "❌ Email validation failed\n";
}

echo "\n";

// Test 6: Test validation with URL
echo "Test 6: validate_input() with URL validation\n";
$errors = validate_input(
    ['url' => 'not-a-url'],
    ['url' => 'url']
);

if (!empty($errors['url'])) {
    echo "✅ URL validation works: " . implode(', ', $errors['url']) . "\n";
} else {
    echo "❌ URL validation failed\n";
}

echo "\n";

// Test 7: Check GLOBALS in admin.php context
echo "Test 7: Loading admin.php to check GLOBALS are set\n";
$_SERVER['REQUEST_METHOD'] = 'GET'; // Prevent POST processing

// Suppress output
ob_start();
@include __DIR__ . '/admin.php';
ob_end_clean();

if (isset($GLOBALS['valid_domains']) && !empty($GLOBALS['valid_domains'])) {
    echo "✅ \$GLOBALS['valid_domains'] is set with " . count($GLOBALS['valid_domains']) . " domains\n";
} else {
    echo "⚠️  \$GLOBALS['valid_domains'] not properly set\n";
}

if (isset($GLOBALS['valid_statuses']) && !empty($GLOBALS['valid_statuses'])) {
    echo "✅ \$GLOBALS['valid_statuses'] is set with " . count($GLOBALS['valid_statuses']) . " statuses\n";
} else {
    echo "⚠️  \$GLOBALS['valid_statuses'] not properly set\n";
}

if (isset($GLOBALS['valid_accesses']) && !empty($GLOBALS['valid_accesses'])) {
    echo "✅ \$GLOBALS['valid_accesses'] is set with " . count($GLOBALS['valid_accesses']) . " accesses\n";
} else {
    echo "⚠️  \$GLOBALS['valid_accesses'] not properly set\n";
}

echo "\n=== All Tests Complete ===\n";
?>
