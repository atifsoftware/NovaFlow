<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Drivers\MySqlConnection;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\RawExpression;
use Exception;

/**
 * Live MySQL Integration & Execution Tests
 * Tests QueryBuilder, CRUD, Transactions, and MySQL-specific Grammars on real MySQL server (XAMPP/MySQL)
 */
class MySqlLiveExecutionTest extends TestCase
{
    private ?MySqlConnection $connection = null;
    private const TEST_TABLE = '_test_mysql_live';

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'host' => getenv('DB_HOST') ?: '127.0.0.1',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'database' => getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: 'novaflow_db'),
            'username' => getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'root'),
            'password' => getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: ''),
            'charset' => 'utf8mb4',
        ];

        try {
            $this->connection = new MySqlConnection($config);
            // Test ping
            $this->connection->getPdo();
        } catch (Exception $e) {
            $this->markTestSkipped('MySQL server is not accessible: ' . $e->getMessage());
        }

        // Clean & recreate test table
        $this->dropTestTable();
        $this->createTestTable();
    }

    protected function tearDown(): void
    {
        if ($this->connection) {
            $this->dropTestTable();
        }
        parent::tearDown();
    }

    private function dropTestTable(): void
    {
        try {
            $this->connection->getPdo()->exec("DROP TABLE IF EXISTS " . self::TEST_TABLE);
        } catch (Exception $e) {
            // Ignore during cleanup
        }
    }

    private function createTestTable(): void
    {
        $sql = "CREATE TABLE " . self::TEST_TABLE . " (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(150) NOT NULL UNIQUE,
            `role` VARCHAR(50) DEFAULT 'user',
            `balance` DECIMAL(10, 2) DEFAULT 0.00,
            `is_active` TINYINT(1) DEFAULT 1,
            `metadata` JSON NULL,
            `created_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->connection->getPdo()->exec($sql);
    }

    public function testConnectionAndDriverIdentity()
    {
        $this->assertEquals('mysql', $this->connection->getDriverName());
        $this->assertInstanceOf(\PDO::class, $this->connection->getPdo());
    }

    public function testInsertAndInsertGetId()
    {
        $qb = new QueryBuilder($this->connection);

        $id1 = (clone $qb)->table(self::TEST_TABLE)->insertGetId([
            'name' => 'Alice Rahman',
            'email' => 'alice@example.com',
            'role' => 'admin',
            'balance' => 150.75,
            'is_active' => 1,
            'metadata' => json_encode(['level' => 'senior']),
            'created_at' => '2026-03-01 12:00:00'
        ]);

        $this->assertGreaterThan(0, $id1);

        $id2 = (clone $qb)->table(self::TEST_TABLE)->insertGetId([
            'name' => 'Bob Hasan',
            'email' => 'bob@example.com',
            'role' => 'user',
            'balance' => 45.00,
            'is_active' => 1,
            'metadata' => json_encode(['level' => 'junior']),
            'created_at' => '2026-03-02 15:30:00'
        ]);

        $this->assertGreaterThan($id1, $id2);

        // Verify count
        $count = (clone $qb)->table(self::TEST_TABLE)->count();
        $this->assertEquals(2, $count);
    }

    public function testWhereClausesAndFiltering()
    {
        $qb = new QueryBuilder($this->connection);

        // Seed 3 records
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'User A', 'email' => 'a@test.com', 'role' => 'admin', 'balance' => 200, 'is_active' => 1
        ]);
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'User B', 'email' => 'b@test.com', 'role' => 'editor', 'balance' => 100, 'is_active' => 1
        ]);
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'User C', 'email' => 'c@test.com', 'role' => 'guest', 'balance' => 0, 'is_active' => 0
        ]);

        // whereIn
        $roles = (clone $qb)->table(self::TEST_TABLE)->whereIn('role', ['admin', 'editor'])->get();
        $this->assertCount(2, $roles);

        // whereBetween
        $balances = (clone $qb)->table(self::TEST_TABLE)->whereBetween('balance', [50, 250])->get();
        $this->assertCount(2, $balances);

        // first()
        $admin = (clone $qb)->table(self::TEST_TABLE)->where('role', 'admin')->first();
        $this->assertNotNull($admin);
        $this->assertEquals('User A', $admin->name);
    }

    public function testUpdateAndIncrementDecrement()
    {
        $qb = new QueryBuilder($this->connection);

        $id = (clone $qb)->table(self::TEST_TABLE)->insertGetId([
            'name' => 'Kamal Hossain',
            'email' => 'kamal@example.com',
            'role' => 'customer',
            'balance' => 500.00,
            'is_active' => 1
        ]);

        // Update
        $affected = (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->update(['name' => 'Kamal Ahmed']);
        $this->assertEquals(1, $affected);

        $updated = (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->first();
        $this->assertEquals('Kamal Ahmed', $updated->name);

        // Increment balance by 50
        (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->increment('balance', 50);
        $afterInc = (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->first();
        $this->assertEquals(550.00, (float) $afterInc->balance);

        // Decrement balance by 100
        (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->decrement('balance', 100);
        $afterDec = (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->first();
        $this->assertEquals(450.00, (float) $afterDec->balance);
    }

    public function testAggregates()
    {
        $qb = new QueryBuilder($this->connection);

        (clone $qb)->table(self::TEST_TABLE)->insert(['name' => 'P1', 'email' => 'p1@x.com', 'balance' => 100]);
        (clone $qb)->table(self::TEST_TABLE)->insert(['name' => 'P2', 'email' => 'p2@x.com', 'balance' => 200]);
        (clone $qb)->table(self::TEST_TABLE)->insert(['name' => 'P3', 'email' => 'p3@x.com', 'balance' => 300]);

        $this->assertEquals(3, (clone $qb)->table(self::TEST_TABLE)->count());
        $this->assertEquals(600, (float) (clone $qb)->table(self::TEST_TABLE)->sum('balance'));
        $this->assertEquals(200, (float) (clone $qb)->table(self::TEST_TABLE)->avg('balance'));
        $this->assertEquals(300, (float) (clone $qb)->table(self::TEST_TABLE)->max('balance'));
        $this->assertEquals(100, (float) (clone $qb)->table(self::TEST_TABLE)->min('balance'));
    }

    public function testTransactionsCommitAndRollback()
    {
        $qb = new QueryBuilder($this->connection);

        // Test Commit
        $this->connection->beginTransaction();
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Tx User 1',
            'email' => 'tx1@x.com',
            'balance' => 10
        ]);
        $this->connection->commit();

        $count1 = (clone $qb)->table(self::TEST_TABLE)->where('email', 'tx1@x.com')->count();
        $this->assertEquals(1, $count1);

        // Test Rollback
        $this->connection->beginTransaction();
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Tx User 2',
            'email' => 'tx2@x.com',
            'balance' => 20
        ]);
        $this->connection->rollback();

        $count2 = (clone $qb)->table(self::TEST_TABLE)->where('email', 'tx2@x.com')->count();
        $this->assertEquals(0, $count2);
    }

    public function testDeleteExecution()
    {
        $qb = new QueryBuilder($this->connection);

        $id = (clone $qb)->table(self::TEST_TABLE)->insertGetId([
            'name' => 'To Delete',
            'email' => 'delete_me@x.com',
            'balance' => 5
        ]);

        $this->assertNotNull((clone $qb)->table(self::TEST_TABLE)->where('id', $id)->first());

        $affected = (clone $qb)->table(self::TEST_TABLE)->where('id', $id)->delete();
        $this->assertEquals(1, $affected);

        $this->assertNull((clone $qb)->table(self::TEST_TABLE)->where('id', $id)->first());
    }

    public function testMySqlJsonContains()
    {
        $qb = new QueryBuilder($this->connection);

        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Developer A',
            'email' => 'dev_a@test.com',
            'metadata' => json_encode(['role' => 'admin', 'skills' => ['php', 'mysql']])
        ]);
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Developer B',
            'email' => 'dev_b@test.com',
            'metadata' => json_encode(['role' => 'tester', 'skills' => ['python']])
        ]);

        $admins = (clone $qb)->table(self::TEST_TABLE)->whereJsonContains('metadata->role', 'admin')->get();
        $this->assertCount(1, $admins);
        $this->assertEquals('Developer A', $admins[0]->name);
    }

    public function testMySqlPagination()
    {
        $qb = new QueryBuilder($this->connection);

        for ($i = 1; $i <= 25; $i++) {
            (clone $qb)->table(self::TEST_TABLE)->insert([
                'name' => "User {$i}",
                'email' => "user{$i}@paginate.test",
                'balance' => $i * 10
            ]);
        }

        $paginator = (clone $qb)->table(self::TEST_TABLE)->orderBy('id', 'ASC')->paginate(10, 2);

        $this->assertEquals(25, $paginator->total());
        $this->assertEquals(10, $paginator->perPage());
        $this->assertEquals(2, $paginator->currentPage());
        $this->assertEquals(3, $paginator->lastPage());
        $this->assertCount(10, $paginator->items());
        $this->assertEquals('User 11', $paginator->items()[0]->name);
    }

    public function testMySqlSavepointsAndNestedTransactions()
    {
        $qb = new QueryBuilder($this->connection);

        // Level 1 transaction
        $this->connection->beginTransaction();
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Outer User',
            'email' => 'outer@test.com',
            'balance' => 100
        ]);

        // Level 2 (Savepoint)
        $this->connection->beginTransaction();
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Inner User 1',
            'email' => 'inner1@test.com',
            'balance' => 200
        ]);
        // Rollback Level 2
        $this->connection->rollback();

        // Level 2 again (Savepoint commit)
        $this->connection->beginTransaction();
        (clone $qb)->table(self::TEST_TABLE)->insert([
            'name' => 'Inner User 2',
            'email' => 'inner2@test.com',
            'balance' => 300
        ]);
        $this->connection->commit();

        // Commit Level 1
        $this->connection->commit();

        // Outer and Inner 2 should exist, Inner 1 should be rolled back
        $outer = (clone $qb)->table(self::TEST_TABLE)->where('email', 'outer@test.com')->first();
        $inner1 = (clone $qb)->table(self::TEST_TABLE)->where('email', 'inner1@test.com')->first();
        $inner2 = (clone $qb)->table(self::TEST_TABLE)->where('email', 'inner2@test.com')->first();

        $this->assertNotNull($outer);
        $this->assertNull($inner1);
        $this->assertNotNull($inner2);
    }
}
