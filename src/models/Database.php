<?php

class Database {
    private $pdo;
    private static $instance = null;
    
    public function __construct() {
        global $config;
        try {
            $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['database']};charset={$config['db']['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // Log connection attempt if debug is enabled
            if ($config['debug']) {
                Debug::log("Attempting database connection to: {$config['db']['host']}", 'debug');
            }

            $this->pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], $options);
            
            if ($config['debug']) {
                Debug::log("Database connection successful", 'info');
            }
        } catch (PDOException $e) {
            if ($config['debug']) {
                $error = "Database connection failed: " . $e->getMessage() . "\n";
                $error .= "DSN: " . $dsn . "\n";
                $error .= "Username: " . $config['db']['username'] . "\n";
                $error .= "Error Code: " . $e->getCode();
                Debug::log($error, 'error');
                die($error);
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // Log query if debug is enabled
            if (isset($GLOBALS['config']['debug']) && $GLOBALS['config']['debug']) {
                $query = $sql;
                foreach ($params as $key => $value) {
                    $query = str_replace($key, "'" . $value . "'", $query);
                }
                if (!isset($GLOBALS['db_queries'])) {
                    $GLOBALS['db_queries'] = [];
                }
                $GLOBALS['db_queries'][] = $query;
            }

            return $stmt;
        } catch (PDOException $e) {
            if (isset($GLOBALS['config']['debug']) && $GLOBALS['config']['debug']) {
                $error = "Database error: " . $e->getMessage() . "\n";
                $error .= "SQL: " . $sql . "\n";
                $error .= "Params: " . print_r($params, true);
                Debug::log($error, 'error');
                throw $e;
            } else {
                die("Database error occurred. Please try again later.");
            }
        }
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
} 