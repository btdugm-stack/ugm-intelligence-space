<?php
// Load functions
require_once __DIR__ . '/functions.php';

// Initialize session
if (function_exists('init_session')) {
    @init_session();
}

// Apply security headers
if (function_exists('apply_security_headers')) {
    @apply_security_headers();
}

$error = '';
$expired = isset($_GET['expired']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $error = 'Security token invalid. Please try again.';
        Logger::warning('CSRF token validation failed on login form');
    } else {
        // Check rate limiting
        if (!check_rate_limit('login_attempt', RATE_LIMIT_LOGIN_ATTEMPTS, RATE_LIMIT_WINDOW)) {
            $error = 'Too many login attempts. Please try again in ' . RATE_LIMIT_WINDOW . ' seconds.';
            Logger::warning('Rate limit exceeded for login', ['ip' => $_SERVER['REMOTE_ADDR']]);
        } else {
            // Validate input
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $validation_errors = validate_input(
                ['username' => $username, 'password' => $password],
                [
                    'username' => 'required|min:3',
                    'password' => 'required|min:6'
                ]
            );
            
            if (!empty($validation_errors)) {
                $error = 'Please check your input.';
                Logger::warning('Login validation failed', $validation_errors);
            } else {
                // Authenticate user
                $auth = get_authenticator();
                $result = $auth->authenticate($username, $password);
                
                if ($result['success']) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['username'] = $result['username'];
                    $_SESSION['user_name'] = $result['name'] ?? $result['username'];
                    $_SESSION['role'] = $result['role'] ?? 'Administrator';
                    $_SESSION['login_time'] = time();
                    
                    Logger::log_auth_attempt($username, true);
                    
                    header('Location: admin.php');
                    exit;
                } else {
                    $error = $result['message'] ?? 'Authentication failed.';
                    Logger::log_auth_attempt($username, false);
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login - UGM Intelligence Space</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="login-wrap">
  <form class="login-card" method="post">
    <div class="brand">
      <div class="logo">UGM</div>
      <div><strong>Admin Console</strong><span>UGM Intelligence Space</span></div>
    </div>
    <h1>Admin Console</h1>
    <p class="lead" style="font-size:14px">Masuk untuk mengelola konten dashboard yang tampil di halaman publik.</p>
    <?php if ($expired): ?><div class="alert" style="background:#fff3cd;border-color:#ffc107;color:#664d03">Session Anda telah berakhir. Silakan login kembali.</div><?php endif; ?>
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    
    <form method="post" style="margin-top:24px">
      <?= csrf_field() ?>
      
      <label>Username</label>
      <input type="text" name="username" placeholder="Masukkan username" required autocomplete="username" 
             pattern="^[a-zA-Z0-9_]{3,}$" title="Username minimal 3 karakter (huruf, angka, underscore)">
      <br><br>
      
      <label>Password</label>
      <input type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
      <br><br>
      
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Login</button>
    </form>
    
    <?php if (!LDAP_ENABLED): ?>
    <p style="color:#687386;font-size:13px;margin-top:16px;border-top:1px solid #e6edf5;padding-top:16px">
      <strong>Demo Credentials (Development Only):</strong><br>
      Admin: <code style="background:#f4f7fb;padding:2px 6px;border-radius:4px">admin</code> / <code style="background:#f4f7fb;padding:2px 6px;border-radius:4px">admin123</code><br>
      Editor: <code style="background:#f4f7fb;padding:2px 6px;border-radius:4px">editor</code> / <code style="background:#f4f7fb;padding:2px 6px;border-radius:4px">editor123</code>
    </p>
    <?php else: ?>
    <p style="color:#687386;font-size:13px;margin-top:16px;border-top:1px solid #e6edf5;padding-top:16px">
      <strong>Menggunakan UGM SSO (LDAP)</strong><br>
      Gunakan credential UGM Anda untuk login.
    </p>
    <?php endif; ?>
    <a href="index.php" class="btn btn-ghost" style="width:100%;justify-content:center;margin-top:8px">Kembali ke Publik</a>
  </form>
</div>
</body>
</html>
