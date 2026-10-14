<?php

namespace Tests\Unit\Middleware;

use PHPUnit\Framework\TestCase;
use App\Middleware\CorsMiddleware;
use App\Middleware\ThrottleRequestsMiddleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Cache;

class SecurityMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    protected function tearDown(): void
    {
        Cache::clear();
        parent::tearDown();
    }

    public function testCorsMiddlewarePassesAndSetsHeaders()
    {
        $middleware = new CorsMiddleware();
        $request = new Request();
        $response = new Response();

        $result = $middleware->handle($request, $response);
        $this->assertTrue($result);
    }

    public function testThrottleMiddlewareLimitsExcessiveRequests()
    {
        $middleware = new ThrottleRequestsMiddleware();
        $request = new Request();
        $response = new Response();

        // Limit to 3 requests per 1 minute
        $args = [3, 1];

        // Requests 1, 2, 3 should succeed
        $this->assertTrue($middleware->handle($request, $response, $args));
        $this->assertTrue($middleware->handle($request, $response, $args));
        $this->assertTrue($middleware->handle($request, $response, $args));

        // Request 4 should be throttled (returns false)
        // We buffer output to catch the 429 response
        ob_start();
        $result = $middleware->handle($request, $response, $args);
        $output = ob_get_clean();

        $this->assertFalse($result);
        $this->assertStringContainsString('Too Many Requests', $output);
    }
}
