<?php
/**
 * NovaFlow MVC — Application Bootstrap
 */

// 1. Directory & Filesystem Setup
$dirs = [
    dirname(__DIR__) . '/public/assets/css',
    dirname(__DIR__) . '/public/assets/js',
    dirname(__DIR__) . '/public/assets/images',
    dirname(__DIR__) . '/public/uploads',
    dirname(__DIR__) . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// 2. Initialize DI Container
$container = \NovaFlow\Core\Container::getInstance();

// 3. Register Service Providers
$providers = [
    \NovaFlow\Core\AppServiceProvider::class
];

foreach ($providers as $providerClass) {
    if (!class_exists($providerClass)) continue;
    $provider = new $providerClass($container);
    $provider->register();
    $provider->boot();
}

// 4. Global Constants
define('TIMEZONE', 'Asia/Dhaka');
date_default_timezone_set(TIMEZONE);
