<?php

namespace NovaFlow\Core;

/**
 * Base Job Class
 * All background jobs should extend this class
 */
abstract class Job
{
    /**
     * Max attempts before giving up
     */
    public int $tries = 3;

    /**
     * The number of seconds to delay the job
     */
    public int $delay = 0;

    /**
     * Execute the job logic
     */
    abstract public function handle(): void;

    /**
     * Handle a job failure
     */
    public function failed(\Exception $e): void
    {
        Logger::error("Job " . get_class($this) . " failed.", [
            'error' => $e->getMessage()
        ]);
    }
}
