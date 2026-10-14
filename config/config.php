<?php
// ============================================================
//  NovaFlow MVC — Configuration Settings
// ============================================================

// App URL Root
define('BASE_URL', '/NovaFlow');

// Site Name
define('APP_NAME', 'NovaFlow PHP Framework');

// App Version
define('APP_VERSION', '1.0.0');

// Environment (local, production)
define('APP_ENV', 'local');

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Session config
define('SESSION_NAME', 'NovaFlow_Session');
define('SESSION_LIFETIME', 3600);

// Path Constants
define('APP_PATH', dirname(__DIR__) . '/app');
define('VIEW_PATH', APP_PATH . '/views');
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads');

// Default Controller & Method
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_METHOD', 'index');
