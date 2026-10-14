<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Cache;

/**
 * ThrottleRequestsMiddleware
 * Rate limiter to protect endpoints from brute-force and DDoS attacks
 */
class ThrottleRequestsMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        // Default: 60 requests per 1 minute
        $maxAttempts = isset($args[0]) ? (int) $args[0] : 60;
        $decayMinutes = isset($args[1]) ? (int) $args[1] : 1;
        $decaySeconds = max(1, $decayMinutes * 60);

        $clientIdentifier = $request->ip();
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($authHeader) {
            $clientIdentifier .= '|' . substr($authHeader, -10);
        }

        $key = 'rate_limit:' . sha1($clientIdentifier . '|' . $request->getPath());
        $now = time();

        $data = Cache::get($key);

        if (!$data || !is_array($data) || ($now - $data['start_time']) >= $decaySeconds) {
            // Fresh time window
            $data = [
                'attempts' => 1,
                'start_time' => $now,
            ];
            Cache::set($key, $data, $decaySeconds);
        } else {
            $data['attempts']++;
            Cache::set($key, $data, $decaySeconds - ($now - $data['start_time']));
        }

        $remaining = max(0, $maxAttempts - $data['attempts']);
        $retryAfter = max(1, $decaySeconds - ($now - $data['start_time']));

        header("X-RateLimit-Limit: $maxAttempts");
        header("X-RateLimit-Remaining: $remaining");

        if ($data['attempts'] > $maxAttempts) {
            header("Retry-After: $retryAfter");
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status'  => 'error',
                'message' => 'Too Many Requests. Please slow down and try again later.',
                'retry_after' => $retryAfter . ' seconds'
            ], JSON_UNESCAPED_UNICODE);

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            return false;
        }

        return true;
    }
}
