<?php

/**
 * Advanced PSR-4 Compatible Autoloader
 */
class Autoloader
{
    /**
     * Namespace to directory mappings
     */
    private static array $prefixes = [];

    /**
     * Register the autoloader
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load'], true, true);
    }

    /**
     * Add a namespace prefix with base directory
     */
    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!isset(self::$prefixes[$prefix])) {
            self::$prefixes[$prefix] = [];
        }

        self::$prefixes[$prefix][] = $baseDir;
    }

    /**
     * Load the class file
     */
    public static function load(string $className): void
    {
        $prefix = $className;

        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($className, 0, $pos + 1);
            $relativeClass = substr($className, $pos + 1);

            $mappedFile = self::loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return;
            }

            $prefix = rtrim($prefix, '\\');
        }

        // Fallback for global namespace classes if any directories were registered without prefix
        // (Maintaining backward compatibility for transition)
        foreach (self::$prefixes['global'] ?? [] as $dir) {
            $file = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }

    protected static function loadMappedFile(string $prefix, string $relativeClass): ?string
    {
        if (!isset(self::$prefixes[$prefix])) {
            return null;
        }

        foreach (self::$prefixes[$prefix] as $baseDir) {
            $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
            if (is_file($file)) {
                require_once $file;
                return $file;
            }
        }

        return null;
    }

    /**
     * Legacy support: Register array of directories as 'global'
     */
    public static function registerDirectories(array $directories): void
    {
        foreach ($directories as $dir) {
            self::addNamespace('global', $dir);
        }
    }
}
