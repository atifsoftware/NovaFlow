<?php

namespace Tests\Unit\Pagination;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Pagination\LengthAwarePaginator;
use NovaFlow\Core\Database\Drivers\SqliteConnection;
use NovaFlow\Core\QueryBuilder\QueryBuilder;

class PaginatorTest extends TestCase
{
    public function testLengthAwarePaginatorDirectUsage()
    {
        $items = ['Item 1', 'Item 2', 'Item 3'];
        $paginator = new LengthAwarePaginator($items, 50, 10, 2);

        $this->assertEquals(50, $paginator->total());
        $this->assertEquals(10, $paginator->perPage());
        $this->assertEquals(2, $paginator->currentPage());
        $this->assertEquals(5, $paginator->lastPage());
        $this->assertEquals(11, $paginator->from());
        $this->assertEquals(13, $paginator->to());
        $this->assertTrue($paginator->hasPages());
        $this->assertTrue($paginator->hasMorePages());

        // Test URLs
        $this->assertStringContainsString('page=1', $paginator->previousPageUrl());
        $this->assertStringContainsString('page=3', $paginator->nextPageUrl());

        // Test Bootstrap HTML Links
        $linksHtml = $paginator->links();
        $this->assertStringContainsString('<nav', $linksHtml);
        $this->assertStringContainsString('page-item active', $linksHtml);
        $this->assertStringContainsString('pagination', $linksHtml);

        // Test API toArray
        $array = $paginator->toArray();
        $this->assertEquals(50, $array['total']);
        $this->assertEquals(2, $array['current_page']);
        $this->assertCount(3, $array['data']);
    }

    public function testQueryBuilderPaginateExecution()
    {
        $sqlite = new SqliteConnection(['database' => ':memory:']);
        $sqlite->query("CREATE TABLE products (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)");

        // Insert 25 products
        for ($i = 1; $i <= 25; $i++) {
            (new QueryBuilder($sqlite))->table('products')->insert(['name' => "Product $i"]);
        }

        // Paginate page 1 (10 per page)
        $page1 = (new QueryBuilder($sqlite))->table('products')->paginate(10, 1);
        $this->assertEquals(25, $page1->total());
        $this->assertEquals(3, $page1->lastPage());
        $this->assertCount(10, $page1->items());
        $this->assertEquals('Product 1', $page1->items()[0]->name);
        $this->assertEquals('Product 10', $page1->items()[9]->name);

        // Paginate page 3 (remaining 5 items)
        $page3 = (new QueryBuilder($sqlite))->table('products')->paginate(10, 3);
        $this->assertCount(5, $page3->items());
        $this->assertFalse($page3->hasMorePages());
        $this->assertEquals('Product 21', $page3->items()[0]->name);
        $this->assertEquals('Product 25', $page3->items()[4]->name);
    }
}
