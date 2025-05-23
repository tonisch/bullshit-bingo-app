<?php
require_once __DIR__ . '/config.php';

function getDbConnection() {
    global $config;
    
    try {
        $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['database']};charset={$config['db']['charset']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        if ($config['debug']) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        } else {
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
} 