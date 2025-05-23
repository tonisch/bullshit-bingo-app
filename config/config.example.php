<?php
/**
 * Example configuration file for Bullshit Bingo
 * 
 * Copy this file to config/local.php or config/production.php
 * and adjust the values according to your environment.
 */

$config = [
    // Application settings
    'debug' => true, // Set to false in production
    'timezone' => 'Europe/Berlin',
    'app_url' => 'http://localhost:8000', // Change to your domain in production
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
        'host' => 'localhost', // Change to your domain in production
        'port' => 8080,
    ],

    // Optional: Session configuration
    'session' => [
        'lifetime' => 120, // Session lifetime in minutes
        'path' => '/',
        'domain' => null, // Set to your domain in production
        'secure' => false, // Set to true in production
        'httponly' => true,
    ],

    // Optional: Security settings
    'security' => [
        'allowed_origins' => ['http://localhost:8000'], // Add your domains
        'cors_headers' => [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ],
    ],
]; 