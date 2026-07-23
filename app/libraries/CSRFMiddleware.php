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
            $token = $_POST['_csrf_token'] ?? $request->header('X-CSRF-TOKEN') ?? '';
            
            if (!Security::validateCSRFToken($token)) {
                if (Request::isAjax()) {
                    $response->setStatusCode(403);
                    echo json_encode(['error' => 'CSRF token mismatch.']);
                    return false;
                }
                
                $_SESSION['error'] = 'নিরাপত্তা ত্রুটি (CSRF Mismatch)। অনুগ্রহ করে পেইজটি রিলোড দিয়ে আবার চেষ্টা করুন।';
                $_SESSION['error_class'] = 'alert alert-danger';
                
                $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/login';
                header('Location: ' . $referer);
                exit;
            }
        }

        return true;
    }
}
