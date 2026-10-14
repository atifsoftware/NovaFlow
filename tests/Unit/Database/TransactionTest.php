<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\Schema;
use NovaFlow\Core\QueryBuilder\Blueprint;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\Container;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\DB;
use NovaFlow\Core\DatabaseException;
use Exception;

class TransactionTest extends TestCase
{
    private SqliteConnection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new SqliteConnection(['database' => ':memory:']);

        Container::getInstance()->singleton(DatabaseInterface::class, function () {
            return $this->db;
        });

        Schema::setDatabase($this->db);
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('balance', 10, 2);
        });

        $this->db->query("INSERT INTO accounts (name, balance) VALUES (?, ?)", ['Alice', 1000]);
        $this->db->query("INSERT INTO accounts (name, balance) VALUES (?, ?)", ['Bob', 500]);
    }

    public function testTransactionCommitsOnSuccess()
    {
        $result = DB::transaction(function () {
            (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->decrement('balance', 200);
            (new QueryBuilder($this->db))->table('accounts')->where('name', 'Bob')->increment('balance', 200);
            return 'transferred';
        });

        $this->assertEquals('transferred', $result);
        $this->assertEquals(0, DB::transactionLevel());
        $this->assertFalse(DB::inTransaction());

        $alice = (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->first();
        $bob = (new QueryBuilder($this->db))->table('accounts')->where('name', 'Bob')->first();

        $this->assertEquals(800, $alice->balance);
        $this->assertEquals(700, $bob->balance);
    }

    public function testTransactionRollsBackOnException()
    {
        try {
            DB::transaction(function () {
                (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->decrement('balance', 300);

                // Simulate error before bob receives money
                throw new Exception("Network failure during transfer");
            });
        } catch (DatabaseException $e) {
            $this->assertStringContainsString("Network failure during transfer", $e->getMessage());
        }

        $this->assertEquals(0, DB::transactionLevel());
        $this->assertFalse(DB::inTransaction());

        // Balance should remain untouched
        $alice = (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->first();
        $this->assertEquals(1000, $alice->balance);
    }

    public function testManualTransactionControl()
    {
        DB::beginTransaction();
        $this->assertTrue(DB::inTransaction());
        $this->assertEquals(1, DB::transactionLevel());

        (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->update(['balance' => 999]);
        DB::rollback();

        $this->assertFalse(DB::inTransaction());
        $this->assertEquals(0, DB::transactionLevel());

        $alice = (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->first();
        $this->assertEquals(1000, $alice->balance);
    }

    public function testNestedTransactionsWithSavepoints()
    {
        DB::transaction(function () {
            // Level 1: Deduct 100 from Alice
            (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->decrement('balance', 100);
            $this->assertEquals(1, DB::transactionLevel());

            // Level 2 (Nested): Attempt to add 100 to Charlie (fails)
            try {
                DB::transaction(function () {
                    $this->assertEquals(2, DB::transactionLevel());
                    (new QueryBuilder($this->db))->table('accounts')->where('name', 'Bob')->increment('balance', 100);
                    throw new Exception("Sub-transaction failed");
                });
            } catch (DatabaseException $e) {
                // Caught nested error
            }

            // We are back at level 1
            $this->assertEquals(1, DB::transactionLevel());
        });

        $this->assertEquals(0, DB::transactionLevel());

        $alice = (new QueryBuilder($this->db))->table('accounts')->where('name', 'Alice')->first();
        $bob = (new QueryBuilder($this->db))->table('accounts')->where('name', 'Bob')->first();

        // Alice deduction committed, Bob increment was rolled back via savepoint!
        $this->assertEquals(900, $alice->balance);
        $this->assertEquals(500, $bob->balance);
    }

    public function testTransactionRetryAttempts()
    {
        $attemptsRun = 0;

        try {
            DB::transaction(function () use (&$attemptsRun) {
                $attemptsRun++;
                throw new Exception("Temporary lock");
            }, 3);
        } catch (DatabaseException $e) {
            $this->assertStringContainsString("attempt 3", $e->getMessage());
        }

        $this->assertEquals(3, $attemptsRun);
        $this->assertEquals(0, DB::transactionLevel());
    }
}
