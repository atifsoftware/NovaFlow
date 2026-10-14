<?php

namespace NovaFlow\Core\Database\Drivers;

use PDO;
use PDOException;
use RuntimeException;
use NovaFlow\Core\Database\Contracts\GrammarInterface;
use NovaFlow\Core\Database\Grammar\PostgresGrammar;

/**
 * PostgreSQL PDO Database Connection
 */
class PostgresConnection extends Connection
{
    public function getDriverName(): string
    {
        return 'pgsql';
    }

    protected function getDefaultGrammar(): GrammarInterface
    {
        return new PostgresGrammar();
    }

    protected function createPdoConnection(): PDO
    {
        $host = $this->config['host'] ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port = $this->config['port'] ?? (defined('DB_PORT') ? DB_PORT : 5432);
        $database = $this->config['database'] ?? (defined('DB_NAME') ? DB_NAME : '');
        $username = $this->config['username'] ?? (defined('DB_USER') ? DB_USER : 'postgres');
        $password = $this->config['password'] ?? (defined('DB_PASS') ? DB_PASS : '');
        $sslmode = $this->config['sslmode'] ?? 'prefer';

        $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslmode}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException("PostgreSQL connection failed: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
