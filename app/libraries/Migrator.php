<?php

namespace NovaFlow\Core;

use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Container;

/**
 * Migrator Engine
 * Tracks and executes database migrations.
 */
class Migrator
{
    protected $db;
    protected $table = 'migrations';
    protected $directory;

    public function __construct(DatabaseInterface $db, $directory = null)
    {
        $this->db = $db;
        $this->directory = $directory ?: \BASE_PATH . '/app/database/migrations';
    }

    /**
     * Ensure the tracking table exists.
     */
    public function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL,
            `batch` INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->query($sql);
    }

    /**
     * Run all pending migrations.
     */
    public function run()
    {
        $this->ensureTableExists();

        // Get executed migrations
        $executed = $this->getExecutedMigrations();

        // Get all files
        $files = $this->getMigrationFiles();

        $batch = $this->getNextBatchNumber();
        $ran = [];

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');

            if (in_array($migrationName, $executed)) {
                continue;
            }

            require_once $file;

            // Resolve 2023_01_01_000000_create_users_table.php -> CreateUsersTable
            $className = $this->resolveClassName($migrationName);

            if (!class_exists($className)) {
                echo "\033[31m✗ Error: Class '{$className}' not found in {$migrationName}.php\033[0m\n";
                continue;
            }

            echo "\033[36mMigrating:\033[0m $migrationName\n";

            $instance = new $className;
            
            // Execute up() method
            $instance->up();

            $this->log($migrationName, $batch);
            $ran[] = $migrationName;

            echo "\033[32mMigrated: \033[0m $migrationName\n";
        }

        return $ran;
    }

    /**
     * Get names of executed migrations.
     */
    protected function getExecutedMigrations()
    {
        $rows = $this->db->fetchAll("SELECT migration FROM `{$this->table}`");
        return array_column($rows, 'migration');
    }

    /**
     * Get list of migration files.
     */
    protected function getMigrationFiles()
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
            return [];
        }
        $files = glob($this->directory . '/*.php');
        sort($files);
        return $files;
    }

    /**
     * Get the next batch number.
     */
    protected function getNextBatchNumber()
    {
        $row = $this->db->fetchOne("SELECT MAX(batch) as batch FROM `{$this->table}`");
        return ($row['batch'] ?? 0) + 1;
    }

    /**
     * Log the migration in the database.
     */
    protected function log($migration, $batch)
    {
        $this->db->query("INSERT INTO `{$this->table}` (migration, batch) VALUES (?, ?)", [$migration, $batch]);
    }

    /**
     * Resolve class name from file name.
     * Convention: YYYY_MM_DD_HHMMSS_create_users_table -> CreateUsersTable
     */
    protected function resolveClassName($migrationName)
    {
        $parts = explode('_', $migrationName);
        
        // Standard Laravel/NovaFlow: 2023_01_01_000000 (4 parts prefix)
        $sliceIndex = 4;
        if (!is_numeric($parts[0])) {
            $sliceIndex = 0;
        }

        $classParts = array_slice($parts, $sliceIndex);
        $className = '';
        foreach ($classParts as $p) {
            $className .= ucfirst($p);
        }
        return $className;
    }
}
