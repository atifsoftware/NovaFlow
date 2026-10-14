<?php

namespace NovaFlow\Core\Database\Drivers;

use PDO;
use PDOException;
use PDOStatement;
use Exception;
use RuntimeException;
use NovaFlow\Core\Database\Contracts\ConnectionInterface;
use NovaFlow\Core\Database\Contracts\GrammarInterface;
use NovaFlow\Core\DatabaseException;
use NovaFlow\Core\RawExpression;

/**
 * Base Abstract PDO Database Connection
 */
abstract class Connection implements ConnectionInterface
{
    protected ?PDO $pdo = null;
    protected array $config = [];
    protected ?GrammarInterface $grammar = null;
    protected int $queryCount = 0;
    protected int $affectedRows = 0;
    protected int $transactions = 0;
    protected array $data = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Create the driver-specific PDO instance
     */
    abstract protected function createPdoConnection(): PDO;

    /**
     * Get the default grammar for this connection
     */
    abstract protected function getDefaultGrammar(): GrammarInterface;

    /**
     * Get the driver name
     */
    abstract public function getDriverName(): string;

    /**
     * Get the underlying PDO connection
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->createPdoConnection();
        }
        return $this->pdo;
    }

    /**
     * Alias for backwards compatibility
     */
    public function getConnection(): PDO
    {
        return $this->getPdo();
    }

    /**
     * Set a custom PDO instance (useful for testing or external connections)
     */
    public function setPdo(PDO $pdo): self
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * Get the query grammar used by the connection
     */
    public function getGrammar(): GrammarInterface
    {
        if ($this->grammar === null) {
            $this->grammar = $this->getDefaultGrammar();
        }
        return $this->grammar;
    }

    /**
     * Set the query grammar
     */
    public function setGrammar(GrammarInterface $grammar): self
    {
        $this->grammar = $grammar;
        return $this;
    }

    /**
     * Execute a statement
     */
    public function query(string $sql, array $params = [])
    {
        $pdo = $this->getPdo();

        try {
            $stmt = $pdo->prepare($sql);
            $this->queryCount++;
            $stmt->execute($params);

            $this->affectedRows = $stmt->rowCount();

            // Determine if it's a SELECT query or not
            $sqlUpper = trim(strtoupper($sql));
            if (preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', $sqlUpper)) {
                return $stmt;
            } else {
                if (strpos($sqlUpper, 'INSERT') === 0) {
                    return $pdo->lastInsertId();
                } else {
                    return $this->affectedRows;
                }
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Query failed: " . $e->getMessage() . " [SQL: $sql]", 0, $e, $sql, $params);
        }
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $result = $this->query($sql, $params);
        if ($result instanceof PDOStatement) {
            return $result->fetchAll();
        }
        return [];
    }

    public function fetchColumn(string $sql, array $params = [])
    {
        $result = $this->query($sql, $params);
        if ($result instanceof PDOStatement) {
            return $result->fetchColumn();
        }
        return null;
    }

    public function fetchOne(string $sql, array $params = [])
    {
        $result = $this->query($sql, $params);
        if ($result instanceof PDOStatement) {
            return $result->fetch();
        }
        return null;
    }

    public function fetchValue(string $sql, array $params = [])
    {
        return $this->fetchColumn($sql, $params);
    }

    public function fetch(string $sql, array $params = [])
    {
        return $this->fetchOne($sql, $params);
    }

    public function beginTransaction(): bool
    {
        $this->transactions++;

        if ($this->transactions === 1) {
            return $this->getPdo()->beginTransaction();
        }

        if ($this->transactions > 1) {
            $this->getPdo()->exec("SAVEPOINT trans_{$this->transactions}");
            return true;
        }

        return false;
    }

    public function commit(): bool
    {
        if ($this->transactions === 1) {
            $this->transactions = 0;
            return $this->getPdo()->commit();
        }

        if ($this->transactions > 1) {
            $savepoint = "trans_{$this->transactions}";
            $this->transactions--;
            if ($this->getDriverName() !== 'sqlite') {
                $this->getPdo()->exec("RELEASE SAVEPOINT $savepoint");
            }
            return true;
        }

        return false;
    }

    public function rollback(): bool
    {
        if ($this->transactions === 1) {
            $this->transactions = 0;
            return $this->getPdo()->rollBack();
        }

        if ($this->transactions > 1) {
            $savepoint = "trans_{$this->transactions}";
            $this->transactions--;
            $this->getPdo()->exec("ROLLBACK TO SAVEPOINT $savepoint");
            return true;
        }

        return false;
    }

    public function inTransaction(): bool
    {
        return $this->transactions > 0 || $this->getPdo()->inTransaction();
    }

    public function transactionLevel(): int
    {
        return $this->transactions;
    }

    public function transaction(callable $callback, int $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();

            try {
                $result = $callback($this);
                $this->commit();
                return $result;
            } catch (\Throwable $e) {
                $this->rollback();

                if ($currentAttempt >= $attempts) {
                    if ($e instanceof DatabaseException) {
                        throw $e;
                    }
                    throw new DatabaseException(
                        "Transaction failed on attempt {$currentAttempt}: " . $e->getMessage(),
                        (int)$e->getCode(),
                        $e
                    );
                }
            }
        }

        return null;
    }

    public function insert(string $table, array $data)
    {
        $grammar = $this->getGrammar();
        $keys = array_keys($data);
        $fields = implode(', ', array_map([$grammar, 'wrap'], $keys));
        $placeholders = $grammar->parameterize(array_values($data));
        $wrappedTable = $grammar->wrapTable($table);

        $sql = "INSERT INTO $wrappedTable ($fields) VALUES ($placeholders)";

        return $this->query($sql, array_values($data));
    }

    public function update(string $table, array $data, array $where)
    {
        $grammar = $this->getGrammar();
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = $grammar->wrap($key) . ' = ?';
            $params[] = $value;
        }

        $wrappedTable = $grammar->wrapTable($table);
        $sql = "UPDATE $wrappedTable SET " . implode(', ', $fields);

        $whereClauses = [];
        foreach ($where as $col => $val) {
            $whereClauses[] = $grammar->wrap($col) . ' = ?';
            $params[] = $val;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        return $this->query($sql, $params);
    }

    public function count(string $table, array $where = [])
    {
        $grammar = $this->getGrammar();
        $wrappedTable = $grammar->wrapTable($table);
        $sql = "SELECT COUNT(*) as count FROM $wrappedTable";
        $params = [];

        $whereClauses = [];
        foreach ($where as $col => $val) {
            $whereClauses[] = $grammar->wrap($col) . ' = ?';
            $params[] = $val;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $result = $this->fetchOne($sql, $params);
        return $result ? (int) $result['count'] : 0;
    }

    public function getLastId(): ?int
    {
        if (!$this->pdo) {
            return null;
        }
        $id = $this->pdo->lastInsertId();
        return $id ? (int) $id : null;
    }

    public function countAffected(): int
    {
        return $this->affectedRows;
    }

    public function raw($value, array $bindings = [])
    {
        return new RawExpression($value, $bindings);
    }

    public function escape(string $value): string
    {
        $key = ':' . count($this->data);
        $this->data[$key] = $value;
        return $key;
    }
}
