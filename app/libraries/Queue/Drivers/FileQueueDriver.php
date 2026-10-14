<?php

namespace NovaFlow\Core\Queue\Drivers;

use NovaFlow\Core\Queue\Contracts\QueueDriverInterface;
use NovaFlow\Core\Job;

/**
 * File-based Queue Driver
 * Zero-configuration persistent queue storing jobs as local JSON files.
 */
class FileQueueDriver implements QueueDriverInterface
{
    protected string $path;

    public function __construct(array $config = [])
    {
        $base = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__)));
        $this->path = rtrim($config['path'] ?? ($base . '/storage/queue'), '/\\');

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    protected function getQueueDir(string $queue): string
    {
        $dir = $this->path . DIRECTORY_SEPARATOR . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $queue);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function push(Job|string $job, mixed $data = '', string $queue = 'default', int $delay = 0): mixed
    {
        $id = uniqid('job_', true);
        $queueDir = $this->getQueueDir($queue);

        $payload = [
            'display_name' => is_string($job) ? $job : get_class($job),
            'instance'     => is_string($job) ? null : serialize($job),
            'data'         => $data,
            'created_at'   => time()
        ];

        $delaySeconds = $delay > 0 ? $delay : ($job instanceof Job ? ($job->delay ?? 0) : 0);
        $availableAt = time() + $delaySeconds;

        $record = [
            'id'           => $id,
            'queue'        => $queue,
            'payload'      => json_encode($payload),
            'attempts'     => 0,
            'available_at' => $availableAt,
            'reserved_at'  => null,
            'created_at'   => time()
        ];

        $filePath = $queueDir . DIRECTORY_SEPARATOR . "{$id}.json";
        file_put_contents($filePath, json_encode($record, JSON_PRETTY_PRINT), LOCK_EX);

        return $id;
    }

    public function pop(string $queue = 'default'): ?object
    {
        $queueDir = $this->getQueueDir($queue);
        $files = glob($queueDir . DIRECTORY_SEPARATOR . '*.json');
        if (empty($files)) {
            return null;
        }

        // Sort files by creation time
        usort($files, fn($a, $b) => filemtime($a) - filemtime($b));

        $now = time();

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if (!$content) continue;

            $record = json_decode($content, true);
            if (!$record) continue;

            // Check if available and not reserved
            if ($record['reserved_at'] === null && $record['available_at'] <= $now) {
                // Reserve job
                $record['reserved_at'] = $now;
                $record['attempts']++;
                file_put_contents($file, json_encode($record, JSON_PRETTY_PRINT), LOCK_EX);

                return (object) $record;
            }
        }

        return null;
    }

    public function delete(mixed $id): bool
    {
        // Search across queue directories
        $dirs = glob($this->path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . "{$id}.json";
            if (file_exists($file)) {
                return unlink($file);
            }
        }
        return false;
    }

    public function release(mixed $id, int $delay = 60): bool
    {
        $dirs = glob($this->path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $file = $dir . DIRECTORY_SEPARATOR . "{$id}.json";
            if (file_exists($file)) {
                $record = json_decode(file_get_contents($file), true);
                if ($record) {
                    $record['reserved_at'] = null;
                    $record['available_at'] = time() + $delay;
                    return file_put_contents($file, json_encode($record, JSON_PRETTY_PRINT), LOCK_EX) !== false;
                }
            }
        }
        return false;
    }

    public function size(string $queue = 'default'): int
    {
        $queueDir = $this->getQueueDir($queue);
        $files = glob($queueDir . DIRECTORY_SEPARATOR . '*.json');
        return is_array($files) ? count($files) : 0;
    }

    public function clear(string $queue = 'default'): bool
    {
        $queueDir = $this->getQueueDir($queue);
        $files = glob($queueDir . DIRECTORY_SEPARATOR . '*.json');
        foreach ($files as $file) {
            @unlink($file);
        }
        return true;
    }
}
