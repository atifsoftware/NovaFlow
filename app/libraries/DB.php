<?php

namespace NovaFlow\Core;

use NovaFlow\Core\QueryBuilder\QueryBuilder;

/**
 * DB Facade for QueryBuilder
 */
class DB
{
    private static $instance;

    /**
     * Get the database driver from container
     */
    private static function getInstance(): DatabaseInterface
    {
        return Container::getInstance()->make(DatabaseInterface::class);
    }

    /**
     * Get a specific connection instance
     */
    public static function connection(?string $name = null): DatabaseInterface
    {
        return Container::getInstance()->make(\NovaFlow\Core\Database\DatabaseManager::class)->connection($name);
    }

    /**
     * Start a new query builder for a table
     */
    public static function table(string $table, ?string $connection = null): QueryBuilder
    {
        $conn = $connection ? self::connection($connection) : self::getInstance();
        return (new QueryBuilder($conn))->table($table);
    }

    /**
     * Create a raw expression
     */
    public static function raw($value, array $bindings = []): RawExpression
    {
        return new RawExpression($value, $bindings);
    }

    /**
     * Execute a callback inside a database transaction
     */
    public static function transaction(callable $callback, int $attempts = 1): mixed
    {
        return self::getInstance()->transaction($callback, $attempts);
    }

    /**
     * Start a new database transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit the active transaction
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Rollback the active transaction
     */
    public static function rollback(): bool
    {
        return self::getInstance()->rollback();
    }

    /**
     * Check if currently inside a transaction
     */
    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }

    /**
     * Get the current transaction depth level
     */
    public static function transactionLevel(): int
    {
        return self::getInstance()->transactionLevel();
    }

    /**
     * Pass static calls to Database instance (like queryInstance, fetchAllInstance, etc.)
     */
    public static function __callStatic($method, $args)
    {
        try {
            $instance = self::getInstance();
            if (method_exists($instance, $method)) {
                return call_user_func_array([$instance, $method], $args);
            }
            throw new \Exception("Method $method does not exist on the database driver (" . get_class($instance) . ").");
        } catch (\Exception $e) {
            if (defined('APP_ENV') && APP_ENV === 'local') {
                Logger::error("FACADE CALL: $method", ['args' => $args, 'error' => $e->getMessage()]);
            }
            throw $e;
        }
    }
}
