<?php

namespace NovaFlow\Core\Database\Schema\Grammar;

use NovaFlow\Core\QueryBuilder\Blueprint;

/**
 * PostgreSQL Schema Grammar
 */
class PostgresSchemaGrammar extends SchemaGrammar
{
    public function wrapTable(string $table): string
    {
        return '"' . str_replace('"', '""', $table) . '"';
    }

    public function wrap(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
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
        return "CREATE TABLE $table ($colsSql$cmdsSql)";
    }

    public function compileColumn($column): string
    {
        $name = $this->wrap($column->getName());
        $type = $column->getType();

        // Translate types to Postgres equivalents
        if (stripos($type, 'INT AUTO_INCREMENT') !== false) {
            $type = 'SERIAL PRIMARY KEY';
        } elseif (stripos($type, 'BIGINT AUTO_INCREMENT') !== false) {
            $type = 'BIGSERIAL PRIMARY KEY';
        } elseif (stripos($type, 'TINYINT(1)') !== false) {
            $type = 'BOOLEAN';
        } elseif (stripos($type, 'DATETIME') !== false) {
            $type = 'TIMESTAMP';
        }

        $sql = "$name $type";

        // Do not add NOT NULL if it's already a primary key
        if (stripos($type, 'PRIMARY KEY') === false) {
            if (!$column->isNullable()) {
                $sql .= " NOT NULL";
            } else {
                $sql .= " NULL";
            }
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
