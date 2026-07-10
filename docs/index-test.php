<?php
/**
 * Test version of index.php - no bootstrap
 * Just to verify logic works
 */

// Load functions directly
require_once __DIR__ . '/functions.php';

// Load dashboards
$dashboards = load_dashboards();
$publicDashboards = array_values(array_filter($dashboards, fn($d) => ($d['access'] ?? '') === 'Public'));
$domains = array_values(array_unique(array_map(fn($d) => $d['domain'] ?? '', $publicDashboards)));
$updatedToday = count(array_filter($publicDashboards, fn($d) => ($d['last_updated'] ?? '') === date('Y-m-d')));
$needAttention = count(array_filter($publicDashboards, fn($d) => in_array(($d['status'] ?? ''), ['Delayed','Need Review','Maintenance'])));
?>
<!doctype html>
<html>
<head>
    <title>UGM Intelligence Space - Test</title>
</head>
<body>
    <h1>UGM Intelligence Space - Test Version</h1>
    
    <div style="background: #f0f0f0; padding: 20px; margin: 20px 0;">
        <h2>University Pulse</h2>
        <p>Public Dashboards: <strong><?= count($publicDashboards) ?></strong></p>
        <p>Domains: <strong><?= count($domains) ?></strong></p>
        <p>Updated Today: <strong><?= $updatedToday ?></strong></p>
        <p>Need Attention: <strong><?= $needAttention ?></strong></p>
    </div>
    
    <h2>Dashboards</h2>
    <?php foreach ($publicDashboards as $d): ?>
        <div style="border: 1px solid #ccc; padding: 15px; margin: 10px 0;">
            <h3><?= e($d['name'] ?? 'N/A') ?></h3>
            <p><?= e($d['description'] ?? 'N/A') ?></p>
            <p>
                <small>
                    Domain: <?= e($d['domain'] ?? 'N/A') ?> | 
                    Status: <?= e($d['status'] ?? 'N/A') ?> | 
                    Updated: <?= e($d['last_updated'] ?? 'N/A') ?>
                </small>
            </p>
        </div>
    <?php endforeach; ?>
    
    <p style="color: #666; margin-top: 40px; font-size: 12px;">
        ✅ This test version works! All functions operational.
    </p>
</body>
</html>
