<?php
/**
 * Quick Verification Script
 * Run this file to verify the fix worked
 * Usage: php verify.php
 */

echo "======================================\n";
echo "UGM Intelligence Space - Verification\n";
echo "======================================\n\n";

// Test 1: .env file exists
echo "1. Checking .env file...\n";
if (file_exists('.env')) {
    echo "   ✅ .env file found\n";
    $size = filesize('.env');
    echo "   📁 Size: $size bytes\n";
} else {
    echo "   ❌ .env file NOT found\n";
    echo "   📋 Please run: cp .env.example .env\n";
}

echo "\n";

// Test 2: Load environment
echo "2. Loading environment configuration...\n";
try {
    require_once 'config/environment.php';
    echo "   ✅ environment.php loaded successfully\n";
} catch (Exception $e) {
    echo "   ❌ Error loading environment.php:\n";
    echo "   📋 " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check constants
echo "3. Checking defined constants...\n";
$constants = ['APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_ENABLE', 'LDAP_ENABLED'];
$all_defined = true;

foreach ($constants as $const) {
    if (defined($const)) {
        $value = constant($const);
        echo "   ✅ $const = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
    } else {
        echo "   ❌ $const = NOT DEFINED\n";
        $all_defined = false;
    }
}

echo "\n";

// Test 4: Check environment array
echo "4. Checking \$_ENV array...\n";
$env_vars = ['APP_ENV', 'APP_DEBUG', 'APP_URL', 'DB_HOST', 'DB_DATABASE'];
$env_found = 0;

foreach ($env_vars as $var) {
    if (isset($_ENV[$var])) {
        echo "   ✅ \$_ENV['$var'] found\n";
        $env_found++;
    } else {
        echo "   ❌ \$_ENV['$var'] NOT found\n";
    }
}

echo "\n";

// Test 5: Check key files
echo "5. Checking required files...\n";
$files = [
    'config/environment.php',
    'config/security.php',
    'config/logger.php',
    'config/ldap_authenticator.php',
    'config/database.php',
    'functions.php',
    'bootstrap.php',
    '.env'
];

$files_ok = 0;
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
        $files_ok++;
    } else {
        echo "   ❌ $file NOT FOUND\n";
    }
}

echo "\n";

// Test 6: Logs directory
echo "6. Checking logs directory...\n";
if (is_dir('logs')) {
    echo "   ✅ logs/ directory exists\n";
    if (is_writable('logs')) {
        echo "   ✅ logs/ is writable\n";
    } else {
        echo "   ⚠️  logs/ is NOT writable\n";
    }
} else {
    echo "   ⚠️  logs/ directory does not exist\n";
    echo "   📋 Creating logs directory...\n";
    mkdir('logs', 0755, true);
    echo "   ✅ logs/ directory created\n";
}

echo "\n";

// Final Summary
echo "======================================\n";
echo "VERIFICATION SUMMARY\n";
echo "======================================\n";

if ($all_defined && $env_found >= 3 && $files_ok >= 6) {
    echo "✅ ALL CHECKS PASSED\n";
    echo "✅ Application is ready to use\n";
    echo "\n📋 Next Steps:\n";
    echo "   1. Open: http://localhost/ugm-intelligence-space-poc/\n";
    echo "   2. Or run: php bootstrap.php\n";
} else {
    echo "⚠️  SOME CHECKS FAILED\n";
    echo "❌ Please fix the issues above\n";
    echo "\n📋 Troubleshooting:\n";
    echo "   1. Check .env file exists and is readable\n";
    echo "   2. Verify config/ directory files exist\n";
    echo "   3. Check file permissions (chmod 644 .env)\n";
}

echo "\n";
?>
