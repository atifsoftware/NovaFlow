<?php

namespace NovaFlow\Core\Database\Schema\Grammar;

use NovaFlow\Core\QueryBuilder\Blueprint;

/**
 * Base Abstract Schema Grammar
 * Compiles database DDL statements (CREATE, ALTER, DROP)
 */
abstract class SchemaGrammar
{
    /**
     * Wrap table name
     */
    abstract public function wrapTable(string $table): string;

    /**
     * Wrap column name
     */
    abstract public function wrap(string $value): string;

    /**
     * Compile a CREATE TABLE statement
     */
    abstract public function compileCreate(Blueprint $blueprint): string;

    /**
     * Compile a DROP TABLE statement
     */
    public function compileDrop(string $table): string
    {
        return "DROP TABLE " . $this->wrapTable($table);
    }

    /**
     * Compile a DROP TABLE IF EXISTS statement
     */
    public function compileDropIfExists(string $table): string
    {
        return "DROP TABLE IF EXISTS " . $this->wrapTable($table);
    }

    /**
     * Compile column definition based on driver dialect
     */
    abstract public function compileColumn($column): string;
}
