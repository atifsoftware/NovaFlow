<?php

namespace NovaFlow\Core\Database\Contracts;

use NovaFlow\Core\DatabaseInterface;
use PDO;

/**
 * Connection Interface
 * Represents an active database connection and driver contract
 */
interface ConnectionInterface extends DatabaseInterface
{
    /**
     * Get the underlying PDO connection
     */
    public function getPdo(): PDO;

    /**
     * Get the query grammar used by the connection
     */
    public function getGrammar(): GrammarInterface;

    /**
     * Set the query grammar used by the connection
     */
    public function setGrammar(GrammarInterface $grammar): self;

    /**
     * Get the driver name (e.g., 'mysql', 'pgsql', 'sqlite')
     */
    public function getDriverName(): string;
}
