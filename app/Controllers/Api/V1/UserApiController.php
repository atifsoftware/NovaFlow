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

    /**
     * List all users - GET /api/v1/users
     * @Summary List all users with pagination (Requires JWT)
     */
    public function index()
    {
        $page = (int) Request::input('page', 1);
        $perPage = min(100, max(1, (int) Request::input('per_page', 20)));
        $search = trim((string) Request::input('search', ''));

        $query = \App\Models\UserModel::query();
        if ($search !== '') {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $total = $query->count();
        $offset = max(0, ($page - 1) * $perPage);
        $users = $query->orderBy('id', 'DESC')
                      ->limit($perPage)
                      ->offset($offset)
                      ->get();

        return $this->success([
            'users' => $users,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int) ceil($total / max(1, $perPage))
            ]
        ], 'Users retrieved successfully');
    }

    /**
     * Get user by ID - GET /api/v1/users/{id}
     * @Summary Fetch single user details by ID (Requires JWT)
     */
    public function show($id)
    {
        $user = \App\Models\UserModel::query()->find($id);
        if (!$user) {
            return $this->notFound('User not found');
        }

        return $this->success([
            'user' => $user
        ], 'User details retrieved successfully');
    }
}
