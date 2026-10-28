<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Schedule;

class ScheduleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schedule::clear();
    }

    protected function tearDown(): void
    {
        Schedule::clear();
        parent::tearDown();
    }

    public function testTaskRegistrationAndFluentIntervals(): void
    {
        $task1 = Schedule::call(fn() => 'task1', 'Task One')->everyMinute();
        $this->assertEquals('* * * * *', $task1->getExpression());
        $this->assertEquals('Task One', $task1->getName());

        $task2 = Schedule::call(fn() => 'task2', 'Task Two')->hourly();
        $this->assertEquals('0 * * * *', $task2->getExpression());

        $task3 = Schedule::call(fn() => 'task3', 'Task Three')->dailyAt('14:45');
        $this->assertEquals('45 14 * * *', $task3->getExpression());
    }

    public function testTaskIsDueEvaluation(): void
    {
        $task = Schedule::call(fn() => 'test')->dailyAt('10:30');

        // Due time
        $dueTime = new \DateTime('2026-10-15 10:30:00');
        $this->assertTrue($task->isDue($dueTime));

        // Not due time
        $notDueTime = new \DateTime('2026-10-15 10:31:00');
        $this->assertFalse($task->isDue($notDueTime));
    }

    public function testScheduleRunExecutesDueTasks(): void
    {
        $executed = false;
        Schedule::call(function() use (&$executed) {
            $executed = true;
            return 'done';
        }, 'Test Run Job')->everyMinute();

        $results = Schedule::run(new \DateTime('2026-10-15 12:00:00'));

        $this->assertTrue($executed);
        $this->assertCount(1, $results);
        $this->assertEquals('success', $results[0]['status']);
        $this->assertEquals('Test Run Job', $results[0]['name']);
        $this->assertEquals('done', $results[0]['output']);
    }

    public function testTaskTimezoneEvaluation(): void
    {
        // 14:00 in UTC is 20:00 in Asia/Dhaka (UTC+6)
        $task = Schedule::call(fn() => 'dhaka')->dailyAt('20:00')->timezone('Asia/Dhaka');
        
        $utcTime = new \DateTime('2026-10-15 14:00:00', new \DateTimeZone('UTC'));
        $this->assertTrue($task->isDue($utcTime));

        $wrongUtcTime = new \DateTime('2026-10-15 20:00:00', new \DateTimeZone('UTC'));
        $this->assertFalse($task->isDue($wrongUtcTime));
    }
}
