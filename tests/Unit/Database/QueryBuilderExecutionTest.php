<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\Database\DatabaseManager;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\PDODriver;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\Database\Contracts\ConnectionInterface;

class QueryBuilderExecutionTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an in-memory SQLite connection for testing execution
        $this->connection = new SqliteConnection(['database' => ':memory:']);

        // Create a test table
        $this->connection->query("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            score INTEGER DEFAULT 0
        )");
    }

    public function testDatabaseManagerResolution()
    {
        $manager = new DatabaseManager([
            'sqlite' => ['database' => ':memory:'],
        ]);

        $sqliteConn = $manager->connection('sqlite');
        $this->assertInstanceOf(ConnectionInterface::class, $sqliteConn);
        $this->assertEquals('sqlite', $sqliteConn->getDriverName());

        $mysqlConn = $manager->connection('mysql');
        $this->assertEquals('mysql', $mysqlConn->getDriverName());

        $pgConn = $manager->connection('pgsql');
        $this->assertEquals('pgsql', $pgConn->getDriverName());
    }

    public function testPdoDriverBackwardCompatibility()
    {
        $driver = new PDODriver();
        $this->assertInstanceOf(DatabaseInterface::class, $driver);
        $this->assertInstanceOf(ConnectionInterface::class, $driver);
        $this->assertEquals('mysql', $driver->getDriverName());
    }

    public function testQueryBuilderInsertAndSelectExecution()
    {
        $builder = (new QueryBuilder($this->connection))->table('users');

        // Test Insert
        $builder->insert(['name' => 'Tanvir', 'email' => 'tanvir@example.com', 'role' => 'admin', 'score' => 95]);
        $builder->insert(['name' => 'Rahim', 'email' => 'rahim@example.com', 'role' => 'user', 'score' => 80]);
        $builder->insert(['name' => 'Karim', 'email' => 'karim@example.com', 'role' => 'user', 'score' => 60]);

        // Test Count
        $count = (new QueryBuilder($this->connection))->table('users')->count();
        $this->assertEquals(3, $count);

        // Test Find
        $user = (new QueryBuilder($this->connection))->table('users')->find(1);
        $this->assertNotNull($user);
        $this->assertEquals('Tanvir', $user->name);
        $this->assertEquals('admin', $user->role);

        // Test Where and Get
        $admins = (new QueryBuilder($this->connection))
            ->table('users')
            ->where('role', 'admin')
            ->get();

        $this->assertCount(1, $admins);
        $this->assertEquals('Tanvir', $admins[0]->name);

        // Test WhereIn
        $users = (new QueryBuilder($this->connection))
            ->table('users')
            ->whereIn('name', ['Rahim', 'Karim'])
            ->orderBy('id', 'ASC')
            ->get();

        $this->assertCount(2, $users);
        $this->assertEquals('Rahim', $users[0]->name);
        $this->assertEquals('Karim', $users[1]->name);
    }

    public function testQueryBuilderUpdateAndIncrementExecution()
    {
        $builder = (new QueryBuilder($this->connection))->table('users');
        $builder->insert(['name' => 'Alice', 'email' => 'alice@example.com', 'score' => 50]);

        // Update
        (new QueryBuilder($this->connection))
            ->table('users')
            ->where('email', 'alice@example.com')
            ->update(['score' => 75]);

        $alice = (new QueryBuilder($this->connection))->table('users')->where('email', 'alice@example.com')->first();
        $this->assertEquals(75, $alice->score);

        // Increment
        (new QueryBuilder($this->connection))
            ->table('users')
            ->where('email', 'alice@example.com')
            ->increment('score', 10);

        $alice = (new QueryBuilder($this->connection))->table('users')->where('email', 'alice@example.com')->first();
        $this->assertEquals(85, $alice->score);
    }

    public function testQueryBuilderDeleteExecution()
    {
        $builder = (new QueryBuilder($this->connection))->table('users');
        $builder->insert(['name' => 'Bob', 'email' => 'bob@example.com']);
        $builder->insert(['name' => 'Charlie', 'email' => 'charlie@example.com']);

        // Delete Bob
        (new QueryBuilder($this->connection))
            ->table('users')
            ->where('name', 'Bob')
            ->delete();

        $count = (new QueryBuilder($this->connection))->table('users')->count();
        $this->assertEquals(1, $count);

        $bob = (new QueryBuilder($this->connection))->table('users')->where('name', 'Bob')->first();
        $this->assertNull($bob);
    }

    public function testInsertGetIdExecution()
    {
        $id = (new QueryBuilder($this->connection))
            ->table('users')
            ->insertGetId(['name' => 'David', 'email' => 'david@example.com']);

        $this->assertIsNumeric($id);
        $this->assertGreaterThan(0, $id);

        $david = (new QueryBuilder($this->connection))->table('users')->find($id);
        $this->assertEquals('David', $david->name);
    }

    public function testExistsAndDoesntExistExecution()
    {
        $builder = (new QueryBuilder($this->connection))->table('users');
        $builder->insert(['name' => 'Eve', 'email' => 'eve@example.com']);

        $this->assertTrue((new QueryBuilder($this->connection))->table('users')->where('name', 'Eve')->exists());
        $this->assertFalse((new QueryBuilder($this->connection))->table('users')->where('name', 'Eve')->doesntExist());

        $this->assertFalse((new QueryBuilder($this->connection))->table('users')->where('name', 'NonExistent')->exists());
        $this->assertTrue((new QueryBuilder($this->connection))->table('users')->where('name', 'NonExistent')->doesntExist());
    }

    public function testWhereColumnAndEmptyWhereInExecution()
    {
        $builder = (new QueryBuilder($this->connection))->table('users');
        $builder->insert(['name' => 'Frank', 'email' => 'frank@example.com', 'role' => 'user', 'score' => 100]);

        // Empty whereIn should return 0 results safely without crashing
        $emptyResults = (new QueryBuilder($this->connection))
            ->table('users')
            ->whereIn('id', [])
            ->get();
        $this->assertEmpty($emptyResults);

        // WhereColumn comparison (name = email will be empty, name = role will be empty, name = name will match)
        $columnMatch = (new QueryBuilder($this->connection))
            ->table('users')
            ->whereColumn('name', '=', 'name')
            ->get();
        $this->assertNotEmpty($columnMatch);
    }
}
