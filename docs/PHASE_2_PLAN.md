# Phase 2: Database Migration - Implementation Plan

## Overview
Transisi dari JSON file storage ke MySQL/PostgreSQL untuk scalability, reliability, dan audit trail yang lebih baik.

**Timeline**: 2 weeks  
**Priority**: P0 (Critical)  
**Dependencies**: Phase 1 (Complete ✅)

---

## Task 1.1: Database Schema Design

### Main Tables

#### `dashboards` Table
```sql
CREATE TABLE dashboards (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    domain VARCHAR(100) NOT NULL,
    owner VARCHAR(100) NOT NULL,
    update_frequency VARCHAR(50),
    last_updated DATE,
    status ENUM('Updated','Scheduled Update','Need Review','Delayed','Maintenance') DEFAULT 'Updated',
    access ENUM('Public','Internal','Restricted') DEFAULT 'Public',
    audience VARCHAR(255),
    url VARCHAR(500),
    tags VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100),
    updated_by VARCHAR(100),
    is_deleted BOOLEAN DEFAULT FALSE,
    
    INDEX idx_access (access),
    INDEX idx_domain (domain),
    INDEX idx_status (status),
    INDEX idx_created_by (created_by),
    FULLTEXT INDEX ft_search (name, description, tags)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `audit_logs` Table
```sql
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    dashboard_id VARCHAR(36),
    action VARCHAR(50) NOT NULL,
    action_type ENUM('CREATE','READ','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
    old_values JSON,
    new_values JSON,
    username VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (dashboard_id) REFERENCES dashboards(id) ON DELETE SET NULL,
    INDEX idx_dashboard_id (dashboard_id),
    INDEX idx_username (username),
    INDEX idx_timestamp (timestamp),
    INDEX idx_action (action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### `users` Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(255),
    email VARCHAR(255),
    role ENUM('admin','editor','viewer') DEFAULT 'viewer',
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Task 1.2: Database Configuration

### File: `config/database.php`

```php
<?php
class Database {
    private static $pdo = null;
    
    public static function connect() {
        if (self::$pdo === null) {
            try {
                $dsn = sprintf(
                    '%s:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                    DB_CONNECTION,
                    DB_HOST,
                    DB_PORT,
                    DB_DATABASE
                );
                
                self::$pdo = new PDO(
                    $dsn,
                    DB_USERNAME,
                    DB_PASSWORD,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                Logger::critical('Database connection failed', [
                    'error' => $e->getMessage(),
                    'host' => DB_HOST
                ]);
                throw new Exception('Database connection failed');
            }
        }
        
        return self::$pdo;
    }
    
    public static function close() {
        self::$pdo = null;
    }
}

function db() {
    return Database::connect();
}
?>
```

---

## Task 1.3: Migration Script

### File: `migrations/001_create_schema.php`

```php
<?php
require_once __DIR__ . '/../config/database.php';

class Migration {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    public function up() {
        try {
            $this->db->exec('START TRANSACTION');
            
            // Create dashboards table
            $this->db->exec(file_get_contents(__DIR__ . '/schemas/dashboards.sql'));
            
            // Create audit_logs table
            $this->db->exec(file_get_contents(__DIR__ . '/schemas/audit_logs.sql'));
            
            // Create users table
            $this->db->exec(file_get_contents(__DIR__ . '/schemas/users.sql'));
            
            $this->db->exec('COMMIT');
            return true;
        } catch (Exception $e) {
            $this->db->exec('ROLLBACK');
            Logger::error('Migration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    public function down() {
        try {
            $this->db->exec('START TRANSACTION');
            $this->db->exec('DROP TABLE IF EXISTS audit_logs');
            $this->db->exec('DROP TABLE IF EXISTS dashboards');
            $this->db->exec('DROP TABLE IF EXISTS users');
            $this->db->exec('COMMIT');
            return true;
        } catch (Exception $e) {
            $this->db->exec('ROLLBACK');
            throw $e;
        }
    }
}

// Run migration
if (php_sapi_name() === 'cli') {
    $migration = new Migration();
    if ($migration->up()) {
        echo "Migration completed successfully\n";
    }
}
?>
```

---

## Task 1.4: Data Migration Script

### File: `scripts/migrate_data.php`

```php
<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';

class DataMigrator {
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    public function migrateFromJSON() {
        $json_file = __DIR__ . '/../data/dashboards.json';
        
        if (!file_exists($json_file)) {
            throw new Exception('JSON file not found: ' . $json_file);
        }
        
        $json_data = json_decode(file_get_contents($json_file), true);
        
        if (!is_array($json_data)) {
            throw new Exception('Invalid JSON data');
        }
        
        $this->db->exec('START TRANSACTION');
        $migrated = 0;
        $errors = [];
        
        try {
            foreach ($json_data as $dashboard) {
                try {
                    $stmt = $this->db->prepare(
                        'INSERT INTO dashboards 
                        (id, name, description, domain, owner, update_frequency, 
                         last_updated, status, access, audience, url, tags, created_by, updated_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    
                    $stmt->execute([
                        $dashboard['id'] ?? 'dash-' . substr(md5(uniqid()), 0, 8),
                        $dashboard['name'] ?? '',
                        $dashboard['description'] ?? '',
                        $dashboard['domain'] ?? '',
                        $dashboard['owner'] ?? '',
                        $dashboard['update_frequency'] ?? '',
                        $dashboard['last_updated'] ?? date('Y-m-d'),
                        $dashboard['status'] ?? 'Updated',
                        $dashboard['access'] ?? 'Public',
                        $dashboard['audience'] ?? '',
                        $dashboard['url'] ?? '#',
                        $dashboard['tags'] ?? '',
                        'migration',
                        'migration'
                    ]);
                    
                    $migrated++;
                } catch (Exception $e) {
                    $errors[] = [
                        'id' => $dashboard['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $this->db->exec('COMMIT');
            
            Logger::info('Data migration completed', [
                'migrated' => $migrated,
                'errors' => count($errors)
            ]);
            
            return [
                'success' => true,
                'migrated' => $migrated,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            $this->db->exec('ROLLBACK');
            Logger::error('Data migration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

// Run migration from CLI
if (php_sapi_name() === 'cli') {
    try {
        $migrator = new DataMigrator();
        $result = $migrator->migrateFromJSON();
        
        echo "Migration Report:\n";
        echo "- Migrated: " . $result['migrated'] . "\n";
        echo "- Errors: " . count($result['errors']) . "\n";
        
        if (!empty($result['errors'])) {
            echo "\nErrors:\n";
            foreach ($result['errors'] as $error) {
                echo "  - ID: " . $error['id'] . " - " . $error['error'] . "\n";
            }
        }
    } catch (Exception $e) {
        echo "Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
```

---

## Task 1.5: Updated Functions for Database

### File: `functions.php` - Add Database Functions

```php
<?php
// ... existing functions ...

/**
 * Load dashboards from database
 */
function load_dashboards_db() {
    try {
        $stmt = db()->prepare('SELECT * FROM dashboards WHERE is_deleted = FALSE ORDER BY created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        Logger::error('Failed to load dashboards', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Load public dashboards
 */
function load_public_dashboards_db() {
    try {
        $stmt = db()->prepare(
            'SELECT * FROM dashboards 
             WHERE is_deleted = FALSE AND access = ? 
             ORDER BY created_at DESC'
        );
        $stmt->execute(['Public']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        Logger::error('Failed to load public dashboards', ['error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Save dashboard to database
 */
function save_dashboard_db($dashboard) {
    try {
        $stmt = db()->prepare(
            'INSERT INTO dashboards 
            (id, name, description, domain, owner, update_frequency, 
             last_updated, status, access, audience, url, tags, created_by, updated_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            name = ?, description = ?, domain = ?, owner = ?, 
            update_frequency = ?, last_updated = ?, status = ?, 
            access = ?, audience = ?, url = ?, tags = ?, updated_by = ?'
        );
        
        $params = [
            $dashboard['id'],
            $dashboard['name'],
            $dashboard['description'],
            $dashboard['domain'],
            $dashboard['owner'],
            $dashboard['update_frequency'],
            $dashboard['last_updated'],
            $dashboard['status'],
            $dashboard['access'],
            $dashboard['audience'],
            $dashboard['url'],
            $dashboard['tags'],
            $_SESSION['username'] ?? 'system',
            $_SESSION['username'] ?? 'system',
            // Duplicate key update values
            $dashboard['name'],
            $dashboard['description'],
            $dashboard['domain'],
            $dashboard['owner'],
            $dashboard['update_frequency'],
            $dashboard['last_updated'],
            $dashboard['status'],
            $dashboard['access'],
            $dashboard['audience'],
            $dashboard['url'],
            $dashboard['tags'],
            $_SESSION['username'] ?? 'system'
        ];
        
        $stmt->execute($params);
        
        Logger::log_audit('SAVE', 'Dashboard', $dashboard['id'], $dashboard);
        return true;
    } catch (PDOException $e) {
        Logger::error('Failed to save dashboard', ['error' => $e->getMessage()]);
        return false;
    }
}

/**
 * Delete dashboard from database
 */
function delete_dashboard_db($id) {
    try {
        $stmt = db()->prepare('UPDATE dashboards SET is_deleted = TRUE WHERE id = ?');
        $stmt->execute([$id]);
        
        Logger::log_audit('DELETE', 'Dashboard', $id, ['action' => 'soft delete']);
        return true;
    } catch (PDOException $e) {
        Logger::error('Failed to delete dashboard', ['error' => $e->getMessage()]);
        return false;
    }
}

?>
```

---

## Task 1.6: Backup Procedures

### File: `scripts/backup_database.php`

```php
<?php
require_once __DIR__ . '/../bootstrap.php';

class DatabaseBackup {
    private $backup_dir;
    private $db_config;
    
    public function __construct() {
        $this->backup_dir = __DIR__ . '/../backups';
        $this->db_config = [
            'host' => DB_HOST,
            'database' => DB_DATABASE,
            'user' => DB_USERNAME,
            'password' => DB_PASSWORD
        ];
        
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }
    
    /**
     * Create database backup using mysqldump
     */
    public function create() {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backup_file = $this->backup_dir . '/' . DB_DATABASE . '_' . $timestamp . '.sql';
            
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg($this->db_config['host']),
                escapeshellarg($this->db_config['user']),
                escapeshellarg($this->db_config['password']),
                escapeshellarg($this->db_config['database']),
                escapeshellarg($backup_file)
            );
            
            exec($command, $output, $return_code);
            
            if ($return_code === 0) {
                Logger::info('Database backup created', ['file' => $backup_file]);
                return ['success' => true, 'file' => $backup_file];
            } else {
                throw new Exception('Backup command failed');
            }
        } catch (Exception $e) {
            Logger::error('Backup creation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Restore from backup file
     */
    public function restore($backup_file) {
        try {
            if (!file_exists($backup_file)) {
                throw new Exception('Backup file not found: ' . $backup_file);
            }
            
            $command = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s',
                escapeshellarg($this->db_config['host']),
                escapeshellarg($this->db_config['user']),
                escapeshellarg($this->db_config['password']),
                escapeshellarg($this->db_config['database']),
                escapeshellarg($backup_file)
            );
            
            exec($command, $output, $return_code);
            
            if ($return_code === 0) {
                Logger::info('Database restored', ['file' => $backup_file]);
                return ['success' => true];
            } else {
                throw new Exception('Restore command failed');
            }
        } catch (Exception $e) {
            Logger::error('Restore failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $files = glob($this->backup_dir . '/*.sql');
        rsort($files); // Most recent first
        
        return array_map(function($file) {
            return [
                'file' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'created' => filemtime($file)
            ];
        }, $files);
    }
}

// CLI commands
if (php_sapi_name() === 'cli') {
    $backup = new DatabaseBackup();
    $action = $argv[1] ?? 'help';
    
    switch ($action) {
        case 'create':
            $result = $backup->create();
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        case 'list':
            $backups = $backup->listBackups();
            echo "Available backups:\n";
            foreach ($backups as $b) {
                echo "- " . $b['file'] . " (" . number_format($b['size'] / 1024 / 1024, 2) . " MB)\n";
            }
            break;
            
        case 'restore':
            if (!isset($argv[2])) {
                echo "Usage: backup_database.php restore <file>\n";
                exit(1);
            }
            $result = $backup->restore($argv[2]);
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            break;
            
        default:
            echo "Usage:\n";
            echo "  create  - Create backup\n";
            echo "  list    - List backups\n";
            echo "  restore - Restore from backup\n";
    }
}
?>
```

---

## Checklist

- [ ] Schema SQL files created
- [ ] Database configuration added
- [ ] Migration scripts completed
- [ ] Data migration script tested
- [ ] Backup procedures implemented
- [ ] Database connection pooling setup
- [ ] Audit logging integrated
- [ ] All functions updated for database
- [ ] Testing completed

---

## Next Steps

1. **Create SQL schema files** in `migrations/schemas/`
2. **Test migration** on local MySQL
3. **Run data migration** from JSON to database
4. **Verify audit logs** working with database
5. **Setup backup automation** (cron job)

