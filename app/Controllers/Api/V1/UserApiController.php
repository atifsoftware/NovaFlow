<?php

namespace App\Controllers\Api\V1;

use NovaFlow\Core\ApiController;
use NovaFlow\Core\Request;
use NovaFlow\Core\ApiResourceResponse;
use App\Transformers\UserResource;
use App\Models\UserModel;

/**
 * UserApiController
 * Demonstrates protected API routes with Resource Transformers
 */
class UserApiController extends ApiController
{
    /**
     * Get Profile - GET /api/v1/profile
     * @Summary Fetch Authenticated User Profile (Requires JWT)
     * Protected by JwtAuthMiddleware
     * 
     * Response Example:
     * {
     *   "status": "success",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "role": "user",
     *     "status": 1,
     *     "created_at": "2024-01-01 00:00:00",
     *     "updated_at": "2024-01-01 00:00:00",
     *     "meta": {
     *       "transformed_at": "2024-01-01 12:00:00",
     *       "resource_type": "App\\Transformers\\UserResource"
     *     }
     *   }
     * }
     */
    public function profile()
    {
        // The middleware stores the decoded user in the request
        $request = new Request();
        $user = $request->getUser();

        if (!$user) {
            return $this->unauthorized();
        }

        // Use Resource Transformer for standardized response
        return $this->json(
            ApiResourceResponse::resource(new UserResource($user)),
            200
        );
    }

    /**
     * Get All Users - GET /api/v1/users
     * @Summary Fetch all users (Admin only)
     * 
     * Response Example:
     * {
     *   "status": "success",
     *   "data": [...],
     *   "meta": {
     *     "total": 10,
     *     "count": 10,
     *     "transformed_at": "2024-01-01 12:00:00",
     *     "resource_type": "App\\Transformers\\UserResource"
     *   }
     * }
     */
    public function index()
    {
        $users = UserModel::all();

        return $this->json(
            ApiResourceResponse::collection(new UserResource(null), null, $users),
            200
        );
    }

    /**
     * Get Single User - GET /api/v1/users/{id}
     * @Summary Fetch single user by ID
     */
    public function show($id)
    {
        $user = UserModel::find($id);

        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->json(
            ApiResourceResponse::resource(new UserResource($user)),
            200
        );
    }
}
