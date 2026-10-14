<?php

/**
 * NovaFlow Filesystem Disks Configuration
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application.
    |
    */
    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver.
    |
    | Supported Drivers: "local", "s3"
    |
    */
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => BASE_PATH . '/storage/app',
            'url'    => env('APP_URL', 'http://localhost/NovaFlow') . '/storage',
        ],

        'public' => [
            'driver' => 'local',
            'root'   => BASE_PATH . '/public/uploads',
            'url'    => env('APP_URL', 'http://localhost/NovaFlow') . '/uploads',
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID', ''),
            'secret'   => env('AWS_SECRET_ACCESS_KEY', ''),
            'region'   => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'   => env('AWS_BUCKET', ''),
            'url'      => env('AWS_URL', null),
            'endpoint' => env('AWS_ENDPOINT', null),
        ],
    ],
];
