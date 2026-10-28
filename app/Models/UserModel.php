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
        $user = static::where('email', $email)->first();
        
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
        return static::where('email', $email)->first();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        return \NovaFlow\Core\RBAC::hasPermission($this, $permission);
    }

    /**
     * Alias for hasPermission
     */
    public function can(string $permission): bool
    {
        return $this->hasPermission($permission);
    }

    /**
     * Check if user has one or more roles
     */
    public function hasRole(string|array $roles): bool
    {
        return \NovaFlow\Core\RBAC::hasRole($this, $roles);
    }

    /**
     * Assign a role to this user
     */
    public function assignRole(string|int $role): bool
    {
        return \NovaFlow\Core\RBAC::assignRole($this, $role);
    }

    /**
     * Remove a role from this user
     */
    public function removeRole(string|int $role): bool
    {
        return \NovaFlow\Core\RBAC::removeRole($this, $role);
    }

    /**
     * Get all assigned roles for this user
     */
    public function getRoles(): array
    {
        return \NovaFlow\Core\RBAC::getUserRoles($this);
    }

    /**
     * Get all active permissions for this user
     */
    public function getPermissions(): array
    {
        return \NovaFlow\Core\RBAC::getUserPermissions($this);
    }
}
