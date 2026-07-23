<?php

namespace App\Models;

use NovaFlow\Core\Model;

/**
 * UserModel
 * Handles admin and user authentication
 */
class UserModel extends Model
{
    protected string $table = 'users';
    protected array $hidden = ['password', 'created_at']; // Never expose password in JSON

    /**
     * Authenticate user
     */
    public static function authenticate(string $email, string $password): ?object
    {
        $user = self::query()->where('email', $email)->first();
        
        if ($user && password_verify($password, $user->password)) {
            return $user;
        }
        
        return null;
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?object
    {
        return self::query()->where('email', $email)->first();
    }
}
