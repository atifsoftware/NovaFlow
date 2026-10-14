<?php

namespace NovaFlow\Core\Database\Grammar;

/**
 * PostgreSQL SQL Grammar
 */
class PostgresGrammar extends Grammar
{
    /**
     * Wrap a single segment in standard SQL double quotes
     */
    protected function wrapValue(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }

    /**
     * Compile DATE condition for PostgreSQL
     */
    protected function compileWhereDate(string $prefix, string $column, string $operator): string
    {
        return $prefix . $this->wrap($column) . "::date $operator ?";
    }

    /**
     * Compile DATE function condition (YEAR, MONTH, DAY) using EXTRACT for PostgreSQL
     */
    protected function compileWhereDateFunc(string $prefix, string $column, string $function, string $operator): string
    {
        $func = strtoupper($function);
        return $prefix . "EXTRACT($func FROM " . $this->wrap($column) . ") $operator ?";
    }

    /**
     * Compile JSON contains condition for PostgreSQL
     */
    protected function compileWhereJsonContains(string $prefix, string $column): string
    {
        return $prefix . $this->wrap($column) . "::jsonb @> ?::jsonb";
    }
}
