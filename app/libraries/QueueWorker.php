<?php

namespace NovaFlow\Core;

/**
 * QueueWorker
 * Processes jobs from the database queue
 */
class QueueWorker
{
    private bool $shouldKeepWorking = true;
    private int $sleep = 3;

    public function work(string $queue = 'default')
    {
        echo $this->color("NovaFlow Queue Worker started. Watching queue: [$queue]\n", 'cyan');
        echo $this->color("Press Ctrl+C to stop.\n\n", 'yellow');

        while ($this->shouldKeepWorking) {
            $job = Queue::pop($queue);

            if ($job) {
                $this->process($job);
            } else {
                sleep($this->sleep);
            }
        }
    }

    /**
     * Run the next available job (for CLI single step or testing)
     */
    public function runNextJob(string $queue = 'default'): bool
    {
        $job = Queue::pop($queue);
        if ($job) {
            $this->process($job);
            return true;
        }
        return false;
    }

    protected function process(object $jobRecord)
    {
        $payload = json_decode($jobRecord->payload, true);
        $displayName = $payload['display_name'];
        
        echo $this->color("[ " . date('Y-m-d H:i:s') . " ] ", 'white') . "Processing: " . $this->color($displayName, 'cyan') . "\n";

        $jobInstance = null;
        try {
            $targetClass = $displayName;
            $allowedClasses = [Job::class];
            if ($targetClass && class_exists($targetClass) && is_subclass_of($targetClass, Job::class)) {
                $allowedClasses[] = $targetClass;
            }

            /** @var Job $jobInstance */
            $jobInstance = unserialize($payload['instance'], ['allowed_classes' => $allowedClasses]);
            
            if (!$jobInstance instanceof Job) {
                throw new \Exception("Job class must extend NovaFlow\\Core\\Job");
            }

            $jobInstance->handle();
            Queue::delete($jobRecord->id);
            
            echo $this->color("[ " . date('Y-m-d H:i:s') . " ] ", 'white') . "✓ Success: " . $this->color($displayName, 'green') . "\n";
        } catch (\Throwable $e) {
            echo $this->color("[ " . date('Y-m-d H:i:s') . " ] ", 'white') . "✗ Failed: " . $this->color($displayName, 'red') . "\n";
            echo $this->color("  Error: " . $e->getMessage() . "\n", 'red');

            $maxTries = ($jobInstance instanceof Job) ? $jobInstance->tries : 3;
            if ($jobRecord->attempts < $maxTries) {
                $delay = 60 * ($jobRecord->attempts + 1); // Exponential backoff wait
                Queue::release($jobRecord->id, $delay);
                echo $this->color("  Released back to queue with " . $delay . "s delay.\n", 'yellow');
            } else {
                if ($jobInstance instanceof Job) {
                    $jobInstance->failed(new \Exception($e->getMessage(), (int)$e->getCode(), $e));
                }
                Queue::delete($jobRecord->id);
                echo $this->color("  Max attempts reached. Job deleted.\n", 'red');
            }
        }
    }

    private function color($text, $color)
    {
        $colors = [
            'cyan' => "\033[36m",
            'yellow' => "\033[33m",
            'green' => "\033[32m",
            'red' => "\033[31m",
            'white' => "\033[37m",
            'reset' => "\033[0m"
        ];
        return $colors[$color] . $text . $colors['reset'];
    }
}
