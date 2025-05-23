<?php

class Database {
    private $conn;
    
    public function __construct() {
        $this->conn = getDbConnection();
    }
    
    public function initialize() {
        try {
            // Read and execute the schema file
            $sql = file_get_contents(__DIR__ . '/../../database/schema.sql');
            $this->conn->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Database initialization error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
} 