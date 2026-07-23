<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

// ============================================================
//  AdminMiddleware — Admin role চেক করে
// ============================================================
class AdminMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        if (!in_array($_SESSION['role'] ?? '', ['admin', 'manager'])) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        return true;
    }
}
