<?php

namespace NovaFlow\Core;

use NovaFlow\Core\Storage\StorageManager;
use NovaFlow\Core\Storage\Contracts\StorageDriverInterface;

/**
 * Storage Facade
 * Provides static access to the active filesystem disks.
 */
class Storage
{
    protected static ?StorageManager $manager = null;

    /**
     * Get or create the StorageManager instance
     */
    public static function getManager(): StorageManager
    {
        if (self::$manager === null) {
            $container = Container::getInstance();
            if ($container->has(StorageManager::class)) {
                self::$manager = $container->make(StorageManager::class);
            } else {
                $config = [];
                $configFile = defined('BASE_PATH') ? BASE_PATH . '/config/filesystems.php' : null;
                if ($configFile && file_exists($configFile)) {
                    $config = require $configFile;
                }

                self::$manager = new StorageManager($config);
                $container->singleton(StorageManager::class, fn() => self::$manager);
            }
        }

        return self::$manager;
    }

    /**
     * Set a custom StorageManager (for tests)
     */
    public static function setManager(?StorageManager $manager): void
    {
        self::$manager = $manager;
    }

    /**
     * Get a specific storage disk
     */
    public static function disk(?string $name = null): StorageDriverInterface
    {
        return self::getManager()->disk($name);
    }

    /**
     * Write contents to a file on default disk
     */
    public static function put(string $path, string $contents, array $options = []): bool
    {
        return self::disk()->put($path, $contents, $options);
    }

    /**
     * Read file contents from default disk
     */
    public static function get(string $path): ?string
    {
        return self::disk()->get($path);
    }

    /**
     * Check if a file exists on default disk
     */
    public static function exists(string $path): bool
    {
        return self::disk()->exists($path);
    }

    /**
     * Check if a file is missing on default disk
     */
    public static function missing(string $path): bool
    {
        return self::disk()->missing($path);
    }

    /**
     * Delete one or more files from default disk
     */
    public static function delete(string|array $paths): bool
    {
        return self::disk()->delete($paths);
    }

    /**
     * Copy a file on default disk
     */
    public static function copy(string $from, string $to): bool
    {
        return self::disk()->copy($from, $to);
    }

    /**
     * Move a file on default disk
     */
    public static function move(string $from, string $to): bool
    {
        return self::disk()->move($from, $to);
    }

    /**
     * Get the file size in bytes
     */
    public static function size(string $path): int
    {
        return self::disk()->size($path);
    }

    /**
     * Get the public URL for a file
     */
    public static function url(string $path): string
    {
        return self::disk()->url($path);
    }

    /**
     * Get mime-type of a file
     */
    public static function mimeType(string $path): ?string
    {
        return self::disk()->mimeType($path);
    }

    /**
     * Forward calls to default disk
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return self::disk()->{$method}(...$parameters);
    }
}
