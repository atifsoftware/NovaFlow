<?php

namespace NovaFlow\Core;

use NovaFlow\Core\Database\Drivers\MySqlConnection;

/**
 * PDO Database Driver
 * Maintained as a backwards-compatible alias for MySqlConnection
 */
class PDODriver extends MySqlConnection
{
    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $config = [
                'host' => defined('DB_HOST') ? DB_HOST : '127.0.0.1',
                'username' => defined('DB_USER') ? DB_USER : 'root',
                'password' => defined('DB_PASS') ? DB_PASS : '',
                'database' => defined('DB_NAME') ? DB_NAME : '',
                'port' => defined('DB_PORT') ? DB_PORT : 3306,
                'charset' => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
            ];
        }

        parent::__construct($config);
    }
}
