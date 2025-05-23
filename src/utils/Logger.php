<?php

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

    public static function init($environment) {
        if (self::$initialized) {
            return;
        }

        $logDir = __DIR__ . '/../../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $date = date('Y-m-d');
        self::$logFile = $logDir . "/{$environment}_{$date}.log";
        self::$initialized = true;
    }

    public static function log($message, $level = 'INFO', $context = []) {
        if (!self::$initialized) {
            throw new Exception('Logger not initialized');
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
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