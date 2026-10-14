<?php

namespace NovaFlow\Core\Queue\Drivers;

use NovaFlow\Core\Queue\Contracts\QueueDriverInterface;
use NovaFlow\Core\Job;
use Throwable;

/**
 * Synchronous Queue Driver
 * Executes jobs immediately in-process without delay.
 */
class SyncQueueDriver implements QueueDriverInterface
{
    public function push(Job|string $job, mixed $data = '', string $queue = 'default', int $delay = 0): mixed
    {
        $instance = is_string($job) ? (class_exists($job) ? new $job($data) : null) : $job;

        if ($instance instanceof Job) {
            try {
                $instance->handle();
                return true;
            } catch (Throwable $e) {
                $instance->failed($e);
                throw $e;
            }
        }

        return false;
    }

    public function pop(string $queue = 'default'): ?object
    {
        // Sync queue executes immediately on push, so pop has nothing pending
        return null;
    }

    public function delete(mixed $id): bool
    {
        return true;
    }

    public function release(mixed $id, int $delay = 60): bool
    {
        return true;
    }

    public function size(string $queue = 'default'): int
    {
        return 0;
    }

    public function clear(string $queue = 'default'): bool
    {
        return true;
    }
}
