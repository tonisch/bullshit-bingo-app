<?php

namespace App\Core;

class Router {
    private $routes = [];
    private $currentMethod;
    
    public function __construct() {
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
    }
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function any($path, $handler) {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE'], $path, $handler);
    }
    
    private function addRoute($method, $path, $handler) {
        if (!is_array($method)) {
            $method = [$method];
        }
        
        foreach ($method as $m) {
            $this->routes[] = [
                'method' => $m,
                'path' => $path,
                'handler' => $handler
            ];
        }
    }
    
    public function dispatch($path) {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $this->currentMethod) {
                continue;
            }
            
            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Remove full match
                return call_user_func_array($route['handler'], $matches);
            }
        }
        
        throw new \App\Exceptions\NotFoundException('Route not found');
    }
    
    private function convertPathToRegex($path) {
        // Convert path parameters to regex patterns
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        // Add start and end anchors
        return '/^' . $pattern . '$/';
    }
} 