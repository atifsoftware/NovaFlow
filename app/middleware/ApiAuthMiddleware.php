<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

// ============================================================
//  ApiAuthMiddleware — Bearer Token চেক করে (API routes)
// ============================================================
class ApiAuthMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (!str_starts_with($auth, 'Bearer ')) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Bearer token required.']);
            exit;
        }

        $token = substr($auth, 7);
        $decoded = \NovaFlow\Core\JWT::decode($token);

        if (!$decoded) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired token.']);
            exit;
        }

        // Store user data in request for later use
        Request::setUser($decoded);

        return true;
    }
}
