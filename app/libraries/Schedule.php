<?php

namespace NovaFlow\Core;

/**
 * Task Scheduler for NovaFlow
 * Allows defining recurring background jobs with fluent intervals
 */
class Schedule
{
    /** @var ScheduledTask[] */
    private static array $tasks = [];

    /**
     * Schedule a PHP callable/closure
     */
    public static function call(callable $callback, string $name = ''): ScheduledTask
    {
        $task = new ScheduledTask($callback, $name);
        self::$tasks[] = $task;
        return $task;
    }

    /**
     * Schedule a CLI or shell command
     */
    public static function command(string $command, string $name = ''): ScheduledTask
    {
        $task = new ScheduledTask(function() use ($command) {
            return shell_exec($command);
        }, $name ?: $command);

        self::$tasks[] = $task;
        return $task;
    }

    /**
     * Run all tasks that are due right now
     */
    public static function run(?\DateTimeInterface $date = null): array
    {
        $results = [];
        $now = $date ?: new \DateTime();

        foreach (self::$tasks as $task) {
            if ($task->isDue($now)) {
                $start = microtime(true);
                try {
                    $output = $task->execute();
                    $duration = round((microtime(true) - $start) * 1000, 2);
                    $results[] = [
                        'name' => $task->getName(),
                        'expression' => $task->getExpression(),
                        'status' => 'success',
                        'duration_ms' => $duration,
                        'output' => $output
                    ];
                } catch (\Throwable $e) {
                    $duration = round((microtime(true) - $start) * 1000, 2);
                    $results[] = [
                        'name' => $task->getName(),
                        'expression' => $task->getExpression(),
                        'status' => 'failed',
                        'duration_ms' => $duration,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Get all registered tasks
     */
    public static function getTasks(): array
    {
        return self::$tasks;
    }

    /**
     * Clear all scheduled tasks (useful in tests)
     */
    public static function clear(): void
    {
        self::$tasks = [];
    }
}
