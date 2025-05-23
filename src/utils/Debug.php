<?php
class Debug {
    private static $log = [];
    private static $startTime;
    private static $startMemory;

    public static function init() {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }

    public static function log($message, $type = 'info') {
        self::$log[] = [
            'time' => microtime(true),
            'type' => $type,
            'message' => $message,
            'memory' => memory_get_usage()
        ];
    }

    public static function render() {
        if (!isset($GLOBALS['config']['debug']) || !$GLOBALS['config']['debug']) {
            return;
        }

        $output = '<div id="debug-panel" style="
            position: fixed;
            bottom: 0;
            right: 0;
            background: rgba(0,0,0,0.8);
            color: #fff;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 9999;
            width: 300px;
        ">';
        
        $output .= '<div style="margin-bottom: 10px;">
            <strong>Debug Information</strong>
            <span style="float: right; cursor: pointer;" onclick="document.getElementById(\'debug-panel\').style.display=\'none\'">[X]</span>
        </div>';

        // Execution time
        $executionTime = microtime(true) - self::$startTime;
        $output .= "<div>Execution Time: {$executionTime} seconds</div>";

        // Memory usage
        $memoryUsage = memory_get_usage() - self::$startMemory;
        $output .= "<div>Memory Usage: " . self::formatBytes($memoryUsage) . "</div>";

        // Database queries
        if (isset($GLOBALS['db_queries'])) {
            $output .= "<div>Database Queries: " . count($GLOBALS['db_queries']) . "</div>";
            foreach ($GLOBALS['db_queries'] as $query) {
                $output .= "<div style='margin-top: 5px; color: #aaa;'>" . htmlspecialchars($query) . "</div>";
            }
        }

        // Log messages
        if (!empty(self::$log)) {
            $output .= "<div style='margin-top: 10px;'><strong>Log Messages:</strong></div>";
            foreach (self::$log as $entry) {
                $time = number_format($entry['time'] - self::$startTime, 4);
                $output .= "<div style='margin-top: 5px; color: #" . self::getColorForType($entry['type']) . ";'>";
                $output .= "[{$time}s] [{$entry['type']}] " . htmlspecialchars($entry['message']);
                $output .= "</div>";
            }
        }

        $output .= '</div>';
        return $output;
    }

    private static function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private static function getColorForType($type) {
        $colors = [
            'info' => '00ff00',
            'warning' => 'ffff00',
            'error' => 'ff0000',
            'debug' => '00ffff'
        ];
        return $colors[$type] ?? 'ffffff';
    }
} 