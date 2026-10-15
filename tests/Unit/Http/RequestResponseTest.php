<?php

namespace Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

class RequestResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function testRequestMethodAndPathDetection(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', Request::method());
        $this->assertEquals('post', Request::getMethod());
        $this->assertTrue(Request::isPost());
        $this->assertFalse(Request::isGet());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Request::isGet());
        $this->assertFalse(Request::isPost());

        $_GET['url'] = '/products/categories';
        $this->assertEquals('/products/categories', Request::getPath());

        unset($_GET['url']);
        $_SERVER['REQUEST_URI'] = '/api/v1/orders?sort=desc&page=2';
        $this->assertEquals('/api/v1/orders', Request::getPath());
    }

    public function testInputRetrievalWithSanitization(): void
    {
        $_POST['username'] = "  john_doe\0  ";
        $_GET['filter'] = "  electronics\0  ";

        // post() strips null bytes and trims
        $cleanPost = Request::post('username');
        $this->assertEquals('john_doe', $cleanPost);

        // get() strips null bytes and trims
        $cleanGet = Request::get('filter');
        $this->assertEquals('electronics', $cleanGet);

        // Fallback default value
        $this->assertEquals('default_page', Request::get('page', 'default_page'));

        // input() merges sources
        $this->assertEquals('john_doe', Request::input('username'));
        $this->assertEquals('electronics', Request::input('filter'));
        $this->assertEquals('fallback_val', Request::input('missing_key', 'fallback_val'));
    }

    public function testAjaxDetection(): void
    {
        $this->assertFalse(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest'; // Case-insensitive
        $this->assertTrue(Request::isAjax());
    }

    public function testClientIpResolution(): void
    {
        $this->assertEquals('192.168.1.100', Request::ip());

        unset($_SERVER['REMOTE_ADDR']);
        $this->assertEquals('127.0.0.1', Request::ip(), "Default to localhost if REMOTE_ADDR missing");
    }

    public function testCaseInsensitiveHeaderRetrieval(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token_abc_123';
        $_SERVER['HTTP_X_API_KEY'] = 'my_secret_api_key';

        // Direct case match
        $this->assertEquals('Bearer token_abc_123', Request::header('Authorization'));

        // Case variations and hyphen/underscore interchangeability
        $this->assertEquals('Bearer token_abc_123', Request::header('authorization'));
        $this->assertEquals('Bearer token_abc_123', Request::header('AUTHORIZATION'));
        $this->assertEquals('my_secret_api_key', Request::header('X-Api-Key'));
        $this->assertEquals('my_secret_api_key', Request::header('x_api_key'));

        // Default value if header absent
        $this->assertEquals('default_agent', Request::header('User-Agent', 'default_agent'));
    }

    public function testUserContextHelpers(): void
    {
        $dummyUser = (object)['id' => 99, 'name' => 'Alice Tester'];
        Request::setUser($dummyUser);

        $this->assertSame($dummyUser, Request::getUser());
        Request::setUser(null);
        $this->assertNull(Request::getUser());
    }

    public function testResponseStatusCode(): void
    {
        $response = new Response();
        $response->setStatusCode(201);
        $this->assertEquals(201, http_response_code());

        $response->setStatusCode(200);
        $this->assertEquals(200, http_response_code());
    }
}
