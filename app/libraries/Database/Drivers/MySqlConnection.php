<?php

namespace NovaFlow\Core\Database\Drivers;

use PDO;
use PDOException;
use RuntimeException;
use NovaFlow\Core\Database\Contracts\GrammarInterface;
use NovaFlow\Core\Database\Grammar\MySqlGrammar;

/**
 * MySQL PDO Database Connection
 */
class MySqlConnection extends Connection
{
    public function getDriverName(): string
    {
        return 'mysql';
    }

    protected function getDefaultGrammar(): GrammarInterface
    {
        return new MySqlGrammar();
    }

    protected function createPdoConnection(): PDO
    {
        $host = $this->config['host'] ?? (defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port = $this->config['port'] ?? (defined('DB_PORT') ? DB_PORT : 3306);
        $database = $this->config['database'] ?? (defined('DB_NAME') ? DB_NAME : '');
        $username = $this->config['username'] ?? (defined('DB_USER') ? DB_USER : 'root');
        $password = $this->config['password'] ?? (defined('DB_PASS') ? DB_PASS : '');
        $charset = $this->config['charset'] ?? (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException("MySQL connection failed: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
