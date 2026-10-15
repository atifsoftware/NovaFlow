<?php
// ============================================================
//  NovaFlow MVC — Configuration Settings
// ============================================================

use NovaFlow\Core\Dotenv;

// App URL Root
if (!defined('BASE_URL')) {
    define('BASE_URL', Dotenv::get('BASE_URL', '/NovaFlow'));
}

// Site Name
if (!defined('APP_NAME')) {
    define('APP_NAME', Dotenv::get('APP_NAME', 'NovaFlow PHP Framework'));
}

// App Version
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '2.1.0');
}

// Environment (local, development, production)
if (!defined('APP_ENV')) {
    define('APP_ENV', Dotenv::get('APP_ENV', 'local'));
}

// Timezone
date_default_timezone_set(Dotenv::get('TIMEZONE', 'Asia/Dhaka'));

// Session config
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', Dotenv::get('SESSION_NAME', 'NovaFlow_Session'));
}
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', (int) Dotenv::get('SESSION_LIFETIME', 3600));
}

// Path Constants
if (!defined('APP_PATH')) define('APP_PATH', dirname(__DIR__) . '/app');
if (!defined('VIEW_PATH')) define('VIEW_PATH', APP_PATH . '/views');
if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads');

// Default Controller & Method
if (!defined('DEFAULT_CONTROLLER')) define('DEFAULT_CONTROLLER', 'Home');
if (!defined('DEFAULT_METHOD')) define('DEFAULT_METHOD', 'index');
