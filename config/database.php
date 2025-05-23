<?php
try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['database']};charset={$config['db']['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password'], $options);
} catch (PDOException $e) {
    if ($config['debug']) {
        die("Connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
} 