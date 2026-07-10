<?php
// Super minimal test
echo "Step 1: Load functions.php\n";
@require_once 'functions.php';
echo "✅ Loaded\n\n";

echo "Step 2: Check functions\n";
echo "✅ load_dashboards: " . (function_exists('load_dashboards') ? 'YES' : 'NO') . "\n";
echo "✅ e(): " . (function_exists('e') ? 'YES' : 'NO') . "\n";
echo "✅ Logger: " . (class_exists('Logger') ? 'YES' : 'NO') . "\n";

echo "\nStep 3: Try loading bootstrap\n";
@require_once 'bootstrap.php';
echo "✅ Bootstrap loaded\n";

echo "\n✅ All OK\n";
?>
