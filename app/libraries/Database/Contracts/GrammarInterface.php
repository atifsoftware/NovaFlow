<?php

namespace NovaFlow\Core\Database\Contracts;

use NovaFlow\Core\QueryBuilder\QueryBuilder;

/**
 * Grammar Interface
 * Defines contract for database-specific SQL compilers
 */
interface GrammarInterface
{
    /**
     * Compile a SELECT query into SQL
     */
    public function compileSelect(QueryBuilder $query): string;

    /**
     * Compile an INSERT statement into SQL
     */
    public function compileInsert(QueryBuilder $query, array $values): string;

    /**
     * Compile an UPDATE statement into SQL
     */
    public function compileUpdate(QueryBuilder $query, array $values): string;

    /**
     * Compile a DELETE statement into SQL
     */
    public function compileDelete(QueryBuilder $query): string;

    /**
     * Wrap a value in keyword identifiers
     */
    public function wrap(string $value): string;

    /**
     * Wrap a table in keyword identifiers
     */
    public function wrapTable(string $table): string;

    /**
     * Convert an array of column names into a comma-delimited string
     */
    public function columnize(array $columns): string;

    /**
     * Create query parameter placeholders
     */
    public function parameterize(array $values): string;

    /**
     * Get the parameter placeholder for a value
     */
    public function parameter($value): string;

    /**
     * Extract parameter bindings from WHERE clauses
     */
    public function getWhereBindings(array $wheres): array;
}
