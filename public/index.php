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

// 1. Autoloading (Composer PSR-4)
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}
require_once BASE_PATH . '/app/libraries/helpers.php';

// 2. Load Environment (CRITICAL: MUST BE BEFORE CONFIG)
\NovaFlow\Core\Dotenv::load(BASE_PATH . '/.env');

// 3. Load Configuration & Session
require_once BASE_PATH . '/config/config.php';
session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4. Database & Error Handling
require_once BASE_PATH . '/config/database.php';
if (defined('APP_ENV') && (APP_ENV === 'development' || APP_ENV === 'local')) {
    \NovaFlow\Core\IntelligentErrorHandler::register();
}

// 5. Bootstrap
require_once BASE_PATH . '/app/bootstrap.php';

// 6. Secure Headers
\NovaFlow\Core\Request::setSecureHeaders();

// 7. Router Initialization
$router = new \NovaFlow\Core\Router();

// Register the same router instance in the DI container so it's shared
\NovaFlow\Core\Container::getInstance()->singleton(\NovaFlow\Core\Router::class, function() use ($router) {
    return $router;
});

// 7. Load Routes
require_once dirname(__DIR__) . '/config/routes.php';

// 8. Resolve
$router->resolve();
