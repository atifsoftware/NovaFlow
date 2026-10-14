<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Grammar\MySqlGrammar;
use NovaFlow\Core\Database\Grammar\PostgresGrammar;
use NovaFlow\Core\Database\Grammar\SqliteGrammar;
use NovaFlow\Core\DatabaseInterface;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\RawExpression;

class GrammarTest extends TestCase
{
    private function createMockDb(): DatabaseInterface
    {
        return $this->createMock(DatabaseInterface::class);
    }

    public function testMySqlGrammarWrapping()
    {
        $grammar = new MySqlGrammar();

        $this->assertEquals('`users`', $grammar->wrap('users'));
        $this->assertEquals('`users`.`email`', $grammar->wrap('users.email'));
        $this->assertEquals('`email` AS `user_email`', $grammar->wrap('email as user_email'));
        $this->assertEquals('*', $grammar->wrap('*'));
    }

    public function testPostgresGrammarWrapping()
    {
        $grammar = new PostgresGrammar();

        $this->assertEquals('"users"', $grammar->wrap('users'));
        $this->assertEquals('"users"."email"', $grammar->wrap('users.email'));
        $this->assertEquals('"email" AS "user_email"', $grammar->wrap('email as user_email'));
        $this->assertEquals('*', $grammar->wrap('*'));
    }

    public function testSqliteGrammarWrapping()
    {
        $grammar = new SqliteGrammar();

        $this->assertEquals('"users"', $grammar->wrap('users'));
        $this->assertEquals('"users"."email"', $grammar->wrap('users.email'));
        $this->assertEquals('"email" AS "user_email"', $grammar->wrap('email as user_email'));
    }

    public function testMySqlSelectCompilation()
    {
        $db = $this->createMockDb();
        $grammar = new MySqlGrammar();
        $builder = (new QueryBuilder($db, $grammar))
            ->table('users')
            ->select('id', 'name', 'email')
            ->where('status', '=', 'active')
            ->whereIn('role', ['admin', 'manager'])
            ->whereBetween('age', [18, 65])
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->offset(5);

        $expectedSql = "SELECT id, name, email FROM `users` WHERE `status` = ? AND `role` IN (?, ?) AND `age` BETWEEN ? AND ? AND `deleted_at` IS NULL ORDER BY id DESC LIMIT 10 OFFSET 5";
        $this->assertEquals($expectedSql, $builder->toSql());

        $expectedBindings = ['active', 'admin', 'manager', 18, 65];
        $this->assertEquals($expectedBindings, $builder->getBindings());
    }

    public function testPostgresSelectCompilation()
    {
        $db = $this->createMockDb();
        $grammar = new PostgresGrammar();
        $builder = (new QueryBuilder($db, $grammar))
            ->table('users')
            ->where('status', 'active')
            ->whereYear('created_at', '=', 2026);

        $expectedSql = 'SELECT * FROM "users" WHERE "status" = ? AND EXTRACT(YEAR FROM "created_at") = ?';
        $this->assertEquals($expectedSql, $builder->toSql());
        $this->assertEquals(['active', 2026], $builder->getBindings());
    }

    public function testSqliteSelectCompilation()
    {
        $db = $this->createMockDb();
        $grammar = new SqliteGrammar();
        $builder = (new QueryBuilder($db, $grammar))
            ->table('orders')
            ->whereYear('created_at', '=', 2026);

        $expectedSql = "SELECT * FROM \"orders\" WHERE strftime('%Y', \"created_at\") = ?";
        $this->assertEquals($expectedSql, $builder->toSql());
    }

    public function testNestedWhereConditions()
    {
        $db = $this->createMockDb();
        $grammar = new MySqlGrammar();
        $builder = (new QueryBuilder($db, $grammar))
            ->table('users')
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->where('role', 'admin')
                  ->orWhere('role', 'editor');
            });

        $expectedSql = "SELECT * FROM `users` WHERE `is_active` = ? AND (`role` = ? OR `role` = ?)";
        $this->assertEquals($expectedSql, $builder->toSql());
        $this->assertEquals([1, 'admin', 'editor'], $builder->getBindings());
    }

    public function testInsertUpdateDeleteStatements()
    {
        $db = $this->createMockDb();
        $grammar = new MySqlGrammar();
        $builder = (new QueryBuilder($db, $grammar))->table('products');

        $insertSql = $grammar->compileInsert($builder, ['name' => 'Book', 'price' => 100]);
        $this->assertEquals("INSERT INTO `products` (`name`, `price`) VALUES (?, ?)", $insertSql);

        $builder->where('id', 42);
        $updateSql = $grammar->compileUpdate($builder, ['name' => 'Pen']);
        $this->assertEquals("UPDATE `products` SET `name` = ? WHERE `id` = ?", $updateSql);

        $deleteSql = $grammar->compileDelete($builder);
        $this->assertEquals("DELETE FROM `products` WHERE `id` = ?", $deleteSql);
    }

    public function testEmptyWhereInAndNotInSafeCompilation()
    {
        $db = $this->createMockDb();
        $grammar = new MySqlGrammar();

        // Empty whereIn should compile to 0 = 1 (safe false condition without SQL syntax error)
        $builderIn = (new QueryBuilder($db, $grammar))->table('users')->whereIn('id', []);
        $this->assertEquals("SELECT * FROM `users` WHERE 0 = 1", $builderIn->toSql());
        $this->assertEmpty($builderIn->getBindings());

        // Empty whereNotIn should compile to 1 = 1 (safe true condition)
        $builderNotIn = (new QueryBuilder($db, $grammar))->table('users')->whereNotIn('id', []);
        $this->assertEquals("SELECT * FROM `users` WHERE 1 = 1", $builderNotIn->toSql());
        $this->assertEmpty($builderNotIn->getBindings());
    }

    public function testWhereColumnCompilation()
    {
        $db = $this->createMockDb();
        $grammar = new MySqlGrammar();

        $builder = (new QueryBuilder($db, $grammar))
            ->table('users')
            ->whereColumn('users.updated_at', '>', 'users.created_at')
            ->orWhereColumn('users.first_name', 'users.last_name');

        $expectedSql = "SELECT * FROM `users` WHERE `users`.`updated_at` > `users`.`created_at` OR `users`.`first_name` = `users`.`last_name`";
        $this->assertEquals($expectedSql, $builder->toSql());
        $this->assertEmpty($builder->getBindings());
    }

    public function testOrWhereVariants()
    {
        $db = $this->createMockDb();
        $grammar = new MySqlGrammar();

        $builder = (new QueryBuilder($db, $grammar))
            ->table('orders')
            ->where('status', 'pending')
            ->orWhereNull('cancelled_at')
            ->orWhereIn('priority', ['urgent', 'high']);

        $expectedSql = "SELECT * FROM `orders` WHERE `status` = ? OR `cancelled_at` IS NULL OR `priority` IN (?, ?)";
        $this->assertEquals($expectedSql, $builder->toSql());
        $this->assertEquals(['pending', 'urgent', 'high'], $builder->getBindings());
    }
}
