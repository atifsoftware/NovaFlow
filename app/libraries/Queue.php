<?php

namespace NovaFlow\Core;

use NovaFlow\Core\Queue\QueueManager;
use NovaFlow\Core\Queue\Contracts\QueueDriverInterface;

/**
 * Queue Facade
 * Directs background jobs across configured queue drivers (Sync, File, Database, Redis).
 */
class Queue
{
    protected static ?QueueManager $manager = null;

    /**
     * Get or initialize the QueueManager
     */
    public static function getManager(): QueueManager
    {
        if (self::$manager === null) {
            $container = Container::getInstance();
            if ($container->has(QueueManager::class)) {
                self::$manager = $container->make(QueueManager::class);
            } else {
                $config = [];
                $configFile = defined('BASE_PATH') ? BASE_PATH . '/config/queue.php' : null;
                if ($configFile && file_exists($configFile)) {
                    $config = require $configFile;
                }

                self::$manager = new QueueManager($config);
                $container->singleton(QueueManager::class, fn() => self::$manager);
            }
        }

        return self::$manager;
    }

    /**
     * Set a custom QueueManager (for tests)
     */
    public static function setManager(?QueueManager $manager): void
    {
        self::$manager = $manager;
    }

    /**
     * Get a specific queue connection
     */
    public static function connection(?string $name = null): QueueDriverInterface
    {
        return self::getManager()->connection($name);
    }

    /**
     * Push a new job onto the queue
     */
    public static function push(Job|string $job, string $queue = 'default', int $delay = 0): mixed
    {
        return self::connection()->push($job, '', $queue, $delay);
    }

    /**
     * Pop the next available job from the queue
     */
    public static function pop(string $queue = 'default'): ?object
    {
        return self::connection()->pop($queue);
    }

    /**
     * Delete a job after success
     */
    public static function delete(mixed $id): bool
    {
        return self::connection()->delete($id);
    }

    /**
     * Release a job back to the queue (on failure)
     */
    public static function release(mixed $id, int $delay = 60): bool
    {
        return self::connection()->release($id, $delay);
    }

    /**
     * Get queue size
     */
    public static function size(string $queue = 'default'): int
    {
        return self::connection()->size($queue);
    }

    /**
     * Clear all jobs in queue
     */
    public static function clear(string $queue = 'default'): bool
    {
        return self::connection()->clear($queue);
    }

    /**
     * Forward static calls to default connection
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return self::connection()->{$method}(...$parameters);
    }
}
