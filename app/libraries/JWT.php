<?php

namespace NovaFlow\Core;

/**
 * JWT - A simple library for JSON Web Tokens
 * Implementation of HMAC SHA256
 */
class JWT
{
    protected static $secret;

    /**
     * Get secret from environment or config
     */
    protected static function getSecret()
    {
        if (self::$secret === null) {
            $secret = env('JWT_SECRET') ?: env('APP_KEY');
            if (empty($secret)) {
                if (defined('APP_ENV') && APP_ENV === 'production') {
                    throw new \RuntimeException("JWT_SECRET or APP_KEY must be configured in production environment.");
                }
                // Auto-generate a secure random 256-bit runtime key rather than using a static guessable string
                if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                    @session_start();
                }
                if (isset($_SESSION['_novaflow_runtime_jwt_key'])) {
                    $secret = $_SESSION['_novaflow_runtime_jwt_key'];
                } else {
                    $secret = bin2hex(random_bytes(32));
                    if (isset($_SESSION)) {
                        $_SESSION['_novaflow_runtime_jwt_key'] = $secret;
                    }
                }
            }
            self::$secret = $secret;
        }
        return self::$secret;
    }

    /**
     * Encode payload into JWT
     */
    public static function encode($payload, $expiry = 86400)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $payload['exp'] = time() + $expiry;
        $payload = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decode and validate JWT
     */
    public static function decode($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        // Verify signature
        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::getSecret(), true);
        if (!hash_equals(self::base64UrlEncode($validSignature), $signature)) {
            return false;
        }

        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);

        // Check expiry
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return false;
        }

        return $decodedPayload;
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
