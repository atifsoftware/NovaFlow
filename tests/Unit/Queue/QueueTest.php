<?php

namespace Tests\Unit\Queue;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Job;
use NovaFlow\Core\Queue;
use NovaFlow\Core\QueueWorker;
use NovaFlow\Core\Queue\QueueManager;
use NovaFlow\Core\Queue\Drivers\SyncQueueDriver;
use NovaFlow\Core\Queue\Drivers\FileQueueDriver;
use NovaFlow\Core\Queue\Drivers\DatabaseQueueDriver;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\Schema;
use NovaFlow\Core\QueryBuilder\Blueprint;

// Test Job class
class SendEmailJob extends Job
{
    public static bool $executed = false;
    public string $email;

    public function __construct(string $email = 'user@example.com')
    {
        $this->email = $email;
    }

    public function handle(): void
    {
        self::$executed = true;
    }
}

class QueueTest extends TestCase
{
    private string $tempQueueDir;

    protected function setUp(): void
    {
        parent::setUp();
        SendEmailJob::$executed = false;
        $this->tempQueueDir = sys_get_temp_dir() . '/novaflow_queue_test_' . uniqid();
        mkdir($this->tempQueueDir, 0755, true);
    }

    protected function tearDown(): void
    {
        Queue::setManager(null);
        $this->removeDirectory($this->tempQueueDir);
        parent::tearDown();
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) return;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    public function testSyncQueueExecutesImmediately()
    {
        $driver = new SyncQueueDriver();
        $this->assertFalse(SendEmailJob::$executed);

        $driver->push(new SendEmailJob('test@sync.com'));
        $this->assertTrue(SendEmailJob::$executed);
    }

    public function testFileQueuePushPopAndDelete()
    {
        $driver = new FileQueueDriver(['path' => $this->tempQueueDir]);

        $this->assertEquals(0, $driver->size());

        $jobId = $driver->push(new SendEmailJob('test@file.com'));
        $this->assertNotEmpty($jobId);
        $this->assertEquals(1, $driver->size());

        $popped = $driver->pop();
        $this->assertNotNull($popped);
        $this->assertEquals($jobId, $popped->id);
        $this->assertEquals(1, $popped->attempts);

        // Delete job
        $this->assertTrue($driver->delete($jobId));
        $this->assertEquals(0, $driver->size());
    }

    public function testDatabaseQueueOperations()
    {
        $sqlite = new SqliteConnection(['database' => ':memory:']);
        Schema::setDatabase($sqlite);

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue');
            $table->longText('payload');
            $table->tinyInt('attempts');
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('available_at');
            $table->dateTime('created_at');
        });

        $driver = new DatabaseQueueDriver(['table' => 'jobs']);
        $driver->setDb($sqlite);

        $this->assertEquals(0, $driver->size());

        $driver->push(new SendEmailJob('db@test.com'), '', 'default');
        $this->assertEquals(1, $driver->size());

        $job = $driver->pop('default');
        $this->assertNotNull($job);
        $this->assertEquals(1, $job->attempts);

        $this->assertTrue($driver->delete($job->id));
        $this->assertEquals(0, $driver->size());
    }

    public function testQueueManagerConnectionSwitching()
    {
        $manager = new QueueManager([
            'default' => 'sync',
            'connections' => [
                'sync' => ['driver' => 'sync'],
                'file' => ['driver' => 'file', 'path' => $this->tempQueueDir],
            ]
        ]);

        Queue::setManager($manager);

        // Test default (sync)
        Queue::push(new SendEmailJob('manager@test.com'));
        $this->assertTrue(SendEmailJob::$executed);

        // Switch to file connection
        SendEmailJob::$executed = false;
        $id = Queue::connection('file')->push(new SendEmailJob('file@manager.com'));
        $this->assertNotEmpty($id);
        $this->assertFalse(SendEmailJob::$executed); // Not executed yet because it is queued in file

        $this->assertEquals(1, Queue::connection('file')->size());
    }

    public function testQueueWorkerProcessesQueuedJob()
    {
        $driver = new FileQueueDriver(['path' => $this->tempQueueDir]);

        $manager = new QueueManager([
            'default' => 'file',
            'connections' => [
                'file' => ['driver' => 'file', 'path' => $this->tempQueueDir],
            ]
        ]);
        Queue::setManager($manager);

        Queue::push(new SendEmailJob('worker@test.com'));
        $this->assertFalse(SendEmailJob::$executed);

        // Worker processes the job
        $worker = new QueueWorker();
        $processed = $worker->runNextJob('default');

        $this->assertTrue($processed);
        $this->assertTrue(SendEmailJob::$executed);
        $this->assertEquals(0, Queue::size());
    }
}
