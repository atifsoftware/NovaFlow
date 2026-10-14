<?php

namespace NovaFlow\Core\Queue\Contracts;

use NovaFlow\Core\Job;

/**
 * Queue Driver Interface
 * Defines queue storage and retrieval contract across Sync, File, DB, and Redis drivers.
 */
interface QueueDriverInterface
{
    /**
     * Push a new job onto the queue
     *
     * @param Job|string $job
     * @param mixed $data
     * @param string $queue
     * @param int $delay
     * @return mixed Job identifier or execution result
     */
    public function push(Job|string $job, mixed $data = '', string $queue = 'default', int $delay = 0): mixed;

    /**
     * Pop the next job off of the queue
     *
     * @param string $queue
     * @return object|null Job object with ->id, ->payload, ->attempts
     */
    public function pop(string $queue = 'default'): ?object;

    /**
     * Delete a reserved job from the queue
     *
     * @param mixed $id
     * @return bool
     */
    public function delete(mixed $id): bool;

    /**
     * Release a reserved job back onto the queue
     *
     * @param mixed $id
     * @param int $delay
     * @return bool
     */
    public function release(mixed $id, int $delay = 60): bool;

    /**
     * Get the size of the queue
     *
     * @param string $queue
     * @return int
     */
    public function size(string $queue = 'default'): int;

    /**
     * Delete all jobs from the queue
     *
     * @param string $queue
     * @return bool
     */
    public function clear(string $queue = 'default'): bool;
}
