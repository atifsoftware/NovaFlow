<?php

namespace NovaFlow\Core;

/**
 * Database Interface
 * Standardizes database operations across different drivers (MySQLi, PDO)
 */
interface DatabaseInterface
{
    public function query(string $sql, array $params = []);
    public function fetchAll(string $sql, array $params = []): array;
    public function fetchOne(string $sql, array $params = []);
    public function fetchColumn(string $sql, array $params = []);
    public function fetchValue(string $sql, array $params = []);
    public function fetch(string $sql, array $params = []);

    public function beginTransaction();
    public function commit();
    public function rollback();
    public function inTransaction(): bool;
    public function transactionLevel(): int;
    public function transaction(callable $callback, int $attempts = 1);

    public function insert(string $table, array $data);
    public function update(string $table, array $data, array $where);
    public function count(string $table, array $where = []);

    public function getLastId(): ?int;
    public function countAffected(): int;

    public function raw($value, array $bindings = []);
    public function escape(string $value): string;

    public function getGrammar();
    public function getDriverName(): string;
    public function getConnection(): \PDO;
}
