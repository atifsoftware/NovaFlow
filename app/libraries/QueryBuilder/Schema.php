<?php

namespace NovaFlow\Core\QueryBuilder;

use Closure;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Container;

/**
 * Schema - Database Schema Builder
 */
class Schema
{
    protected static $db;

    public static function setDatabase(DatabaseInterface $db)
    {
        static::$db = $db;
    }

    protected static function getDatabase()
    {
        if (!static::$db) {
            static::$db = Container::getInstance()->make(DatabaseInterface::class);
        }
        return static::$db;
    }

    public static function create($table, Closure $callback)
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $sql = $blueprint->toSql();
        static::getDatabase()->query($sql);
    }

    public static function drop($table)
    {
        $sql = "DROP TABLE IF EXISTS `$table`";
        static::getDatabase()->query($sql);
    }

    public static function dropIfExists($table)
    {
        static::drop($table);
    }

    public static function table($table, Closure $callback)
    {
        $blueprint = new Blueprint($table, true); // true for altering
        $callback($blueprint);

        $queries = $blueprint->toSqlAlter();
        foreach ($queries as $sql) {
            static::getDatabase()->query($sql);
        }
    }
}
