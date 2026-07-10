<?php
/**
 * Database Backup & Restore Script
 * Handles database backup, restoration, and scheduling
 */

require_once __DIR__ . '/../config/environment.php';
require_once __DIR__ . '/../config/logger.php';

class DatabaseBackup {
    private $backup_dir;
    private $db_config;
    private $retention_days = 30;
    
    public function __construct() {
        $this->backup_dir = __DIR__ . '/../backups';
        $this->db_config = [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'database' => DB_DATABASE,
            'user' => DB_USERNAME,
            'password' => DB_PASSWORD
        ];
        
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }
    
    /**
     * Create database backup
     */
    public function create($verbose = true) {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backup_file = $this->backup_dir . '/' . DB_DATABASE . '_' . $timestamp . '.sql.gz';
            
            if ($verbose) echo "📦 Creating backup: " . basename($backup_file) . "\n";
            
            // Build mysqldump command
            $command = sprintf(
                'mysqldump --host=%s --port=%d --user=%s --password=%s --single-transaction --quick --lock-tables=false %s | gzip > %s',
                escapeshellarg($this->db_config['host']),
                intval($this->db_config['port']),
                escapeshellarg($this->db_config['user']),
                escapeshellarg($this->db_config['password']),
                escapeshellarg($this->db_config['database']),
                escapeshellarg($backup_file)
            );
            
            // For Windows, use different command
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $backup_file_uncompressed = $this->backup_dir . '/' . DB_DATABASE . '_' . $timestamp . '.sql';
                
                $command = sprintf(
                    'mysqldump --host=%s --port=%d --user=%s --password=%s --single-transaction --quick --lock-tables=false %s > %s',
                    escapeshellarg($this->db_config['host']),
                    intval($this->db_config['port']),
                    escapeshellarg($this->db_config['user']),
                    escapeshellarg($this->db_config['password']),
                    escapeshellarg($this->db_config['database']),
                    escapeshellarg($backup_file_uncompressed)
                );
                
                $backup_file = $backup_file_uncompressed;
            }
            
            exec($command, $output, $return_code);
            
            if ($return_code === 0 && file_exists($backup_file)) {
                $file_size = number_format(filesize($backup_file) / 1024 / 1024, 2);
                
                if ($verbose) echo "✅ Backup created: " . $file_size . " MB\n";
                
                Logger::info('Database backup created', [
                    'file' => basename($backup_file),
                    'size' => $file_size . ' MB'
                ]);
                
                return ['success' => true, 'file' => $backup_file, 'size' => $file_size];
            } else {
                throw new Exception('Backup command failed with code ' . $return_code);
            }
        } catch (Exception $e) {
            if ($verbose) echo "❌ Backup failed: " . $e->getMessage() . "\n";
            Logger::error('Backup creation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Restore from backup
     */
    public function restore($backup_file, $verbose = true) {
        try {
            if (!file_exists($backup_file)) {
                throw new Exception('Backup file not found: ' . $backup_file);
            }
            
            if ($verbose) echo "📥 Restoring from: " . basename($backup_file) . "\n";
            
            // Build restore command
            $command = sprintf(
                'mysql --host=%s --port=%d --user=%s --password=%s %s < %s',
                escapeshellarg($this->db_config['host']),
                intval($this->db_config['port']),
                escapeshellarg($this->db_config['user']),
                escapeshellarg($this->db_config['password']),
                escapeshellarg($this->db_config['database']),
                escapeshellarg($backup_file)
            );
            
            exec($command, $output, $return_code);
            
            if ($return_code === 0) {
                if ($verbose) echo "✅ Restore completed successfully\n";
                
                Logger::info('Database restored', [
                    'file' => basename($backup_file)
                ]);
                
                return ['success' => true];
            } else {
                throw new Exception('Restore command failed with code ' . $return_code);
            }
        } catch (Exception $e) {
            if ($verbose) echo "❌ Restore failed: " . $e->getMessage() . "\n";
            Logger::error('Restore failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * List all backups
     */
    public function listBackups() {
        $backups = [];
        
        if (is_dir($this->backup_dir)) {
            $files = glob($this->backup_dir . '/*.sql*');
            rsort($files); // Most recent first
            
            foreach ($files as $file) {
                $backups[] = [
                    'file' => basename($file),
                    'path' => $file,
                    'size' => number_format(filesize($file) / 1024 / 1024, 2) . ' MB',
                    'created' => date('Y-m-d H:i:s', filemtime($file)),
                    'age_days' => floor((time() - filemtime($file)) / 86400)
                ];
            }
        }
        
        return $backups;
    }
    
    /**
     * Clean old backups
     */
    public function cleanupOldBackups() {
        $cutoff_time = time() - ($this->retention_days * 86400);
        $deleted_count = 0;
        
        $backups = $this->listBackups();
        
        foreach ($backups as $backup) {
            $file_time = strtotime($backup['created']);
            
            if ($file_time < $cutoff_time) {
                if (unlink($backup['path'])) {
                    $deleted_count++;
                    Logger::info('Old backup deleted', ['file' => $backup['file']]);
                }
            }
        }
        
        return $deleted_count;
    }
    
    /**
     * Get backup statistics
     */
    public function getStats() {
        $backups = $this->listBackups();
        
        $total_size = 0;
        foreach ($backups as $backup) {
            $total_size += floatval($backup['size']);
        }
        
        return [
            'total_backups' => count($backups),
            'total_size' => number_format($total_size, 2) . ' MB',
            'oldest' => end($backups)['created'] ?? 'N/A',
            'newest' => reset($backups)['created'] ?? 'N/A',
            'retention_days' => $this->retention_days
        ];
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $backup = new DatabaseBackup();
    $action = $argv[1] ?? 'help';
    
    echo "🗄️  Database Backup & Restore Tool\n";
    echo "Database: " . DB_DATABASE . " @ " . DB_HOST . "\n\n";
    
    switch ($action) {
        case 'create':
            $result = $backup->create(true);
            echo "\n";
            break;
            
        case 'list':
            echo "📋 Available Backups:\n\n";
            $backups = $backup->listBackups();
            
            if (empty($backups)) {
                echo "No backups found.\n";
            } else {
                foreach ($backups as $b) {
                    echo "  File: " . $b['file'] . "\n";
                    echo "  Size: " . $b['size'] . "\n";
                    echo "  Created: " . $b['created'] . " (" . $b['age_days'] . " days ago)\n";
                    echo "  ---\n";
                }
                
                $stats = $backup->getStats();
                echo "\n📊 Statistics:\n";
                echo "  Total backups: " . $stats['total_backups'] . "\n";
                echo "  Total size: " . $stats['total_size'] . "\n";
                echo "  Retention: " . $stats['retention_days'] . " days\n";
            }
            echo "\n";
            break;
            
        case 'restore':
            if (!isset($argv[2])) {
                echo "Usage: backup_database.php restore <filename or path>\n";
                exit(1);
            }
            
            $file_to_restore = $argv[2];
            
            // Check if it's just a filename
            if (!file_exists($file_to_restore)) {
                $file_to_restore = __DIR__ . '/../backups/' . $file_to_restore;
            }
            
            if (!file_exists($file_to_restore)) {
                echo "❌ Backup file not found\n";
                exit(1);
            }
            
            // Confirm before restoring
            echo "⚠️  WARNING: This will overwrite all database contents!\n";
            echo "Are you sure? (yes/no): ";
            
            if (php_sapi_name() === 'cli') {
                $input = trim(fgets(STDIN));
                if ($input !== 'yes') {
                    echo "Restore cancelled.\n";
                    exit(0);
                }
            }
            
            $result = $backup->restore($file_to_restore, true);
            echo "\n";
            break;
            
        case 'cleanup':
            echo "🧹 Cleaning up old backups...\n";
            $deleted = $backup->cleanupOldBackups();
            echo "Deleted: " . $deleted . " backups\n\n";
            break;
            
        case 'stats':
            echo "📊 Backup Statistics:\n\n";
            $stats = $backup->getStats();
            foreach ($stats as $key => $value) {
                echo "  " . ucfirst(str_replace('_', ' ', $key)) . ": " . $value . "\n";
            }
            echo "\n";
            break;
            
        default:
            echo "Usage:\n";
            echo "  create              Create new backup\n";
            echo "  list                List all backups\n";
            echo "  restore <file>      Restore from backup\n";
            echo "  cleanup             Delete old backups (>30 days)\n";
            echo "  stats               Show backup statistics\n";
            echo "\n";
    }
}

?>
