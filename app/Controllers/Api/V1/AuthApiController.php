<?php

namespace App\Controllers\Api\V1;

use NovaFlow\Core\ApiController;
use App\Models\UserModel;
use NovaFlow\Core\JWT;
use NovaFlow\Core\Request;

/**
 * AuthApiController
 * Handles API authentication and Token generation
 */
class AuthApiController extends ApiController
{
    /**
     * Login - POST /api/v1/login
     * @Summary User Authentication & Token Generation
     * @Param email (string, User registered email, required)
     * @Param password (string, User password, required)
     */
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->error('Email and Password are required', 422);
        }

        $user = UserModel::authenticate($email, $password);

        if (!$user) {
            return $this->unauthorized('Invalid email or password');
        }

        // Generate JWT
        $payload = [
            'id'    => $user->id,
            'email' => $user->email,
            'role'  => $user->role ?? 'user'
        ];

        $token = JWT::encode($payload);

        return $this->success([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'email' => $user->email,
                'name'  => $user->full_name ?? $user->username
            ]
        ], 'Login successful');
    }
}
