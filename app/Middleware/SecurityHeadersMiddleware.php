<?php

namespace App\Middleware;

use NovaFlow\Core\Middleware;
use NovaFlow\Core\Request;
use NovaFlow\Core\Response;

/**
 * SecurityHeadersMiddleware
 * Injects robust HTTP security response headers into every response
 */
class SecurityHeadersMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, array $args = []): bool
    {
        Request::setSecureHeaders();
        return true;
    }
}
