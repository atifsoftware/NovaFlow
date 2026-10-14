<?php

namespace NovaFlow\Core\Queue\Drivers;

use NovaFlow\Core\Queue\Contracts\QueueDriverInterface;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Container;
use NovaFlow\Core\Job;

/**
 * Database Queue Driver
 * Persists and processes jobs using a relational database table (MySQL, PostgreSQL, SQLite).
 */
class DatabaseQueueDriver implements QueueDriverInterface
{
    protected ?DatabaseInterface $db = null;
    protected string $table = 'jobs';

    public function __construct(array $config = [])
    {
        $this->table = $config['table'] ?? 'jobs';
    }

    protected function getDb(): DatabaseInterface
    {
        if ($this->db === null) {
            $this->db = Container::getInstance()->make(DatabaseInterface::class);
        }
        return $this->db;
    }

    public function setDb(DatabaseInterface $db): self
    {
        $this->db = $db;
        return $this;
    }

    public function push(Job|string $job, mixed $data = '', string $queue = 'default', int $delay = 0): mixed
    {
        $payload = [
            'display_name' => is_string($job) ? $job : get_class($job),
            'instance'     => is_string($job) ? null : serialize($job),
            'data'         => $data,
            'created_at'   => time()
        ];

        $delaySeconds = $delay > 0 ? $delay : ($job instanceof Job ? ($job->delay ?? 0) : 0);
        $availableAt = date('Y-m-d H:i:s', time() + $delaySeconds);
        $createdAt = date('Y-m-d H:i:s');

        return $this->getDb()->insert($this->table, [
            'queue'        => $queue,
            'payload'      => json_encode($payload),
            'attempts'     => 0,
            'reserved_at'  => null,
            'available_at' => $availableAt,
            'created_at'   => $createdAt
        ]);
    }

    public function pop(string $queue = 'default'): ?object
    {
        $db = $this->getDb();
        $now = date('Y-m-d H:i:s');

        // Fetch candidate job
        $sql = "SELECT * FROM {$this->table} WHERE queue = ? AND reserved_at IS NULL AND available_at <= ? ORDER BY id ASC LIMIT 1";
        $job = $db->fetchOne($sql, [$queue, $now]);

        if ($job) {
            $id = $job['id'];
            $db->query(
                "UPDATE {$this->table} SET reserved_at = ?, attempts = attempts + 1 WHERE id = ?",
                [$now, $id]
            );

            $job['attempts'] = (int) $job['attempts'] + 1;
            return (object) $job;
        }

        return null;
    }

    public function delete(mixed $id): bool
    {
        $res = $this->getDb()->query("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return $res !== false;
    }

    public function release(mixed $id, int $delay = 60): bool
    {
        $availableAt = date('Y-m-d H:i:s', time() + $delay);
        $res = $this->getDb()->query(
            "UPDATE {$this->table} SET reserved_at = NULL, available_at = ? WHERE id = ?",
            [$availableAt, $id]
        );
        return $res !== false;
    }

    public function size(string $queue = 'default'): int
    {
        $res = $this->getDb()->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE queue = ?",
            [$queue]
        );
        return (int) ($res['count'] ?? 0);
    }

    public function clear(string $queue = 'default'): bool
    {
        $res = $this->getDb()->query("DELETE FROM {$this->table} WHERE queue = ?", [$queue]);
        return $res !== false;
    }
}
