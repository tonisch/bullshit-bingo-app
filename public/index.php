<?php
session_start();

// Load configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/utils/Debug.php';

// Initialize debug
Debug::init();

// Log the request
Debug::log("Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Get the requested path
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove script name from path if it exists
if (strpos($path, $script_name) === 0) {
    $path = substr($path, strlen($script_name));
}

// Remove leading slash
$path = ltrim($path, '/');

// Route the request
try {
    if (empty($path)) {
        // Home page
        require_once __DIR__ . '/../src/views/home.php';
    } else if ($path === 'create-event') {
        // Create event page
        require_once __DIR__ . '/../src/views/create-event.php';
    } else if ($path === 'join-event') {
        // Join event page
        require_once __DIR__ . '/../src/views/join-event.php';
    } else if ($path === 'game') {
        // Game page
        require_once __DIR__ . '/../src/views/game.php';
    } else if (strpos($path, 'api/') === 0) {
        // API endpoints
        $api_path = __DIR__ . '/../' . $path . '/index.php';
        if (file_exists($api_path)) {
            require_once $api_path;
        } else {
            throw new Exception('API endpoint not found');
        }
    } else {
        // 404 page
        require_once __DIR__ . '/../src/views/404.php';
    }
} catch (Exception $e) {
    Debug::log($e->getMessage(), 'error');
    if ($config['debug']) {
        throw $e;
    } else {
        require_once __DIR__ . '/../src/views/404.php';
    }
}

// Output debug information if enabled
if ($config['debug']) {
    echo Debug::render();
} 