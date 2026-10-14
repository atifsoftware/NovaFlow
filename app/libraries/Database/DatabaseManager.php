<?php

namespace NovaFlow\Core\Database;

use NovaFlow\Core\Database\Contracts\ConnectionInterface;
use NovaFlow\Core\Database\Drivers\MySqlConnection;
use NovaFlow\Core\Database\Drivers\PostgresConnection;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use InvalidArgumentException;

/**
 * Database Manager
 * Factory and registry for database connections
 */
class DatabaseManager
{
    /**
     * Active connection instances
     */
    protected array $connections = [];

    /**
     * Database configuration
     */
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get a database connection instance
     */
    public function connection(?string $name = null): ConnectionInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Get default database driver name
     */
    public function getDefaultDriver(): string
    {
        if (defined('DB_CONNECTION')) {
            return strtolower(DB_CONNECTION);
        }

        if (defined('DB_DRIVER')) {
            $driver = strtolower(DB_DRIVER);
            if ($driver === 'pdo' || $driver === 'mysqli') {
                return 'mysql';
            }
            return $driver;
        }

        return 'mysql';
    }

    /**
     * Create a new connection instance
     */
    protected function makeConnection(string $name): ConnectionInterface
    {
        $config = $this->config[$name] ?? [];

        switch (strtolower($name)) {
            case 'mysql':
            case 'pdo':
            case 'mysqli':
                return new MySqlConnection($config);

            case 'pgsql':
            case 'postgres':
            case 'postgresql':
                return new PostgresConnection($config);

            case 'sqlite':
                return new SqliteConnection($config);

            default:
                throw new InvalidArgumentException("Unsupported database driver: [{$name}].");
        }
    }

    /**
     * Dynamically pass methods to default connection
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}
