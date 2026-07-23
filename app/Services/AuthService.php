<?php

namespace App\Services;

use App\Models\UserModel;
use NovaFlow\Core\JWT;
use NovaFlow\Core\Security;
use NovaFlow\Core\Flash;

/**
 * AuthService
 * Complete authentication management
 */
class AuthService
{
    /**
     * Login user with email/password
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        $user = UserModel::authenticate($email, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }

        if ($user->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Account is inactive'
            ];
        }

        $this->setSession($user, $remember);

        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    }

    /**
     * Login user via API (returns JWT token)
     */
    public function loginApi(string $email, string $password): array
    {
        $user = UserModel::authenticate($email, $password);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        if ($user->status !== 'active') {
            return [
                'success' => false,
                'message' => 'Account is inactive'
            ];
        }

        $token = $this->generateToken($user);

        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ];
    }

    /**
     * Social login (Google/Facebook)
     */
    public function socialLogin(string $provider, string $token): array
    {
        // Mock implementation - in real app, verify token with provider API
        $userData = $this->getSocialUserData($provider, $token);
        if (!$userData) {
            return ['success' => false, 'message' => 'Invalid social token'];
        }

        $user = $this->findOrCreateUser($userData);
        $this->loginUser($user);

        return ['success' => true, 'user' => $user];
    }

    private function getSocialUserData(string $provider, string $token): ?array
    {
        // Placeholder for API calls to Google/Facebook
        return ['email' => 'user@example.com', 'name' => 'Social User', 'provider' => $provider];
    }

    private function findOrCreateUser(array $data): array
    {
        $user = DB::fetchOne("SELECT * FROM users WHERE email = ?", [$data['email']]);
        if (!$user) {
            $userId = DB::query("INSERT INTO users (name, email, password, provider) VALUES (?, ?, '', ?)",
                [$data['name'], $data['email'], $data['provider']]);
            $user = ['id' => $userId, 'name' => $data['name'], 'email' => $data['email']];
        }
        return $user;
    }

    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? '';

        if (empty($email) || empty($password) || empty($name)) {
            return [
                'success' => false,
                'message' => 'All fields are required'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        if (UserModel::findByEmail($email)) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }

        $user = new UserModel();
        $user->name = $name;
        $user->email = $email;
        $user->password = Security::hashPassword($password);
        $user->role = $data['role'] ?? 'user';
        $user->status = 'active';
        $user->save();

        return [
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user
        ];
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Get current user
     */
    public function getUser(): ?object
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return UserModel::query()->find($_SESSION['user_id']);
    }

    /**
     * Generate JWT token for user
     */
    public function generateToken(object $user, int $expiry = 86400): string
    {
        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'user',
            'iat' => time()
        ];

        return JWT::encode($payload, $expiry);
    }

    /**
     * Set session data
     */
    protected function setSession(object $user, bool $remember = false): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_role'] = $user->role ?? 'user';
        $_SESSION['login_time'] = time();

        if ($remember) {
            $token = $this->generateToken($user, 2592000); // 30 days
            setcookie('remember_token', $token, time() + 2592000, '/', '', false, true);
        }

        session_regenerate_id(true);
    }

    /**
     * Validate remember token
     */
    public function validateRememberToken(string $token): ?object
    {
        $decoded = JWT::decode($token);
        
        if (!$decoded || !isset($decoded['sub'])) {
            return null;
        }

        return UserModel::query()->find($decoded['sub']);
    }

    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = UserModel::query()->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if (!password_verify($currentPassword, $user->password)) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        $user->password = Security::hashPassword($newPassword);
        $user->save();

        return ['success' => true, 'message' => 'Password changed successfully'];
    }
}