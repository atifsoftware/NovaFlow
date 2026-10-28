<?php
// Start output buffering and suppress direct error display to ensure clean JSON responses
ob_start();
ini_set('display_errors', '0');
error_reporting(E_ALL);

// ============================================================
//  NovaFlow MVC — Front Controller (Entry Point)
// ============================================================

// 0. Base Path Setup
define('BASE_PATH', dirname(__DIR__));

// 1. Composer & Autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
require_once dirname(__DIR__) . '/app/libraries/Autoloader.php';
require_once dirname(__DIR__) . '/app/libraries/helpers.php';

Autoloader::register();
Autoloader::addNamespace('NovaFlow\Core', dirname(__DIR__) . '/app/libraries');
Autoloader::addNamespace('App\Controllers', dirname(__DIR__) . '/app/controllers');
Autoloader::addNamespace('App\Models', dirname(__DIR__) . '/app/models');
Autoloader::addNamespace('App\Middleware', dirname(__DIR__) . '/app/middleware');
Autoloader::addNamespace('App\Services', dirname(__DIR__) . '/app/services');
Autoloader::addNamespace('App\Modules', dirname(__DIR__) . '/app/Modules');

// 2. Load Environment
\NovaFlow\Core\Dotenv::load(dirname(__DIR__) . '/.env');

// 3. Configuration & Database
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// 4. Register Error Handler (Intelligent Debugger in Local/Dev, Secure Logger in Production)
if (defined('APP_ENV') && (APP_ENV === 'development' || APP_ENV === 'local')) {
    \NovaFlow\Core\IntelligentErrorHandler::register();
} else {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) return false;
        error_log("[Production Error] $message in $file on line $line");
        return true;
    });

    set_exception_handler(function(\Throwable $e) {
        error_log("[Production Exception] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        if (ob_get_length()) ob_clean();

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $isApi = str_contains($uri, '/api/') || str_ends_with($uri, '/api') ||
                 (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        if ($isApi) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'status'  => 'error',
                'message' => 'Internal Server Error'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(500);
        $errorView = dirname(__DIR__) . '/app/views/errors/500.php';
        if (file_exists($errorView)) {
            require $errorView;
        } else {
            echo '<!DOCTYPE html><html><head><title>500 Error</title></head><body><h1>500 Internal Server Error</h1></body></html>';
        }
        exit;
    });
}

// 5. Bootstrap
require_once dirname(__DIR__) . '/app/bootstrap.php';

// 5. Secure Headers & Session (Stateless API routes bypass session_start for optimal performance)
\NovaFlow\Core\Request::setSecureHeaders();
$uri = $_SERVER['REQUEST_URI'] ?? '';
$isApi = str_contains($uri, '/api/') || str_ends_with($uri, '/api');

if (!$isApi && session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// 6. Router Initialization
$router = new \NovaFlow\Core\Router();

// Register the same router instance in the DI container so it's shared
\NovaFlow\Core\Container::getInstance()->singleton(\NovaFlow\Core\Router::class, function() use ($router) {
    return $router;
});

// 7. Load Routes (Web, Admin, API & ERP Modules)
$router->loadAllRoutes();

// 8. Resolve
$router->resolve();
