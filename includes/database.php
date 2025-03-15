<?php
/**
 * Database Connection Handler
 */

// Include configuration
require_once __DIR__ . '/../config/config.php';

/**
 * Get database connection
 * 
 * @return PDO Database connection
 */
function getDbConnection() {
    static $pdo;
    
    if (!$pdo) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Execute a query and return all results
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array Results
 */
function dbQuery($sql, $params = []) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Query failed: " . $e->getMessage());
        } else {
            die("Database error. Please try again later.");
        }
    }
}

/**
 * Execute a query and return a single row
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return array|null Single row or null if not found
 */
function dbQuerySingle($sql, $params = []) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Query failed: " . $e->getMessage());
        } else {
            die("Database error. Please try again later.");
        }
    }
}

/**
 * Execute an insert, update, or delete query
 * 
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return int Number of affected rows
 */
function dbExecute($sql, $params = []) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Query failed: " . $e->getMessage());
        } else {
            die("Database error. Please try again later.");
        }
    }
}

/**
 * Get the last inserted ID
 * 
 * @return string Last inserted ID
 */
function dbLastInsertId() {
    return getDbConnection()->lastInsertId();
}
