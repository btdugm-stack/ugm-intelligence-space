<?php
// Load functions directly
require_once __DIR__ . '/functions.php';

// Initialize session
if (function_exists('init_session')) {
    @init_session();
}

// Check if user is logged in and not expired
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Verify session hasn't been hijacked
$timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : (30 * 60);
if (!isset($_SESSION['login_time']) || (time() - $_SESSION['login_time'] > $timeout)) {
    session_destroy();
    header('Location: login.php?expired=1');
    exit;
}

// Update last activity for timeout tracking
$_SESSION['last_activity'] = time();
?>
