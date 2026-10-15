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

// 2. Load Environment
\NovaFlow\Core\Dotenv::load(dirname(__DIR__) . '/.env');

// 3. Configuration & Database
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// 4. Conditional Register Error Handler (Only for Development/Local)
if (defined('APP_ENV') && (APP_ENV === 'development' || APP_ENV === 'local')) {
    \NovaFlow\Core\IntelligentErrorHandler::register();
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
