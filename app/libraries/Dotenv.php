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
    public static function load($path = null, bool $force = false)
    {
        if (self::$loaded && !$force) {
            return;
        }

        // Project root .env path
        $path = $path ?? (file_exists(dirname(dirname(__DIR__)) . '/.env') ? dirname(dirname(__DIR__)) . '/.env' : dirname(__DIR__) . '/.env');

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

                // Strip unquoted inline comments
                if (!str_starts_with($value, '"') && !str_starts_with($value, "'")) {
                    if (strpos($value, ' #') !== false) {
                        $value = trim(explode(' #', $value, 2)[0]);
                    } elseif (strpos($value, ' //') !== false) {
                        $value = trim(explode(' //', $value, 2)[0]);
                    }
                }

                // Remove quotes
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }

                $lower = strtolower($value);
                $parsedValue = match ($lower) {
                    'true', '(true)' => true,
                    'false', '(false)' => false,
                    'null', '(null)' => null,
                    'empty', '(empty)' => '',
                    default => $value,
                };

                // Set environment variable
                $stringVal = is_bool($parsedValue) ? ($parsedValue ? 'true' : 'false') : (string)$parsedValue;
                putenv("$key=$stringVal");
                $_ENV[$key] = $parsedValue;
                $_SERVER[$key] = $parsedValue;

                self::$variables[$key] = $parsedValue;
            }
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $val = getenv($key);
        if ($val === false || $val === null) {
            return $default;
        }

        $lower = strtolower((string)$val);
        return match ($lower) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $val,
        };
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
