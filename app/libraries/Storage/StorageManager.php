<?php

namespace NovaFlow\Core\Storage;

use NovaFlow\Core\Storage\Contracts\StorageDriverInterface;
use NovaFlow\Core\Storage\Drivers\LocalStorageDriver;
use NovaFlow\Core\Storage\Drivers\S3StorageDriver;
use InvalidArgumentException;

/**
 * Storage Manager
 * Creates and caches storage disk driver instances.
 */
class StorageManager
{
    protected array $disks = [];
    protected array $customCreators = [];
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get a storage disk instance
     */
    public function disk(?string $name = null): StorageDriverInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (isset($this->disks[$name])) {
            return $this->disks[$name];
        }

        return $this->disks[$name] = $this->resolve($name);
    }

    /**
     * Resolve the given disk
     */
    protected function resolve(string $name): StorageDriverInterface
    {
        $config = $this->getConfig($name);

        if (isset($this->customCreators[$name])) {
            return $this->customCreators[$name]($config);
        }

        $driver = $config['driver'] ?? $name;

        switch ($driver) {
            case 'local':
                return new LocalStorageDriver($config);

            case 's3':
                return new S3StorageDriver($config);

            default:
                throw new InvalidArgumentException("Storage driver [{$driver}] not supported.");
        }
    }

    /**
     * Get the storage configuration for a disk
     */
    protected function getConfig(string $name): array
    {
        if (isset($this->config['disks'][$name])) {
            return $this->config['disks'][$name];
        }

        $base = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));

        // Default configurations
        $defaults = [
            'local' => [
                'driver' => 'local',
                'root'   => $base . '/storage/app',
                'url'    => '/storage',
            ],
            'public' => [
                'driver' => 'local',
                'root'   => $base . '/public/uploads',
                'url'    => '/uploads',
            ],
            's3' => [
                'driver'   => 's3',
                'key'      => getenv('AWS_ACCESS_KEY_ID') ?: '',
                'secret'   => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
                'region'   => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
                'bucket'   => getenv('AWS_BUCKET') ?: '',
                'url'      => getenv('AWS_URL') ?: null,
                'endpoint' => getenv('AWS_ENDPOINT') ?: null,
            ]
        ];

        return $defaults[$name] ?? ['driver' => 'local'];
    }

    /**
     * Get the default driver name
     */
    public function getDefaultDriver(): string
    {
        return $this->config['default'] ?? (getenv('FILESYSTEM_DISK') ?: 'local');
    }

    /**
     * Register a custom driver creator Closure
     */
    public function extend(string $driver, callable $callback): self
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * Pass methods directly to default disk
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->disk()->{$method}(...$parameters);
    }
}
