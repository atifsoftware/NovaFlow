<?php

namespace NovaFlow\Core\Database\Grammar;

/**
 * SQLite SQL Grammar
 */
class SqliteGrammar extends Grammar
{
    /**
     * Wrap a single segment in SQLite double quotes
     */
    protected function wrapValue(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }

    /**
     * Compile DATE condition for SQLite
     */
    protected function compileWhereDate(string $prefix, string $column, string $operator): string
    {
        return $prefix . "date(" . $this->wrap($column) . ") $operator ?";
    }

    /**
     * Compile DATE function condition (YEAR, MONTH, DAY) for SQLite using strftime
     */
    protected function compileWhereDateFunc(string $prefix, string $column, string $function, string $operator): string
    {
        $formats = [
            'YEAR' => '%Y',
            'MONTH' => '%m',
            'DAY' => '%d',
        ];

        $format = $formats[strtoupper($function)] ?? '%Y';
        return $prefix . "strftime('$format', " . $this->wrap($column) . ") $operator ?";
    }
}
