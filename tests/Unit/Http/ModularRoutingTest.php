<?php

namespace Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Router;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

class ModularRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($_GET['url']);
        unset($_POST['_method']);
        unset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        (new Router())->clearRouteCache();
    }

    protected function tearDown(): void
    {
        (new Router())->clearRouteCache();
        parent::tearDown();
    }

    public function testRestfulVerbsRegistration(): void
    {
        $router = new Router();
        $router->put('/items/{id}', fn() => 'updated');
        $router->patch('/items/{id}', fn() => 'patched');
        $router->delete('/items/{id}', fn() => 'deleted');
        $router->options('/items', fn() => 'options');
        $router->any('/webhook', fn() => 'any');

        $routes = $router->getRoutes();

        $this->assertArrayHasKey('put', $routes);
        $this->assertArrayHasKey('/items/{id}', $routes['put']);

        $this->assertArrayHasKey('patch', $routes);
        $this->assertArrayHasKey('/items/{id}', $routes['patch']);

        $this->assertArrayHasKey('delete', $routes);
        $this->assertArrayHasKey('/items/{id}', $routes['delete']);

        $this->assertArrayHasKey('options', $routes);
        $this->assertArrayHasKey('/items', $routes['options']);

        // any registers across all verbs
        $this->assertArrayHasKey('/webhook', $routes['get']);
        $this->assertArrayHasKey('/webhook', $routes['post']);
        $this->assertArrayHasKey('/webhook', $routes['put']);
        $this->assertArrayHasKey('/webhook', $routes['delete']);
    }

    public function testResourceRoutingGeneration(): void
    {
        $router = new Router();
        $router->resource('invoices', 'InvoiceController');

        $routes = $router->getRoutes();

        // Index & create
        $this->assertArrayHasKey('/invoices', $routes['get']);
        $this->assertEquals('InvoiceController@index', $routes['get']['/invoices']['callback']);

        $this->assertArrayHasKey('/invoices/create', $routes['get']);
        $this->assertEquals('InvoiceController@create', $routes['get']['/invoices/create']['callback']);

        // Store
        $this->assertArrayHasKey('/invoices', $routes['post']);
        $this->assertEquals('InvoiceController@store', $routes['post']['/invoices']['callback']);

        // Show & Edit
        $this->assertArrayHasKey('/invoices/{id}', $routes['get']);
        $this->assertEquals('InvoiceController@show', $routes['get']['/invoices/{id}']['callback']);

        $this->assertArrayHasKey('/invoices/{id}/edit', $routes['get']);
        $this->assertEquals('InvoiceController@edit', $routes['get']['/invoices/{id}/edit']['callback']);

        // Update (PUT + POST fallback)
        $this->assertArrayHasKey('/invoices/{id}', $routes['put']);
        $this->assertEquals('InvoiceController@update', $routes['put']['/invoices/{id}']['callback']);
        $this->assertArrayHasKey('/invoices/{id}/update', $routes['post']);

        // Destroy (DELETE + POST fallback)
        $this->assertArrayHasKey('/invoices/{id}', $routes['delete']);
        $this->assertEquals('InvoiceController@destroy', $routes['delete']['/invoices/{id}']['callback']);
        $this->assertArrayHasKey('/invoices/{id}/delete', $routes['post']);
    }

    public function testResourceWithOnlyOption(): void
    {
        $router = new Router();
        $router->resource('reports', 'ReportController', ['only' => ['index', 'show']]);

        $routes = $router->getRoutes();

        $this->assertArrayHasKey('/reports', $routes['get']);
        $this->assertArrayHasKey('/reports/{id}', $routes['get']);
        $this->assertArrayNotHasKey('/reports/create', $routes['get']);
        $this->assertArrayNotHasKey('post', $routes);
    }

    public function testModuleGrouping(): void
    {
        $router = new Router();
        $router->module('Sales', function ($r) {
            $r->get('/dashboard', fn() => 'Sales Dashboard');
            $r->resource('orders', 'OrderController');
        }, ['middleware' => ['auth', 'rbac:sales']]);

        $routes = $router->getRoutes();

        $this->assertArrayHasKey('/sales/dashboard', $routes['get']);
        $this->assertEquals(['auth', 'rbac:sales'], $routes['get']['/sales/dashboard']['middleware']);

        $this->assertArrayHasKey('/sales/orders', $routes['get']);
        $this->assertEquals(['auth', 'rbac:sales'], $routes['get']['/sales/orders']['middleware']);
    }

    public function testMethodSpoofingViaPostParameter(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_method'] = 'PUT';

        $this->assertEquals('PUT', Request::method());
        $this->assertEquals('put', Request::getMethod());

        $_POST['_method'] = 'DELETE';
        $this->assertEquals('DELETE', Request::method());
        $this->assertEquals('delete', Request::getMethod());
    }

    public function testMethodSpoofingViaHeader(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_POST['_method']);
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PATCH';

        $this->assertEquals('PATCH', Request::method());
        $this->assertEquals('patch', Request::getMethod());
    }

    public function testLoadModulesAutoDiscovery(): void
    {
        $router = new Router();
        $router->loadModules();

        $routes = $router->getRoutes();

        // Check Sales module
        $this->assertArrayHasKey('/sales/invoices', $routes['get']);
        $this->assertArrayHasKey('/sales/quotations', $routes['get']);
        $this->assertArrayHasKey('/sales/customers', $routes['get']);

        // Check Inventory module
        $this->assertArrayHasKey('/inventory/items', $routes['get']);
        $this->assertArrayHasKey('/inventory/adjustments', $routes['get']);

        // Check Accounts module
        $this->assertArrayHasKey('/accounts/chart-of-accounts', $routes['get']);
        $this->assertArrayHasKey('/accounts/vouchers', $routes['get']);
        $this->assertArrayHasKey('/accounts/ledger', $routes['get']);
    }

    public function testLoadAllRoutesUnifiedLoader(): void
    {
        $router = new Router();
        $router->loadAllRoutes();

        $routes = $router->getRoutes();

        // Check Web routes
        $this->assertArrayHasKey('/', $routes['get']);
        $this->assertArrayHasKey('/login', $routes['get']);

        // Check Admin routes
        $this->assertArrayHasKey('/admin/dashboard', $routes['get']);
        $this->assertArrayHasKey('/admin/users', $routes['get']);

        // Check API routes
        $this->assertArrayHasKey('/api/v1/users', $routes['get']);

        // Check ERP Modules
        $this->assertArrayHasKey('/sales/invoices', $routes['get']);
        $this->assertArrayHasKey('/inventory/items', $routes['get']);
        $this->assertArrayHasKey('/accounts/vouchers', $routes['get']);
    }

    public function testDynamicNewRouteFileAutoLoaded(): void
    {
        $baseDir = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3);
        $tempRouteFile = $baseDir . '/routes/_test_dynamic_custom.php';

        try {
            // Create a custom route file e.g. inventory.php or custom.php
            file_put_contents($tempRouteFile, '<?php $router->get("/dynamic-custom-test", fn() => "custom_loaded");');

            $router = new Router();
            $router->loadAllRoutes();

            $routes = $router->getRoutes();
            $this->assertArrayHasKey('/dynamic-custom-test', $routes['get']);
        } finally {
            if (file_exists($tempRouteFile)) {
                unlink($tempRouteFile);
            }
        }
    }

    public function testRouteCachingLifecycle(): void
    {
        $router = new Router();
        $router->loadAllRoutes();

        // Ensure clean slate
        $router->clearRouteCache();
        $this->assertFalse($router->isCached());

        // Cache routes
        $cached = $router->cacheRoutes();
        $this->assertTrue($cached);
        $this->assertTrue($router->isCached());
        $this->assertFileExists($router->getCachePath());

        // Load fresh router from cache
        $freshRouter = new Router();
        $freshRouter->loadAllRoutes();
        $routes = $freshRouter->getRoutes();

        $this->assertArrayHasKey('/login', $routes['get']);
        $this->assertArrayHasKey('/sales/invoices', $routes['get']);

        // Clean up cache
        $router->clearRouteCache();
        $this->assertFalse($router->isCached());
        $this->assertFileDoesNotExist($router->getCachePath());
    }

    public function testRouteCachingThrowsExceptionOnClosure(): void
    {
        $router = new Router();
        $router->get('/closure-route', fn() => 'closure');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Route uses an anonymous Closure');
        $router->cacheRoutes();
    }
}
