<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function assertArrayHas(array $array, string $key, string $message = ''): void
    {
        $this->assertTrue(
            array_key_exists($key, $array),
            $message ?: "Array does not have key: $key"
        );
    }

    protected function assertArrayNotHas(array $array, string $key, string $message = ''): void
    {
        $this->assertTrue(
            !array_key_exists($key, $array),
            $message ?: "Array has key: $key"
        );
    }
}