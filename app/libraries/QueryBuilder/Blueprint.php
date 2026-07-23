<?php

namespace NovaFlow\Core\QueryBuilder;

class Blueprint
{
    protected $table;
    protected $columns = [];
    protected $commands = [];
    protected $isAlter = false;

    public function __construct($table, $isAlter = false)
    {
        $this->table = $table;
        $this->isAlter = $isAlter;
    }

    public function increments($column)
    {
        return $this->addColumn($column, 'INT AUTO_INCREMENT PRIMARY KEY');
    }

    public function id($column = 'id')
    {
        return $this->increments($column);
    }

    public function string($column, $length = 255)
    {
        return $this->addColumn($column, "VARCHAR($length)");
    }

    public function integer($column)
    {
        return $this->addColumn($column, 'INT');
    }

    public function bigInteger($column)
    {
        return $this->addColumn($column, 'BIGINT');
    }

    public function text($column)
    {
        return $this->addColumn($column, 'TEXT');
    }

    public function boolean($column)
    {
        return $this->addColumn($column, 'TINYINT(1)');
    }

    public function date($column)
    {
        return $this->addColumn($column, 'DATE');
    }

    public function dateTime($column)
    {
        return $this->addColumn($column, 'DATETIME');
    }

    public function tinyInt($column)
    {
        return $this->addColumn($column, 'TINYINT');
    }

    public function longText($column)
    {
        return $this->addColumn($column, 'LONGTEXT');
    }

    public function timestamp($column)
    {
        return $this->addColumn($column, 'TIMESTAMP');
    }

    public function index($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: 'idx_' . implode('_', $columns);
        $this->commands[] = "INDEX `$name` (`" . implode("`, `", $columns) . "`)";
        return $this;
    }

    public function timestamps()
    {
        $this->dateTime('created_at')->nullable();
        $this->dateTime('updated_at')->nullable();
    }

    public function softDeletes()
    {
        $this->dateTime('deleted_at')->nullable();
    }

    protected function addColumn($name, $type)
    {
        $column = new FluentColumn($name, $type, $this->isAlter, $this);
        $this->columns[] = $column;
        return $column;
    }

    public function dropColumn($column)
    {
        $this->commands[] = "DROP COLUMN `$column`";
    }

    public function toSql()
    {
        $cols = [];
        foreach ($this->columns as $col) {
            $cols[] = $col->toSql();
        }

        $colsSql = implode(', ', $cols);
        $cmdsSql = !empty($this->commands) ? ', ' . implode(', ', $this->commands) : '';
        
        return "CREATE TABLE `{$this->table}` ($colsSql $cmdsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    public function toSqlAlter()
    {
        $queries = [];

        // Add columns
        foreach ($this->columns as $col) {
            $queries[] = "ALTER TABLE `{$this->table}` ADD COLUMN " . $col->toSql();
        }

        // Commands (drop, etc)
        foreach ($this->commands as $cmd) {
            $queries[] = "ALTER TABLE `{$this->table}` $cmd";
        }

        return $queries;
    }
}

class FluentColumn
{
    protected $name;
    protected $type;
    protected $nullable = false;
    protected $default = null;
    protected $isAlter = false;
    protected $parent;

    public function __construct($name, $type, $isAlter = false, $parent = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isAlter = $isAlter;
        $this->parent = $parent;
    }

    public function index($name = null)
    {
        if ($this->parent) {
            $this->parent->index($this->name, $name);
        }
        return $this;
    }

    public function nullable()
    {
        $this->nullable = true;
        return $this;
    }

    public function default($value)
    {
        $this->default = $value;
        return $this;
    }

    public function useCurrent()
    {
        $this->default = 'CURRENT_TIMESTAMP';
        return $this;
    }

    public function toSql()
    {
        $sql = "`{$this->name}` {$this->type}";

        if (!$this->nullable) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }

        if ($this->default !== null) {
            if ($this->default === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $val = is_string($this->default) ? "'{$this->default}'" : $this->default;
                $sql .= " DEFAULT $val";
            }
        }

        return $sql;
    }
}
