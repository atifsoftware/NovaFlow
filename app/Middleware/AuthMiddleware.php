<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

// ============================================================
//  AuthMiddleware — Login চেক করে, না থাকলে /login redirect
// ============================================================
class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        if (empty($_SESSION['user_id'])) {
            $response->setStatusCode(302);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        return true;
    }
}
