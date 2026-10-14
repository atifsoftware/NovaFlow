<?php

namespace Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Http\Resources\JsonResource;
use NovaFlow\Core\Http\Resources\ResourceCollection;
use NovaFlow\Core\Pagination\LengthAwarePaginator;
use NovaFlow\Core\Request;

// Custom Resource for testing
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->resource['id'],
            'name'  => strtoupper($this->resource['name']),
            'email' => $this->resource['email'],
            // password is excluded!
        ];
    }

    public function with(Request $request): array
    {
        return [
            'version' => '1.0'
        ];
    }
}

class JsonResourceTest extends TestCase
{
    public function testSingleResourceTransformation()
    {
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret_hash_value',
        ];

        $resource = UserResource::make($userData);
        $resolved = $resource->resolve();

        $this->assertArrayHasKey('data', $resolved);
        $this->assertEquals(1, $resolved['data']['id']);
        $this->assertEquals('JOHN DOE', $resolved['data']['name']);
        $this->assertEquals('john@example.com', $resolved['data']['email']);
        $this->assertArrayNotHasKey('password', $resolved['data']);
        $this->assertEquals('1.0', $resolved['version']);
    }

    public function testAdditionalMetaData()
    {
        $resource = UserResource::make(['id' => 5, 'name' => 'Alice', 'email' => 'alice@example.com'])
            ->additional(['message' => 'User retrieved successfully']);

        $resolved = $resource->resolve();

        $this->assertEquals('User retrieved successfully', $resolved['message']);
        $this->assertEquals(5, $resolved['data']['id']);
    }

    public function testResourceCollection()
    {
        $users = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@test.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@test.com'],
        ];

        $collection = UserResource::collection($users);
        $resolved = $collection->resolve();

        $this->assertCount(2, $resolved['data']);
        $this->assertEquals('JOHN', $resolved['data'][0]['name']);
        $this->assertEquals('JANE', $resolved['data'][1]['name']);
    }

    public function testResourceCollectionWithPaginator()
    {
        $items = [
            ['id' => 1, 'name' => 'Product 1', 'email' => 'p1@test.com'],
            ['id' => 2, 'name' => 'Product 2', 'email' => 'p2@test.com'],
        ];

        $paginator = new LengthAwarePaginator($items, 20, 2, 1, ['path' => '/api/products']);
        $collection = UserResource::collection($paginator);

        $resolved = $collection->resolve();

        $this->assertArrayHasKey('data', $resolved);
        $this->assertArrayHasKey('links', $resolved);
        $this->assertArrayHasKey('meta', $resolved);

        $this->assertEquals(1, $resolved['meta']['current_page']);
        $this->assertEquals(20, $resolved['meta']['total']);
        $this->assertEquals(10, $resolved['meta']['last_page']);
        $this->assertCount(2, $resolved['data']);
    }

    public function testJsonSerialization()
    {
        $resource = UserResource::make(['id' => 10, 'name' => 'Bob', 'email' => 'bob@test.com']);
        $json = $resource->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('BOB', $decoded['data']['name']);
    }
}
