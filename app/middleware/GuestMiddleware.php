<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

// ============================================================
//  GuestMiddleware — Logged in থাকলে home redirect
// ============================================================
class GuestMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        if (!empty($_SESSION['user_id'])) {
            $response->setStatusCode(302);
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        return true;
    }
}
