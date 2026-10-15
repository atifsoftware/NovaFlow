<?php

namespace Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Router;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Container;

class MockCustomService
{
    public function getServiceName(): string
    {
        return 'MockInjectedService';
    }
}

class MockUserController
{
    public function index(): string
    {
        return 'user_index_rendered';
    }

    public function show(Request $req, Response $res, MockCustomService $service, $id): string
    {
        return json_encode([
            'id' => $id,
            'service' => $service->getServiceName(),
            'method' => $req->getMethod()
        ]);
    }
}

class RouterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Reset server and get variables
        unset($_GET['url']);
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public function testRouteRegistrationAndRetrieval(): void
    {
        $router = new Router();
        $router->get('/health', fn() => 'OK');
        $router->post('/submit', fn() => 'Submitted');

        $routes = $router->getRoutes();

        $this->assertArrayHasKey('get', $routes);
        $this->assertArrayHasKey('/health', $routes['get']);
        $this->assertArrayHasKey('post', $routes);
        $this->assertArrayHasKey('/submit', $routes['post']);
    }

    public function testRouteGroupWithPrefixAndMiddleware(): void
    {
        $router = new Router();

        $router->group(['prefix' => 'api/v1', 'middleware' => ['api_auth']], function ($r) {
            $r->get('/users', fn() => 'Users List');
            $r->get('/posts', fn() => 'Posts List')->middleware('throttle:60');
        });

        $routes = $router->getRoutes();

        $this->assertArrayHasKey('/api/v1/users', $routes['get']);
        $this->assertEquals(['api_auth'], $routes['get']['/api/v1/users']['middleware']);

        $this->assertArrayHasKey('/api/v1/posts', $routes['get']);
        $this->assertEquals(['api_auth', 'throttle:60'], $routes['get']['/api/v1/posts']['middleware']);
    }

    public function testNestedRouteGroups(): void
    {
        $router = new Router();

        $router->group(['prefix' => 'admin'], function ($admin) {
            $admin->group(['prefix' => 'settings'], function ($settings) {
                $settings->get('/general', fn() => 'Settings Page');
            });
        });

        $routes = $router->getRoutes();
        $this->assertArrayHasKey('/admin/settings/general', $routes['get']);
    }

    public function testExplicitRouteDispatchWithDynamicParameters(): void
    {
        $router = new Router();
        $capturedId = null;

        $router->get('/users/{id}', function (Request $req, Response $res, $id) use (&$capturedId) {
            $capturedId = $id;
            return "User:$id";
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '/users/987';

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->assertEquals('987', $capturedId);
        $this->assertEquals('User:987', $output);
    }

    public function testControllerExecutionWithFullDependencyInjection(): void
    {
        $router = new Router();

        // Register custom service in DI Container
        Container::getInstance()->bind(MockCustomService::class, function () {
            return new MockCustomService();
        });

        $router->get('/users/{id}/details', MockUserController::class . '@show');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '/users/42/details';

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $data = json_decode($output, true);

        $this->assertEquals('42', $data['id']);
        $this->assertEquals('MockInjectedService', $data['service']);
        $this->assertEquals('get', $data['method']);
    }

    public function testApiRouteReturningArrayOutputsValidJson(): void
    {
        $router = new Router();

        $router->get('/api/status', function () {
            return ['status' => 'healthy', 'version' => '2.1.0'];
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '/api/status';

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertEquals('healthy', $data['status']);
        $this->assertEquals('2.1.0', $data['version']);
    }
}
