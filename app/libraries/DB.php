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
     * Start a new query builder for a table
     */
    public static function table(string $table): QueryBuilder
    {
        return (new QueryBuilder(self::getInstance()))->table($table);
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
