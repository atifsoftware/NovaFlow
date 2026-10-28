<?php

namespace Tests\Unit\Security;

use Tests\TestCase;
use NovaFlow\Core\Router;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Security;
use NovaFlow\Core\Dotenv;

class DummyAutoController
{
    public function hello(): string
    {
        return 'auto_routed_success';
    }

    public function mutate(): string
    {
        return 'mutation_success';
    }
}

class RouterSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($_GET['url']);
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Dotenv::set('ENABLE_AUTO_ROUTING', 'false');
    }

    protected function tearDown(): void
    {
        Dotenv::set('ENABLE_AUTO_ROUTING', 'false');
        parent::tearDown();
    }

    public function testAutoRoutingDisabledByDefaultTriggersNotFound(): void
    {
        $router = new Router();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '/dummyAuto/hello';

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->assertEquals(404, http_response_code());
        $this->assertStringNotContainsString('auto_routed_success', $output);
    }

    public function testUnmappedApiRouteReturnsJson404(): void
    {
        $router = new Router();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '/api/unknown-endpoint';

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->assertEquals(404, http_response_code());
        $data = json_decode($output, true);
        $this->assertIsArray($data);
        $this->assertEquals('error', $data['status']);
        $this->assertEquals('API route not found.', $data['message']);
    }

    public function testExplicitRoutesFunctionNormallyWhenAutoRoutingDisabled(): void
    {
        $router = new Router();
        $router->get('/explicit/test', fn() => 'explicit_success');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '/explicit/test';

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->assertEquals('explicit_success', $output);
    }

    public function testControllerBlocklistRejectsAutoRoutingToSensitiveControllers(): void
    {
        Dotenv::set('ENABLE_AUTO_ROUTING', 'true');
        $router = new Router();

        $blockedControllers = [
            'auth' => 'AuthController',
            'adminDashboard' => 'AdminDashboardController',
            'adminSettings' => 'AdminSettingsController',
            'adminUser' => 'AdminUserController',
            'backup' => 'BackupController',
            'settings' => 'SettingsController',
            'user' => 'UserController',
            'role' => 'RoleController',
            'customerAdmin' => 'CustomerAdminController',
            'customerAuth' => 'CustomerAuthController',
        ];

        foreach ($blockedControllers as $urlSegment => $controller) {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $_GET['url'] = "/$urlSegment/index";

            ob_start();
            $router->resolve();
            $output = ob_get_clean();

            $this->assertEquals(404, http_response_code(), "Controller [$controller] should be blocked from auto-routing.");
        }
    }

    public function testAutoRoutedMutationsRequireCsrfValidation(): void
    {
        Dotenv::set('ENABLE_AUTO_ROUTING', 'true');
        $router = new Router();

        // Register dummy controller in global namespace or App\Controllers alias
        if (!class_exists('App\\Controllers\\DummyAutoController')) {
            class_alias(DummyAutoController::class, 'App\\Controllers\\DummyAutoController');
        }

        foreach (['POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $_SERVER['REQUEST_METHOD'] = $method;
            $_GET['url'] = '/dummyAuto/mutate';
            $_POST = []; // No CSRF token

            ob_start();
            $router->resolve();
            $output = ob_get_clean();

            $this->assertEquals(419, http_response_code(), "HTTP method [$method] should require CSRF token under auto-routing.");
            $this->assertStringContainsString('419', $output);
        }
    }

    public function testAutoRoutedMutationsPassWithValidCsrfToken(): void
    {
        Dotenv::set('ENABLE_AUTO_ROUTING', 'true');
        $router = new Router();

        if (!class_exists('App\\Controllers\\DummyAutoController')) {
            class_alias(DummyAutoController::class, 'App\\Controllers\\DummyAutoController');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $validToken = Security::generateCSRFToken();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['url'] = '/dummyAuto/mutate';
        $_POST = ['_csrf_token' => $validToken];

        ob_start();
        $router->resolve();
        $output = ob_get_clean();

        $this->assertEquals('mutation_success', $output);
    }

    public function testSafe404RenderingMultipleDispatches(): void
    {
        $router = new Router();

        ob_start();
        $router->notFound();
        $output1 = ob_get_clean();

        ob_start();
        $router->notFound();
        $output2 = ob_get_clean();

        $this->assertEquals(404, http_response_code());
        $this->assertNotEmpty($output1);
        $this->assertEquals($output1, $output2);
    }
}
