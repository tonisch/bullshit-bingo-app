<?php

namespace App\Core;

class View {
    private $layout = 'default';
    private $templateDir;
    
    public function __construct() {
        $this->templateDir = APP_ROOT . '/src/views';
    }
    
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    
    public function render($template, $data = []) {
        // Extract data to make variables available in template
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        $templateFile = "{$this->templateDir}/{$template}.php";
        if (!file_exists($templateFile)) {
            throw new \App\Exceptions\NotFoundException("Template not found: {$template}");
        }
        
        require $templateFile;
        
        // Get the contents of the buffer
        $content = ob_get_clean();
        
        // If no layout is specified, return the content directly
        if ($this->layout === false) {
            return $content;
        }
        
        // Include the layout
        $layoutFile = "{$this->templateDir}/layouts/{$this->layout}.php";
        if (!file_exists($layoutFile)) {
            throw new \App\Exceptions\NotFoundException("Layout not found: {$this->layout}");
        }
        
        require $layoutFile;
    }
    
    public function partial($template, $data = []) {
        extract($data);
        $templateFile = "{$this->templateDir}/partials/{$template}.php";
        if (!file_exists($templateFile)) {
            throw new \App\Exceptions\NotFoundException("Partial not found: {$template}");
        }
        require $templateFile;
    }
    
    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
} 