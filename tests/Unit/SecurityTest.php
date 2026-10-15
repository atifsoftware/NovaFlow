<?php

namespace Tests\Unit;

use Tests\TestCase;
use NovaFlow\Core\Security;

class SecurityTest extends TestCase
{
    public function testPasswordHash(): void
    {
        $password = 'testPassword123';
        $hash = Security::hashPassword($password);

        $this->assertNotEquals($password, $hash);
        $this->assertTrue(Security::verifyPassword($password, $hash));
    }

    public function testPasswordVerificationFails(): void
    {
        $password = 'testPassword123';
        $wrongPassword = 'wrongPassword';
        $hash = Security::hashPassword($password);

        $this->assertFalse(Security::verifyPassword($wrongPassword, $hash));
    }

    public function testSanitize(): void
    {
        $input = '<script>alert("xss")</script>';
        $output = Security::sanitize($input);

        $this->assertStringNotContainsString('<script>', $output);
    }

    public function testSanitizeArray(): void
    {
        $input = [
            'name' => '<b>John</b>',
            'email' => 'john@example.com'
        ];

        $output = Security::sanitize($input);

        $this->assertStringNotContainsString('<b>', $output['name']);
        $this->assertEquals('john@example.com', $output['email']);
    }

    public function testCleanInput(): void
    {
        $input = "  test@email.com  \0";
        $output = Security::cleanInput($input);

        $this->assertEquals('test@email.com', $output);
    }

    public function testRandomStringLength(): void
    {
        $length = 32;
        $result = Security::randomString($length);

        $this->assertEquals($length, strlen($result));
    }

    public function testEncryptionDecryption(): void
    {
        $secretKey = 'customSecretKey';
        $plaintext = 'Confidential Message';
        
        $encrypted = Security::encrypt($plaintext, $secretKey);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);
        
        $decrypted = Security::decrypt($encrypted, $secretKey);
        $this->assertEquals($plaintext, $decrypted);
    }

    public function testDecryptionFailsWithWrongKey(): void
    {
        $plaintext = 'Secret Data';
        $encrypted = Security::encrypt($plaintext, 'key1');
        
        $decrypted = Security::decrypt($encrypted, 'key2');
        $this->assertFalse($decrypted);
    }

    public function testDecryptionFailsWithTamperedData(): void
    {
        $plaintext = 'Secret Data';
        $encrypted = Security::encrypt($plaintext, 'key1');
        
        // Tamper with the ciphertext (change one byte)
        $decoded = base64_decode($encrypted);
        $decoded[strlen($decoded) - 1] = chr(ord($decoded[strlen($decoded) - 1]) ^ 1);
        $tampered = base64_encode($decoded);
        
        $decrypted = Security::decrypt($tampered, 'key1');
        $this->assertFalse($decrypted);
    }

    public function testRequestHeaderParsing(): void
    {
        $_SERVER['HTTP_X_CUSTOM_HEADER'] = 'test-val';
        $val = \NovaFlow\Core\Request::header('X-Custom-Header');
        $this->assertEquals('test-val', $val);
        unset($_SERVER['HTTP_X_CUSTOM_HEADER']);
    }

    public function testUploadHandlerTraversalProtection(): void
    {
        $uploader = new \NovaFlow\Core\UploadHandler(sys_get_temp_dir() . '/test_uploads/');
        // Attempting to delete using path traversal
        $result = $uploader->delete('../../../some_critical_file.txt');
        $this->assertFalse($result);
    }
}