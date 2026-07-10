<?php
// Test files
echo "Checking PHP syntax...\n\n";

$files_to_check = [
    'bootstrap.php',
    'functions.php',
    'index.php',
    'login.php',
    'admin.php',
    'auth.php',
    'detail.php'
];

$all_ok = true;

foreach ($files_to_check as $file) {
    if (!file_exists($file)) {
        echo "❌ $file - NOT FOUND\n";
        $all_ok = false;
        continue;
    }
    
    $output = [];
    $return_code = 0;
    exec("php -l \"$file\" 2>&1", $output, $return_code);
    
    if ($return_code === 0 && strpos(implode('', $output), 'No syntax errors') !== false) {
        echo "✅ $file - OK\n";
    } else {
        echo "❌ $file - ERROR\n";
        echo "   " . implode("\n   ", $output) . "\n";
        $all_ok = false;
    }
}

echo "\n";
if ($all_ok) {
    echo "✅ All files have correct syntax!\n";
} else {
    echo "❌ Some files have syntax errors\n";
}
?>
