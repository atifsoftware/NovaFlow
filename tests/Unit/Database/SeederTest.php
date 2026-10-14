<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Seeder;
use NovaFlow\Core\Database\Factory;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\Schema;
use NovaFlow\Core\QueryBuilder\Blueprint;
use NovaFlow\Core\QueryBuilder\QueryBuilder;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Sample Stationery',
            'price' => 25.50,
            'stock' => 100
        ];
    }
}

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        ProductFactory::new()->count(5)->create();
    }
}

class SeederTest extends TestCase
{
    public function testFactoryMakeAndCreate()
    {
        $factory = ProductFactory::new();
        $made = $factory->make(['name' => 'Custom Notebook']);

        $this->assertEquals('Custom Notebook', $made['name']);
        $this->assertEquals(25.50, $made['price']);

        // Multiple make
        $multiple = ProductFactory::new()->count(3)->make();
        $this->assertCount(3, $multiple);
    }

    public function testSeederExecutionOnDatabase()
    {
        $sqlite = new SqliteConnection(['database' => ':memory:']);
        Schema::setDatabase($sqlite);

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('price', 8, 2);
            $table->integer('stock');
        });

        // Run seeder manually
        $seeder = new ProductSeeder();
        // Point DB connection to sqlite for test
        \NovaFlow\Core\Container::getInstance()->singleton(\NovaFlow\Core\DatabaseInterface::class, function () use ($sqlite) {
            return $sqlite;
        });

        $seeder->run();

        $count = (new QueryBuilder($sqlite))->table('products')->count();
        $this->assertEquals(5, $count);
    }
}
