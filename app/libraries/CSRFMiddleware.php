<?php

namespace NovaFlow\Core;

/**
 * CSRF Protection Middleware
 */
class CSRFMiddleware implements Middleware
{
    /**
     * Handle the request
     */
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        // 1. Ensure token exists in session
        Security::generateCSRFToken();

        // 2. Validate only for non-read methods
        $method = $request->method();
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $_POST['_csrf_token'] 
                ?? $_POST['_token'] 
                ?? Request::json('_token') 
                ?? Request::json('_csrf_token') 
                ?? $request->header('X-CSRF-TOKEN') 
                ?? $request->header('X-XSRF-TOKEN') 
                ?? '';
            
            if (!Security::validateCSRFToken($token)) {
                if (Request::isAjax() || Request::wantsJson()) {
                    $response->setStatusCode(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'CSRF token mismatch.'], JSON_UNESCAPED_UNICODE);
                    return false;
                }
                
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['error'] = 'নিরাপত্তা ত্রুটি (CSRF Mismatch)। অনুগ্রহ করে পেইজটি রিলোড দিয়ে আবার চেষ্টা করুন।';
                $_SESSION['error_class'] = 'alert alert-danger';
                
                $fallback = defined('BASE_URL') ? BASE_URL . '/login' : '/login';
                $referer = Request::safeReferer($fallback);
                header('Location: ' . $referer);
                exit;
            }
        }

        return true;
    }
}
