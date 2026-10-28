<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;
use NovaFlow\Core\Flash;
use App\Models\UserModel;

/**
 * PermissionMiddleware
 * Enforces dynamic permissions on routes (e.g. middleware: 'permission:sales.view,sales.manage')
 */
class PermissionMiddleware implements Middleware
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

        $userId = (int) $_SESSION['user_id'];
        $requiredPermissions = array_filter(array_map('trim', $args));

        if (!empty($requiredPermissions)) {
            $user = (new UserModel())->find($userId);
            $hasAny = false;

            foreach ($requiredPermissions as $perm) {
                if ($user && $user->hasPermission($perm)) {
                    $hasAny = true;
                    break;
                }
            }

            if (!$hasAny) {
                if (Request::wantsJson()) {
                    if (ob_get_length()) ob_clean();
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Forbidden: You do not have permission to access this resource.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                Flash::error('আপনার এই পেজে প্রবেশের অনুমতি নেই।');
                header('Location: ' . BASE_URL . '/admin/dashboard');
                exit;
            }
        }

        return true;
    }
}
