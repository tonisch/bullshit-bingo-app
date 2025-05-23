<?php
session_start();

// Define application root path
define('APP_ROOT', dirname(__DIR__));

// Autoloader
spl_autoload_register(function ($class) {
    $file = APP_ROOT . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load configuration
$config = require_once APP_ROOT . '/config/config.php';

// Initialize logger
\App\Utils\Logger::init($config['environment']);

// Log request
\App\Utils\Logger::info('Request received', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'ip' => $_SERVER['REMOTE_ADDR']
]);

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
    $router = new \App\Core\Router();
    
    // Define routes
    $router->get('/', function() {
        require_once APP_ROOT . '/src/views/home.php';
    });
    
    $router->get('/create-event', function() {
        require_once APP_ROOT . '/src/views/create-event.php';
    });
    
    $router->get('/join-event', function() {
        require_once APP_ROOT . '/src/views/join-event.php';
    });
    
    $router->get('/game', function() {
        require_once APP_ROOT . '/src/views/game.php';
    });
    
    $router->any('/api/*', function($path) {
        $api_path = APP_ROOT . '/public/' . $path . '/index.php';
        if (file_exists($api_path)) {
            require_once $api_path;
        } else {
            throw new \App\Exceptions\NotFoundException('API endpoint not found');
        }
    });
    
    // Handle the request
    $router->dispatch($path);
    
} catch (\App\Exceptions\NotFoundException $e) {
    \App\Utils\Logger::warning('Page not found', [
        'path' => $path,
        'message' => $e->getMessage()
    ]);
    require_once APP_ROOT . '/src/views/404.php';
} catch (\Exception $e) {
    \App\Utils\Logger::error('Application error', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if ($config['debug']) {
        throw $e;
    } else {
        require_once APP_ROOT . '/src/views/500.php';
    }
} 