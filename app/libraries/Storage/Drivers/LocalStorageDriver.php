<?php

namespace NovaFlow\Core\Storage\Drivers;

use NovaFlow\Core\Storage\Contracts\StorageDriverInterface;

/**
 * Local Filesystem Storage Driver
 */
class LocalStorageDriver implements StorageDriverInterface
{
    protected string $root;
    protected string $baseUrl;

    public function __construct(array $config = [])
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));
        $this->root = rtrim($config['root'] ?? ($base . '/storage/app'), '/\\');
        $this->baseUrl = rtrim($config['url'] ?? '/storage', '/');

        if (!is_dir($this->root)) {
            mkdir($this->root, 0755, true);
        }
    }

    protected function fullPath(string $path): string
    {
        $clean = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        return $this->root . DIRECTORY_SEPARATOR . $clean;
    }

    public function put(string $path, string $contents, array $options = []): bool
    {
        $fullPath = $this->fullPath($path);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($fullPath, $contents, LOCK_EX) !== false;
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->fullPath($path);
        if (!file_exists($fullPath) || !is_file($fullPath)) {
            return null;
        }

        return file_get_contents($fullPath) ?: null;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->fullPath($path));
    }

    public function missing(string $path): bool
    {
        return !$this->exists($path);
    }

    public function delete(string|array $paths): bool
    {
        $success = true;
        foreach ((array) $paths as $path) {
            $fullPath = $this->fullPath($path);
            if (file_exists($fullPath)) {
                if (!unlink($fullPath)) {
                    $success = false;
                }
            }
        }
        return $success;
    }

    public function copy(string $from, string $to): bool
    {
        $src = $this->fullPath($from);
        $dst = $this->fullPath($to);

        if (!file_exists($src)) {
            return false;
        }

        $directory = dirname($dst);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return copy($src, $dst);
    }

    public function move(string $from, string $to): bool
    {
        $src = $this->fullPath($from);
        $dst = $this->fullPath($to);

        if (!file_exists($src)) {
            return false;
        }

        $directory = dirname($dst);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return rename($src, $dst);
    }

    public function size(string $path): int
    {
        $fullPath = $this->fullPath($path);
        return file_exists($fullPath) ? (int) filesize($fullPath) : 0;
    }

    public function lastModified(string $path): int
    {
        $fullPath = $this->fullPath($path);
        return file_exists($fullPath) ? (int) filemtime($fullPath) : 0;
    }

    public function mimeType(string $path): ?string
    {
        $fullPath = $this->fullPath($path);
        if (!file_exists($fullPath)) {
            return null;
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fullPath);
            finfo_close($finfo);
            return $mime ?: null;
        }

        return mime_content_type($fullPath) ?: null;
    }

    public function url(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        return $this->baseUrl . '/' . $clean;
    }

    public function makeDirectory(string $path): bool
    {
        $fullPath = $this->fullPath($path);
        if (is_dir($fullPath)) {
            return true;
        }
        return mkdir($fullPath, 0755, true);
    }

    public function deleteDirectory(string $path): bool
    {
        $fullPath = $this->fullPath($path);
        if (!is_dir($fullPath)) {
            return false;
        }

        $files = array_diff(scandir($fullPath) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $current = $fullPath . DIRECTORY_SEPARATOR . $file;
            is_dir($current) ? $this->deleteDirectory($path . '/' . $file) : unlink($current);
        }

        return rmdir($fullPath);
    }

    public function files(string $directory = '', bool $recursive = false): array
    {
        $fullPath = $this->fullPath($directory);
        if (!is_dir($fullPath)) {
            return [];
        }

        $results = [];
        $items = array_diff(scandir($fullPath) ?: [], ['.', '..']);

        foreach ($items as $item) {
            $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
            $relative = ($directory ? rtrim($directory, '/\\') . '/' : '') . $item;

            if (is_file($itemPath)) {
                $results[] = $relative;
            } elseif ($recursive && is_dir($itemPath)) {
                $results = array_merge($results, $this->files($relative, true));
            }
        }

        return $results;
    }

    public function append(string $path, string $data): bool
    {
        $fullPath = $this->fullPath($path);
        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        return file_put_contents($fullPath, $data, FILE_APPEND | LOCK_EX) !== false;
    }

    public function prepend(string $path, string $data): bool
    {
        $existing = $this->get($path) ?? '';
        return $this->put($path, $data . $existing);
    }
}
