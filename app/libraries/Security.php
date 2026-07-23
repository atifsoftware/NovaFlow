<?php
declare(strict_types=1);

namespace NovaFlow\Core;

/**
 * Security Helper Class
 * Handles CSRF protection, input sanitization, and password hashing
 */
class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // If no session token is set or submitted token is not a string, fail early.
        // This prevents TypeError in hash_equals() with PHP 8.1+
        if (!isset($_SESSION['csrf_token']) || !is_string($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token for forms
     */
    public static function getCSRFToken()
    {
        return self::generateCSRFToken();
    }

    /**
     * Sanitize input data (HTML Escape)
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }

        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
     * Clean input data (strip control characters ONLY)
     */
    public static function cleanInput(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([self::class, 'cleanInput'], $data);
        }

        if (is_string($data)) {
            // Remove null bytes and other common control chars
            $data = preg_replace('/\x00|\x0B|\x0C/', '', $data);
            // WE REMOVED strip_tags() here to prevent data loss. 
            // We MUST escape variables in the View using htmlspecialchars().
            return trim($data);
        }

        return $data;
    }

    /**
     * Generate secure random string
     */
    public static function randomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash password securely using Argon2ID
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Encrypt data using Encrypt-then-MAC (AES-256-CBC + HMAC-SHA256)
     */
    public static function encrypt($data, $key = null)
    {
        // 1. Resolve key (Derive cryptographically strong keys using SHA-256)
        $key = $key ?? env('APP_KEY') ?? env('JWT_SECRET') ?? 'default_key_change_this_32_bytes_long_secret_key_!!!';
        
        $derivedKey = hash_hmac('sha256', 'encryption', $key, true);
        $macKey = hash_hmac('sha256', 'authentication', $key, true);
        
        // 2. Encrypt
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($data, 'AES-256-CBC', $derivedKey, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            return false;
        }
        
        // 3. Generate MAC
        $mac = hash_hmac('sha256', $iv . $ciphertext, $macKey, true);
        
        return base64_encode($iv . $mac . $ciphertext);
    }

    /**
     * Decrypt data and verify MAC signature
     */
    public static function decrypt($data, $key = null)
    {
        $key = $key ?? env('APP_KEY') ?? env('JWT_SECRET') ?? 'default_key_change_this_32_bytes_long_secret_key_!!!';
        
        $data = base64_decode($data);
        if (strlen($data) < 48) { // 16 bytes IV + 32 bytes MAC
            return false;
        }
        
        $iv = substr($data, 0, 16);
        $mac = substr($data, 16, 32);
        $ciphertext = substr($data, 48);
        
        $derivedKey = hash_hmac('sha256', 'encryption', $key, true);
        $macKey = hash_hmac('sha256', 'authentication', $key, true);
        
        // 4. Verify MAC (Timing-safe comparison)
        $calculatedMac = hash_hmac('sha256', $iv . $ciphertext, $macKey, true);
        if (!hash_equals($mac, $calculatedMac)) {
            return false;
        }
        
        return openssl_decrypt($ciphertext, 'AES-256-CBC', $derivedKey, OPENSSL_RAW_DATA, $iv);
    }
}
