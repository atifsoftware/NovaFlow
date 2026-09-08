<?php

namespace Tests\Unit;

use Tests\TestCase;
use NovaFlow\Core\Validator;

class ValidatorTest extends TestCase
{
    public function test_required_rule()
    {
        $data = ['name' => ''];
        $rules = ['name' => 'required'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());
        $this->assertTrue($validator->hasErrors('name'));

        $data = ['name' => 'John'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_email_rule()
    {
        $data = ['email' => 'invalid-email'];
        $rules = ['email' => 'email'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['email' => 'test@example.com'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_min_rule()
    {
        // String
        $data = ['password' => '123'];
        $rules = ['password' => 'min:6'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['password' => '123456'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());

        // Numeric
        $data = ['age' => '17'];
        $rules = ['age' => 'numeric|min:18'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['age' => '18'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_max_rule()
    {
        // String
        $data = ['username' => 'verylongusername'];
        $rules = ['username' => 'max:10'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['username' => 'short'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());

        // Numeric
        $data = ['age' => '61'];
        $rules = ['age' => 'numeric|max:60'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['age' => '60'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_numeric_rule()
    {
        $data = ['amount' => 'abc'];
        $rules = ['amount' => 'numeric'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['amount' => '123.45'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_integer_rule()
    {
        $data = ['count' => '12.5'];
        $rules = ['count' => 'integer'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['count' => '12'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_string_rule()
    {
        $data = ['name' => 123];
        $rules = ['name' => 'string'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['name' => 'John'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_date_rule()
    {
        $data = ['date' => 'invalid-date'];
        $rules = ['date' => 'date'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['date' => '2023-10-25'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_url_rule()
    {
        $data = ['website' => 'invalid-url'];
        $rules = ['website' => 'url'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['website' => 'https://example.com'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_in_rule()
    {
        $data = ['status' => 'pending'];
        $rules = ['status' => 'in:active,inactive'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['status' => 'active'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_confirmed_rule()
    {
        $data = ['password' => 'secret', 'password_confirmation' => 'different'];
        $rules = ['password' => 'confirmed'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['password' => 'secret', 'password_confirmation' => 'secret'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_between_rule()
    {
        // String length
        $data = ['code' => 'ab'];
        $rules = ['code' => 'between:3,5'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['code' => 'abcde'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());

        // Numeric value
        $data = ['age' => 15];
        $rules = ['age' => 'numeric|between:18,65'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['age' => 30];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_required_if_rule()
    {
        $data = ['has_car' => 'yes', 'car_model' => ''];
        $rules = ['car_model' => 'required_if:has_car,yes'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['has_car' => 'yes', 'car_model' => 'Toyota'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());

        $data = ['has_car' => 'no', 'car_model' => ''];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }

    public function test_different_rule()
    {
        $data = ['password' => 'secret', 'old_password' => 'secret'];
        $rules = ['password' => 'different:old_password'];
        $validator = Validator::make($data, $rules);
        $this->assertFalse($validator->validate());

        $data = ['password' => 'new_secret', 'old_password' => 'secret'];
        $validator = Validator::make($data, $rules);
        $this->assertTrue($validator->validate());
    }
}
