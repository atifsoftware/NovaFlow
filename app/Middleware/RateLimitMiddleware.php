<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

/**
 * RateLimitMiddleware
 * Prevents brute force attacks by limiting requests per IP
 */
class RateLimitMiddleware implements Middleware
{
    protected array $limits = [];
    protected string $storagePath;

    public function __construct()
    {
        $this->storagePath = dirname(__DIR__, 2) . '/storage/rate_limits/';
        
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }

        $this->limits = [
            'default' => ['max' => 60, 'window' => 60],        // 60 requests per minute
            'login' => ['max' => 5, 'window' => 300],          // 5 attempts per 5 minutes
            'api' => ['max' => 100, 'window' => 60],          // 100 API requests per minute
            'upload' => ['max' => 10, 'window' => 3600]       // 10 uploads per hour
        ];
    }

    public function handle(Request $request, Response $response, array $args = []): bool
    {
        $key = $args[0] ?? 'default';
        $limit = $this->limits[$key] ?? $this->limits['default'];
        
        $identifier = $this->getIdentifier($request);
        $filePath = $this->storagePath . md5($identifier . '_' . $key) . '.json';
        
        $now = time();
        $windowStart = $now - $limit['window'];
        
        $attempts = $this->getAttempts($filePath, $windowStart);
        
        if ($attempts >= $limit['max']) {
            $this->rateLimitExceeded($request, $response, $key, $limit['window']);
            return false;
        }
        
        $this->recordAttempt($filePath, $now);
        
        $response->setHeader('X-RateLimit-Limit', $limit['max']);
        $response->setHeader('X-RateLimit-Remaining', $limit['max'] - $attempts - 1);
        $response->setHeader('X-RateLimit-Reset', $windowStart + $limit['window']);
        
        return true;
    }

    protected function getIdentifier(Request $request): string
    {
        $ip = $request::ip();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'default';
        
        return $ip . '_' . md5($userAgent);
    }

    protected function getAttempts(string $filePath, int $windowStart): int
    {
        if (!file_exists($filePath)) {
            return 0;
        }

        $data = json_decode(file_get_contents($filePath), true);
        
        if (!$data || !isset($data['attempts'])) {
            return 0;
        }

        $recentAttempts = array_filter($data['attempts'], function ($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });

        return count($recentAttempts);
    }

    protected function recordAttempt(string $filePath, int $timestamp): void
    {
        $data = [
            'attempts' => [],
            'first_attempt' => $timestamp
        ];

        if (file_exists($filePath)) {
            $existing = json_decode(file_get_contents($filePath), true);
            if ($existing) {
                $data = $existing;
            }
        }

        $data['attempts'][] = $timestamp;
        
        $data['attempts'] = array_filter($data['attempts'], function ($ts) use ($timestamp) {
            return $ts > $timestamp - 3600;
        });

        file_put_contents($filePath, json_encode($data));
    }

    protected function rateLimitExceeded(Request $request, Response $response, string $key, int $window): void
    {
        http_response_code(429);
        header('Content-Type: application/json');
        
        echo json_encode([
            'status' => 'error',
            'message' => "Too many attempts. Please try again after {$window} seconds.",
            'code' => 'RATE_LIMIT_EXCEEDED'
        ], JSON_UNESCAPED_UNICODE);
        
        exit;
    }

    /**
     * Add custom rate limit rule
     */
    public function addLimitRule(string $name, int $maxAttempts, int $windowSeconds): self
    {
        $this->limits[$name] = [
            'max' => $maxAttempts,
            'window' => $windowSeconds
        ];
        
        return $this;
    }
}