<?php

namespace NovaFlow\Core\QueryBuilder;

use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Database\Contracts\GrammarInterface;
use NovaFlow\Core\Database\Grammar\MySqlGrammar;
use NovaFlow\Core\Model;
use NovaFlow\Core\RawExpression;
use NovaFlow\Core\Pagination\LengthAwarePaginator;
use Closure;
use InvalidArgumentException;
use Exception;

/**
 * QueryBuilder - Fluent Interface for SQL Generation
 */
class QueryBuilder
{
    private $db;
    private $grammar;
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
    private $eagerLoads = [];

    // For WITH clauses (CTEs)
    private $withClauses = [];

    public function getDbClass()
    {
        return get_class($this->db);
    }

    public function __construct(DatabaseInterface $db, ?GrammarInterface $grammar = null)
    {
        $this->db = $db;
        $this->grammar = $grammar ?? ((method_exists($db, 'getGrammar') && $db->getGrammar()) ? $db->getGrammar() : new MySqlGrammar());
    }

    /**
     * Set the table
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
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
            $query = new self($this->db, $this->grammar);
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
            $query = new self($this->db, $this->grammar);
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

    public function orWhereIn($column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'IN',
            'value' => $values,
            'boolean' => 'OR'
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

    public function orWhereNotIn($column, array $values): self
    {
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'NOT IN',
            'value' => $values,
            'boolean' => 'OR'
        ];
        return $this;
    }

    public function whereNull($column): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NULL', 'value' => null, 'boolean' => 'AND'];
        return $this;
    }

    public function orWhereNull($column): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NULL', 'value' => null, 'boolean' => 'OR'];
        return $this;
    }

    public function whereNotNull($column): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NOT NULL', 'value' => null, 'boolean' => 'AND'];
        return $this;
    }

    public function orWhereNotNull($column): self
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'IS NOT NULL', 'value' => null, 'boolean' => 'OR'];
        return $this;
    }

    public function whereColumn($first, $operator = null, $second = null, $boolean = 'AND'): self
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $first,
            'operator' => 'COLUMN',
            'sql_operator' => $operator,
            'value' => $second,
            'boolean' => $boolean
        ];

        return $this;
    }

    public function orWhereColumn($first, $operator = null, $second = null): self
    {
        return $this->whereColumn($first, $operator, $second, 'OR');
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

    public function escapeIdentifier($identifier)
    {
        return $this->grammar->wrap($identifier);
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'INNER'): self
    {
        $table = $this->escapeIdentifier($table);

        if ($first instanceof Closure) {
            $joinBuilder = new class {
                public $type;
                public $table;
                public $clauses = [];
                public function on($first, $operator, $second)
                {
                    $this->clauses[] = "$first $operator $second";
                    return $this;
                }
                public function where($column, $operator, $value)
                {
                    $val = is_numeric($value) ? $value : "'" . addslashes($value) . "'";
                    $this->clauses[] = "$column $operator $val";
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

        $validOperators = ['=', '>', '<', '>=', '<=', '<>', '!=', 'LIKE', 'NOT LIKE'];
        $operator = in_array(strtoupper(trim($operator)), $validOperators) ? strtoupper(trim($operator)) : '=';
        $wrappedCol = $this->escapeIdentifier($column);
        $escapedValue = is_numeric($value) ? $value : "'" . addcslashes((string)$value, "\000\n\r\\'\"\032") . "'";

        $condition = "$wrappedCol $operator $escapedValue";
        if ($this->having) {
            $this->having .= " AND $condition";
        } else {
            $this->having = "HAVING $condition";
        }
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
     * Get the query grammar instance
     */
    public function getGrammar(): GrammarInterface
    {
        return $this->grammar;
    }

    /**
     * Set the query grammar instance
     */
    public function setGrammar(GrammarInterface $grammar): self
    {
        $this->grammar = $grammar;
        return $this;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getWheres(): array
    {
        return $this->wheres;
    }

    public function getJoins(): array
    {
        return $this->joins;
    }

    public function getOrders()
    {
        return $this->orderBy;
    }

    public function getGroups()
    {
        return $this->groupBy;
    }

    public function getHaving(): ?string
    {
        return $this->having;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getUnions(): array
    {
        return $this->unions;
    }

    public function getDistinct(): bool
    {
        return (strpos((string) $this->columns, 'DISTINCT') !== false);
    }

    /**
     * Build the query SQL
     */
    public function toSql(): string
    {
        return $this->grammar->compileSelect($this);
    }

    public function buildWhere(): array
    {
        $sql = $this->grammar->compileWheres($this);
        if (strpos($sql, 'WHERE ') === 0) {
            $sql = substr($sql, 6);
        }
        return [$sql, $this->getBindings()];
    }

    public function getBindings(): array
    {
        return $this->grammar->getWhereBindings($this->wheres);
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

                if (!empty($this->eagerLoads) && !empty($objects)) {
                    $this->eagerLoadRelations($objects);
                }

                return $objects;
            }
        }

        return array_map(function ($row) {
            return (object) $row;
        }, $results);
    }

    /**
     * Paginate the results into a LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, ?int $page = null, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = $page ?: (isset($_GET[$pageName]) ? (int) $_GET[$pageName] : 1);
        $page = max(1, $page);

        // Count total results before applying limit/offset
        $total = $this->count();

        // Calculate offset
        $offset = ($page - 1) * $perPage;

        // Fetch slice
        $items = $this->limit($perPage)->offset($offset)->get();

        return new LengthAwarePaginator($items, $total, $perPage, $page, ['pageName' => $pageName]);
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Check if any matching records exist
     */
    public function exists(): bool
    {
        return $this->first() !== null;
    }

    /**
     * Check if no matching records exist
     */
    public function doesntExist(): bool
    {
        return !$this->exists();
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
        if (!empty($this->groupBy)) {
            $originalLimit = $this->limit;
            $originalOffset = $this->offset;
            $originalOrderBy = $this->orderBy;

            $this->limit = null;
            $this->offset = null;
            $this->orderBy = '';

            $subSql = $this->toSql();
            $params = $this->getBindings();

            $this->limit = $originalLimit;
            $this->offset = $originalOffset;
            $this->orderBy = $originalOrderBy;

            $sql = "SELECT COUNT(*) as count FROM ({$subSql}) as count_subquery";
            $result = $this->db->fetchOne($sql, $params);
            return (int) ($result['count'] ?? 0);
        }

        $originalColumns = $this->columns;
        $originalLimit = $this->limit;
        $originalOffset = $this->offset;
        $originalOrderBy = $this->orderBy;

        $this->columns = 'COUNT(*) as count';
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = '';

        $sql = $this->toSql();
        $params = $this->getBindings();

        $this->columns = $originalColumns;
        $this->limit = $originalLimit;
        $this->offset = $originalOffset;
        $this->orderBy = $originalOrderBy;

        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['count'] ?? 0);
    }

    public function aggregate(string $function, string $column = '*'): mixed
    {
        $wrapped = $column === '*' ? '*' : $this->grammar->wrap($column);
        $originalColumns = $this->columns;
        $originalLimit = $this->limit;
        $originalOffset = $this->offset;
        $originalOrderBy = $this->orderBy;

        $this->columns = "{$function}({$wrapped}) as aggregate";
        $this->limit = null;
        $this->offset = null;
        $this->orderBy = '';

        $sql = $this->toSql();
        $params = $this->getBindings();

        $this->columns = $originalColumns;
        $this->limit = $originalLimit;
        $this->offset = $originalOffset;
        $this->orderBy = $originalOrderBy;

        $result = $this->db->fetchOne($sql, $params);
        return $result['aggregate'] ?? null;
    }

    public function sum(string $column)
    {
        $res = $this->aggregate('SUM', $column);
        return $res !== null ? (is_numeric($res) ? $res + 0 : $res) : 0;
    }

    public function avg(string $column)
    {
        $res = $this->aggregate('AVG', $column);
        return $res !== null ? (float)$res : 0.0;
    }

    public function min(string $column)
    {
        return $this->aggregate('MIN', $column);
    }

    public function max(string $column)
    {
        return $this->aggregate('MAX', $column);
    }

    public function value(string $column)
    {
        $row = $this->first();
        if (!$row) return null;
        return is_object($row) ? ($row->$column ?? null) : ($row[$column] ?? null);
    }

    public function pluck(string $column, ?string $key = null): array
    {
        $results = $this->get();
        $values = [];
        foreach ($results as $item) {
            $val = is_object($item) ? ($item->$column ?? null) : ($item[$column] ?? null);
            if ($key !== null) {
                $k = is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null);
                $values[$k] = $val;
            } else {
                $values[] = $val;
            }
        }
        return $values;
    }

    public function latest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'ASC');
    }

    public function insert(array $data)
    {
        if (!$this->table) {
            throw new Exception("Table not set");
        }

        $sql = $this->grammar->compileInsert($this, $data);
        $params = [];
        foreach ($data as $val) {
            if ($val instanceof RawExpression) {
                $params = array_merge($params, $val->getBindings());
            } else {
                $params[] = $val;
            }
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Insert a new record and return its ID
     */
    public function insertGetId(array $data)
    {
        $res = $this->insert($data);
        $lastId = $this->db->getLastId();
        return $lastId ?: (is_numeric($res) ? (int)$res : $res);
    }

    public function update(array $data)
    {
        if (!$this->table) {
            throw new Exception("Table not set");
        }

        $sql = $this->grammar->compileUpdate($this, $data);
        $params = [];
        foreach ($data as $val) {
            if ($val instanceof RawExpression) {
                $params = array_merge($params, $val->getBindings());
            } else {
                $params[] = $val;
            }
        }
        $params = array_merge($params, $this->getBindings());

        return $this->db->query($sql, $params);
    }

    /**
     * Increment a column's value
     */
    public function increment(string $column, int $amount = 1)
    {
        $wrapped = $this->grammar->wrap($column);
        return $this->update([
            $column => new RawExpression("$wrapped + $amount")
        ]);
    }

    /**
     * Decrement a column's value
     */
    public function decrement(string $column, int $amount = 1)
    {
        $wrapped = $this->grammar->wrap($column);
        return $this->update([
            $column => new RawExpression("$wrapped - $amount")
        ]);
    }

    public function delete()
    {
        if (!$this->table) {
            throw new Exception("Table not set");
        }

        $sql = $this->grammar->compileDelete($this);
        return $this->db->query($sql, $this->getBindings());
    }
    
    public function orderBy($column, $direction = 'ASC'): self
    {
        if ($column instanceof RawExpression) {
            $clause = (string)$column;
            $this->orderBy = $this->orderBy ? "$this->orderBy, $clause" : $clause;
            return $this;
        }

        $direction = in_array(strtoupper(trim((string)$direction)), ['ASC', 'DESC']) ? strtoupper(trim((string)$direction)) : 'ASC';
        $column = preg_replace('/[^a-zA-Z0-9_\.`"]/', '', trim((string)$column));
        if ($column === '') {
            return $this;
        }
        $clause = "$column $direction";
        $this->orderBy = $this->orderBy ? "$this->orderBy, $clause" : $clause;
        return $this;
    }

    /**
     * Add a raw ORDER BY clause
     */
    public function orderByRaw(string $sql): self
    {
        $sql = trim($sql);
        if ($sql !== '') {
            $this->orderBy = $this->orderBy ? "$this->orderBy, $sql" : $sql;
        }
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

    /**
     * Set the relationships that should be eager loaded.
     */
    public function with(...$relations): self
    {
        $flattened = [];
        foreach ($relations as $relation) {
            if (is_array($relation)) {
                $flattened = array_merge($flattened, $relation);
            } else {
                $flattened[] = $relation;
            }
        }
        $this->eagerLoads = array_unique(array_merge($this->eagerLoads, $flattened));
        return $this;
    }

    /**
     * Eager load relations on collection of models.
     */
    protected function eagerLoadRelations(array &$models): void
    {
        if (empty($models)) {
            return;
        }

        foreach ($this->eagerLoads as $relationName) {
            $first = $models[0];
            if (!method_exists($first, $relationName)) {
                continue;
            }

            $relationObj = $first->$relationName();

            if ($relationObj instanceof \NovaFlow\Core\Relations\BelongsTo) {
                $foreignKey = $relationObj->getForeignKey();
                $ownerKey = $relationObj->getOwnerKey();
                $relatedClass = $relationObj->getRelatedModel();

                $fkValues = [];
                foreach ($models as $m) {
                    $val = $m->$foreignKey;
                    if ($val !== null && $val !== '') {
                        $fkValues[] = $val;
                    }
                }
                $fkValues = array_values(array_unique($fkValues));

                $relatedMap = [];
                if (!empty($fkValues)) {
                    $relatedList = $relatedClass::whereIn($ownerKey, $fkValues)->get();
                    foreach ($relatedList as $rItem) {
                        $relatedMap[$rItem->$ownerKey] = $rItem;
                    }
                }

                foreach ($models as $m) {
                    $val = $m->$foreignKey;
                    $m->setRelation($relationName, $relatedMap[$val] ?? null);
                }
            } elseif ($relationObj instanceof \NovaFlow\Core\Relations\HasOne) {
                $foreignKey = $relationObj->getForeignKey();
                $localKey = $relationObj->getLocalKey();
                $relatedClass = $relationObj->getRelatedModel();

                $lkValues = [];
                foreach ($models as $m) {
                    $val = $m->$localKey;
                    if ($val !== null && $val !== '') {
                        $lkValues[] = $val;
                    }
                }
                $lkValues = array_values(array_unique($lkValues));

                $relatedMap = [];
                if (!empty($lkValues)) {
                    $relatedList = $relatedClass::whereIn($foreignKey, $lkValues)->get();
                    foreach ($relatedList as $rItem) {
                        $relatedMap[$rItem->$foreignKey] = $rItem;
                    }
                }

                foreach ($models as $m) {
                    $val = $m->$localKey;
                    $m->setRelation($relationName, $relatedMap[$val] ?? null);
                }
            } elseif ($relationObj instanceof \NovaFlow\Core\Relations\HasMany) {
                $foreignKey = $relationObj->getForeignKey();
                $localKey = $relationObj->getLocalKey();
                $relatedClass = $relationObj->getRelatedModel();

                $lkValues = [];
                foreach ($models as $m) {
                    $val = $m->$localKey;
                    if ($val !== null && $val !== '') {
                        $lkValues[] = $val;
                    }
                }
                $lkValues = array_values(array_unique($lkValues));

                $relatedGroup = [];
                if (!empty($lkValues)) {
                    $relatedList = $relatedClass::whereIn($foreignKey, $lkValues)->get();
                    foreach ($relatedList as $rItem) {
                        $relatedGroup[$rItem->$foreignKey][] = $rItem;
                    }
                }

                foreach ($models as $m) {
                    $val = $m->$localKey;
                    $m->setRelation($relationName, $relatedGroup[$val] ?? []);
                }
            }
        }
    }

    /**
     * Hydrate a single row into a model instance or stdClass.
     */
    protected function hydrateRow(array $row): mixed
    {
        if ($this->model) {
            $class = is_string($this->model) ? $this->model : get_class($this->model);
            $obj = new $class();
            if (method_exists($obj, 'syncAttributes')) {
                $obj->syncAttributes($row, true);
            } else {
                foreach ($row as $k => $v) { $obj->$k = $v; }
            }
            return $obj;
        }
        return (object) $row;
    }

    /**
     * Set the limit and offset for a given page.
     */
    public function forPage(int $page, int $perPage = 15): self
    {
        return $this->offset(max(0, ($page - 1) * $perPage))->limit($perPage);
    }

    /**
     * Chunk the results of the query page-by-page.
     */
    public function chunk(int $count, callable $callback): bool
    {
        $page = 1;

        do {
            $clone = clone $this;
            $results = $clone->forPage($page, $count)->get();
            $countResults = count($results);

            if ($countResults === 0) {
                break;
            }

            if ($callback($results, $page) === false) {
                return false;
            }

            unset($results);
            $page++;
        } while ($countResults === $count);

        return true;
    }

    /**
     * Chunk the results of a query by comparing IDs (ideal for massive datasets).
     */
    public function chunkById(int $count, callable $callback, ?string $column = null): bool
    {
        $column = $column ?: ($this->model && method_exists($this->model, 'getPrimaryKey') ? $this->model->getPrimaryKey() : 'id');
        $lastId = null;

        do {
            $clone = clone $this;
            if ($lastId !== null) {
                $clone->where($column, '>', $lastId);
            }

            $results = $clone->orderBy($column, 'ASC')->limit($count)->get();
            $countResults = count($results);

            if ($countResults === 0) {
                break;
            }

            if ($callback($results) === false) {
                return false;
            }

            $lastItem = end($results);
            $lastId = is_object($lastItem) ? ($lastItem->$column ?? $lastItem->id ?? null) : ($lastItem[$column] ?? $lastItem['id'] ?? null);
            unset($results);
        } while ($countResults === $count);

        return true;
    }

    /**
     * Stream query results one by one using a PDO generator cursor.
     * Keeps memory consumption at O(1) regardless of total rows.
     */
    public function cursor(): \Generator
    {
        $sql = $this->toSql();
        $params = $this->getBindings();

        $stmt = $this->db->query($sql, $params);

        if ($stmt instanceof \PDOStatement) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                yield $this->hydrateRow($row);
            }
            $stmt->closeCursor();
        }
    }
}
