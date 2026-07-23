<?php

namespace NovaFlow\Core;

use PDO;
use PDOException;
use RuntimeException;
use Exception;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\DatabaseException;
use PDOStatement;

/**
 * PDO Database Driver
 */
class PDODriver implements DatabaseInterface
{
    private $pdo;
    private $config = [];
    private $queryCount = 0;
    private $data = [];
    private $affected = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load Configuration
     */
    private function loadConfig()
    {
        $this->config = [
            'host' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS,
            'database' => DB_NAME,
            'port' => defined('DB_PORT') ? DB_PORT : 3306,
            'charset' => 'utf8mb4'
        ];
    }

    /**
     * Connect to Database
     */
    private function connect()
    {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};port={$this->config['port']};charset={$this->config['charset']}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $options);

        } catch (PDOException $e) {
            throw new RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        if (!$this->pdo) {
            $this->connect();
        }
        return $this->pdo;
    }

    /**
     * Execute a statement
     */
    public function query(string $sql, array $params = [])
    {
        if (!$this->pdo) {
            $this->connect();
        }

        try {
            $stmt = $this->pdo->prepare($sql);

            $this->queryCount++;
            $stmt->execute($params);

            // Determine if it's a SELECT query or not
            $sqlUpper = trim(strtoupper($sql));
            if (preg_match('/^(SELECT|SHOW|DESCRIBE|EXPLAIN)\s/i', $sqlUpper)) {
                return $stmt;
            } else {
                if (strpos($sqlUpper, 'INSERT') === 0) {
                    return $this->pdo->lastInsertId();
                } else {
                    return $stmt->rowCount();
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

    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    public function commit()
    {
        return $this->getConnection()->commit();
    }

    public function rollback()
    {
        return $this->getConnection()->rollBack();
    }

    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw new DatabaseException(
                "Transaction failed: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function insert(string $table, array $data)
    {
        $keys = array_keys($data);
        $fields = '`' . implode('`, `', $keys) . '`';
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $sql = "INSERT INTO `$table` ($fields) VALUES ($placeholders)";

        return $this->query($sql, array_values($data));
    }

    public function update(string $table, array $data, array $where)
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $params[] = $value;
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $fields);

        $whereClauses = [];
        foreach ($where as $col => $val) {
            $whereClauses[] = "`$col` = ?";
            $params[] = $val;
        }

        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        return $this->query($sql, $params);
    }

    public function count(string $table, array $where = [])
    {
        $sql = "SELECT COUNT(*) as count FROM `$table`";
        $params = [];

        $whereClauses = [];
        foreach ($where as $col => $val) {
            $whereClauses[] = "`$col` = ?";
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
        return 0;
    }

    public function raw($value, array $bindings = [])
    {
        return new RawExpression($value, $bindings);
    }

    public function escape(string $value): string
    {
        return $this->getConnection()->quote($value);
    }
}
