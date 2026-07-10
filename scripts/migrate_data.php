<?php
/**
 * Data Migration Script
 * Migrate dashboards from JSON to database
 */

require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/../config/logger.php';

class DataMigrator {
    private $db;
    private $stats = [
        'total' => 0,
        'migrated' => 0,
        'failed' => 0,
        'errors' => []
    ];
    
    public function __construct() {
        try {
            $this->db = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_DATABASE),
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            Logger::critical('Cannot connect to database', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * Migrate data from JSON file
     */
    public function migrateFromJSON() {
        $json_file = __DIR__ . '/../data/dashboards.json';
        
        if (!file_exists($json_file)) {
            throw new Exception('JSON file not found: ' . $json_file);
        }
        
        echo "📂 Loading JSON file: " . $json_file . "\n";
        
        $json_data = json_decode(file_get_contents($json_file), true);
        
        if (!is_array($json_data)) {
            throw new Exception('Invalid JSON data');
        }
        
        $this->stats['total'] = count($json_data);
        echo "📊 Total records to migrate: " . $this->stats['total'] . "\n\n";
        
        // Start transaction
        try {
            $this->db->beginTransaction();
            
            foreach ($json_data as $index => $dashboard) {
                try {
                    $this->migrateRecord($dashboard);
                    $this->stats['migrated']++;
                    
                    // Show progress
                    if (($index + 1) % 10 === 0) {
                        echo "  ✅ Migrated " . ($index + 1) . "/" . $this->stats['total'] . " records\n";
                    }
                } catch (Exception $e) {
                    $this->stats['failed']++;
                    $this->stats['errors'][] = [
                        'id' => $dashboard['id'] ?? 'unknown',
                        'name' => $dashboard['name'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    
                    echo "  ❌ Failed to migrate: " . ($dashboard['id'] ?? 'unknown') . " - " . $e->getMessage() . "\n";
                }
            }
            
            $this->db->commit();
            
            Logger::info('Data migration completed', $this->stats);
            
            return $this->stats;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Data migration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * Migrate single record
     */
    private function migrateRecord($dashboard) {
        $stmt = $this->db->prepare(
            'INSERT INTO dashboards 
            (id, name, description, domain, owner, update_frequency, 
             last_updated, status, access, audience, url, tags, created_by, updated_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        
        $params = [
            $dashboard['id'] ?? 'dash-' . substr(md5(uniqid()), 0, 8),
            $dashboard['name'] ?? '',
            $dashboard['description'] ?? '',
            $dashboard['domain'] ?? '',
            $dashboard['owner'] ?? '',
            $dashboard['update_frequency'] ?? 'Manual',
            $dashboard['last_updated'] ?? date('Y-m-d'),
            in_array($dashboard['status'] ?? '', ['Updated', 'Scheduled Update', 'Need Review', 'Delayed', 'Maintenance']) 
                ? $dashboard['status'] 
                : 'Updated',
            in_array($dashboard['access'] ?? '', ['Public', 'Internal', 'Restricted']) 
                ? $dashboard['access'] 
                : 'Public',
            $dashboard['audience'] ?? '',
            $dashboard['url'] ?? '#',
            $dashboard['tags'] ?? '',
            'migration',
            'migration'
        ];
        
        $stmt->execute($params);
    }
    
    /**
     * Validate data integrity
     */
    public function validate() {
        echo "\n🔍 Validating migrated data...\n";
        
        $stmt = $this->db->query('SELECT COUNT(*) as count FROM dashboards WHERE is_deleted = FALSE');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $db_count = $result['count'] ?? 0;
        
        if ($db_count === $this->stats['migrated']) {
            echo "✅ Data integrity verified: " . $db_count . " records in database\n";
            return true;
        } else {
            echo "❌ Data mismatch: Expected " . $this->stats['migrated'] . ", found " . $db_count . "\n";
            return false;
        }
    }
    
    /**
     * Create backup of JSON file
     */
    public function backupJSON() {
        $source = __DIR__ . '/../data/dashboards.json';
        $backup = __DIR__ . '/../backups/dashboards_backup_' . date('Y-m-d_H-i-s') . '.json';
        
        if (copy($source, $backup)) {
            echo "📦 JSON file backed up to: " . basename($backup) . "\n";
            return true;
        }
        
        return false;
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    try {
        echo "🚀 Starting data migration...\n";
        echo "Source: JSON file\n";
        echo "Target: MySQL database\n";
        echo "Database: " . DB_DATABASE . " @ " . DB_HOST . "\n\n";
        
        $migrator = new DataMigrator();
        
        // Backup JSON first
        $migrator->backupJSON();
        echo "\n";
        
        // Perform migration
        $stats = $migrator->migrateFromJSON();
        
        // Validate
        $migrator->validate();
        
        // Print summary
        echo "\n📊 Migration Summary:\n";
        echo "├─ Total records: " . $stats['total'] . "\n";
        echo "├─ Migrated: " . $stats['migrated'] . "\n";
        echo "├─ Failed: " . $stats['failed'] . "\n";
        echo "└─ Success rate: " . round(($stats['migrated'] / $stats['total']) * 100, 2) . "%\n";
        
        if (!empty($stats['errors'])) {
            echo "\n⚠️  Errors:\n";
            foreach ($stats['errors'] as $error) {
                echo "  • " . $error['id'] . " (" . $error['name'] . "): " . $error['error'] . "\n";
            }
        }
        
        if ($stats['failed'] === 0) {
            echo "\n✅ Migration completed successfully!\n";
            exit(0);
        } else {
            echo "\n⚠️  Migration completed with errors\n";
            exit(1);
        }
        
    } catch (Exception $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}

?>
