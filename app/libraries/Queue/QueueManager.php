<?php

namespace NovaFlow\Core\Queue;

use NovaFlow\Core\Queue\Contracts\QueueDriverInterface;
use NovaFlow\Core\Queue\Drivers\SyncQueueDriver;
use NovaFlow\Core\Queue\Drivers\FileQueueDriver;
use NovaFlow\Core\Queue\Drivers\DatabaseQueueDriver;
use NovaFlow\Core\Queue\Drivers\RedisQueueDriver;
use InvalidArgumentException;

/**
 * Queue Manager
 * Resolves, caches, and directs queue drivers.
 */
class QueueManager
{
    protected array $connections = [];
    protected array $customCreators = [];
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get a queue connection instance
     */
    public function connection(?string $name = null): QueueDriverInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        return $this->connections[$name] = $this->resolve($name);
    }

    /**
     * Resolve the queue driver for connection
     */
    protected function resolve(string $name): QueueDriverInterface
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$name])) {
            return $this->customCreators[$name]($config);
        }

        $driver = $config['driver'] ?? $name;

        switch ($driver) {
            case 'sync':
                return new SyncQueueDriver();

            case 'file':
                return new FileQueueDriver($config);

            case 'database':
                return new DatabaseQueueDriver($config);

            case 'redis':
                return new RedisQueueDriver($config);

            default:
                throw new InvalidArgumentException("Queue driver [{$driver}] is not supported.");
        }
    }

    /**
     * Get queue configuration for connection
     */
    protected function getConfig(string $name): array
    {
        if (isset($this->config['connections'][$name])) {
            return $this->config['connections'][$name];
        }

        $base = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));

        $defaults = [
            'sync' => [
                'driver' => 'sync'
            ],
            'file' => [
                'driver' => 'file',
                'path'   => $base . '/storage/queue'
            ],
            'database' => [
                'driver' => 'database',
                'table'  => 'jobs'
            ],
            'redis' => [
                'driver'   => 'redis',
                'host'     => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port'     => (int)(getenv('REDIS_PORT') ?: 6379),
                'password' => getenv('REDIS_PASSWORD') ?: null,
                'database' => 0,
            ]
        ];

        return $defaults[$name] ?? ['driver' => $name];
    }

    /**
     * Get default queue connection name
     */
    public function getDefaultDriver(): string
    {
        return $this->config['default'] ?? (getenv('QUEUE_CONNECTION') ?: 'file');
    }

    /**
     * Register a custom driver creator
     */
    public function extend(string $driver, callable $callback): self
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * Forward calls to default driver
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->connection()->{$method}(...$parameters);
    }
}
