<?php
// ============================================================
//  NovaFlow MVC — Route Configuration
// ============================================================

/**
 * Load route files from the routes/ directory
 * This allows modular route management for better maintainability
 */

$routeDirectory = __DIR__ . '/../routes';

if (is_dir($routeDirectory)) {
    $routeFiles = [
        'web.php',      // Frontend & Auth routes
        'admin.php',    // Admin panel routes
        'api.php',      // API routes
    ];

    foreach ($routeFiles as $file) {
        $filePath = $routeDirectory . '/' . $file;
        if (file_exists($filePath)) {
            require_once $filePath;
        }
    }
}
