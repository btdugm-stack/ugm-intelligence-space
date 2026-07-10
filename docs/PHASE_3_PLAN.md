# Phase 3: Enhanced Error Handling & Centralized Logging

**Objective**: Replace basic error handling with professional error management, centralized logging, and compliance tracking.

**Timeline**: 1 week  
**Priority**: P0 - Critical  
**Dependencies**: Phase 2 (database required)

---

## 🎯 PHASE 3 GOALS

### Error Management
- [ ] Custom error pages with error IDs for support
- [ ] Structured error logging to database
- [ ] Error categorization (4 types)
- [ ] Error tracking dashboard
- [ ] Email alerts for critical errors

### Logging Infrastructure
- [ ] Centralized structured logging
- [ ] Log levels: DEBUG, INFO, WARN, ERROR, CRITICAL
- [ ] Database + file logging
- [ ] Log rotation (daily, size-based)
- [ ] Log analysis queries

### User Experience
- [ ] User-friendly error pages
- [ ] Error reference numbers
- [ ] Helpful next steps
- [ ] Contact support information
- [ ] No sensitive debug data

### Compliance
- [ ] Complete audit trail
- [ ] Error tracking for compliance
- [ ] Data retention policies
- [ ] Export capabilities for audits

---

## 📋 DELIVERABLES CHECKLIST

### Database Changes
- [ ] Create `error_logs` table
  ```sql
  CREATE TABLE error_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    error_code VARCHAR(20) UNIQUE,        -- ERR-2024-001
    error_type VARCHAR(50),               -- VALIDATION, DATABASE, AUTH, SYSTEM
    severity ENUM('LOW','MEDIUM','HIGH','CRITICAL'),
    message TEXT,
    stacktrace TEXT,
    context JSON,                         -- Request data, user info
    user_id INT FOREIGN KEY,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    occurred_at TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    resolved_by VARCHAR(100),
    resolution_notes TEXT,
    INDEX idx_error_code, idx_severity, idx_occurred_at, idx_user_id
  );
  ```

- [ ] Add `log_entries` table for application logs
  ```sql
  CREATE TABLE log_entries (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(20),                    -- DEBUG, INFO, WARN, ERROR, CRITICAL
    category VARCHAR(100),                -- auth, database, email, api
    message TEXT,
    context JSON,
    user_id INT,
    ip_address VARCHAR(45),
    occurred_at TIMESTAMP,
    INDEX idx_level, idx_category, idx_occurred_at, idx_user_id
  );
  ```

### Configuration Files
- [ ] `config/error_handler.php` - Error capture & categorization
  ```php
  class ErrorHandler {
    public static function handle($errno, $errstr, $errfile, $errline);
    public static function handleException($exception);
    private static function categorizeError($type);
    private static function getErrorCode();
    private static function logToDatabase($error);
    private static function sendAlert($error);  // For critical errors
  }
  ```

- [ ] `config/log_manager.php` - Unified logging interface
  ```php
  class LogManager {
    public static function debug($message, $context = []);
    public static function info($message, $context = []);
    public static function warning($message, $context = []);
    public static function error($message, $context = []);
    public static function critical($message, $context = []);
    private static function writeToDatabase($level, $message, $context);
    private static function writeToFile($level, $message, $context);
    private static function rotate();
  }
  ```

### Error Pages (Enhanced)
- [ ] `views/error_400.php` - Bad Request
  ```html
  Error Code: ERR-XXXX-001
  Message: Invalid input provided
  Reference: [Support ticket link]
  ```

- [ ] `views/error_403.php` - Forbidden (already created)
  - Update with error code + reference

- [ ] `views/error_404.php` - Not Found (already created)
  - Update with error code + reference

- [ ] `views/error_500.php` - Server Error (already created)
  - Update with error code + reference

- [ ] `views/error_503.php` - Service Unavailable
  - Database connection failed

- [ ] `views/error_429.php` - Too Many Requests
  - Rate limit exceeded

### Admin Tools
- [ ] `admin/error_dashboard.php` - View recent errors
  - Filter by severity, type, user
  - Search by error code
  - Show resolution status
  - Mark as resolved

- [ ] `admin/logs_dashboard.php` - View application logs
  - Filter by level, category, date range
  - Search by message
  - Export to CSV
  - Real-time log tail

- [ ] `admin/analytics.php` - Error analytics
  - Most common errors (pie chart)
  - Error trend over time (line chart)
  - Affected users (bar chart)
  - Error categories (breakdown)

### Utilities
- [ ] `scripts/cleanup_logs.php` - Log retention management
  ```bash
  php scripts/cleanup_logs.php --days=90
  # Deletes error_logs and log_entries older than 90 days
  ```

- [ ] `scripts/export_logs.php` - Compliance export
  ```bash
  php scripts/export_logs.php --type=error --format=csv --output=audit_report_2024.csv
  # Generates compliance audit reports
  ```

- [ ] `scripts/alert_config.php` - Email alert configuration
  - Critical errors email admin
  - Authentication errors email security team
  - Database errors email DBA

### Documentation
- [ ] `PHASE_3_README.md` - Quick start guide
- [ ] `PHASE_3_PLAN.md` - Detailed planning (this file)
- [ ] `PHASE_3_ERROR_CODES.md` - Error code reference
  - All error codes
  - What causes each
  - How to resolve

---

## 🔍 ERROR CATEGORIZATION

### 1. Validation Errors (LOW severity)
```
ERR-XXXX-V01: Required field missing
ERR-XXXX-V02: Email format invalid
ERR-XXXX-V03: URL format invalid
ERR-XXXX-V04: Minimum length not met
ERR-XXXX-V05: Maximum length exceeded
```

### 2. Authentication Errors (HIGH severity)
```
ERR-XXXX-A01: Invalid credentials
ERR-XXXX-A02: Account locked
ERR-XXXX-A03: Session expired
ERR-XXXX-A04: Permission denied
ERR-XXXX-A05: CSRF token invalid
```

### 3. Database Errors (CRITICAL severity)
```
ERR-XXXX-D01: Connection failed
ERR-XXXX-D02: Query failed
ERR-XXXX-D03: Constraint violated
ERR-XXXX-D04: Transaction rolled back
```

### 4. System Errors (CRITICAL severity)
```
ERR-XXXX-S01: Disk space low
ERR-XXXX-S02: Memory limit exceeded
ERR-XXXX-S03: File not found
ERR-XXXX-S04: Permission denied
ERR-XXXX-S05: External service down
```

---

## 🛠️ IMPLEMENTATION ROADMAP

### Week 1: Core Infrastructure

**Day 1: Database Migration**
```powershell
# Add tables
php migrations/run.php  # Creates error_logs, log_entries

# Verify
mysql ugm_intelligence -e "SHOW TABLES LIKE '%log%';"
```

**Day 2-3: Error Handler Config**
```php
// config/error_handler.php
- Intercept PHP errors
- Capture exceptions
- Generate error codes
- Log to database
- Send alerts
```

**Day 3-4: Log Manager Config**
```php
// config/logger.php (updated)
- Write to database
- Write to file
- Rotate logs daily
- Structured JSON format
```

**Day 5: Error Pages**
```html
<!-- views/error_XXX.php -->
- Error code display
- User-friendly message
- Support contact
- Reference number
```

### Week 2: Admin Tools & Documentation

**Day 6-7: Error Dashboard**
```php
// admin/error_dashboard.php
- List recent errors
- Filter options
- Resolution marking
- Export capability
```

**Day 7-8: Log Export & Cleanup**
```php
// scripts/cleanup_logs.php
// scripts/export_logs.php
- Retention policies
- Compliance exports
- Scheduled cleanup
```

**Day 9-10: Documentation**
- Error code reference
- Admin guide
- Developer guide
- Troubleshooting

---

## 💻 CODE EXAMPLES

### Error Handler Implementation
```php
<?php
// config/error_handler.php

class ErrorHandler {
    private static $errorCodes = [];
    
    public static function initialize() {
        set_error_handler([self::class, 'handle']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }
    
    public static function handle($errno, $errstr, $errfile, $errline) {
        $type = self::categorizeError($errno);
        $code = self::generateErrorCode($type);
        
        $error = [
            'error_code' => $code,
            'error_type' => $type,
            'severity' => self::getSeverity($type),
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'stacktrace' => debug_backtrace(),
            'context' => $_REQUEST,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'occurred_at' => date('Y-m-d H:i:s')
        ];
        
        self::logToDatabase($error);
        
        if ($type === 'CRITICAL') {
            self::sendAlert($error);
            self::displayErrorPage($code);
        }
        
        return true;
    }
    
    private static function generateErrorCode($type) {
        $timestamp = date('YmdHis');
        return "ERR-{$timestamp}-" . substr($type, 0, 1);
    }
    
    private static function logToDatabase($error) {
        $db = db();
        $db->query(
            "INSERT INTO error_logs 
            (error_code, error_type, severity, message, stacktrace, context, 
             user_id, endpoint, method, ip_address, occurred_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $error['error_code'],
                $error['error_type'],
                $error['severity'],
                $error['message'],
                json_encode($error['stacktrace']),
                json_encode($error['context']),
                $error['user_id'],
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD'],
                $error['ip_address'],
                $error['occurred_at']
            ]
        );
    }
    
    private static function sendAlert($error) {
        // Send email to admin
        mail(
            ALERT_EMAIL,
            "[CRITICAL] Error #{$error['error_code']}",
            "Error: {$error['message']}\n" .
            "Time: {$error['occurred_at']}\n" .
            "Reference: {$error['error_code']}"
        );
    }
}

ErrorHandler::initialize();
?>
```

### Log Manager Implementation
```php
<?php
// config/log_manager.php

class LogManager {
    private static $logFile = LOGS_DIR . '/app.log';
    
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function critical($message, $context = []) {
        self::log('CRITICAL', $message, $context);
        self::sendAlert($message, $context);
    }
    
    private static function log($level, $message, $context = []) {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ];
        
        // Write to database
        self::writeToDatabase($level, $message, $entry);
        
        // Write to file
        self::writeToFile($level, $message, $entry);
    }
    
    private static function writeToDatabase($level, $message, $entry) {
        if (!DB_ENABLE) return;
        
        $db = db();
        $db->query(
            "INSERT INTO log_entries (level, message, context, user_id, ip_address, occurred_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $level,
                $message,
                json_encode($entry),
                $entry['user_id'],
                $entry['ip_address'],
                $entry['timestamp']
            ]
        );
    }
    
    private static function writeToFile($level, $message, $entry) {
        $log = json_encode($entry) . PHP_EOL;
        file_put_contents(self::$logFile, $log, FILE_APPEND);
        self::rotate();
    }
    
    private static function rotate() {
        $size = filesize(self::$logFile);
        if ($size > 10485760) { // 10MB
            rename(
                self::$logFile,
                self::$logFile . '.' . date('Y-m-d-H-i-s')
            );
        }
    }
}
?>
```

### Error Dashboard
```php
<?php
// admin/error_dashboard.php
require '../auth.php';
require '../functions.php';

$severity = $_GET['severity'] ?? '';
$type = $_GET['type'] ?? '';

$query = "SELECT * FROM error_logs WHERE resolved_at IS NULL";
$params = [];

if ($severity) {
    $query .= " AND severity = ?";
    $params[] = $severity;
}

if ($type) {
    $query .= " AND error_type = ?";
    $params[] = $type;
}

$query .= " ORDER BY occurred_at DESC LIMIT 50";

$errors = db()->fetchAll($query, $params);
?>

<div class="error-dashboard">
    <h2>Error Dashboard</h2>
    
    <div class="filters">
        <select name="severity">
            <option value="">All Severities</option>
            <option value="LOW">Low</option>
            <option value="MEDIUM">Medium</option>
            <option value="HIGH">High</option>
            <option value="CRITICAL">Critical</option>
        </select>
        
        <select name="type">
            <option value="">All Types</option>
            <option value="VALIDATION">Validation</option>
            <option value="AUTH">Authentication</option>
            <option value="DATABASE">Database</option>
            <option value="SYSTEM">System</option>
        </select>
    </div>
    
    <table class="error-list">
        <thead>
            <tr>
                <th>Error Code</th>
                <th>Severity</th>
                <th>Type</th>
                <th>Message</th>
                <th>Occurred</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($errors as $error): ?>
            <tr>
                <td><?= e($error['error_code']) ?></td>
                <td><span class="badge-<?= strtolower($error['severity']) ?>">
                    <?= e($error['severity']) ?></span>
                </td>
                <td><?= e($error['error_type']) ?></td>
                <td><?= e(substr($error['message'], 0, 50)) ?></td>
                <td><?= e($error['occurred_at']) ?></td>
                <td>
                    <a href="?error_id=<?= $error['id'] ?>">Details</a>
                    <a href="?resolve=<?= $error['id'] ?>">Resolve</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
```

---

## 📊 MIGRATION SCRIPT TEMPLATE

```php
<?php
// migrations/schemas/error_logs.sql
// Run during Phase 3 upgrade

CREATE TABLE error_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  error_code VARCHAR(20) UNIQUE NOT NULL,
  error_type VARCHAR(50) NOT NULL,
  severity ENUM('LOW','MEDIUM','HIGH','CRITICAL') NOT NULL,
  message TEXT NOT NULL,
  stacktrace LONGTEXT,
  context JSON,
  user_id INT,
  endpoint VARCHAR(255),
  method VARCHAR(10),
  ip_address VARCHAR(45),
  user_agent VARCHAR(500),
  occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at TIMESTAMP NULL,
  resolved_by VARCHAR(100),
  resolution_notes TEXT,
  
  KEY idx_error_code (error_code),
  KEY idx_severity (severity),
  KEY idx_occurred_at (occurred_at),
  KEY idx_user_id (user_id),
  KEY idx_error_type (error_type),
  FULLTEXT ft_search (message, resolution_notes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE log_entries (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  level VARCHAR(20) NOT NULL,
  category VARCHAR(100),
  message TEXT NOT NULL,
  context JSON,
  user_id INT,
  ip_address VARCHAR(45),
  occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  KEY idx_level (level),
  KEY idx_category (category),
  KEY idx_occurred_at (occurred_at),
  KEY idx_user_id (user_id),
  FULLTEXT ft_search (message, category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
?>
```

---

## 🔄 INTEGRATION WITH PREVIOUS PHASES

### Phase 1 (Security)
- ✅ Uses session info from security.php
- ✅ Logs authentication attempts
- ✅ Tracks IP addresses

### Phase 2 (Database)
- ✅ Stores error logs in database
- ✅ Retrieves logs for dashboard
- ✅ Transactional error logging

### Phase 3 Integration Points
- Error handler captures all errors
- Log manager writes structured logs
- Database stores audit trail
- Dashboard visualizes errors
- Admin tools manage errors

---

## 📈 SUCCESS METRICS

### Reliability
- [ ] 99.9% of errors captured and logged
- [ ] Error response time < 100ms
- [ ] No lost logs during database operations

### Performance
- [ ] Dashboard loads < 1 second
- [ ] Export < 5 seconds for 10K records
- [ ] Log rotation automatic, no manual intervention

### Usability
- [ ] All errors have actionable messages
- [ ] Support can find errors by code
- [ ] Admins can resolve errors easily

### Compliance
- [ ] 100% audit trail coverage
- [ ] Export works for compliance reports
- [ ] Retention policies enforced automatically

---

## ⚠️ IMPORTANT NOTES

### Before Deployment
1. Backup database (Phase 2)
2. Test error capture with artificial errors
3. Verify email alerts work
4. Test dashboard performance
5. Document error codes for team

### Database Considerations
- error_logs table will grow rapidly
- Implement retention policy (90-day default)
- Use cleanup script regularly
- Archive old logs quarterly

### Performance Impact
- Error logging: +5-10% CPU
- Dashboard queries optimized with indexes
- File I/O batched, no impact

---

## 🚀 NEXT PHASE PREVIEW

### Phase 4: Performance & Caching
- Redis cache for frequently accessed data
- Query optimization
- Asset minification
- CDN integration
- Load testing (100+ concurrent users)

---

**STATUS: 📋 PHASE 3 PLANNING COMPLETE**

Ready to begin implementation. All deliverables identified, timeline set, code examples provided.

To start Phase 3:
1. Review this plan
2. Create database migrations
3. Implement error handler
4. Build admin dashboard
5. Create documentation

