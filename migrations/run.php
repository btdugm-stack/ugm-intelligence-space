<?php
/**
 * Database Migration Runner
 * Manages database schema creation and versioning
 */

require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/../config/logger.php';

class MigrationRunner {
    private $db;
    private $schema_dir;
    
    public function __construct() {
        $this->schema_dir = __DIR__ . '/schemas';
    }
    
    /**
     * Create migration table if not exists
     */
    private function createMigrationTable() {
        try {
            $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) UNIQUE NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            
            $db = new PDO(
                sprintf('mysql:host=%s;port=%d', DB_HOST, DB_PORT),
                DB_USERNAME,
                DB_PASSWORD
            );
            $db->exec($sql);
            Logger::info('Migrations table created');
            
        } catch (PDOException $e) {
            Logger::warning('Migrations table creation attempted');
        }
    }
    
    /**
     * Run all migrations
     */
    public function runAll() {
        try {
            // Connect without selecting database first
            $db = new PDO(
                sprintf('mysql:host=%s;port=%d', DB_HOST, DB_PORT),
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Create database if not exists
            $db->exec(sprintf("CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci", DB_DATABASE));
            Logger::info('Database created/verified', ['database' => DB_DATABASE]);
            
            // Now connect to the database
            $db = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_DATABASE),
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $this->createMigrationTable();
            
            // Execute schema files
            $schemas = ['dashboards.sql', 'audit_logs.sql', 'users.sql'];
            $batch = $this->getNextBatch($db);
            
            foreach ($schemas as $schema) {
                $this->executeMigration($db, $schema, $batch);
            }
            
            Logger::info('All migrations completed successfully');
            return ['success' => true, 'message' => 'All migrations executed'];
            
        } catch (Exception $e) {
            Logger::error('Migration failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Execute single migration
     */
    private function executeMigration($db, $schema, $batch) {
        $schema_file = $this->schema_dir . '/' . $schema;
        
        if (!file_exists($schema_file)) {
            throw new Exception('Schema file not found: ' . $schema_file);
        }
        
        $sql = file_get_contents($schema_file);
        
        if (empty($sql)) {
            throw new Exception('Empty schema file: ' . $schema);
        }
        
        $db->exec($sql);
        
        // Record migration
        try {
            $stmt = $db->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
            $stmt->execute([$schema, $batch]);
        } catch (Exception $e) {
            // Migration might already be recorded
        }
        
        Logger::info('Migration executed', ['schema' => $schema]);
    }
    
    /**
     * Get next batch number
     */
    private function getNextBatch($db) {
        try {
            $stmt = $db->query('SELECT MAX(batch) as max_batch FROM migrations');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['max_batch'] ?? 0) + 1;
        } catch (Exception $e) {
            return 1;
        }
    }
    
    /**
     * Check if migrations are complete
     */
    public function isComplete() {
        try {
            $db = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_DATABASE),
                DB_USERNAME,
                DB_PASSWORD
            );
            
            // Check if all required tables exist
            $tables = ['dashboards', 'audit_logs', 'users'];
            foreach ($tables as $table) {
                $stmt = $db->prepare('SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name = ?');
                $stmt->execute([DB_DATABASE, $table]);
                if ($stmt->rowCount() === 0) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $runner = new MigrationRunner();
    
    echo "Starting database migrations...\n";
    echo "Database: " . DB_DATABASE . " @ " . DB_HOST . "\n\n";
    
    $result = $runner->runAll();
    
    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n";
        echo "\nMigrations completed successfully!\n";
        exit(0);
    } else {
        echo "❌ Migration failed: " . $result['message'] . "\n";
        exit(1);
    }
}

?>
