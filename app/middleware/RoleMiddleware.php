<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

// ============================================================
//  RoleMiddleware — Specific role চেক করে (e.g. role:admin)
// ============================================================
class RoleMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $allowedRoles = $args;
        $userRole = $_SESSION['role'] ?? 'customer';

        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
            header('Location: ' . BASE_URL . '/');
            exit;
        }

        return true;
    }
}
