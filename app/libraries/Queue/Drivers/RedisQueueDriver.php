<?php

namespace NovaFlow\Core\Queue\Drivers;

use NovaFlow\Core\Queue\Contracts\QueueDriverInterface;
use NovaFlow\Core\Job;
use Exception;

/**
 * Redis Queue Driver
 * High-throughput, in-memory queue using Redis Lists and Sorted Sets.
 */
class RedisQueueDriver implements QueueDriverInterface
{
    protected mixed $redis = null;
    protected array $config = [];
    protected string $prefix = 'novaflow_queue:';

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->prefix = $config['prefix'] ?? 'novaflow_queue:';
    }

    /**
     * Get or connect to Redis client
     */
    protected function getRedis(): mixed
    {
        if ($this->redis === null) {
            $host = $this->config['host'] ?? (getenv('REDIS_HOST') ?: '127.0.0.1');
            $port = (int) ($this->config['port'] ?? (getenv('REDIS_PORT') ?: 6379));
            $password = $this->config['password'] ?? (getenv('REDIS_PASSWORD') ?: null);
            $database = (int) ($this->config['database'] ?? 0);

            if (class_exists('Redis')) {
                $client = new \Redis();
                $connected = @$client->connect($host, $port, 2.5);
                if (!$connected) {
                    throw new Exception("Unable to connect to Redis server at {$host}:{$port}");
                }
                if ($password) {
                    $client->auth($password);
                }
                if ($database) {
                    $client->select($database);
                }
                $this->redis = $client;
            } else {
                throw new Exception("The PHP Redis extension is required to use the RedisQueueDriver.");
            }
        }

        return $this->redis;
    }

    public function setClient(mixed $client): self
    {
        $this->redis = $client;
        return $this;
    }

    protected function queueKey(string $queue): string
    {
        return $this->prefix . $queue;
    }

    protected function delayedKey(string $queue): string
    {
        return $this->prefix . $queue . ':delayed';
    }

    public function push(Job|string $job, mixed $data = '', string $queue = 'default', int $delay = 0): mixed
    {
        $id = uniqid('redis_job_', true);
        $payload = [
            'id'           => $id,
            'queue'        => $queue,
            'display_name' => is_string($job) ? $job : get_class($job),
            'instance'     => is_string($job) ? null : serialize($job),
            'data'         => $data,
            'attempts'     => 0,
            'created_at'   => time()
        ];

        $encoded = json_encode($payload);
        $redis = $this->getRedis();

        $delaySeconds = $delay > 0 ? $delay : ($job instanceof Job ? ($job->delay ?? 0) : 0);

        if ($delaySeconds > 0) {
            $availableAt = time() + $delaySeconds;
            $redis->zAdd($this->delayedKey($queue), $availableAt, $encoded);
        } else {
            $redis->rPush($this->queueKey($queue), $encoded);
        }

        return $id;
    }

    public function pop(string $queue = 'default'): ?object
    {
        $redis = $this->getRedis();
        $now = time();

        // Migrate ready delayed jobs to active queue
        $delayedJobs = $redis->zRangeByScore($this->delayedKey($queue), '-inf', (string) $now);
        if (!empty($delayedJobs)) {
            foreach ($delayedJobs as $delayed) {
                if ($redis->zRem($this->delayedKey($queue), $delayed) > 0) {
                    $redis->rPush($this->queueKey($queue), $delayed);
                }
            }
        }

        $raw = $redis->lPop($this->queueKey($queue));
        if (!$raw) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!$decoded) {
            return null;
        }

        $decoded['attempts'] = (int) ($decoded['attempts'] ?? 0) + 1;
        return (object) [
            'id'       => $decoded['id'],
            'queue'    => $queue,
            'payload'  => json_encode($decoded),
            'attempts' => $decoded['attempts'],
            'raw'      => $decoded
        ];
    }

    public function delete(mixed $id): bool
    {
        return true;
    }

    public function release(mixed $id, int $delay = 60): bool
    {
        // Re-add to delayed sorted set
        $raw = is_array($id) || is_object($id) ? (array) $id : ['id' => $id];
        $queue = $raw['queue'] ?? 'default';
        $encoded = json_encode($raw);

        return $this->getRedis()->zAdd($this->delayedKey($queue), time() + $delay, $encoded) > 0;
    }

    public function size(string $queue = 'default'): int
    {
        $redis = $this->getRedis();
        $active = (int) $redis->lLen($this->queueKey($queue));
        $delayed = (int) $redis->zCard($this->delayedKey($queue));
        return $active + $delayed;
    }

    public function clear(string $queue = 'default'): bool
    {
        $redis = $this->getRedis();
        $redis->del($this->queueKey($queue));
        $redis->del($this->delayedKey($queue));
        return true;
    }
}
