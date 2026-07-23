<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Logger;

/**
 * LogRequestMiddleware
 * Logs every incoming request for auditing and performance monitoring.
 */
class LogRequestMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        $method = $request->getMethod();
        $path   = $request->getPath();
        $ip     = $request->ip();
        
        // Simple log entry
        $logMessage = "[$method] $path from $ip";
        
        // Use Core Logger if available, else use error_log
        if (class_exists('NovaFlow\Core\Logger')) {
            Logger::info($logMessage, 'request');
        } else {
            error_log("Jahin-Web Request: " . $logMessage);
        }

        return true; // Proceed to next middleware/controller
    }
}
