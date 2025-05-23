<?php
session_start();

// Load configuration
require_once __DIR__ . '/../config/database.php';

// Class autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Routing
$request = $_SERVER['REQUEST_URI'];

// Remove query string
$request = strtok($request, '?');

// Simple routing
switch ($request) {
    case '/':
    case '/index.php':
        require __DIR__ . '/../src/views/home.php';
        break;
    case '/create-event':
        require __DIR__ . '/../src/views/create-event.php';
        break;
    case '/join-event':
        require __DIR__ . '/../src/views/join-event.php';
        break;
    default:
        http_response_code(404);
        require __DIR__ . '/../src/views/404.php';
        break;
} 