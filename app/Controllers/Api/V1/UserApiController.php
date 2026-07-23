<?php

namespace App\Controllers\Api\V1;

use NovaFlow\Core\ApiController;
use NovaFlow\Core\Request;

/**
 * UserApiController
 * Demonstrates protected API routes
 */
class UserApiController extends ApiController
{
    /**
     * Get Profile - GET /api/v1/profile
     * @Summary Fetch Authenticated User Profile (Requires JWT)
     * Protected by JwtAuthMiddleware
     */
    public function profile()
    {
        // The middleware stores the decoded user in the request
        $request = new Request();
        $user = $request->getUser();

        if (!$user) {
            return $this->unauthorized();
        }

        return $this->success([
            'profile' => $user,
            'server_time' => date('Y-m-d H:i:s'),
            'permissions' => ['read', 'write'] // Mock permissions
        ], 'Profile retrieved successfully');
    }
}
