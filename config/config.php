<?php
// ============================================================
//  NovaFlow MVC — Configuration Settings
// ============================================================

use NovaFlow\Core\Dotenv;

// App URL Root
define('BASE_URL', Dotenv::get('BASE_URL', '/NovaFlow'));

// Site Name
define('APP_NAME', Dotenv::get('APP_NAME', 'NovaFlow PHP Framework'));

// App Version
define('APP_VERSION', '1.0.0');

// Environment (local, production)
define('APP_ENV', Dotenv::get('APP_ENV', 'local'));

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Session config
define('SESSION_NAME', Dotenv::get('SESSION_NAME', 'NovaFlow_Session'));
define('SESSION_LIFETIME', (int)Dotenv::get('SESSION_LIFETIME', 3600));

// Path Constants
define('APP_PATH', dirname(__DIR__) . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads');

// Default Controller & Method
define('DEFAULT_CONTROLLER', Dotenv::get('DEFAULT_CONTROLLER', 'Home'));
define('DEFAULT_METHOD', Dotenv::get('DEFAULT_METHOD', 'index'));
