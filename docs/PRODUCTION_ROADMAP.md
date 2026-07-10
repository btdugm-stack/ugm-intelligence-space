# UGM Intelligence Space - Production Finalization Roadmap

## Executive Summary
This document outlines the critical changes required to transition the UGM Intelligence Space PoC from development to production. The roadmap is organized by priority (P0-P3) and implementation complexity.

---

## Phase 1: Critical Security Hardening (P0 - Week 1-2)

### 1.1 Authentication & Authorization (CRITICAL)
**Current State:** Hardcoded demo credentials in `login.php`
**Action Items:**
- [ ] Implement SSO integration (UGM LDAP/OIDC)
  - Use `\LdapRecord\Container` for LDAP bind
  - Store credentials in `.env` file (PHP-DotEnv package)
  - Hash passwords with `password_hash()` if database auth needed
- [ ] Replace hardcoded credentials:
  ```php
  // BEFORE (login.php)
  if ($username === 'admin' && $password === 'admin123') { ... }
  
  // AFTER
  require_once 'vendor/autoload.php';
  $ldap = new LdapConnection(['servers' => [getenv('LDAP_SERVER')]]);
  $user = $ldap->authenticate($username, $password);
  ```
- [ ] Implement role-based access control (RBAC):
  - Admin: full CRUD
  - Editor: create/update own dashboards
  - Viewer: read-only
- [ ] Session management:
  - Set `session.cookie_secure = 1` and `session.cookie_httponly = 1`
  - Implement session timeout (30 min idle)
  - Add CSRF token to all forms

### 1.2 Input Validation & Output Encoding (CRITICAL)
**Current State:** `e()` function used for output, but input validation minimal
**Action Items:**
- [ ] Add server-side input validation for all form fields:
  ```php
  // In admin.php save handler
  $errors = [];
  if (empty(trim($_POST['name']))) $errors[] = 'Dashboard name required';
  if (!filter_var($_POST['url'], FILTER_VALIDATE_URL)) $errors[] = 'Invalid URL';
  if (!in_array($_POST['domain'], $VALID_DOMAINS)) $errors[] = 'Invalid domain';
  if (strlen($_POST['tags']) > 200) $errors[] = 'Tags too long';
  
  if (!empty($errors)) {
      $_SESSION['errors'] = $errors;
      header('Location: admin.php?edit=' . urlencode($id));
      exit;
  }
  ```
- [ ] Sanitize input with `filter_var()` and regex patterns
- [ ] Whitelist approach for domain, status, access fields
- [ ] Validate JSON before saving to `dashboards.json`

### 1.3 CSRF Protection (CRITICAL)
**Action Items:**
- [ ] Add CSRF token generation in `functions.php`:
  ```php
  function csrf_token() {
      if (empty($_SESSION['csrf_token'])) {
          $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['csrf_token'];
  }
  ```
- [ ] Verify CSRF in all POST requests (admin.php):
  ```php
  if (!hash_equals($_POST['csrf_token'] ?? '', $_SESSION['csrf_token'] ?? '')) {
      http_response_code(403);
      exit('CSRF token validation failed');
  }
  ```
- [ ] Include token in all admin forms: `<input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">`

### 1.4 File Permission & Security Headers (HIGH)
**Action Items:**
- [ ] Set restrictive file permissions:
  ```powershell
  # Windows NTFS
  icacls "data\dashboards.json" /grant:r "%USERNAME%":F /inheritance:r
  icacls "data" /grant:r "IUSR":R /grant:r "IIS_IUSRS":R
  ```
- [ ] Add security headers in `.htaccess` or nginx config:
  ```
  Header set X-Content-Type-Options "nosniff"
  Header set X-Frame-Options "SAMEORIGIN"
  Header set X-XSS-Protection "1; mode=block"
  Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
  ```

---

## Phase 2: Data Persistence & Database Migration (P0 - Week 2-3)

### 2.1 Database Schema Design (CRITICAL)
**Transition:** JSON → MySQL/PostgreSQL

**Recommended Schema:**
```sql
CREATE TABLE dashboards (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    domain VARCHAR(100) NOT NULL,
    owner VARCHAR(100),
    update_frequency VARCHAR(50),
    last_updated DATE,
    status ENUM('Updated','Scheduled Update','Need Review','Delayed','Maintenance'),
    access ENUM('Public','Internal','Restricted'),
    audience VARCHAR(255),
    url VARCHAR(500),
    tags VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    INDEX idx_access (access),
    INDEX idx_domain (domain),
    INDEX idx_status (status)
);

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dashboard_id VARCHAR(36),
    action VARCHAR(50),
    old_values JSON,
    new_values JSON,
    user VARCHAR(100),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (dashboard_id) REFERENCES dashboards(id) ON DELETE CASCADE
);
```

### 2.2 Database Connection Management (HIGH)
**Action Items:**
- [ ] Create `config/database.php` with PDO connection:
  ```php
  <?php
  $dsn = sprintf(
      'mysql:host=%s;dbname=%s;charset=utf8mb4',
      getenv('DB_HOST'),
      getenv('DB_NAME')
  );
  $pdo = new PDO(
      $dsn,
      getenv('DB_USER'),
      getenv('DB_PASS'),
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
  ?>
  ```
- [ ] Implement prepared statements to prevent SQL injection:
  ```php
  // functions.php
  function load_dashboards_db() {
      global $pdo;
      $stmt = $pdo->prepare('SELECT * FROM dashboards WHERE access = ?');
      $stmt->execute(['Public']);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  ```
- [ ] Add connection pooling (if using persistent connections)

### 2.3 Migration Strategy (HIGH)
**Action Items:**
- [ ] Create migration script `migrations/001_create_dashboards.php`
- [ ] Implement rollback capability
- [ ] Script to migrate existing JSON data:
  ```php
  $jsonData = json_decode(file_get_contents('data/dashboards.json'), true);
  foreach ($jsonData as $dashboard) {
      $stmt = $pdo->prepare(
          'INSERT INTO dashboards (...) VALUES (...)'
      );
      $stmt->execute($dashboard);
  }
  ```
- [ ] Data validation during migration (referential integrity checks)

---

## Phase 3: Error Handling & Logging (P1 - Week 2)

### 3.1 Centralized Error Handling (HIGH)
**Action Items:**
- [ ] Create `config/error_handler.php`:
  ```php
  set_exception_handler(function ($exception) {
      error_log(json_encode([
          'timestamp' => date('Y-m-d H:i:s'),
          'exception' => get_class($exception),
          'message' => $exception->getMessage(),
          'file' => $exception->getFile(),
          'line' => $exception->getLine(),
          'trace' => $exception->getTraceAsString(),
          'user' => $_SESSION['username'] ?? 'anonymous',
          'ip' => $_SERVER['REMOTE_ADDR'],
          'url' => $_SERVER['REQUEST_URI']
      ]));
      
      if (getenv('APP_ENV') === 'production') {
          http_response_code(500);
          include 'views/error_500.php';
      } else {
          throw $exception;
      }
  });
  ```
- [ ] Create user-friendly error pages (`views/error_*.php`)
- [ ] Never expose debug info in production

### 3.2 Audit Trail Implementation (CRITICAL)
**Action Items:**
- [ ] Log all CRUD operations to `audit_logs` table:
  ```php
  function log_audit($dashboard_id, $action, $old_values, $new_values) {
      global $pdo;
      $stmt = $pdo->prepare(
          'INSERT INTO audit_logs (dashboard_id, action, old_values, new_values, user, ip_address)
           VALUES (?, ?, ?, ?, ?, ?)'
      );
      $stmt->execute([
          $dashboard_id,
          $action,
          json_encode($old_values),
          json_encode($new_values),
          $_SESSION['username'] ?? 'system',
          $_SERVER['REMOTE_ADDR']
      ]);
  }
  ```
- [ ] Log authentication attempts (successful & failed)
- [ ] Create audit dashboard for administrators

---

## Phase 4: Performance & Caching (P1 - Week 3)

### 4.1 Query Optimization (MEDIUM)
**Action Items:**
- [ ] Add database indexes (see schema above)
- [ ] Use LIMIT in list queries
- [ ] Avoid N+1 queries in dashboard listings

### 4.2 Caching Strategy (MEDIUM)
**Action Items:**
- [ ] Implement Redis caching for public dashboards:
  ```php
  function get_public_dashboards() {
      $redis = new Redis();
      $redis->connect('localhost', 6379);
      $cache_key = 'public_dashboards';
      
      if ($redis->exists($cache_key)) {
          return json_decode($redis->get($cache_key), true);
      }
      
      $dashboards = load_dashboards_db();
      $redis->setex($cache_key, 3600, json_encode($dashboards));
      return $dashboards;
  }
  ```
- [ ] Cache invalidation on admin updates
- [ ] Browser caching headers for static assets

### 4.3 Asset Optimization (MEDIUM)
**Action Items:**
- [ ] Minify CSS/JS for production
- [ ] Add asset versioning in URLs
- [ ] Implement lazy loading for dashboard cards

---

## Phase 5: Compliance & Governance (P1 - Week 3-4)

### 5.1 Data Protection (HIGH)
**Action Items:**
- [ ] Encrypt sensitive fields at rest:
  ```php
  function encrypt_field($value) {
      $key = getenv('ENCRYPTION_KEY');
      $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
      $encrypted = sodium_crypto_secretbox($value, $nonce, $key);
      return base64_encode($nonce . $encrypted);
  }
  ```
- [ ] Implement rate limiting on login endpoint
- [ ] Add HTTPS enforcement (HTTP → HTTPS redirect)

### 5.2 Access Control Policy (MEDIUM)
**Action Items:**
- [ ] Document access policies by role
- [ ] Implement principle of least privilege
- [ ] Add "who can access what" logic based on UGM organizational structure

### 5.3 Data Governance (MEDIUM)
**Action Items:**
- [ ] Create dashboard ownership & stewardship model
- [ ] Implement data quality status validation
- [ ] Add metadata versioning

---

## Phase 6: Testing Strategy (P1-P2 - Week 3-4)

### 6.1 Unit & Integration Testing (MEDIUM)
**Setup:** PHPUnit + Laravel TestCase

```php
// tests/DashboardTest.php
use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase {
    public function test_load_dashboards_returns_array() {
        $dashboards = load_dashboards_db();
        $this->assertIsArray($dashboards);
    }
    
    public function test_dashboard_save_requires_name() {
        $this->expectException(ValidationException::class);
        save_dashboard(['name' => '', 'domain' => 'Executive']);
    }
    
    public function test_public_filter_excludes_internal() {
        $dashboards = get_public_dashboards();
        foreach ($dashboards as $d) {
            $this->assertEquals('Public', $d['access']);
        }
    }
}
```

**Action Items:**
- [ ] Test all CRUD operations
- [ ] Test access control filters
- [ ] Test input validation
- [ ] Achieve 80%+ code coverage

### 6.2 Security Testing (HIGH)
**Action Items:**
- [ ] Manual OWASP Top 10 testing
- [ ] SQL injection testing
- [ ] XSS payload testing
- [ ] CSRF token validation
- [ ] Consider OWASP ZAP for automated scanning

### 6.3 Performance Testing (MEDIUM)
**Action Items:**
- [ ] Load testing with 1000+ concurrent users
- [ ] Response time benchmarks (target <200ms for public pages)
- [ ] Database query performance analysis

---

## Phase 7: Infrastructure & Deployment (P0 - Week 4)

### 7.1 Environment Configuration (CRITICAL)
**Action Items:**
- [ ] Create `.env` template and `.env.production`:
  ```
  APP_ENV=production
  DB_HOST=prod-db-server
  DB_NAME=ugm_intelligence
  LDAP_SERVER=ldap.ugm.ac.id
  REDIS_HOST=redis.internal
  LOG_LEVEL=warning
  ENCRYPTION_KEY=<base64-encoded-32-bytes>
  ```
- [ ] Use environment variables for all secrets
- [ ] Never commit `.env` to version control

### 7.2 Web Server Configuration (HIGH)
**Nginx Configuration:**
```nginx
server {
    listen 443 ssl http2;
    server_name dashboards.ugm.ac.id;
    
    ssl_certificate /etc/ssl/certs/ugm-dashboards.crt;
    ssl_certificate_key /etc/ssl/private/ugm-dashboards.key;
    
    root /var/www/ugm-intelligence-space;
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~* \.(js|css|png|jpg|jpeg|gif|svg|webp)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 7.3 Backup & Disaster Recovery (CRITICAL)
**Action Items:**
- [ ] Daily database backups (automated via cron)
- [ ] Off-site backup replication (AWS S3, etc.)
- [ ] Backup restore procedure documentation
- [ ] Recovery Time Objective (RTO): 4 hours
- [ ] Recovery Point Objective (RPO): 1 hour

### 7.4 Monitoring & Alerting (HIGH)
**Action Items:**
- [ ] Application error monitoring (Sentry, New Relic)
- [ ] Database performance monitoring
- [ ] Disk space alerts
- [ ] SSL certificate expiry alerts
- [ ] Setup alerts for:
  - High error rates (>0.1%)
  - Slow queries (>1s)
  - Authentication failures (>5 in 5 min)

---

## Phase 8: Documentation & Training (P2 - Week 4)

### 8.1 Technical Documentation
- [ ] Database schema documentation
- [ ] API documentation (if REST API added)
- [ ] Administrator guide
- [ ] Deployment runbook

### 8.2 Operational Documentation
- [ ] Incident response procedures
- [ ] Log interpretation guide
- [ ] Backup/restore procedures
- [ ] Troubleshooting guide

### 8.3 Administrator Training
- [ ] CRUD operations training
- [ ] RBAC configuration training
- [ ] Monitoring dashboard walkthrough

---

## Dependencies to Install

```bash
# Core
composer require vlucas/phpdotenv                # Environment variables
composer require monolog/monolog                 # Logging
composer require symfony/http-foundation        # Security utilities

# Database
composer require doctrine/orm                    # ORM (optional)

# Cache
composer require predis/predis                   # Redis client

# Testing
composer require phpunit/phpunit --dev
composer require friendsofphp/php-cs-fixer --dev

# Security
composer require symfony/security-bundle        # CSRF, etc.
composer require defuse/php-encryption          # Data encryption
```

---

## Production Deployment Checklist

- [ ] All P0 security items completed
- [ ] Database migrated and tested
- [ ] Error handling & logging configured
- [ ] HTTPS configured with valid certificate
- [ ] LDAP/SSO tested with real credentials
- [ ] Backup procedures tested and verified
- [ ] Monitoring & alerting configured
- [ ] Performance tested (load test passed)
- [ ] Security audit completed
- [ ] All tests passing (80%+ coverage)
- [ ] Documentation completed
- [ ] Team training completed
- [ ] Rollback plan prepared

---

## Post-Production (Week 5+)

- Monitor error rates and performance metrics
- Gather user feedback
- Plan Phase 2 enhancements (REST API, advanced analytics, etc.)
- Schedule security audits (quarterly)
- Plan database optimization based on real usage patterns

