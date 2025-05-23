<?php

namespace App\Utils;

class Logger {
    private static $logFile;
    private static $initialized = false;
    private static $logLevels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    private static $currentLevel = 'INFO';
    
    public static function init($environment) {
        if (self::$initialized) {
            return;
        }
        
        $logDir = APP_ROOT . '/logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $date = date('Y-m-d');
        self::$logFile = $logDir . "/{$environment}_{$date}.log";
        self::$initialized = true;
        
        // Set minimum log level based on environment
        self::$currentLevel = $environment === 'production' ? 'WARNING' : 'DEBUG';
    }
    
    public static function setLogLevel($level) {
        if (!isset(self::$logLevels[$level])) {
            throw new \InvalidArgumentException("Invalid log level: {$level}");
        }
        self::$currentLevel = $level;
    }
    
    private static function shouldLog($level) {
        return self::$logLevels[$level] >= self::$logLevels[self::$currentLevel];
    }
    
    public static function log($message, $level = 'INFO', $context = []) {
        if (!self::$initialized) {
            throw new \RuntimeException('Logger not initialized');
        }
        
        if (!self::shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message} {$contextStr}\n";
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    public static function debug($message, $context = []) {
        self::log($message, 'DEBUG', $context);
    }
    
    public static function info($message, $context = []) {
        self::log($message, 'INFO', $context);
    }
    
    public static function warning($message, $context = []) {
        self::log($message, 'WARNING', $context);
    }
    
    public static function error($message, $context = []) {
        self::log($message, 'ERROR', $context);
    }
    
    public static function critical($message, $context = []) {
        self::log($message, 'CRITICAL', $context);
    }
} 