<?php
// Load environment-specific configuration
$env = getenv('APP_ENV') ?: 'production';
$configFile = __DIR__ . "/{$env}.php";

if (!file_exists($configFile)) {
    die("Configuration file for environment '{$env}' not found.");
}

require_once $configFile;

// Load database configuration
require_once __DIR__ . '/database.php';

// Set error reporting based on environment
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set default timezone
date_default_timezone_set($config['timezone'] ?? 'UTC'); 