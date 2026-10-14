<?php

namespace NovaFlow\Core\Database\Grammar;

/**
 * MySQL SQL Grammar
 */
class MySqlGrammar extends Grammar
{
    /**
     * Wrap a single segment in MySQL backticks
     */
    protected function wrapValue(string $value): string
    {
        if ($value === '*') {
            return $value;
        }

        return '`' . str_replace('`', '``', $value) . '`';
    }

    /**
     * Compile JSON contains condition for MySQL
     */
    protected function compileWhereJsonContains(string $prefix, string $column): string
    {
        $path = '$';
        if (strpos($column, '->') !== false) {
            $parts = explode('->', $column);
            $column = $parts[0];
            $path .= '.' . implode('.', array_slice($parts, 1));
        }
        $wrappedCol = $this->wrap($column);
        return "$prefix JSON_CONTAINS($wrappedCol, ?, ?)";
    }

    /**
     * Get JSON contains bindings for MySQL (needs value and path)
     */
    protected function getJsonContainsBindings(array $where): array
    {
        $column = $where['column'] ?? '';
        $path = '$';
        if (strpos($column, '->') !== false) {
            $parts = explode('->', $column);
            $path .= '.' . implode('.', array_slice($parts, 1));
        }
        return [json_encode($where['value']), $path];
    }
}
