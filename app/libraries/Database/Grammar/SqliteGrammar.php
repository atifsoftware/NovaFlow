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

    /**
     * Extract parameter bindings from WHERE clauses, formatting 2-digit months/days for SQLite
     */
    public function getWhereBindings(array $wheres): array
    {
        $params = parent::getWhereBindings($wheres);

        $paramIndex = 0;
        foreach ($wheres as $where) {
            if (isset($where['type']) && $where['type'] === 'NESTED') {
                $paramIndex += count($where['query']->getBindings());
                continue;
            }

            $op = isset($where['operator']) ? strtoupper($where['operator']) : '=';
            if ($op === 'DATE_FUNC') {
                $func = strtoupper($where['function'] ?? '');
                if (($func === 'MONTH' || $func === 'DAY') && isset($params[$paramIndex]) && is_numeric($params[$paramIndex])) {
                    $params[$paramIndex] = sprintf('%02d', (int)$params[$paramIndex]);
                }
                $paramIndex++;
            } elseif ($op !== 'IS NULL' && $op !== 'IS NOT NULL' && $op !== 'COLUMN') {
                if ($op === 'IN' || $op === 'NOT IN') {
                    $paramIndex += count((array)($where['value'] ?? []));
                } elseif ($op === 'BETWEEN' || $op === 'NOT BETWEEN') {
                    $paramIndex += 2;
                } elseif ($op === 'RAW') {
                    $paramIndex += count((array)($where['value'] ?? []));
                } elseif ($where['column'] ?? null) {
                    $paramIndex++;
                }
            }
        }

        return $params;
    }
}
