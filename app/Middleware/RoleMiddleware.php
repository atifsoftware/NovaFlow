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
            if (Request::wantsJson()) {
                if (ob_get_length()) ob_clean();
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Unauthenticated'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $allowedRoles = $args;
        $userId = (int) $_SESSION['user_id'];

        if (!empty($allowedRoles) && !\NovaFlow\Core\RBAC::hasRole($userId, $allowedRoles)) {
            if (Request::wantsJson()) {
                if (ob_get_length()) ob_clean();
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Forbidden: Unauthorized role.'], JSON_UNESCAPED_UNICODE);
                exit;
            }

            \NovaFlow\Core\Flash::error('আপনার এই পেজে প্রবেশের অনুমতি নেই।');
            header('Location: ' . Request::safeReferer(BASE_URL . '/'));
            exit;
        }

        return true;
    }
}
