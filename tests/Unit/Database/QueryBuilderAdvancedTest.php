<?php

namespace Tests\Unit\Database;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\QueryBuilder;
use NovaFlow\Core\RawExpression;
use Exception;

class QueryBuilderAdvancedTest extends TestCase
{
    private SqliteConnection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = new SqliteConnection(['database' => ':memory:']);

        // Create sample tables: categories, products, orders
        $this->connection->query("CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        )");

        $this->connection->query("CREATE TABLE products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER,
            title TEXT NOT NULL,
            price REAL NOT NULL,
            stock INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at TEXT NULL
        )");

        // Seed sample data
        $this->connection->query("INSERT INTO categories (name) VALUES ('Electronics'), ('Books'), ('Clothing')");

        $this->connection->query("INSERT INTO products (category_id, title, price, stock, is_active, created_at) VALUES 
            (1, 'Laptop', 1200.50, 15, 1, '2026-01-15 10:00:00'),
            (1, 'Smartphone', 800.00, 25, 1, '2026-02-20 14:30:00'),
            (1, 'Headphones', 150.00, 0, 0, '2026-03-05 09:15:00'),
            (2, 'PHP Guide', 45.00, 50, 1, '2025-11-10 12:00:00'),
            (2, 'Clean Code', 55.00, 30, 1, '2025-12-01 16:45:00'),
            (3, 'T-Shirt', 25.00, 100, 1, '2026-04-10 11:20:00')
        ");
    }

    public function testWhereOperatorsAndConditions()
    {
        $qb = new QueryBuilder($this->connection);

        // Price > 100
        $expensive = (clone $qb)->table('products')->where('price', '>', 100)->get();
        $this->assertCount(3, $expensive);

        // Price <= 45
        $cheap = (clone $qb)->table('products')->where('price', '<=', 45)->get();
        $this->assertCount(2, $cheap);

        // whereBetween
        $midRange = (clone $qb)->table('products')->whereBetween('price', [50, 500])->get();
        $this->assertCount(2, $midRange); // Headphones (150) and Clean Code (55)

        // whereNotBetween
        $outliers = (clone $qb)->table('products')->whereNotBetween('price', [50, 500])->get();
        $this->assertCount(4, $outliers); // Laptop, Smartphone, PHP Guide, T-Shirt
    }

    public function testWhereLikeAndNullConditions()
    {
        $qb = new QueryBuilder($this->connection);

        // whereLike
        $phones = (clone $qb)->table('products')->whereLike('title', '%phone%')->get();
        $this->assertCount(2, $phones); // Smartphone, Headphones

        // whereNull & whereNotNull
        $allWithDate = (clone $qb)->table('products')->whereNotNull('created_at')->count();
        $this->assertEquals(6, $allWithDate);

        $noneNull = (clone $qb)->table('products')->whereNull('created_at')->count();
        $this->assertEquals(0, $noneNull);
    }

    public function testWhereInAndNotInConditions()
    {
        $qb = new QueryBuilder($this->connection);

        $selected = (clone $qb)->table('products')
            ->whereIn('title', ['Laptop', 'T-Shirt', 'NonExistent'])
            ->pluck('title');

        $this->assertEquals(['Laptop', 'T-Shirt'], $selected);

        $excluded = (clone $qb)->table('products')
            ->whereNotIn('category_id', [1, 2])
            ->pluck('title');

        $this->assertEquals(['T-Shirt'], $excluded);
    }

    public function testNestedWhereWithClosure()
    {
        $qb = new QueryBuilder($this->connection);

        // Select active products that are EITHER expensive (> 500) OR in Category 2
        $results = (clone $qb)->table('products')
            ->where('is_active', 1)
            ->where(function ($query) {
                $query->where('price', '>', 500)
                      ->orWhere('category_id', 2);
            })
            ->get();

        $titles = array_map(fn($item) => $item->title, $results);
        sort($titles);

        // Expected: Laptop (>500), Smartphone (>500), PHP Guide (cat 2), Clean Code (cat 2)
        $this->assertEquals(['Clean Code', 'Laptop', 'PHP Guide', 'Smartphone'], $titles);
    }

    public function testDateFunctions()
    {
        $qb = new QueryBuilder($this->connection);

        // whereYear
        $items2026 = (clone $qb)->table('products')->whereYear('created_at', 2026)->count();
        $this->assertEquals(4, $items2026);

        $items2025 = (clone $qb)->table('products')->whereYear('created_at', 2025)->count();
        $this->assertEquals(2, $items2025);

        // whereMonth
        $febItems = (clone $qb)->table('products')->whereMonth('created_at', 2)->count();
        $this->assertEquals(1, $febItems);
    }

    public function testJoins()
    {
        $qb = new QueryBuilder($this->connection);

        // Inner Join
        $results = (clone $qb)->table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.title', 'categories.name as category_name')
            ->where('categories.name', 'Books')
            ->get();

        $this->assertCount(2, $results);
        $this->assertEquals('Books', $results[0]->category_name);

        // Left Join
        $leftResults = (clone $qb)->table('categories')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->select('categories.name', 'products.title')
            ->get();

        $this->assertGreaterThanOrEqual(6, count($leftResults));
    }

    public function testAggregates()
    {
        $qb = new QueryBuilder($this->connection);

        // Count
        $totalCount = (clone $qb)->table('products')->count();
        $this->assertEquals(6, $totalCount);

        // Sum
        $totalStock = (clone $qb)->table('products')->sum('stock');
        $this->assertEquals(220, $totalStock); // 15+25+0+50+30+100 = 220

        // Avg
        $avgPrice = (clone $qb)->table('products')->avg('price');
        $expectedAvg = (1200.50 + 800 + 150 + 45 + 55 + 25) / 6;
        $this->assertEqualsWithDelta($expectedAvg, $avgPrice, 0.01);

        // Min & Max
        $minPrice = (clone $qb)->table('products')->min('price');
        $this->assertEquals(25.00, (float)$minPrice);

        $maxPrice = (clone $qb)->table('products')->max('price');
        $this->assertEquals(1200.50, (float)$maxPrice);
    }

    public function testPluckAndValue()
    {
        $qb = new QueryBuilder($this->connection);

        // Pluck list
        $titles = (clone $qb)->table('products')->where('category_id', 2)->pluck('title');
        $this->assertEquals(['PHP Guide', 'Clean Code'], $titles);

        // Pluck keyed list
        $keyed = (clone $qb)->table('categories')->pluck('name', 'id');
        $this->assertEquals([
            1 => 'Electronics',
            2 => 'Books',
            3 => 'Clothing'
        ], $keyed);

        // Value of single column
        $val = (clone $qb)->table('products')->where('title', 'Laptop')->value('price');
        $this->assertEquals(1200.50, (float)$val);

        $nullVal = (clone $qb)->table('products')->where('title', 'GhostProduct')->value('price');
        $this->assertNull($nullVal);
    }

    public function testPaginationStructure()
    {
        $qb = (new QueryBuilder($this->connection))->table('products')->orderBy('id', 'ASC');

        // Page 1, 2 items per page
        $paginator = $qb->paginate(2, 1);
        $this->assertEquals(6, $paginator->total());
        $this->assertEquals(2, $paginator->perPage());
        $this->assertEquals(1, $paginator->currentPage());
        $this->assertEquals(3, $paginator->lastPage());
        $this->assertTrue($paginator->hasMorePages());

        $items = $paginator->items();
        $this->assertCount(2, $items);
        $this->assertEquals('Laptop', $items[0]->title);
        $this->assertEquals('Smartphone', $items[1]->title);

        // Page 3 (last page)
        $paginatorPage3 = (new QueryBuilder($this->connection))->table('products')->orderBy('id', 'ASC')->paginate(2, 3);
        $this->assertEquals(3, $paginatorPage3->currentPage());
        $this->assertFalse($paginatorPage3->hasMorePages());
        $this->assertEquals('T-Shirt', $paginatorPage3->items()[1]->title);
    }

    public function testWhenAndUnlessConditionals()
    {
        $qb = new QueryBuilder($this->connection);

        $filterCat = 1;
        $results = (clone $qb)->table('products')
            ->when($filterCat, function ($q, $catId) {
                $q->where('category_id', $catId);
            })
            ->get();
        $this->assertCount(3, $results);

        // When false should not apply
        $resultsAll = (clone $qb)->table('products')
            ->when(null, function ($q, $catId) {
                $q->where('category_id', $catId);
            })
            ->get();
        $this->assertCount(6, $resultsAll);

        // Unless true should not apply, unless false should apply
        $unlessResults = (clone $qb)->table('products')
            ->unless(false, function ($q) {
                $q->where('stock', '>', 50);
            })
            ->get();
        $this->assertCount(1, $unlessResults); // T-Shirt (100)
    }

    public function testOrderByAndLatestOldest()
    {
        $qb = new QueryBuilder($this->connection);

        $latestProduct = (clone $qb)->table('products')->latest('id')->first();
        $this->assertEquals('T-Shirt', $latestProduct->title);

        $oldestProduct = (clone $qb)->table('products')->oldest('id')->first();
        $this->assertEquals('Laptop', $oldestProduct->title);
    }

    public function testFindOrFailThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Record not found with ID: 9999");

        (new QueryBuilder($this->connection))->table('products')->findOrFail(9999);
    }

    public function testSqlInjectionResilienceInWhereValues()
    {
        $qb = new QueryBuilder($this->connection);

        // Classic SQL injection attempt: ' OR '1'='1
        $attack1 = (clone $qb)->table('products')
            ->where('title', "' OR '1'='1")
            ->get();
        $this->assertEmpty($attack1, "SQL Injection payload in value must be treated as literal string");

        // DROP TABLE injection attempt
        $attack2 = (clone $qb)->table('products')
            ->where('title', "Laptop'; DROP TABLE products; --")
            ->get();
        $this->assertEmpty($attack2);

        // Table should still exist intact
        $count = (clone $qb)->table('products')->count();
        $this->assertEquals(6, $count, "Table must remain intact after malicious query attempt");
    }

    public function testOrderBySanitizationPreventsSqlInjection()
    {
        $qb = (new QueryBuilder($this->connection))->table('products');

        // Order by with injection attempt
        $qb->orderBy("price; DROP TABLE categories; --", "DESC");

        // The compiled SQL must not contain semicolon or raw DROP command
        $sql = $qb->toSql();
        $this->assertStringNotContainsString(';', $sql);
        $this->assertStringContainsString('ORDER BY priceDROPTABLEcategories DESC', $sql);

        // Try to execute: column does not exist, but no SQL injection executes
        try {
            $qb->get();
        } catch (\Throwable $e) {
            // Expected: column 'priceDROPTABLEcategories' does not exist
        }

        // Categories table must still exist intact
        $catCount = (new QueryBuilder($this->connection))->table('categories')->count();
        $this->assertEquals(3, $catCount);
    }
}
