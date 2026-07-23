<?php

namespace Tests\Unit;

use Tests\TestCase;
use NovaFlow\Core\JWT;

class JWTTest extends TestCase
{
    public function testEncodeDecode(): void
    {
        $payload = [
            'sub' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com'
        ];

        $token = JWT::encode($payload);

        $this->assertIsString($token);
        $this->assertCount(3, explode('.', $token));
    }

    public function testDecode(): void
    {
        $payload = [
            'sub' => 1,
            'name' => 'Test User'
        ];

        $token = JWT::encode($payload);
        $decoded = JWT::decode($token);

        $this->assertIsArray($decoded);
        $this->assertEquals(1, $decoded['sub']);
        $this->assertEquals('Test User', $decoded['name']);
    }

    public function testExpiredToken(): void
    {
        $payload = ['sub' => 1];
        $token = JWT::encode($payload, -10); // Expired

        $decoded = JWT::decode($token);

        $this->assertFalse($decoded);
    }

    public function testInvalidToken(): void
    {
        $decoded = JWT::decode('invalid.token.here');

        $this->assertFalse($decoded);
    }
}