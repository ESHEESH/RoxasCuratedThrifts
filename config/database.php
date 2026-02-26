<?php
/**
 * Database Configuration File
 * 
 * This file handles the database connection using PDO.
 * All database interactions should use prepared statements
 * to prevent SQL injection attacks.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

// Prevent direct access to this file
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Database credentials - Change these for production
// For XAMPP, default is: host=localhost, user=root, password='', database=thrift_store
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Default XAMPP password is empty
define('DB_NAME', 'thrift_store');
define('DB_CHARSET', 'utf8mb4');

/**
 * Class Database
 * 
 * Singleton pattern for database connection management.
 * Ensures only one database connection exists throughout the application.
 */
class Database {
    /** @var PDO|null The PDO connection instance */
    private static ?PDO $instance = null;
    
    /**
     * Get database connection instance
     * 
     * Creates a new PDO connection if one doesn't exist.
     * Uses UTF-8 charset and enables error reporting for debugging.
     * 
     * @return PDO The database connection
     * @throws PDOException If connection fails
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Throw exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Fetch associative arrays
                    PDO::ATTR_EMULATE_PREPARES => false,                // Use real prepared statements
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
                ];
                
                self::$instance = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
                
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database connection failed. Please try again later.");
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Close database connection
     * 
     * Call this when you need to explicitly close the connection.
     * Usually not needed as PHP closes connections automatically.
     */
    public static function closeConnection(): void {
        self::$instance = null;
    }
    
    /**
     * Check if database is connected
     * 
     * @return bool True if connected, false otherwise
     */
    public static function isConnected(): bool {
        return self::$instance !== null;
    }
}

/**
 * Execute a prepared query with parameters
 * 
 * Helper function to execute prepared statements safely.
 * Prevents SQL injection by using parameter binding.
 * 
 * @param string $sql The SQL query with placeholders
 * @param array $params Array of parameters to bind
 * @return PDOStatement The executed statement
 * @throws PDOException If query fails
 */
function executeQuery(string $sql, array $params = []): PDOStatement {
    $db = Database::getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch a single row from database
 * 
 * @param string $sql The SQL query
 * @param array $params Query parameters
 * @return array|null The row data or null if not found
 */
function fetchOne(string $sql, array $params = []): ?array {
    $stmt = executeQuery($sql, $params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Fetch all rows from database
 * 
 * @param string $sql The SQL query
 * @param array $params Query parameters
 * @return array Array of rows
 */
function fetchAll(string $sql, array $params = []): array {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Get the last inserted ID
 * 
 * @return string The last insert ID
 */
function getLastInsertId(): string {
    return Database::getConnection()->lastInsertId();
}

/**
 * Begin a database transaction
 */
function beginTransaction(): void {
    Database::getConnection()->beginTransaction();
}

/**
 * Commit a database transaction
 */
function commitTransaction(): void {
    Database::getConnection()->commit();
}

/**
 * Rollback a database transaction
 */
function rollbackTransaction(): void {
    Database::getConnection()->rollBack();
}
