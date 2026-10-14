<?php

namespace NovaFlow\Core\QueryBuilder;

use Closure;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Container;
use NovaFlow\Core\Database\Schema\Grammar\SchemaGrammar;
use NovaFlow\Core\Database\Schema\Grammar\MySqlSchemaGrammar;
use NovaFlow\Core\Database\Schema\Grammar\PostgresSchemaGrammar;
use NovaFlow\Core\Database\Schema\Grammar\SqliteSchemaGrammar;

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

    /**
     * Resolve the SchemaGrammar based on active database connection
     */
    public static function getSchemaGrammar(): SchemaGrammar
    {
        $db = static::getDatabase();
        $driver = method_exists($db, 'getDriverName') ? $db->getDriverName() : 'mysql';

        switch (strtolower($driver)) {
            case 'pgsql':
            case 'postgres':
            case 'postgresql':
                return new PostgresSchemaGrammar();
            case 'sqlite':
                return new SqliteSchemaGrammar();
            default:
                return new MySqlSchemaGrammar();
        }
    }

    public static function create($table, Closure $callback)
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $grammar = static::getSchemaGrammar();
        $sql = $grammar->compileCreate($blueprint);
        static::getDatabase()->query($sql);
    }

    public static function drop($table)
    {
        $grammar = static::getSchemaGrammar();
        $sql = $grammar->compileDrop($table);
        static::getDatabase()->query($sql);
    }

    public static function dropIfExists($table)
    {
        $grammar = static::getSchemaGrammar();
        $sql = $grammar->compileDropIfExists($table);
        static::getDatabase()->query($sql);
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
