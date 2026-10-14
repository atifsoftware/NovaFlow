<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\QueryBuilder\Blueprint;
use NovaFlow\Core\QueryBuilder\Schema;
use NovaFlow\Core\Database\Schema\Grammar\MySqlSchemaGrammar;
use NovaFlow\Core\Database\Schema\Grammar\PostgresSchemaGrammar;
use NovaFlow\Core\Database\Schema\Grammar\SqliteSchemaGrammar;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\QueryBuilder;

class SchemaGrammarTest extends TestCase
{
    public function testMySqlSchemaCreateCompilation()
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('email');
        $blueprint->boolean('is_active');

        $grammar = new MySqlSchemaGrammar();
        $sql = $grammar->compileCreate($blueprint);

        $this->assertStringContainsString('CREATE TABLE `users`', $sql);
        $this->assertStringContainsString('`id` INT AUTO_INCREMENT PRIMARY KEY', $sql);
        $this->assertStringContainsString('`email` VARCHAR(255) NOT NULL', $sql);
        $this->assertStringContainsString('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4', $sql);
    }

    public function testPostgresSchemaCreateCompilation()
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('email');
        $blueprint->boolean('is_active');

        $grammar = new PostgresSchemaGrammar();
        $sql = $grammar->compileCreate($blueprint);

        $this->assertStringContainsString('CREATE TABLE "users"', $sql);
        $this->assertStringContainsString('"id" SERIAL PRIMARY KEY', $sql);
        $this->assertStringContainsString('"email" VARCHAR(255) NOT NULL', $sql);
        $this->assertStringContainsString('"is_active" BOOLEAN NOT NULL', $sql);
        $this->assertStringNotContainsString('ENGINE=InnoDB', $sql);
    }

    public function testSqliteSchemaCreateCompilation()
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->string('name');
        $blueprint->boolean('is_active');

        $grammar = new SqliteSchemaGrammar();
        $sql = $grammar->compileCreate($blueprint);

        $this->assertStringContainsString('CREATE TABLE "users"', $sql);
        $this->assertStringContainsString('"id" INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
        $this->assertStringContainsString('"name" VARCHAR(255) NOT NULL', $sql);
        $this->assertStringContainsString('"is_active" INTEGER NOT NULL', $sql);
        $this->assertStringNotContainsString('ENGINE=InnoDB', $sql);
    }

    public function testSchemaCreateRealExecutionOnSqlite()
    {
        $sqlite = new SqliteConnection(['database' => ':memory:']);
        Schema::setDatabase($sqlite);

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->boolean('published')->default(0);
        });

        // Insert and verify table actually exists
        (new QueryBuilder($sqlite))->table('articles')->insert([
            'title' => 'First Post',
            'body' => 'Hello World',
            'published' => 1
        ]);

        $article = (new QueryBuilder($sqlite))->table('articles')->first();
        $this->assertNotNull($article);
        $this->assertEquals('First Post', $article->title);
    }
}
