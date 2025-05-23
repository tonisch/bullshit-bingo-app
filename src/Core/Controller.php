<?php

namespace App\Core;

class Controller {
    protected $config;
    protected $view;
    
    public function __construct() {
        global $config;
        $this->config = $config;
        $this->view = new View();
    }
    
    protected function render($template, $data = []) {
        return $this->view->render($template, $data);
    }
    
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    protected function getPostData() {
        return $_POST;
    }
    
    protected function getQueryParams() {
        return $_GET;
    }
    
    protected function getJsonInput() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
    
    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Field {$field} is required";
            }
        }
        return $errors;
    }
} 