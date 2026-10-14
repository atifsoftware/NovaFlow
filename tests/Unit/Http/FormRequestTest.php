<?php

namespace Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\Http\FormRequest;
use NovaFlow\Core\Request;
use NovaFlow\Core\Validator;
use NovaFlow\Core\Exceptions\ValidationException;
use Exception;

// Test FormRequest implementations
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|min:3',
            'email' => 'required|email',
            'is_active' => 'boolean',
            'tags'  => 'array',
        ];
    }
}

class UnauthorizedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return ['title' => 'required'];
    }
}

class FormRequestTest extends TestCase
{
    public function testFormRequestPassesValidation()
    {
        // Mock request input
        $_POST = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => 'true',
            'tags' => ['admin', 'manager'],
            'extra_field' => 'should_be_ignored'
        ];

        $request = new StoreUserRequest(new Request());
        $validated = $request->validate();

        $this->assertEquals('John Doe', $validated['name']);
        $this->assertEquals('john@example.com', $validated['email']);
        $this->assertArrayNotHasKey('extra_field', $validated);
        $this->assertFalse($request->fails());
    }

    public function testFormRequestFailsValidation()
    {
        $_POST = [
            'name' => 'Jo', // too short (< 3)
            'email' => 'not-an-email',
            'is_active' => 'not-boolean',
        ];

        $request = new StoreUserRequest(new Request());

        $this->expectException(ValidationException::class);
        $request->validate();
    }

    public function testFormRequestAuthorizationFailure()
    {
        $request = new UnauthorizedRequest(new Request());

        $this->expectException(Exception::class);
        $this->expectExceptionCode(403);
        $request->validate();
    }

    public function testAdvancedValidationRules()
    {
        // 1. JSON rule
        $validJson = Validator::make(['payload' => '{"key": "value"}'], ['payload' => 'json']);
        $invalidJson = Validator::make(['payload' => '{invalid json}'], ['payload' => 'json']);
        $this->assertTrue($validJson->passesValidation());
        $this->assertTrue($invalidJson->fails());

        // 2. UUID rule
        $validUuid = Validator::make(['id' => '123e4567-e89b-12d3-a456-426614174000'], ['id' => 'uuid']);
        $invalidUuid = Validator::make(['id' => 'not-a-valid-uuid'], ['id' => 'uuid']);
        $this->assertTrue($validUuid->passesValidation());
        $this->assertTrue($invalidUuid->fails());

        // 3. IP rule
        $validIp = Validator::make(['ip' => '192.168.1.1'], ['ip' => 'ip']);
        $invalidIp = Validator::make(['ip' => '999.999.999.999'], ['ip' => 'ip']);
        $this->assertTrue($validIp->passesValidation());
        $this->assertTrue($invalidIp->fails());
    }
}
