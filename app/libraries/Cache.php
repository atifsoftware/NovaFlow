<?php

namespace NovaFlow\Core;

/**
 * Simple File-based Caching System
 */
class Cache
{
    private static $cacheDir = 'storage/cache';
    private static $defaultTtl = 3600; // 1 hour

    /**
     * Initialize cache directory
     */
    public static function init()
    {
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        $path = $base . self::$cacheDir;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Get cache key file path
     */
    private static function getCacheFile($key)
    {
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        return $base . self::$cacheDir . '/' . md5($key) . '.cache';
    }

    /**
     * Check if cache exists and is valid
     */
    private static function isValid($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);

        if (!$data || !isset($data['expires'])) {
            return false;
        }

        return time() < $data['expires'];
    }

    /**
     * Get cached value
     */
    public static function get($key, $default = null)
    {
        self::init();
        $file = self::getCacheFile($key);

        if (!self::isValid($file)) {
            return $default;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);

        return $data['value'] ?? $default;
    }

    /**
     * Set cache value
     */
    public static function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? self::$defaultTtl;
        self::init();
        $file = self::getCacheFile($key);
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        return file_put_contents($file, serialize($data), LOCK_EX) !== false;
    }

    /**
     * Check if cache key exists
     */
    public static function has($key)
    {
        self::init();
        $file = self::getCacheFile($key);
        return self::isValid($file);
    }

    /**
     * Delete cache key
     */
    public static function delete($key)
    {
        $file = self::getCacheFile($key);
        if (file_exists($file)) {
            unlink($file);
            return true;
        }
        return false;
    }

    /**
     * Clear all cache
     */
    public static function clear()
    {
        self::init();
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        $files = glob($base . self::$cacheDir . '/*.cache');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get or set cache (cache miss callback)
     */
    public static function remember($key, $ttl, $callback)
    {
        $value = self::get($key);

        if ($value === null) {
            $value = $callback();
            self::set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Clean expired cache entries
     */
    public static function clean()
    {
        self::init();
        $base = defined('BASE_PATH') ? BASE_PATH . '/' : '';
        $files = glob($base . self::$cacheDir . '/*.cache');
        $cleaned = 0;

        foreach ($files as $file) {
            if (!self::isValid($file)) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
