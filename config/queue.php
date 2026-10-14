<?php

/**
 * NovaFlow Queue Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | NovaFlow supports a variety of queue backends via a unified API.
    |
    | Supported Drivers: "sync", "file", "database", "redis"
    |
    */
    'default' => env('QUEUE_CONNECTION', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */
    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'file' => [
            'driver' => 'file',
            'path'   => BASE_PATH . '/storage/queue',
        ],

        'database' => [
            'driver' => 'database',
            'table'  => 'jobs',
            'queue'  => 'default',
        ],

        'redis' => [
            'driver'   => 'redis',
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => (int) env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => (int) env('REDIS_DB', 0),
            'queue'    => 'default',
        ],
    ],
];
