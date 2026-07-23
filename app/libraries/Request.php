<?php

namespace NovaFlow\Core;

/**
 * Request Class
 * Handles input gathering, sanitization, and security headers
 */
class Request
{
    private static $jsonBuffer = null;
    private static $user = null; // Store authenticated user

    /**
     * Get request method
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Get the current request path
     */
    public static function getPath()
    {
        $path = $_GET['url'] ?? '/';
        $path = '/' . trim($path, '/');
        
        // Remove query strings if they exist
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        
        return $path;
    }

    /**
     * Get the current request method in lowercase
     */
    public static function getMethod()
    {
        return strtolower(self::method());
    }

    /**
     * Get POST data
     */
    public static function post($key = null, $default = null, $sanitize = true)
    {
        if ($key === null) {
            return $sanitize ? Security::cleanInput($_POST) : $_POST;
        }
        $value = $_POST[$key] ?? $default;
        return $sanitize ? Security::cleanInput($value) : $value;
    }

    /**
     * Get GET data
     */
    public static function get($key = null, $default = null, $sanitize = true)
    {
        if ($key === null) {
            return $sanitize ? Security::cleanInput($_GET) : $_GET;
        }
        $value = $_GET[$key] ?? $default;
        return $sanitize ? Security::cleanInput($value) : $value;
    }

    /**
     * Get JSON input
     */
    public static function json($key = null, $default = null, $sanitize = true)
    {
        if (self::$jsonBuffer === null) {
            $input = file_get_contents('php://input');
            self::$jsonBuffer = json_decode($input, true) ?: [];
        }

        if ($key === null) {
            return $sanitize ? Security::cleanInput(self::$jsonBuffer) : self::$jsonBuffer;
        }

        $value = self::$jsonBuffer[$key] ?? $default;
        return $sanitize ? Security::cleanInput($value) : $value;
    }

    /**
     * Get input from any source (GET, POST, JSON)
     */
    public static function input($key, $default = null, $sanitize = true)
    {
        $data = array_merge($_GET, $_POST, self::json(null, [], false));
        $value = $data[$key] ?? $default;
        return $sanitize ? Security::cleanInput($value) : $value;
    }

    /**
     * Get all input data
     */
    public static function all($sanitize = true)
    {
        $data = array_merge($_GET, $_POST, self::json(null, [], false));
        return $sanitize ? Security::cleanInput($data) : $data;
    }

    /**
     * Check request type
     */
    public static function isPost() { return self::method() === 'POST'; }
    public static function isGet() { return self::method() === 'GET'; }
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Security Headers
     */
    public static function setSecureHeaders()
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        $isProduction = defined('APP_ENV') && APP_ENV === 'production';
        if ($isProduction) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * CSRF Protection Facade
     */
    public static function generateCsrfToken() { return Security::generateCSRFToken(); }
    public static function validateCsrfToken($token) { return Security::validateCSRFToken($token); }

    /**
     * IP Address
     */
    public static function ip()
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Get uploaded file data
     * 
     * @param string|null $key
     * @return array|null
     */
    public static function file($key = null)
    {
        if ($key === null) {
            return $_FILES;
        }

        return $_FILES[$key] ?? null;
    }

    /**
     * Get request header value
     */
    public static function header(string $name, ?string $default = null): ?string
    {
        $name = str_replace('-', '_', strtoupper($name));
        if (isset($_SERVER['HTTP_' . $name])) {
            return $_SERVER['HTTP_' . $name];
        }
        if (in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH']) && isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $key => $val) {
                if (strcasecmp($key, str_replace('_', '-', $name)) === 0) {
                    return $val;
                }
            }
        }
        return $default;
    }

    /**
     * Auth User helpers
     */
    public static function setUser($user) { self::$user = $user; }
    public static function getUser() { return self::$user; }
}
