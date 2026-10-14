<?php

namespace NovaFlow\Core\Database\Grammar;

use NovaFlow\Core\Database\Contracts\GrammarInterface;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\RawExpression;
use InvalidArgumentException;

/**
 * Base Abstract SQL Grammar
 * Compiles fluent query components into standard SQL
 */
abstract class Grammar implements GrammarInterface
{
    /**
     * Component compilation sequence for SELECT statements
     */
    protected array $selectComponents = [
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions',
    ];

    /**
     * Wrap an identifier (table or column) with driver-specific quotes
     */
    public function wrap(string $value): string
    {
        if (stripos($value, ' as ') !== false) {
            $parts = preg_split('/ as /i', $value);
            return $this->wrap(trim($parts[0])) . ' AS ' . $this->wrapValue(trim($parts[1]));
        }

        // Do not wrap expressions containing functions or parentheses or wildcards
        if (strpos($value, '(') !== false || strpos($value, ')') !== false || $value === '*') {
            return $value;
        }

        if (strpos($value, '.') !== false) {
            $parts = explode('.', $value);
            return implode('.', array_map([$this, 'wrapValue'], $parts));
        }

        return $this->wrapValue($value);
    }

    /**
     * Wrap table name
     */
    public function wrapTable(string $table): string
    {
        return $this->wrap($table);
    }

    /**
     * Wrap single segment (driver specific)
     */
    abstract protected function wrapValue(string $value): string;

    /**
     * Convert an array of column names into a comma-delimited string
     */
    public function columnize(array $columns): string
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * Create query parameter placeholders
     */
    public function parameterize(array $values): string
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    /**
     * Get the parameter placeholder for a value
     */
    public function parameter($value): string
    {
        if ($value instanceof RawExpression) {
            return $value->getExpression();
        }
        return '?';
    }

    /**
     * Compile a SELECT query into SQL
     */
    public function compileSelect(QueryBuilder $query): string
    {
        $sqlParts = [];

        foreach ($this->selectComponents as $component) {
            $method = 'compile' . ucfirst($component);
            if (method_exists($this, $method)) {
                $val = $this->$method($query);
                if ($val !== '' && $val !== null) {
                    $sqlParts[] = $val;
                }
            }
        }

        return trim(implode(' ', $sqlParts));
    }

    /**
     * Compile the columns portion of SELECT query
     */
    protected function compileColumns(QueryBuilder $query): string
    {
        $columns = $query->getColumns();
        if (empty($columns)) {
            $columns = '*';
        }

        $distinct = $query->getDistinct() ? 'DISTINCT ' : '';

        if (is_array($columns)) {
            return 'SELECT ' . $distinct . $this->columnize($columns);
        }

        // If string already has DISTINCT or is already formatted
        return 'SELECT ' . $distinct . $columns;
    }

    /**
     * Compile the FROM portion of the query
     */
    protected function compileFrom(QueryBuilder $query): string
    {
        $table = $query->getTable();
        if (!$table) {
            throw new InvalidArgumentException("No table specified for query.");
        }
        return 'FROM ' . $this->wrapTable($table);
    }

    /**
     * Compile the JOIN portion of the query
     */
    protected function compileJoins(QueryBuilder $query): string
    {
        $joins = $query->getJoins();
        if (empty($joins)) {
            return '';
        }

        return implode(' ', $joins);
    }

    /**
     * Compile WHERE clauses
     */
    public function compileWheres(QueryBuilder $query): string
    {
        $wheres = $query->getWheres();
        if (empty($wheres)) {
            return '';
        }

        $sqlParts = [];

        foreach ($wheres as $i => $where) {
            $prefix = ($i === 0) ? '' : ($where['boolean'] . ' ');

            if (isset($where['type']) && $where['type'] === 'NESTED') {
                $nestedQuery = $where['query'];
                $nestedSql = $this->compileNestedWhere($nestedQuery);
                if ($nestedSql !== '') {
                    $sqlParts[] = "$prefix($nestedSql)";
                }
                continue;
            }

            $op = isset($where['operator']) ? strtoupper($where['operator']) : '=';
            $col = $where['column'] ?? null;
            $val = $where['value'] ?? null;

            if ($op === 'IN' || $op === 'NOT IN') {
                $valArray = array_values((array) $val);
                if (empty($valArray)) {
                    $sqlParts[] = ($op === 'IN') ? "{$prefix}0 = 1" : "{$prefix}1 = 1";
                } else {
                    $wrappedCol = $this->wrap($col);
                    $placeholders = $this->parameterize($valArray);
                    $sqlParts[] = "$prefix$wrappedCol $op ($placeholders)";
                }
            } elseif ($op === 'BETWEEN' || $op === 'NOT BETWEEN') {
                $wrappedCol = $this->wrap($col);
                $sqlParts[] = "$prefix$wrappedCol $op ? AND ?";
            } elseif ($op === 'COLUMN') {
                $wrappedCol = $this->wrap($col);
                $wrappedVal = $this->wrap($val);
                $sqlOp = $where['sql_operator'] ?? '=';
                $sqlParts[] = "$prefix$wrappedCol $sqlOp $wrappedVal";
            } elseif ($op === 'DATE') {
                $sqlParts[] = $this->compileWhereDate($prefix, $col, $where['sql_operator']);
            } elseif ($op === 'DATE_FUNC') {
                $sqlParts[] = $this->compileWhereDateFunc($prefix, $col, $where['function'], $where['sql_operator']);
            } elseif ($op === 'RAW') {
                $sqlParts[] = "$prefix" . $where['sql'];
            } elseif ($op === 'JSON_CONTAINS') {
                $sqlParts[] = $this->compileWhereJsonContains($prefix, $col);
            } elseif ($op === 'IS NULL' || $op === 'IS NOT NULL') {
                $wrappedCol = $this->wrap($col);
                $sqlParts[] = "$prefix$wrappedCol $op";
            } else {
                if ($col) {
                    $wrappedCol = $this->wrap($col);
                    $sqlParts[] = "$prefix$wrappedCol $op ?";
                }
            }
        }

        return empty($sqlParts) ? '' : 'WHERE ' . implode(' ', $sqlParts);
    }

    /**
     * Compile a nested where clause
     */
    protected function compileNestedWhere(QueryBuilder $query): string
    {
        $whereSql = $this->compileWheres($query);
        // Strip out the leading 'WHERE '
        return strpos($whereSql, 'WHERE ') === 0 ? substr($whereSql, 6) : $whereSql;
    }

    /**
     * Compile DATE condition
     */
    protected function compileWhereDate(string $prefix, string $column, string $operator): string
    {
        return $prefix . "DATE(" . $this->wrap($column) . ") $operator ?";
    }

    /**
     * Compile DATE function condition (YEAR, MONTH, DAY)
     */
    protected function compileWhereDateFunc(string $prefix, string $column, string $function, string $operator): string
    {
        return $prefix . "$function(" . $this->wrap($column) . ") $operator ?";
    }

    /**
     * Compile JSON contains condition
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
        return $prefix . "JSON_CONTAINS($wrappedCol, ?, ?)";
    }

    /**
     * Extract parameter bindings from WHERE clauses
     */
    public function getWhereBindings(array $wheres): array
    {
        $params = [];

        foreach ($wheres as $where) {
            if (isset($where['type']) && $where['type'] === 'NESTED') {
                $nestedQuery = $where['query'];
                $params = array_merge($params, $nestedQuery->getBindings());
                continue;
            }

            $op = isset($where['operator']) ? strtoupper($where['operator']) : '=';
            $val = $where['value'] ?? null;

            if ($op === 'IN' || $op === 'NOT IN') {
                $valArray = array_values((array) $val);
                if (!empty($valArray)) {
                    $params = array_merge($params, $valArray);
                }
            } elseif ($op === 'BETWEEN' || $op === 'NOT BETWEEN') {
                $params[] = $val[0];
                $params[] = $val[1];
            } elseif ($op === 'COLUMN') {
                // Column comparisons compare two database columns, no parameters bound
            } elseif ($op === 'DATE' || $op === 'DATE_FUNC') {
                $params[] = $val;
            } elseif ($op === 'RAW') {
                $params = array_merge($params, (array) $val);
            } elseif ($op === 'JSON_CONTAINS') {
                $params = array_merge($params, $this->getJsonContainsBindings($where));
            } elseif ($op === 'IS NULL' || $op === 'IS NOT NULL') {
                // No parameters
            } else {
                if ($where['column'] ?? null) {
                    $params[] = $val;
                }
            }
        }

        return $params;
    }

    /**
     * Get JSON contains bindings
     */
    protected function getJsonContainsBindings(array $where): array
    {
        return [json_encode($where['value'])];
    }

    /**
     * Compile GROUP BY
     */
    protected function compileGroups(QueryBuilder $query): string
    {
        $groups = $query->getGroups();
        if (empty($groups)) {
            return '';
        }

        if (is_array($groups)) {
            return 'GROUP BY ' . implode(', ', array_map([$this, 'wrap'], $groups));
        }

        return $groups;
    }

    /**
     * Compile HAVING
     */
    protected function compileHavings(QueryBuilder $query): string
    {
        $having = $query->getHaving();
        return $having ? $having : '';
    }

    /**
     * Compile ORDER BY
     */
    protected function compileOrders(QueryBuilder $query): string
    {
        $orders = $query->getOrders();
        if (empty($orders)) {
            return '';
        }

        if (is_string($orders)) {
            return strpos(strtoupper($orders), 'ORDER BY') === 0 ? $orders : 'ORDER BY ' . $orders;
        }

        return 'ORDER BY ' . implode(', ', $orders);
    }

    /**
     * Compile LIMIT
     */
    protected function compileLimit(QueryBuilder $query): string
    {
        $limit = $query->getLimit();
        return ($limit !== null) ? 'LIMIT ' . (int) $limit : '';
    }

    /**
     * Compile OFFSET
     */
    protected function compileOffset(QueryBuilder $query): string
    {
        $offset = $query->getOffset();
        return ($offset !== null) ? 'OFFSET ' . (int) $offset : '';
    }

    /**
     * Compile UNIONS
     */
    protected function compileUnions(QueryBuilder $query): string
    {
        $unions = $query->getUnions();
        if (empty($unions)) {
            return '';
        }

        $sql = '';
        foreach ($unions as $union) {
            $sql .= ' ' . ($union['all'] ? 'UNION ALL ' : 'UNION ') . $union['query']->toSql();
        }

        return trim($sql);
    }

    /**
     * Compile INSERT
     */
    public function compileInsert(QueryBuilder $query, array $values): string
    {
        $table = $this->wrapTable($query->getTable());

        $columns = [];
        $parameters = [];

        foreach ($values as $col => $val) {
            $columns[] = $this->wrap($col);
            if ($val instanceof RawExpression) {
                $parameters[] = $val->getExpression();
            } else {
                $parameters[] = '?';
            }
        }

        $colList = implode(', ', $columns);
        $paramList = implode(', ', $parameters);

        return "INSERT INTO $table ($colList) VALUES ($paramList)";
    }

    /**
     * Compile UPDATE
     */
    public function compileUpdate(QueryBuilder $query, array $values): string
    {
        $table = $this->wrapTable($query->getTable());

        $sets = [];
        foreach ($values as $col => $val) {
            if ($val instanceof RawExpression) {
                $sets[] = $this->wrap($col) . ' = ' . $val->getExpression();
            } else {
                $sets[] = $this->wrap($col) . ' = ?';
            }
        }

        $setSql = implode(', ', $sets);
        $whereSql = $this->compileWheres($query);

        $sql = "UPDATE $table SET $setSql";
        if ($whereSql !== '') {
            $sql .= " $whereSql";
        }

        return $sql;
    }

    /**
     * Compile DELETE
     */
    public function compileDelete(QueryBuilder $query): string
    {
        $table = $this->wrapTable($query->getTable());
        $whereSql = $this->compileWheres($query);

        $sql = "DELETE FROM $table";
        if ($whereSql !== '') {
            $sql .= " $whereSql";
        }

        return $sql;
    }
}
