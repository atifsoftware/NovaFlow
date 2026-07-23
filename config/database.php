<?php
// ============================================================
//  NovaFlow MVC — Database Configuration
// ============================================================

// Database configuration loaded from .env or defaults
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'novaflow_db');
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'pdo');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
