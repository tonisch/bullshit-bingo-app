<?php

// Load environment configuration
$environment = getenv('APP_ENV') ?: 'local';
$configFile = __DIR__ . "/{$environment}.php";

if (!file_exists($configFile)) {
    throw new Exception("Configuration file for environment '{$environment}' not found");
}

$config = require $configFile;

// Set timezone
date_default_timezone_set($config['timezone']);

// Error reporting based on environment
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

return $config; 