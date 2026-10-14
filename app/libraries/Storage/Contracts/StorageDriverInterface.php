<?php

namespace NovaFlow\Core\Storage\Contracts;

/**
 * Storage Driver Interface
 * Defines unified filesystem operations for local and cloud disks.
 */
interface StorageDriverInterface
{
    /**
     * Write contents to a file
     */
    public function put(string $path, string $contents, array $options = []): bool;

    /**
     * Read file contents
     */
    public function get(string $path): ?string;

    /**
     * Check if a file exists
     */
    public function exists(string $path): bool;

    /**
     * Check if a file is missing
     */
    public function missing(string $path): bool;

    /**
     * Delete one or more files
     */
    public function delete(string|array $paths): bool;

    /**
     * Copy a file to a new location
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file to a new location
     */
    public function move(string $from, string $to): bool;

    /**
     * Get the file size in bytes
     */
    public function size(string $path): int;

    /**
     * Get the file last modified UNIX timestamp
     */
    public function lastModified(string $path): int;

    /**
     * Get the mime-type of a given file
     */
    public function mimeType(string $path): ?string;

    /**
     * Get the public URL for a file
     */
    public function url(string $path): string;

    /**
     * Create a directory
     */
    public function makeDirectory(string $path): bool;

    /**
     * Delete a directory
     */
    public function deleteDirectory(string $path): bool;

    /**
     * Get an array of all files in a directory
     */
    public function files(string $directory = '', bool $recursive = false): array;

    /**
     * Append to a file
     */
    public function append(string $path, string $data): bool;

    /**
     * Prepend to a file
     */
    public function prepend(string $path, string $data): bool;
}
