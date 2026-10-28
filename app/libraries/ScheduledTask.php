<?php

namespace NovaFlow\Core;

/**
 * Scheduled Task representation with fluent cadence rules
 */
class ScheduledTask
{
    /** @var callable */
    protected $callback;
    protected string $name;
    protected string $expression = '* * * * *';
    protected ?string $timezone = null;

    public function __construct(callable $callback, string $name = '')
    {
        $this->callback = $callback;
        $this->name = $name ?: 'Anonymous Task #' . uniqid();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * Set the timezone for the task
     */
    public function timezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * Set a raw cron expression
     */
    public function cron(string $expression): self
    {
        $this->expression = $expression;
        return $this;
    }

    /**
     * Run every minute
     */
    public function everyMinute(): self
    {
        return $this->cron('* * * * *');
    }

    /**
     * Run every five minutes
     */
    public function everyFiveMinutes(): self
    {
        return $this->cron('*/5 * * * *');
    }

    /**
     * Run every ten minutes
     */
    public function everyTenMinutes(): self
    {
        return $this->cron('*/10 * * * *');
    }

    /**
     * Run every fifteen minutes
     */
    public function everyFifteenMinutes(): self
    {
        return $this->cron('*/15 * * * *');
    }

    /**
     * Run every thirty minutes
     */
    public function everyThirtyMinutes(): self
    {
        return $this->cron('*/30 * * * *');
    }

    /**
     * Run hourly at minute 0
     */
    public function hourly(): self
    {
        return $this->cron('0 * * * *');
    }

    /**
     * Run hourly at specific minute
     */
    public function hourlyAt(int $minute): self
    {
        return $this->cron("{$minute} * * * *");
    }

    /**
     * Run daily at midnight (00:00)
     */
    public function daily(): self
    {
        return $this->cron('0 0 * * *');
    }

    /**
     * Run daily at specific time (e.g. '14:30')
     */
    public function dailyAt(string $time): self
    {
        $segments = explode(':', $time);
        $hour = (int)($segments[0] ?? 0);
        $minute = (int)($segments[1] ?? 0);
        return $this->cron("{$minute} {$hour} * * *");
    }

    /**
     * Run weekly on Sunday at midnight
     */
    public function weekly(): self
    {
        return $this->cron('0 0 * * 0');
    }

    /**
     * Run monthly on the 1st at midnight
     */
    public function monthly(): self
    {
        return $this->cron('0 0 1 * *');
    }

    /**
     * Execute the task callback
     */
    public function execute(): mixed
    {
        return call_user_func($this->callback);
    }

    /**
     * Check if task is due at given DateTime
     */
    public function isDue(?\DateTimeInterface $date = null): bool
    {
        if ($date === null) {
            $tz = $this->timezone ? new \DateTimeZone($this->timezone) : null;
            $date = new \DateTime('now', $tz);
        } elseif ($this->timezone !== null) {
            $date = \DateTime::createFromInterface($date)->setTimezone(new \DateTimeZone($this->timezone));
        }

        $parts = preg_split('/\s+/', trim($this->expression));
        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;

        $m = (int)$date->format('i');
        $h = (int)$date->format('G');
        $dom = (int)$date->format('j');
        $mon = (int)$date->format('n');
        $dow = (int)$date->format('w'); // 0 (Sun) to 6 (Sat)

        return $this->matchCronPart($minute, $m)
            && $this->matchCronPart($hour, $h)
            && $this->matchCronPart($dayOfMonth, $dom)
            && $this->matchCronPart($month, $mon)
            && $this->matchCronPart($dayOfWeek, $dow);
    }

    /**
     * Match a single cron expression field
     */
    protected function matchCronPart(string $pattern, int $value): bool
    {
        if ($pattern === '*') {
            return true;
        }

        // Handle step values: e.g. "*/5"
        if (str_starts_with($pattern, '*/')) {
            $step = (int)substr($pattern, 2);
            return $step > 0 && ($value % $step === 0);
        }

        // Handle lists: e.g. "1,2,5"
        if (str_contains($pattern, ',')) {
            $list = array_map('intval', explode(',', $pattern));
            return in_array($value, $list, true);
        }

        // Handle ranges: e.g. "1-5"
        if (str_contains($pattern, '-')) {
            [$min, $max] = array_map('intval', explode('-', $pattern, 2));
            return $value >= $min && $value <= $max;
        }

        return (int)$pattern === $value;
    }
}
