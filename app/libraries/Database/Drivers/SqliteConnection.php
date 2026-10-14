<?php

namespace NovaFlow\Core\Database\Drivers;

use PDO;
use PDOException;
use RuntimeException;
use NovaFlow\Core\Database\Contracts\GrammarInterface;
use NovaFlow\Core\Database\Grammar\SqliteGrammar;

/**
 * SQLite PDO Database Connection
 */
class SqliteConnection extends Connection
{
    public function getDriverName(): string
    {
        return 'sqlite';
    }

    protected function getDefaultGrammar(): GrammarInterface
    {
        return new SqliteGrammar();
    }

    protected function createPdoConnection(): PDO
    {
        $database = $this->config['database'] ?? (defined('DB_NAME') ? DB_NAME : ':memory:');

        // Check if database is a relative file path or memory
        if ($database !== ':memory:' && !file_exists($database)) {
            $dir = dirname($database);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            touch($database);
        }

        $dsn = "sqlite:{$database}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, null, null, $options);
            // Enable foreign keys for SQLite
            $pdo->exec('PRAGMA foreign_keys = ON;');
            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException("SQLite connection failed: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
