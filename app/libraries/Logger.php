<?php

namespace NovaFlow\Core;

/**
 * Advanced Logging System
 * Provides structured logging with different levels and outputs
 */
class Logger
{
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    private static $logFile = 'logs/app.log';
    private static $errorLogFile = 'logs/error.log';
    private static $levels = [
        self::EMERGENCY => 0,
        self::ALERT => 1,
        self::CRITICAL => 2,
        self::ERROR => 3,
        self::WARNING => 4,
        self::NOTICE => 5,
        self::INFO => 6,
        self::DEBUG => 7,
    ];

    /**
     * Initialize logging directories
     */
    public static function init()
    {
        // Use BASE_PATH if defined, otherwise assume current directory
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        
        $appLog = $base . self::$logFile;
        $errLog = $base . self::$errorLogFile;

        $logDir = dirname($appLog);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log emergency message
     */
    public static function emergency($message, $context = []) { self::log(self::EMERGENCY, $message, $context); }
    public static function alert($message, $context = []) { self::log(self::ALERT, $message, $context); }
    public static function critical($message, $context = []) { self::log(self::CRITICAL, $message, $context); }
    public static function error($message, $context = []) { self::log(self::ERROR, $message, $context); }
    public static function warning($message, $context = []) { self::log(self::WARNING, $message, $context); }
    public static function notice($message, $context = []) { self::log(self::NOTICE, $message, $context); }
    public static function info($message, $context = []) { self::log(self::INFO, $message, $context); }
    public static function debug($message, $context = []) { self::log(self::DEBUG, $message, $context); }

    /**
     * Core logging method
     */
    public static function log($level, $message, $context = [])
    {
        self::init();

        $timestamp = date('Y-m-d H:i:s');
        $ip = Request::ip(); // Assuming Request::ip() exists or handles it
        $userId = $_SESSION['user_id'] ?? 'guest';

        // Format context
        $contextStr = empty($context) ? '' : ' | Context: ' . json_encode($context);

        // Create log entry
        $logEntry = sprintf(
            "[%s] %s | IP: %s | User: %s | %s: %s%s\n",
            $timestamp,
            strtoupper($level),
            $ip,
            $userId,
            $_SERVER['REQUEST_URI'] ?? 'CLI',
            $message,
            $contextStr
        );

        // Write to appropriate log file
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        $logPath = (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL, self::ERROR]))
            ? $base . self::$errorLogFile
            : $base . self::$logFile;

        file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);

        // For critical errors, also send email alert
        if (in_array($level, [self::EMERGENCY, self::ALERT, self::CRITICAL])) {
            self::sendAlert($level, $message, $context);
        }
    }

    /**
     * Send email alert for critical errors
     */
    private static function sendAlert($level, $message, $context)
    {
        $alertMessage = "CRITICAL ALERT: $level - $message";
        error_log($alertMessage);
    }

    /**
     * Log database query (for debugging)
     */
    public static function logQuery($query, $bindings = [], $executionTime = null)
    {
        $context = [
            'query' => $query,
            'bindings' => $bindings,
            'execution_time' => $executionTime
        ];

        if ($executionTime > 0.1) {
            self::warning("Slow query detected", $context);
        } else {
            self::debug("Database query executed", $context);
        }
    }

    /**
     * Log user activity
     */
    public static function logActivity($action, $details = [])
    {
        $context = array_merge($details, [
            'action' => $action,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct'
        ]);

        self::info("User activity: $action", $context);
    }

    /**
     * Log security events
     */
    public static function logSecurity($event, $details = [])
    {
        $context = array_merge($details, [
            'event' => $event,
            'severity' => 'high'
        ]);

        self::warning("Security event: $event", $context);
    }

    /**
     * Get recent logs
     */
    public static function getRecentLogs($lines = 50, $file = null)
    {
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        $file = $file ? $base . $file : $base . self::$logFile;

        if (!file_exists($file)) {
            return [];
        }

        $logs = file($file);
        return array_slice(array_reverse($logs), 0, $lines);
    }
}
