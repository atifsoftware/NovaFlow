<?php

namespace NovaFlow\Core\Database\Schema\Grammar;

use NovaFlow\Core\QueryBuilder\Blueprint;

/**
 * MySQL Schema Grammar
 */
class MySqlSchemaGrammar extends SchemaGrammar
{
    public function wrapTable(string $table): string
    {
        return '`' . str_replace('`', '``', $table) . '`';
    }

    public function wrap(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }

    public function compileCreate(Blueprint $blueprint): string
    {
        $cols = [];
        foreach ($blueprint->getColumns() as $col) {
            $cols[] = $this->compileColumn($col);
        }

        $colsSql = implode(', ', $cols);
        $cmds = $blueprint->getCommands();
        $cmdsSql = !empty($cmds) ? ', ' . implode(', ', $cmds) : '';

        $table = $this->wrapTable($blueprint->getTable());
        return "CREATE TABLE $table ($colsSql$cmdsSql) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    public function compileColumn($column): string
    {
        $name = $this->wrap($column->getName());
        $type = $column->getType();

        $sql = "$name $type";

        if (!$column->isNullable()) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }

        $default = $column->getDefault();
        if ($default !== null) {
            if ($default === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $val = is_string($default) ? "'$default'" : $default;
                $sql .= " DEFAULT $val";
            }
        }

        return $sql;
    }
}
