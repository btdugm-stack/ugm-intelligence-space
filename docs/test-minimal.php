<?php
// Minimal test
require_once 'functions.php';

echo "✅ functions.php loaded successfully\n";
echo "✅ validate_input exists: " . (function_exists('validate_input') ? 'YES' : 'NO') . "\n";
echo "✅ csrf_token exists: " . (function_exists('csrf_token') ? 'YES' : 'NO') . "\n";
echo "✅ check_rate_limit exists: " . (function_exists('check_rate_limit') ? 'YES' : 'NO') . "\n";
echo "✅ Logger class exists: " . (class_exists('Logger') ? 'YES' : 'NO') . "\n";
echo "✅ get_authenticator exists: " . (function_exists('get_authenticator') ? 'YES' : 'NO') . "\n";
?>
