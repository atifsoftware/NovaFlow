<?php
// ============================================================
//  NovaFlow MVC — Database Configuration
// ============================================================

use NovaFlow\Core\Dotenv;

// Supported connections: 'mysql', 'pgsql', 'sqlite'
$connection = Dotenv::get('DB_CONNECTION', Dotenv::get('DB_DRIVER', 'mysql'));

if (!defined('DB_CONNECTION')) {
    define('DB_CONNECTION', $connection);
}

if (!defined('DB_HOST')) define('DB_HOST', Dotenv::get('DB_HOST', 'localhost'));
if (!defined('DB_PORT')) define('DB_PORT', (int) Dotenv::get('DB_PORT', 3306));
if (!defined('DB_USER')) define('DB_USER', Dotenv::get('DB_USER', 'root'));
if (!defined('DB_PASS')) define('DB_PASS', Dotenv::get('DB_PASS', ''));
if (!defined('DB_NAME')) define('DB_NAME', Dotenv::get('DB_NAME', 'novaflow_db'));
if (!defined('DB_DRIVER')) define('DB_DRIVER', Dotenv::get('DB_DRIVER', 'pdo'));
if (!defined('DB_CHARSET')) define('DB_CHARSET', Dotenv::get('DB_CHARSET', 'utf8mb4'));
