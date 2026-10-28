<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Request;

class RequestValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function testWantsJsonDetection(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'text/html,application/xhtml+xml';
        $this->assertFalse(Request::wantsJson());

        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $this->assertTrue(Request::wantsJson());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(Request::wantsJson());
    }

    public function testSuccessfulValidationReturnsData(): void
    {
        $_POST = [
            'name' => 'NovaFlow ERP',
            'email' => 'admin@novaflow.test',
            'amount' => '250.50'
        ];

        $validated = Request::validate([
            'name' => 'required',
            'email' => 'required|email',
            'amount' => 'required|numeric'
        ]);

        $this->assertEquals('NovaFlow ERP', $validated['name']);
        $this->assertEquals('admin@novaflow.test', $validated['email']);
        $this->assertEquals('250.50', $validated['amount']);
    }

    public function testSafeRefererBlocksOpenRedirect(): void
    {
        $_SERVER['HTTP_HOST'] = 'localhost';

        // Same host URL is allowed
        $_SERVER['HTTP_REFERER'] = 'http://localhost/admin/users';
        $this->assertEquals('http://localhost/admin/users', Request::safeReferer('/fallback'));

        // Relative internal URL is allowed
        $_SERVER['HTTP_REFERER'] = '/admin/dashboard';
        $this->assertEquals('/admin/dashboard', Request::safeReferer('/fallback'));

        // External malicious URL is blocked and returns fallback
        $_SERVER['HTTP_REFERER'] = 'https://evil-phishing-site.com/steal';
        $this->assertEquals('/fallback', Request::safeReferer('/fallback'));

        // Empty referer returns fallback
        unset($_SERVER['HTTP_REFERER']);
        $this->assertEquals('/fallback', Request::safeReferer('/fallback'));
    }
}
