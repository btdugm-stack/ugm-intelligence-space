<?php
/**
 * Database Connection Manager
 * Provides PDO connection with connection pooling support
 */

class Database {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                DB_CONNECTION ?? 'mysql',
                DB_HOST ?? 'localhost',
                DB_PORT ?? 3306,
                DB_DATABASE ?? 'ugm_intelligence'
            );
            
            $this->pdo = new PDO(
                $dsn,
                DB_USERNAME ?? 'root',
                DB_PASSWORD ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                ]
            );
            
            // Set timezone
            $this->pdo->exec("SET time_zone = '+07:00'");
            
            Logger::debug('Database connection established', [
                'host' => DB_HOST,
                'database' => DB_DATABASE
            ]);
            
        } catch (PDOException $e) {
            Logger::critical('Database connection failed', [
                'error' => $e->getMessage(),
                'host' => DB_HOST ?? 'localhost',
                'database' => DB_DATABASE ?? 'unknown'
            ]);
            
            if (APP_DEBUG) {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            } else {
                throw new Exception('Database connection failed. Please contact administrator.');
            }
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error('Database query error', [
                'query' => $sql,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->pdo = null;
        self::$instance = null;
    }
}

/**
 * Global database helper function
 */
function db() {
    return Database::getInstance()->getConnection();
}

?>
