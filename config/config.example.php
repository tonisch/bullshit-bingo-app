<?php
/**
 * Example configuration file for Bullshit Bingo
 * 
 * Copy this file to config/local.php or config/production.php
 * and adjust the values according to your environment.
 */

return [
    'environment' => 'local', // or 'production'
    'debug' => true, // Set to false in production
    'timezone' => 'Europe/Berlin',
    'app_url' => 'http://' . $_SERVER['HTTP_HOST'], // Change to https:// in production
    'app_name' => 'Bullshit Bingo',
    
    // Database configuration
    'db' => [
        'host' => 'localhost',
        'database' => 'bullshit_bingo',
        'username' => 'your_username',
        'password' => 'your_password',
        'charset' => 'utf8',
        'collation' => 'utf8_general_ci',
    ],
    
    // WebSocket configuration
    'websocket' => [
        'host' => $_SERVER['HTTP_HOST'],
        'port' => 8080,
    ],

    // Logging configuration
    'logging' => [
        'enabled' => true,
        'level' => 'DEBUG', // DEBUG, INFO, WARNING, ERROR, CRITICAL
        'max_files' => 30, // Number of days to keep log files
    ],

    // Session configuration
    'session' => [
        'lifetime' => 120, // Session lifetime in minutes
        'path' => '/',
        'domain' => null, // Set to your domain in production
        'secure' => false, // Set to true in production
        'httponly' => true,
    ],

    // Security settings
    'security' => [
        'allowed_origins' => ['*'], // Add your domains in production
        'cors_headers' => [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ],
    ],
]; 