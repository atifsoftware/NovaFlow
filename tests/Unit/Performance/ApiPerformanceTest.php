<?php

namespace Tests\Unit\Performance;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Grammar\MySqlGrammar;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\DatabaseInterface;

class ApiPerformanceTest extends TestCase
{
    public function testQueryBuilderCompilationSpeed()
    {
        $mockDb = $this->createMock(DatabaseInterface::class);
        $grammar = new MySqlGrammar();

        $start = microtime(true);
        $iterations = 1000;

        for ($i = 0; $i < $iterations; $i++) {
            $qb = (new QueryBuilder($mockDb, $grammar))
                ->table('orders')
                ->where('status', 'completed')
                ->where('total', '>', 50)
                ->limit(10);
            $sql = $qb->toSql();
        }

        $duration = microtime(true) - $start;

        // 1000 queries should compile in less than 200 milliseconds (0.2s)
        $this->assertLessThan(0.2, $duration, "QueryBuilder compilation is too slow: {$duration}s");
    }

    public function testJsonSerializationThroughput()
    {
        $data = [
            'status' => 'success',
            'data' => array_fill(0, 50, ['id' => 1, 'name' => 'Pen', 'price' => 10])
        ];

        $start = microtime(true);
        for ($i = 0; $i < 2000; $i++) {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $duration = microtime(true) - $start;

        // 2000 JSON payloads should serialize in less than 150 milliseconds (0.15s)
        $this->assertLessThan(0.15, $duration, "JSON serialization is too slow: {$duration}s");
    }

    public function testSqliteBatchExecutionSpeed()
    {
        $sqlite = new SqliteConnection(['database' => ':memory:']);
        $sqlite->query("CREATE TABLE perf_test (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT)");

        $start = microtime(true);
        for ($i = 0; $i < 500; $i++) {
            (new QueryBuilder($sqlite))->table('perf_test')->insert(['title' => "Title $i"]);
        }
        $insertDuration = microtime(true) - $start;

        $this->assertLessThan(0.1, $insertDuration, "Batch SQLite inserts took too long: {$insertDuration}s");

        $count = (new QueryBuilder($sqlite))->table('perf_test')->count();
        $this->assertEquals(500, $count);
    }
}
