<?php

namespace NovaFlow\Core;

/**
 * Queue Manager
 * Directs jobs to the database or other drivers
 */
class Queue
{
    /**
     * Push a new job onto the queue
     * 
     * @param Job $job
     * @param string $queue
     * @return int|bool
     */
    public static function push(Job $job, string $queue = 'default')
    {
        $payload = [
            'display_name' => get_class($job),
            'instance' => serialize($job),
            'created_at' => time()
        ];

        $availableAt = time() + ($job->delay ?? 0);

        $db = Container::getInstance()->make(DatabaseInterface::class);
        
        // Using raw SQL as QueryBuilder might not be fully available in some contexts
        return $db->query(
            "INSERT INTO jobs (queue, payload, attempts, available_at, created_at) VALUES (?, ?, ?, ?, ?)",
            [
                $queue,
                json_encode($payload),
                0,
                date('Y-m-d H:i:s', $availableAt),
                date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Pop the next available job from the queue
     * 
     * @param string $queue
     * @return object|null
     */
    public static function pop(string $queue = 'default')
    {
        $db = Container::getInstance()->make(DatabaseInterface::class);
        $now = date('Y-m-d H:i:s');

        // Select the first available candidate
        $job = $db->fetchOne(
            "SELECT * FROM jobs WHERE queue = ? AND reserved_at IS NULL AND available_at <= ? ORDER BY created_at ASC LIMIT 1",
            [$queue, $now]
        );

        if ($job) {
            $reservedAt = date('Y-m-d H:i:s');
            // Try to reserve it atomically. Only 1 worker will succeed in updating
            $affected = $db->query(
                "UPDATE jobs SET reserved_at = ? WHERE id = ? AND reserved_at IS NULL",
                [$reservedAt, $job['id']]
            );

            if ($affected > 0) {
                $job['reserved_at'] = $reservedAt;
                return (object)$job;
            }
        }

        return null;
    }

    /**
     * Delete a job after success
     */
    public static function delete(int $id)
    {
        $db = Container::getInstance()->make(DatabaseInterface::class);
        return $db->query("DELETE FROM jobs WHERE id = ?", [$id]);
    }

    /**
     * Release a job back to the queue (on failure)
     */
    public static function release(int $id, int $delay = 60)
    {
        $db = Container::getInstance()->make(DatabaseInterface::class);
        return $db->query(
            "UPDATE jobs SET reserved_at = NULL, available_at = ?, attempts = attempts + 1 WHERE id = ?",
            [date('Y-m-d H:i:s', time() + $delay), $id]
        );
    }
}
