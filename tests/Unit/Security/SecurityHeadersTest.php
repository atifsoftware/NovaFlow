<?php

namespace Tests\Unit\Security;

use Tests\TestCase;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Security;
use NovaFlow\Core\Dotenv;
use App\Middleware\SecurityHeadersMiddleware;
use App\Middleware\Kernel;
use App\Controllers\CustomerAuthController;

class SecurityHeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        unset($_SERVER['HTTPS']);
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        Dotenv::set('APP_ENV', 'development');
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTPS']);
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        Dotenv::set('APP_ENV', 'development');
        parent::tearDown();
    }

    public function testGetSecureHeadersReturnsExpectedDefaults(): void
    {
        $headers = Request::getSecureHeaders();

        $this->assertArrayHasKey('X-Content-Type-Options', $headers);
        $this->assertEquals('nosniff', $headers['X-Content-Type-Options']);

        $this->assertArrayHasKey('X-Frame-Options', $headers);
        $this->assertEquals('SAMEORIGIN', $headers['X-Frame-Options']);

        $this->assertArrayHasKey('X-XSS-Protection', $headers);
        $this->assertEquals('1; mode=block', $headers['X-XSS-Protection']);

        $this->assertArrayHasKey('Referrer-Policy', $headers);
        $this->assertEquals('strict-origin-when-cross-origin', $headers['Referrer-Policy']);

        $this->assertArrayHasKey('Permissions-Policy', $headers);
        $this->assertEquals('camera=(), microphone=(), geolocation=(self)', $headers['Permissions-Policy']);

        // In standard development over HTTP, HSTS should not be sent
        $this->assertArrayNotHasKey('Strict-Transport-Security', $headers);
    }

    public function testDynamicHstsSentUnderHttps(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $headers = Request::getSecureHeaders();

        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertEquals('max-age=31536000; includeSubDomains; preload', $headers['Strict-Transport-Security']);
    }

    public function testDynamicHstsSentUnderForwardedProto(): void
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $headers = Request::getSecureHeaders();

        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertEquals('max-age=31536000; includeSubDomains; preload', $headers['Strict-Transport-Security']);
    }

    public function testDynamicHstsSentInProduction(): void
    {
        Dotenv::set('APP_ENV', 'production');
        $headers = Request::getSecureHeaders();

        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertEquals('max-age=31536000; includeSubDomains; preload', $headers['Strict-Transport-Security']);
    }

    public function testSetSecureHeadersRunsWithoutError(): void
    {
        Request::setSecureHeaders();
        $this->assertTrue(true);
    }

    public function testSecurityHeadersMiddlewareImplementationAndRegistration(): void
    {
        $middleware = new SecurityHeadersMiddleware();
        $this->assertInstanceOf(\NovaFlow\Core\Middleware::class, $middleware);

        $request = new Request();
        $response = new Response();
        $this->assertTrue($middleware->handle($request, $response));

        // Verify registered in Kernel
        $this->assertContains(SecurityHeadersMiddleware::class, Kernel::$global);
        $this->assertArrayHasKey('security_headers', Kernel::$aliases);
        $this->assertEquals(SecurityHeadersMiddleware::class, Kernel::$aliases['security_headers']);
    }

    public function testSecurityEscapeAndHelperE(): void
    {
        $payload = '<script>alert("xss & \'attack\'")</script>';
        $escaped = Security::escape($payload);

        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringNotContainsString('"', $escaped);
        $this->assertStringNotContainsString("'", $escaped);
        $this->assertStringContainsString('&lt;script&gt;', $escaped);
        $this->assertStringContainsString('&quot;', $escaped);

        // Verify global helper e() is defined and produces identical output
        $this->assertTrue(function_exists('e'));
        $this->assertEquals($escaped, e($payload));
    }

    public function testCleanInputStripsInvisibleControlCharactersWhilePreservingWhitespace(): void
    {
        // Construct string with null byte, backspace, VT, FF, and control characters 0x0E-0x1F
        // Along with valid whitespace \r, \n, \t
        $raw = "Hello\x00World\x08!\tLine1\r\nLine2\x0B\x0C\x0E\x1F";
        $cleaned = Security::cleanInput($raw);

        $this->assertStringNotContainsString("\x00", $cleaned, "Null bytes must be stripped.");
        $this->assertStringNotContainsString("\x08", $cleaned, "Backspace must be stripped.");
        $this->assertStringNotContainsString("\x0B", $cleaned, "Vertical tab must be stripped.");
        $this->assertStringNotContainsString("\x0C", $cleaned, "Form feed must be stripped.");
        $this->assertStringNotContainsString("\x0E", $cleaned, "Control character 0x0E must be stripped.");
        $this->assertStringNotContainsString("\x1F", $cleaned, "Control character 0x1F must be stripped.");

        // Valid whitespace must be preserved
        $this->assertStringContainsString("\t", $cleaned, "Tab must be preserved.");
        $this->assertStringContainsString("\r\n", $cleaned, "CRLF must be preserved.");
        $this->assertStringContainsString("HelloWorld!	Line1\r\nLine2", $cleaned);
    }

    public function testCustomerSessionFixationProtection(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $controller = new CustomerAuthController();

        // 1. Pre-login state
        $initialSessionId = session_id();
        $customer = (object)[
            'id' => 101,
            'name' => 'Alice Customer',
            'email' => 'alice@example.com'
        ];

        // 2. Perform login
        $controller->loginCustomer($customer);
        $loggedInSessionId = session_id();

        // Verify session was regenerated upon login
        $this->assertNotEquals($initialSessionId, $loggedInSessionId);
        $this->assertEquals(101, $_SESSION['customer_id']);
        $this->assertEquals('Alice Customer', $_SESSION['customer_name']);
        $this->assertEquals('alice@example.com', $_SESSION['customer_email']);
        $this->assertTrue($_SESSION['customer_logged_in']);
        $this->assertTrue($controller->isLoggedIn());

        // 3. Perform logout
        $controller->logout();
        $loggedOutSessionId = session_id();

        // Verify session was regenerated upon logout and credentials cleared
        $this->assertNotEquals($loggedInSessionId, $loggedOutSessionId);
        $this->assertArrayNotHasKey('customer_id', $_SESSION);
        $this->assertArrayNotHasKey('customer_name', $_SESSION);
        $this->assertArrayNotHasKey('customer_email', $_SESSION);
        $this->assertArrayNotHasKey('customer_logged_in', $_SESSION);
        $this->assertFalse($controller->isLoggedIn());
    }
}
