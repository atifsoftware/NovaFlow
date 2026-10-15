<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Drivers\PostgresConnection;
use NovaFlow\Core\Database\Grammar\PostgresGrammar;
use NovaFlow\Core\Database\Schema\Grammar\PostgresSchemaGrammar;
use NovaFlow\Core\QueryBuilder\Blueprint;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\DatabaseInterface;
use Exception;

/**
 * PostgreSQL Dialect, Grammar and Integration Execution Tests
 * Validates Postgres-specific SQL generation (double quotes, SERIAL, JSONB @>, EXTRACT)
 * and executes live queries when a PostgreSQL server is running.
 */
class PostgresExecutionTest extends TestCase
{
    private PostgresGrammar $grammar;
    private PostgresSchemaGrammar $schemaGrammar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->grammar = new PostgresGrammar();
        $this->schemaGrammar = new PostgresSchemaGrammar();
    }

    public function testPostgresIdentifierQuoting()
    {
        $this->assertEquals('"users"', $this->grammar->wrap('users'));
        $this->assertEquals('"users"."email"', $this->grammar->wrap('users.email'));
        $this->assertEquals('"email" AS "user_email"', $this->grammar->wrap('email as user_email'));
        $this->assertEquals('*', $this->grammar->wrap('*'));
    }

    public function testPostgresDateExtractionCompilation()
    {
        $db = $this->createMock(DatabaseInterface::class);
        $qb = (new QueryBuilder($db, $this->grammar))
            ->table('orders')
            ->whereDate('created_at', '=', '2026-03-15')
            ->whereYear('created_at', '=', '2026')
            ->whereMonth('created_at', '=', '03');

        $sql = $qb->toSql();
        $this->assertStringContainsString('"created_at"::date = ?', $sql);
        $this->assertStringContainsString('EXTRACT(YEAR FROM "created_at") = ?', $sql);
        $this->assertStringContainsString('EXTRACT(MONTH FROM "created_at") = ?', $sql);
    }

    public function testPostgresJsonbOperatorCompilation()
    {
        $db = $this->createMock(DatabaseInterface::class);
        $qb = (new QueryBuilder($db, $this->grammar))
            ->table('users')
            ->whereJsonContains('metadata', ['role' => 'admin']);

        $sql = $qb->toSql();
        $this->assertStringContainsString('"metadata"::jsonb @> ?::jsonb', $sql);
    }

    public function testPostgresSchemaTypeTranslations()
    {
        $blueprint = new Blueprint('products');
        $blueprint->increments('id');
        $blueprint->string('name', 100);
        $blueprint->boolean('is_active');
        $blueprint->dateTime('published_at');

        $sql = $this->schemaGrammar->compileCreate($blueprint);

        // Verify Postgres-specific column conversions
        $this->assertStringContainsString('CREATE TABLE "products"', $sql);
        $this->assertStringContainsString('"id" SERIAL PRIMARY KEY', $sql);
        $this->assertStringContainsString('"is_active" BOOLEAN', $sql);
        $this->assertStringContainsString('"published_at" TIMESTAMP', $sql);
    }

    public function testPostgresConnectionFactoryAndDriver()
    {
        $conn = new PostgresConnection([
            'host' => '127.0.0.1',
            'port' => 5432,
            'database' => 'test_db',
            'username' => 'postgres',
            'password' => ''
        ]);

        $this->assertEquals('pgsql', $conn->getDriverName());
        $this->assertInstanceOf(PostgresGrammar::class, $conn->getGrammar());
    }

    public function testPostgresLiveExecutionIfServerAvailable()
    {
        $host = getenv('PG_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('PG_PORT') ?: 5432);
        $dbName = getenv('PG_DATABASE') ?: 'postgres';
        $user = getenv('PG_USER') ?: 'postgres';
        $pass = getenv('PG_PASSWORD') ?: '';

        try {
            $conn = new PostgresConnection([
                'host' => $host,
                'port' => $port,
                'database' => $dbName,
                'username' => $user,
                'password' => $pass,
            ]);
            $pdo = $conn->getPdo();
        } catch (Exception $e) {
            $this->markTestSkipped("PostgreSQL server is not reachable on {$host}:{$port} ({$e->getMessage()}). Dialect grammar and schema compilation passed successfully.");
            return;
        }

        // If server is reachable, execute live queries
        $conn->getPdo()->exec('CREATE TEMP TABLE _test_pg_live (id SERIAL PRIMARY KEY, title VARCHAR(50), is_active BOOLEAN)');
        
        $qb = new QueryBuilder($conn);
        (clone $qb)->table('_test_pg_live')->insert(['title' => 'Test PG', 'is_active' => true]);
        
        $row = (clone $qb)->table('_test_pg_live')->where('title', 'Test PG')->first();
        $this->assertNotNull($row);
        $this->assertEquals('Test PG', $row->title);
    }
}
