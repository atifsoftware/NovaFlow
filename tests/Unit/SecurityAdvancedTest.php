<?php

namespace Tests\Unit;

use Tests\TestCase;
use NovaFlow\Core\Security;
use NovaFlow\Core\Database\Grammar\MySqlGrammar;
use NovaFlow\Core\Database\Grammar\PostgresGrammar;
use NovaFlow\Core\Database\Grammar\SqliteGrammar;
use NovaFlow\Core\UploadHandler;

class SecurityAdvancedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    /* --------------------------------------------------------------------------
     * 1. CSRF Protection Tests
     * -------------------------------------------------------------------------- */

    public function testCsrfTokenGenerationAndPersistence(): void
    {
        $token1 = Security::generateCSRFToken();
        $this->assertNotEmpty($token1);
        $this->assertEquals(64, strlen($token1), "CSRF token must be 64 hex characters (32 bytes)");
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token1);

        // Calling again within the same session must return the identical token
        $token2 = Security::generateCSRFToken();
        $this->assertEquals($token1, $token2);
    }

    public function testCsrfTokenValidationPassesWithCorrectToken(): void
    {
        $token = Security::generateCSRFToken();
        $this->assertTrue(Security::validateCSRFToken($token));
    }

    public function testCsrfTokenValidationFailsWithInvalidOrMismatchedToken(): void
    {
        Security::generateCSRFToken();

        $wrongToken = str_repeat('a', 64);
        $this->assertFalse(Security::validateCSRFToken($wrongToken));
        $this->assertFalse(Security::validateCSRFToken(''));
        $this->assertFalse(Security::validateCSRFToken('invalid_token'));
    }

    public function testCsrfTokenValidationSafelyHandlesNonStringTypes(): void
    {
        Security::generateCSRFToken();

        // Must not crash or throw TypeError when non-string values are supplied
        $this->assertFalse(Security::validateCSRFToken(null));
        $this->assertFalse(Security::validateCSRFToken(12345));
        $this->assertFalse(Security::validateCSRFToken(['malicious' => 'array']));
        $this->assertFalse(Security::validateCSRFToken((object)['token' => 'val']));
    }

    /* --------------------------------------------------------------------------
     * 2. XSS & Input Sanitization Tests
     * -------------------------------------------------------------------------- */

    public function testSanitizeEscapesDangerousHtmlAndScripts(): void
    {
        $payloads = [
            '<script>alert("XSS")</script>' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
            '<img src=x onerror=alert(1)>' => '&lt;img src=x onerror=alert(1)&gt;',
            '<a href="javascript:steal()">Click</a>' => '&lt;a href=&quot;javascript:steal()&quot;&gt;Click&lt;/a&gt;',
            '"><script>alert(1)</script>' => '&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;',
            "It's a 'test' & \"demo\"" => "It&#039;s a &#039;test&#039; &amp; &quot;demo&quot;",
        ];

        foreach ($payloads as $dangerous => $expected) {
            $this->assertEquals($expected, Security::sanitize($dangerous));
        }
    }

    public function testSanitizeHandlesMultiDimensionalArraysRecursively(): void
    {
        $input = [
            'title' => '<b>Title</b>',
            'meta' => [
                'author' => '<script>hack()</script>',
                'tags' => ['<i>tag1</i>', '<span onclick="evil()">tag2</span>']
            ]
        ];

        $cleaned = Security::sanitize($input);

        $this->assertEquals('&lt;b&gt;Title&lt;/b&gt;', $cleaned['title']);
        $this->assertEquals('&lt;script&gt;hack()&lt;/script&gt;', $cleaned['meta']['author']);
        $this->assertEquals('&lt;i&gt;tag1&lt;/i&gt;', $cleaned['meta']['tags'][0]);
        $this->assertEquals('&lt;span onclick=&quot;evil()&quot;&gt;tag2&lt;/span&gt;', $cleaned['meta']['tags'][1]);
    }

    public function testCleanInputRemovesNullBytesAndControlCharacters(): void
    {
        // Null-byte poison attempt
        $poisonedPath = "document.pdf\x00.exe";
        $cleaned = Security::cleanInput($poisonedPath);
        $this->assertEquals("document.pdf.exe", $cleaned);

        // Control characters: vertical tab (\x0B), form feed (\x0C)
        $dirty = "Hello\x0BWorld\x0C!";
        $this->assertEquals("HelloWorld!", Security::cleanInput($dirty));

        // Leading and trailing whitespaces
        $spaced = "   valid_username   ";
        $this->assertEquals("valid_username", Security::cleanInput($spaced));
    }

    public function testCleanInputRecursivelyProcessesArrays(): void
    {
        $data = [
            'file1' => "avatar.png\x00.php",
            'nested' => [
                'val' => " test\x00 "
            ]
        ];

        $cleaned = Security::cleanInput($data);
        $this->assertEquals('avatar.png.php', $cleaned['file1']);
        $this->assertEquals('test', $cleaned['nested']['val']);
    }

    /* --------------------------------------------------------------------------
     * 3. Authenticated Encryption Tests (AES-256-CBC + HMAC-SHA256)
     * -------------------------------------------------------------------------- */

    public function testEncryptionAndDecryptionSucceedsWithUnicodeAndJson(): void
    {
        $key = 'StrongSecretKeyForUnitTesting_32!';
        
        $samples = [
            'Simple Text',
            'বাংলা ইউনিকোড টেক্সট 🇧🇩 ও ইমোজি 🚀🎉',
            json_encode(['user_id' => 123, 'role' => 'admin', 'scopes' => ['read', 'write']]),
            'Very long text string ' . str_repeat('ABC123xyz!@# ', 50),
        ];

        foreach ($samples as $plaintext) {
            $ciphertext = Security::encrypt($plaintext, $key);
            $this->assertNotEmpty($ciphertext);
            $this->assertNotEquals($plaintext, $ciphertext);

            $decrypted = Security::decrypt($ciphertext, $key);
            $this->assertEquals($plaintext, $decrypted);
        }
    }

    public function testEncryptionProducesDifferentCiphertextsForSamePlaintext(): void
    {
        $key = 'StrongSecretKeyForUnitTesting_32!';
        $plaintext = 'Fixed Secret Message';

        $c1 = Security::encrypt($plaintext, $key);
        $c2 = Security::encrypt($plaintext, $key);

        // Each encryption must use a unique cryptographically random IV
        $this->assertNotEquals($c1, $c2);

        // Both must decrypt to the original plaintext
        $this->assertEquals($plaintext, Security::decrypt($c1, $key));
        $this->assertEquals($plaintext, Security::decrypt($c2, $key));
    }

    public function testDecryptionFailsIfCiphertextIsTampered(): void
    {
        $key = 'StrongSecretKeyForUnitTesting_32!';
        $plaintext = 'Top Secret Financial Record';

        $ciphertext = Security::encrypt($plaintext, $key);
        $raw = base64_decode($ciphertext);

        // Tamper with one byte in ciphertext
        $raw[20] = chr(ord($raw[20]) ^ 0xFF);
        $tamperedCiphertext = base64_encode($raw);

        // HMAC verification MUST fail before decryption
        $result = Security::decrypt($tamperedCiphertext, $key);
        $this->assertFalse($result, "Tampered ciphertext must be rejected by HMAC verification");
    }

    public function testDecryptionFailsIfMacIsTampered(): void
    {
        $key = 'StrongSecretKeyForUnitTesting_32!';
        $plaintext = 'Confidential payload';

        $ciphertext = Security::encrypt($plaintext, $key);
        $raw = base64_decode($ciphertext);

        // The first 32 bytes are HMAC-SHA256 signature
        $raw[0] = chr(ord($raw[0]) ^ 0x01);
        $tamperedMac = base64_encode($raw);

        $this->assertFalse(Security::decrypt($tamperedMac, $key));
    }

    public function testDecryptionFailsWithTruncatedOrShortData(): void
    {
        $key = 'StrongSecretKeyForUnitTesting_32!';

        // Under 48 bytes (32 bytes HMAC + 16 bytes IV)
        $short = base64_encode('short_payload');
        $this->assertFalse(Security::decrypt($short, $key));
        $this->assertFalse(Security::decrypt('', $key));
    }

    public function testDecryptionFailsWithWrongKey(): void
    {
        $plaintext = 'Secret Document';
        $encrypted = Security::encrypt($plaintext, 'CorrectKey123456');

        $this->assertFalse(Security::decrypt($encrypted, 'WrongKey789012'));
    }

    /* --------------------------------------------------------------------------
     * 4. Password Hashing Tests
     * -------------------------------------------------------------------------- */

    public function testPasswordHashingAndVerification(): void
    {
        $password = 'MyP@ssw0rd!#2026';
        $hash = Security::hashPassword($password);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);

        // Verify correct password
        $this->assertTrue(Security::verifyPassword($password, $hash));

        // Verify wrong passwords
        $this->assertFalse(Security::verifyPassword('wrongPassword', $hash));
        $this->assertFalse(Security::verifyPassword($password . 'x', $hash));
        $this->assertFalse(Security::verifyPassword('', $hash));
    }

    public function testPasswordHashingUsesUniqueSalts(): void
    {
        $password = 'SamePasswordEveryTime';

        $h1 = Security::hashPassword($password);
        $h2 = Security::hashPassword($password);

        // Hashes must be unique due to random salt
        $this->assertNotEquals($h1, $h2);

        // Both verify correctly
        $this->assertTrue(Security::verifyPassword($password, $h1));
        $this->assertTrue(Security::verifyPassword($password, $h2));
    }

    /* --------------------------------------------------------------------------
     * 5. Path Traversal & Identifier Escaping Tests
     * -------------------------------------------------------------------------- */

    public function testUploadHandlerDirectoryTraversalPrevention(): void
    {
        $tempDir = sys_get_temp_dir() . '/novaflow_test_uploads_' . uniqid();
        @mkdir($tempDir, 0777, true);

        $uploader = new UploadHandler($tempDir);

        // Directory traversal attack vectors
        $this->assertFalse($uploader->delete('../../etc/passwd'));
        $this->assertFalse($uploader->delete('..\\..\\windows\\system32'));
        $this->assertFalse($uploader->delete('../some_file.txt'));

        @rmdir($tempDir);
    }

    public function testGrammarEscapesSqlIdentifiersSafely(): void
    {
        $mysql = new MySqlGrammar();
        $sqlite = new SqliteGrammar();
        $postgres = new PostgresGrammar();

        // MySQL backtick escaping
        $this->assertEquals('`users`', $mysql->wrap('users'));
        $this->assertEquals('`users`.`email`', $mysql->wrap('users.email'));
        $this->assertEquals('`injected``name`', $mysql->wrap('injected`name'));

        // SQLite double quote escaping
        $this->assertEquals('"users"', $sqlite->wrap('users'));
        $this->assertEquals('"users"."email"', $sqlite->wrap('users.email'));
        $this->assertEquals('"injected""name"', $sqlite->wrap('injected"name'));

        // Postgres double quote escaping
        $this->assertEquals('"users"', $postgres->wrap('users'));
        $this->assertEquals('"users"."email"', $postgres->wrap('users.email'));
        $this->assertEquals('"injected""name"', $postgres->wrap('injected"name'));
    }
}
