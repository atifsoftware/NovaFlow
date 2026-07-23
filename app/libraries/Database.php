<?php

namespace NovaFlow\Core;

/**
 * Database Alias Class
 * Provides backward compatibility - maps to DatabaseInterface
 * 
 * @deprecated Use DB::table() or directly inject DatabaseInterface instead
 */
class Database extends PDODriver
{
    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        return Container::getInstance()->make(self::class);
    }
}