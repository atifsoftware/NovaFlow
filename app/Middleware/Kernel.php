<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;


/**
 * Middleware Kernel
 * Central place to register middleware aliases and global middlewares
 */
class Kernel
{
    /**
     * Middlewares that run on every request
     */
    public static array $global = [
        LogRequestMiddleware::class,
    ];

    /**
     * Middleware groups (e.g. web, api)
     */
    public static array $groups = [
        'web' => [
            'csrf',
        ],
        'api' => [
            'api', // Refers to the alias below
        ],
    ];

    /**
     * Short names for middleware classes
     */
    public static array $aliases = [
        'auth'      => AuthMiddleware::class,
        'guest'     => GuestMiddleware::class,
        'admin'    => AdminMiddleware::class,
        'api'      => ApiAuthMiddleware::class,
        'role'     => RoleMiddleware::class,
        'log'      => LogRequestMiddleware::class,
        'rate'     => RateLimitMiddleware::class,
        'csrf'     => \NovaFlow\Core\CSRFMiddleware::class,
    ];

    /**
     * Resolve middleware name (alias) to class name
     */
    public static function resolve($name)
    {
        return self::$aliases[$name] ?? $name;
    }
}
