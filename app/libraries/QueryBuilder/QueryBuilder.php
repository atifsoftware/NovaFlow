<?php

namespace NovaFlow\Core\QueryBuilder;

use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Model;
use NovaFlow\Core\RawExpression;
use Closure;
use InvalidArgumentException;
use Exception;

/**
 * QueryBuilder - Fluent Interface for SQL Generation
 */
class QueryBuilder
{
    private $db;
    private $table;
    private $model; // Optional: Model class name or instance

    private $wheres = [];
    private $columns = '*';
    private $orderBy = '';
    private $limit = null;
    private $offset = null;
    private $joins = [];
    private $groupBy = '';
    private $having = '';
    private $ignoreSoftDelete = false;
    private $unions = [];
    private $joinBindings = [];
    private $havingBindings = [];

    // For WITH clauses (CTEs)
    private $withClauses = [];

    public function getDbClass()
    {
        return get_class($this->db);
    }

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Belongs To relationship
     */
    public function belongsTo(string $relatedModel, string $foreignKey, string $localKey = 'id'): mixed
    {
        $related = new $relatedModel();
        $result = $this->join($related->getTable(), $this->table . '.' . $foreignKey, '=', $related->getTable() . '.' . $localKey)->first();
        return $result ? $related->fill($result) : null;
    }

    /**
     * Proxy to underlying DB query or return builder if no SQL
     */
    public function query(string $sql = '', array $params = [])
    {
        if (empty($sql)) {
            return $this;
        }
        return $this->db->query($sql, $params);
    }

    /**
     * Proxy to fetchOne
     */
    public function fetchOne(string $sql, array $params = [])
    {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Proxy to fetchAll
     */
    public function fetchAll(string $sql, array $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Set the model (for hydration and soft deletes)
     */
    public function setModel($model): self
    {
        $this->model = $model;
        if ($model && method_exists($model, 'getTable')) {
            $this->table = $model->getTable();
        }
        return $this;
    }

    /**
     * Select specific columns
     */
    public function select(...$columns): self
    {
        if (empty($columns)) {
            $this->columns = '*';
        } elseif (count($columns) === 1 && is_array($columns[0])) {
            $this->columns = implode(', ', $columns[0]);
        } else {
            $this->columns = implode(', ', $columns);
        }
        return $this;
    }

    /**
     * Add WHERE clause
     */
    public function where($column, $operator = null, $value = null): self
    {
        if ($column instanceof Closure) {
            $query = new self($this->db);
            $query->setModel($this->model); // Inherit model
            $query->table($this->table);    // Inherit table
            $column($query);

            $this->wheres[] = [
                'type' => 'NESTED',
                'query' => $query,
                'boolean' => 'AND'
            ];
            return $this;
        }

        if (!is_string($column) || trim($column) === '') {
            throw new InvalidArgumentException("Invalid column name");
        }

        if ($value === null) {
            if ($operator === null) {
                $value = null;
                $operator = '=';
            } else {
                $value = $operator;
                $operator = '=';
            }
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null): self
    {
        if ($column instanceof Closure) {
            $query = new self($this->db);
            $query->setModel($this->model);
            $query->table($this->table);
            $column($query);

            $this->wheres[] = [
                'type' => 'NESTED',
                'query' => $query,
                'boolean' => 'OR'
            ];
            return $this;
        }

        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        return $this;
    }

    public function whereIn($column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IN',
            'value' => $values,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereNotIn($column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'NOT IN',
            'value' => $values,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereNull($column): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NULL', 'value' => null, 'boolean' => 'AND'];
        return $this;
    }

    public function whereNotNull($column): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NOT NULL', 'value' => null, 'boolean' => 'AND'];
        return $this;
    }

    public function whereLike($column, $value): self
    {
        return $this->where($column, 'LIKE', $value);
    }

    public function orWhereLike($column, $value): self
    {
        return $this->orWhere($column, 'LIKE', $value);
    }

    public function whereBetween($column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'BETWEEN',
            'value' => $values,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereNotBetween($column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'NOT BETWEEN',
            'value' => $values,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereDate($column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'DATE', 
            'sql_operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereTrue(string $column): self
    {
        return $this->where($column, 1);
    }

    public function whereFalse(string $column): self
    {
        return $this->where($column, 0);
    }

    public function whereYear($column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        return $this->addDateCondition($column, 'YEAR', $operator, $value);
    }

    public function whereMonth($column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        return $this->addDateCondition($column, 'MONTH', $operator, $value);
    }

    public function whereDay($column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        return $this->addDateCondition($column, 'DAY', $operator, $value);
    }

    private function addDateCondition($column, $function, $operator, $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'DATE_FUNC',
            'function' => $function,
            'sql_operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function selectRaw($expression): self
    {
        return $this->addSelect($expression);
    }

    public function whereRaw($sql, array $bindings = []): self
    {
        $this->wheres[] = [
            'column' => null,
            'operator' => 'RAW',
            'sql' => $sql,
            'value' => $bindings,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function whereJsonContains($column, $value): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'JSON_CONTAINS',
            'value' => $value,
            'boolean' => 'AND'
        ];
        return $this;
    }

    public function addJoinBinding($value): void
    {
        $this->joinBindings[] = $value;
    }

    public function escapeIdentifier($identifier)
    {
        if (stripos($identifier, ' as ') !== false) {
            $parts = preg_split('/ as /i', $identifier);
            return $this->escapeIdentifier(trim($parts[0])) . ' AS ' . $this->escapeIdentifier(trim($parts[1]));
        }

        if (strpos($identifier, '(') !== false || strpos($identifier, ')') !== false) {
            return $identifier; 
        }

        if (strpos($identifier, '.') !== false) {
            $parts = explode('.', $identifier);
            return '`' . implode('`.`', $parts) . '`';
        }
        
        if (strpos($identifier, '`') !== false) {
            return $identifier;
        }

        return '`' . trim($identifier) . '`';
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'INNER'): self
    {
        $table = $this->escapeIdentifier($table);

        if ($first instanceof Closure) {
            $builder = $this;
            $joinBuilder = new class($builder) {
                private $builder;
                public $type;
                public $table;
                public $clauses = [];
                
                public function __construct($builder)
                {
                    $this->builder = $builder;
                }
                
                public function on($first, $operator, $second)
                {
                    $firstEsc = $this->builder->escapeIdentifier($first);
                    $secondEsc = $this->builder->escapeIdentifier($second);
                    $this->clauses[] = "$firstEsc $operator $secondEsc";
                    return $this;
                }
                
                public function where($column, $operator, $value)
                {
                    $columnEsc = $this->builder->escapeIdentifier($column);
                    $this->clauses[] = "$columnEsc $operator ?";
                    $this->builder->addJoinBinding($value);
                    return $this;
                }
            };
            $joinBuilder->type = $type;
            $joinBuilder->table = $table;

            $first($joinBuilder);

            $onConditions = implode(' AND ', $joinBuilder->clauses);
            $this->joins[] = "$type JOIN $table ON $onConditions";
            return $this;
        }

        if ($operator === null) {
            $this->joins[] = "$type JOIN $table ON $first";
        } else {
            $first = $this->escapeIdentifier($first);
            $second = $this->escapeIdentifier($second);
            $this->joins[] = "$type JOIN $table ON $first $operator $second";
        }
        return $this;
    }

    public function leftJoin($table, $first, $operator = null, $second = null): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin($table, $first, $operator = null, $second = null): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function groupBy(...$groups): self
    {
        $this->groupBy = 'GROUP BY ' . implode(', ', $groups);
        return $this;
    }

    public function having($column, $operator = null, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $column = $this->escapeIdentifier($column);
        $condition = "$column $operator ?";
        if ($this->having) {
            $this->having .= " AND $condition";
        } else {
            $this->having = "HAVING $condition";
        }
        $this->havingBindings[] = $value;
        return $this;
    }

    public function distinct(): self
    {
        if (strpos($this->columns, 'DISTINCT') === false) {
            $this->columns = 'DISTINCT ' . $this->columns;
        }
        return $this;
    }

    public function addSelect($column): self
    {
        if ($this->columns === '*') {
            $this->columns = $column;
        } else {
            $this->columns .= ", $column";
        }
        return $this;
    }

    public function when($condition, callable $callback, callable $default = null): self
    {
        if ($condition) {
            $callback($this, $condition);
        } elseif ($default) {
            $default($this, $condition);
        }
        return $this;
    }

    public function unless($condition, callable $callback, callable $default = null): self
    {
        return $this->when(!$condition, $callback, $default);
    }

    /**
     * Build the query SQL
     */
    public function toSql(): string
    {
        if (!$this->table) {
            throw new Exception("Table not set");
        }

        $table = $this->escapeIdentifier($this->table);
        $sql = "SELECT {$this->columns} FROM $table";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        list($whereSql, $params) = $this->buildWhere();
        if ($whereSql) {
            $sql .= " WHERE $whereSql";
        }

        if ($this->groupBy)
            $sql .= " " . $this->groupBy;
        if ($this->having)
            $sql .= " " . $this->having;
        if ($this->orderBy)
            $sql .= " ORDER BY " . $this->orderBy;

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
            if ($this->offset !== null) {
                $sql .= " OFFSET " . $this->offset;
            }
        }

        return $sql;
    }

    private function buildWhere(): array
    {
        if (empty($this->wheres))
            return ['', []];

        $sqlParts = [];
        $params = [];

        foreach ($this->wheres as $i => $where) {
            $prefix = ($i === 0) ? '' : ($where['boolean'] . ' ');

            if (isset($where['type']) && $where['type'] === 'NESTED') {
                $nestedQuery = $where['query'];
                list($nestedSql, $nestedParams) = $nestedQuery->buildWhere();
                if ($nestedSql) {
                    $sqlParts[] = "$prefix($nestedSql)";
                    $params = array_merge($params, $nestedParams);
                }
                continue;
            }

            $col = isset($where['column']) ? $where['column'] : null;
            $op = isset($where['operator']) ? strtoupper($where['operator']) : null;
            $val = isset($where['value']) ? $where['value'] : null;

            if ($op === 'IN' || $op === 'NOT IN') {
                $placeholders = implode(', ', array_fill(0, count($val), '?'));
                $col = $this->escapeIdentifier($col);
                $sqlParts[] = "$prefix$col $op ($placeholders)";
                $params = array_merge($params, $val);
            } elseif ($op === 'BETWEEN' || $op === 'NOT BETWEEN') {
                $col = $this->escapeIdentifier($col);
                $sqlParts[] = "$prefix$col $op ? AND ?";
                $params[] = $val[0];
                $params[] = $val[1];
            } elseif ($op === 'DATE') {
                $sqlOp = $where['sql_operator'];
                $colWithBackticks = $this->escapeIdentifier($col);
                $sqlParts[] = "$prefix DATE($colWithBackticks) $sqlOp ?";
                $params[] = $val;
            } elseif ($op === 'DATE_FUNC') {
                $func = $where['function'];
                $sqlOp = $where['sql_operator'];
                $col = $this->escapeIdentifier($col);
                $sqlParts[] = "$prefix $func($col) $sqlOp ?";
                $params[] = $val;
            } elseif ($op === 'RAW') {
                $sqlParts[] = "$prefix " . $where['sql'];
                $params = array_merge($params, $val);
            } elseif ($op === 'JSON_CONTAINS') {
                $path = '$';
                if (strpos($col, '->') !== false) {
                    $parts = explode('->', $col);
                    $col = $parts[0];
                    $path .= '.' . implode('.', array_slice($parts, 1));
                }
                $col = $this->escapeIdentifier($col);
                $sqlParts[] = "$prefix JSON_CONTAINS($col, ?, ?)";
                $params[] = json_encode($val);
                $params[] = $path;

            } elseif ($op === 'IS NULL' || $op === 'IS NOT NULL') {
                $col = $this->escapeIdentifier($col);
                $sqlParts[] = "$prefix$col $op";
            } else {
                if ($col) {
                    $col = $this->escapeIdentifier($col);
                    $sqlParts[] = "$prefix$col $op ?";
                    $params[] = $val;
                }
            }
        }

        return [implode(' ', $sqlParts), $params];
    }

    public function getBindings(): array
    {
        list($sql, $params) = $this->buildWhere();
        return array_merge($this->joinBindings, $params, $this->havingBindings);
    }

    public function get(): array
    {
        $sql = $this->toSql();
        $params = $this->getBindings();

        $results = $this->db->fetchAll($sql, $params);

        if ($this->model) {
            $class = null;
            if (is_string($this->model)) {
                $class = $this->model;
            } elseif (is_object($this->model)) {
                $class = get_class($this->model);
            }

            if ($class) {
                $objects = [];
                foreach ($results as $row) {
                    $obj = new $class();
                    // Assumes Model has syncAttributes method
                    if (method_exists($obj, 'syncAttributes')) {
                        $obj->syncAttributes($row, true);
                    } else {
                        foreach ($row as $k => $v) { $obj->$k = $v; }
                    }
                    $objects[] = $obj;
                }
                return $objects;
            }
        }

        return array_map(function ($row) {
            return (object) $row;
        }, $results);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Get all results
     */
    public function all(): array
    {
        return $this->get();
    }

    /**
     * Find a record by its primary key
     */
    public function find($id)
    {
        $pk = $this->model ? (is_object($this->model) ? $this->model->getPrimaryKey() : 'id') : 'id';
        return $this->where($pk, $id)->first();
    }

    /**
     * Find a record or throw exception
     */
    public function findOrFail($id)
    {
        $result = $this->find($id);
        if (!$result) {
            throw new Exception("Record not found with ID: $id");
        }
        return $result;
    }

    public function count(): int
    {
        $originalColumns = $this->columns;
        $this->columns = 'COUNT(*) as count';
        $sql = $this->toSql();
        $params = $this->getBindings();
        $this->columns = $originalColumns;

        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }

    public function insert(array $data)
    {
        if (!$this->table)
            throw new Exception("Table not set");

        $keys = [];
        $placeholders = [];
        $params = [];

        foreach ($data as $col => $val) {
            $keys[] = "`$col`";
            if ($val instanceof RawExpression) {
                $placeholders[] = $val->getExpression();
                $params = array_merge($params, $val->getBindings());
            } else {
                $placeholders[] = "?";
                $params[] = $val;
            }
        }

        $cols = implode(', ', $keys);
        $placeholderSql = implode(', ', $placeholders);

        $sql = "INSERT INTO `{$this->table}` ($cols) VALUES ($placeholderSql)";

        return $this->db->query($sql, $params); 
    }

    public function update(array $data)
    {
        if (!$this->table)
            throw new Exception("Table not set");

        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            if ($val instanceof RawExpression) {
                $sets[] = "`$col` = " . $val->getExpression();
                $params = array_merge($params, $val->getBindings());
            } else {
                $sets[] = "`$col` = ?";
                $params[] = $val;
            }
        }
        $setSql = implode(', ', $sets);

        list($whereSql, $whereParams) = $this->buildWhere();
        $params = array_merge($params, $whereParams);

        $sql = "UPDATE `{$this->table}` SET $setSql";
        if ($whereSql)
            $sql .= " WHERE $whereSql";

        return $this->db->query($sql, $params);
    }

    /**
     * Increment a column's value
     */
    public function increment(string $column, int $amount = 1)
    {
        return $this->update([
            $column => new RawExpression("`$column` + $amount")
        ]);
    }

    /**
     * Decrement a column's value
     */
    public function decrement(string $column, int $amount = 1)
    {
        return $this->update([
            $column => new RawExpression("`$column` - $amount")
        ]);
    }

    public function delete()
    {
        if (!$this->table)
            throw new Exception("Table not set");

        list($whereSql, $whereParams) = $this->buildWhere();

        $sql = "DELETE FROM `{$this->table}`";
        if ($whereSql)
            $sql .= " WHERE $whereSql";

        return $this->db->query($sql, $whereParams);
    }
    
    public function orderBy($column, $direction = 'ASC'): self
    {
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? strtoupper($direction) : 'ASC';
        $column = $this->escapeIdentifier($column);
        $this->orderBy = $this->orderBy ? "$this->orderBy, $column $direction" : "$column $direction";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
}
