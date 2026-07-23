<?php

namespace NovaFlow\Core;

/**
 * Dotenv - Environment Variable Loader
 * Simplified version without external dependencies
 */
class Dotenv
{
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load .env file
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }

        // If in app/libraries/, dirname(__DIR__, 2) is the project root
        $path = $path ?? dirname(__DIR__, 2) . '/.env';

        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes
                $value = trim($value, '"');
                $value = trim($value, "'");

                // Set environment variable
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;

                self::$variables[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return ($val === false || $val === null) ? $default : $val;
    }

    /**
     * Check if environment variable exists
     */
    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset($_ENV[$key]) || isset($_SERVER[$key]) || getenv($key) !== false;
    }

    /**
     * Set environment variable
     */
    public static function set($key, $value)
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        self::$variables[$key] = $value;
    }

    /**
     * Get all variables
     */
    public static function all()
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$variables;
    }
}
