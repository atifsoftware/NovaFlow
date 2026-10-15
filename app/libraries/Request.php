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
     * Get request method (supports RESTful method spoofing via _method or X-HTTP-Method-Override)
     */
    public static function method()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        if ($method === 'POST') {
            if (!empty($_POST['_method'])) {
                return strtoupper((string)$_POST['_method']);
            }
            if (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                return strtoupper((string)$_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            }
        }

        return $method;
    }

    /**
     * Get the current request path
     */
    public static function getPath()
    {
        if (isset($_GET['url'])) {
            $path = $_GET['url'];
        } else {
            $uri = $_SERVER['REQUEST_URI'] ?? '/';
            $path = parse_url($uri, PHP_URL_PATH) ?? '/';
            
            // Strip BASE_URL prefix if running in subfolder
            if (defined('BASE_URL') && BASE_URL !== '' && BASE_URL !== '/') {
                $base = rtrim(BASE_URL, '/');
                if (str_starts_with($path, $base)) {
                    $path = substr($path, strlen($base));
                }
            }
        }

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
     * Get all request headers
     */
    public static function headers(): array
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if ($headers !== false) {
                return $headers;
            }
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            } elseif (in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'])) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get specific request header (case-insensitive)
     */
    public static function header(string $key, ?string $default = null): ?string
    {
        $headers = self::headers();
        $target = strtolower(str_replace(['_', '-'], '', $key));
        foreach ($headers as $name => $value) {
            if (strtolower(str_replace(['_', '-'], '', $name)) === $target) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * Auth User helpers
     */
    public static function setUser($user) { self::$user = $user; }
    public static function getUser() { return self::$user; }

    /**
     * Get old flashed input from session
     */
    public static function old(?string $key = null, mixed $default = null): mixed
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($key === null) {
            return $_SESSION['_old_input'] ?? [];
        }
        return $_SESSION['_old_input'][$key] ?? $default;
    }

    /**
     * Determine if the current request expects a JSON response
     */
    public static function wantsJson(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return self::isAjax() 
            || str_starts_with(self::getPath(), '/api') 
            || str_contains($accept, '/json') 
            || str_contains($accept, '+json');
    }

    /**
     * Validate current request inputs against validation rules
     */
    public static function validate(array $rules, array $messages = [], array $labels = []): array
    {
        $data = self::all();
        $validator = Validator::make($data, $rules, $messages, $labels);

        if ($validator->fails()) {
            $errors = $validator->getErrors();

            if (self::wantsJson()) {
                if (ob_get_length()) ob_clean();
                http_response_code(422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'The given data was invalid.',
                    'errors' => $errors
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['_old_input'] = $_POST;
            $_SESSION['_flash_errors'] = $errors;

            $referer = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL : '/');
            header('Location: ' . $referer);
            exit;
        }

        return $validator->validated();
    }
}
